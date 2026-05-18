<?php
namespace app\controllers;

use app\core\Controller;
use app\core\Security;
use app\models\Transaction;
use app\models\BudgetLimit;
use app\models\Goal;
use app\models\Category;

class DashboardController extends Controller {
    private $transactionModel;
    private $budgetModel;
    private $goalModel;
    private $categoryModel;
    
    public function __construct($db) {
        parent::__construct($db);
        $this->transactionModel = new Transaction($db);
        $this->budgetModel = new BudgetLimit($db);
        $this->goalModel = new Goal($db);
        $this->categoryModel = new Category($db);
    }
    
    public function index() {
        $this->requireAuth();
        
        $current_month = date('Y-m-01');
        $user_id = $_SESSION['user_id'];
        
        // Получаем транзакции за текущий месяц
        $transactions = $this->transactionModel->findAllByUser(
            $user_id,
            date('Y-m-01'),
            date('Y-m-t')
        );
        
        // Рассчитываем доходы и расходы
        $total_income = 0;
        $total_expense = 0;
        
        foreach ($transactions as $transaction) {
            $category = $transaction->getCategory();
            if ($category && $category->type === 'income') {
                $total_income += $transaction->amount;
            } else {
                $total_expense += $transaction->amount;
            }
        }
        
        $balance = $total_income - $total_expense;
        
        // Получаем бюджетные лимиты
        $budgets = $this->budgetModel->findAllByUser($user_id, $current_month);
        
        // Получаем активные цели
        $goals = $this->goalModel->findAllByUser($user_id, true);
        
        // Последние 5 транзакций
        $recent_transactions = array_slice($transactions, 0, 5);
        
        $this->render('dashboard/index', [
            'total_income' => $total_income,
            'total_expense' => $total_expense,
            'balance' => $balance,
            'budgets' => $budgets,
            'goals' => $goals,
            'recent_transactions' => $recent_transactions
        ]);
    }
}