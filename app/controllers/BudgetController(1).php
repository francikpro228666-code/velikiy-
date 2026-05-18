<?php
namespace app\controllers;

use app\core\Controller;
use app\core\Security;
use app\models\BudgetLimit;
use app\models\Category;

class BudgetController extends Controller {
    private $budgetModel;
    private $categoryModel;
    
    public function __construct($db) {
        parent::__construct($db);
        $this->budgetModel = new BudgetLimit($db);
        $this->categoryModel = new Category($db);
    }
    
    public function index() {
        $this->requireAuth();
        
        $month = $_GET['month'] ?? date('Y-m-01');
        $user_id = $_SESSION['user_id'];
        
        $budgets = $this->budgetModel->findAllByUser($user_id, $month);
        $categories = $this->categoryModel->findAll('expense');
        
        $csrf_token = Security::generateCsrfToken();
        
        $this->render('budgets/index', [
            'budgets' => $budgets,
            'categories' => $categories,
            'selected_month' => $month,
            'csrf_token' => $csrf_token
        ]);
    }
    
    public function update() {
        $this->requireAuth();
        
        if (!Security::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            die('Ошибка безопасности');
        }
        
        $month = $_POST['month'] ?? date('Y-m-01');
        $user_id = $_SESSION['user_id'];
        
        foreach ($_POST['limits'] ?? [] as $category_id => $limit_amount) {
            $limit = new BudgetLimit($this->db);
            $found = $limit->findByUserAndCategory($user_id, $category_id, $month);
            
            if (!$found) {
                $limit->user_id = $user_id;
                $limit->category_id = $category_id;
                $limit->month = $month;
            }
            
            $limit->limit_amount = floatval($limit_amount);
            $limit->save();
        }
        
        $this->redirect('/budgets?month=' . $month . '&success=1');
    }
    
    public function changeMonth($month) {
        $this->redirect('/budgets?month=' . $month);
    }
}