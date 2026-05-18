<?php
namespace app\controllers;

use app\core\Controller;
use app\core\Security;
use app\models\Transaction;
use app\models\Category;

class TransactionController extends Controller {
    private $transactionModel;
    private $categoryModel;
    
    public function __construct($db) {
        parent::__construct($db);
        $this->transactionModel = new Transaction($db);
        $this->categoryModel = new Category($db);
    }
    
    public function index() {
        $this->requireAuth();
        
        $start_date = $_GET['start_date'] ?? date('Y-m-01');
        $end_date = $_GET['end_date'] ?? date('Y-m-t');
        $category_id = isset($_GET['category_id']) ? (int)$_GET['category_id'] : null;
        
        $transactions = $this->transactionModel->findAllByUser(
            $_SESSION['user_id'],
            $start_date,
            $end_date,
            $category_id
        );
        
        $categories = $this->categoryModel->findAll();
        
        $this->render('transactions/index', [
            'transactions' => $transactions,
            'categories' => $categories,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'selected_category' => $category_id
        ]);
    }
    
    public function create() {
        $this->requireAuth();
        
        $categories = $this->categoryModel->findAll();
        $csrf_token = Security::generateCsrfToken();
        
        $this->render('transactions/create', [
            'categories' => $categories,
            'csrf_token' => $csrf_token
        ]);
    }
    
    public function store() {
        $this->requireAuth();
        
        // Проверка CSRF токена
        if (!Security::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            die('Ошибка безопасности: неверный CSRF токен');
        }
        
        // Валидация суммы
        $amount = filter_var($_POST['amount'] ?? 0, FILTER_VALIDATE_FLOAT);
        if (!$amount || $amount <= 0) {
            $error = "Некорректная сумма. Введите положительное число.";
            $categories = $this->categoryModel->findAll();
            $csrf_token = Security::generateCsrfToken();
            $this->render('transactions/create', [
                'categories' => $categories,
                'csrf_token' => $csrf_token,
                'error' => $error
            ]);
            return;
        }
        
        // Валидация даты
        $date = $_POST['date'] ?? date('Y-m-d');
        if (!Security::validateDate($date)) {
            $error = "Некорректный формат даты";
            $categories = $this->categoryModel->findAll();
            $csrf_token = Security::generateCsrfToken();
            $this->render('transactions/create', [
                'categories' => $categories,
                'csrf_token' => $csrf_token,
                'error' => $error
            ]);
            return;
        }
        
        // Валидация категории
        $category_id = filter_var($_POST['category_id'] ?? 0, FILTER_VALIDATE_INT);
        if (!$category_id) {
            $error = "Выберите категорию";
            $categories = $this->categoryModel->findAll();
            $csrf_token = Security::generateCsrfToken();
            $this->render('transactions/create', [
                'categories' => $categories,
                'csrf_token' => $csrf_token,
                'error' => $error
            ]);
            return;
        }
        
        // Очистка текстовых полей (XSS защита)
        $description = Security::sanitize($_POST['description'] ?? '');
        
        // Создание транзакции
        $this->transactionModel->user_id = $_SESSION['user_id'];
        $this->transactionModel->category_id = $category_id;
        $this->transactionModel->amount = $amount;
        $this->transactionModel->date = $date;
        $this->transactionModel->description = $description;
        $this->transactionModel->is_recurring = isset($_POST['is_recurring']) ? 1 : 0;
        
        if ($this->transactionModel->save()) {
            $this->redirect('/transactions?success=created');
        } else {
            $error = "Ошибка сохранения транзакции";
            $categories = $this->categoryModel->findAll();
            $csrf_token = Security::generateCsrfToken();
            $this->render('transactions/create', [
                'categories' => $categories,
                'csrf_token' => $csrf_token,
                'error' => $error
            ]);
        }
    }
    
    public function edit($id) {
        $this->requireAuth();
        
        $id = Security::validateId($id) ? (int)$id : 0;
        
        if (!$id || !$this->transactionModel->findById($id) || 
            $this->transactionModel->user_id != $_SESSION['user_id']) {
            $this->redirect('/transactions?error=not_found');
        }
        
        $categories = $this->categoryModel->findAll();
        $csrf_token = Security::generateCsrfToken();
        
        $this->render('transactions/edit', [
            'transaction' => $this->transactionModel,
            'categories' => $categories,
            'csrf_token' => $csrf_token
        ]);
    }
    
    public function update($id) {
        $this->requireAuth();
        
        $id = Security::validateId($id) ? (int)$id : 0;
        
        if (!$id || !$this->transactionModel->findById($id) || 
            $this->transactionModel->user_id != $_SESSION['user_id']) {
            $this->redirect('/transactions?error=not_found');
        }
        
        // Проверка CSRF
        if (!Security::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            die('Ошибка безопасности');
        }
        
        // Валидация суммы
        $amount = filter_var($_POST['amount'] ?? 0, FILTER_VALIDATE_FLOAT);
        if (!$amount || $amount <= 0) {
            $this->redirect('/transactions/edit/' . $id . '?error=invalid_amount');
            return;
        }
        
        // Валидация даты
        $date = $_POST['date'] ?? date('Y-m-d');
        if (!Security::validateDate($date)) {
            $this->redirect('/transactions/edit/' . $id . '?error=invalid_date');
            return;
        }
        
        // Валидация категории
        $category_id = filter_var($_POST['category_id'] ?? 0, FILTER_VALIDATE_INT);
        if (!$category_id) {
            $this->redirect('/transactions/edit/' . $id . '?error=invalid_category');
            return;
        }
        
        $this->transactionModel->category_id = $category_id;
        $this->transactionModel->amount = $amount;
        $this->transactionModel->date = $date;
        $this->transactionModel->description = Security::sanitize($_POST['description'] ?? '');
        $this->transactionModel->is_recurring = isset($_POST['is_recurring']) ? 1 : 0;
        
        if ($this->transactionModel->save()) {
            $this->redirect('/transactions?success=updated');
        } else {
            $this->redirect('/transactions/edit/' . $id . '?error=save_failed');
        }
    }
    
    public function delete($id) {
        $this->requireAuth();
        
        $id = Security::validateId($id) ? (int)$id : 0;
        
        if (!$id) {
            $this->redirect('/transactions?error=invalid_id');
        }
        
        if ($this->transactionModel->findById($id) && 
            $this->transactionModel->user_id == $_SESSION['user_id']) {
            $this->transactionModel->delete();
            $this->redirect('/transactions?success=deleted');
        } else {
            $this->redirect('/transactions?error=not_found');
        }
    }
}