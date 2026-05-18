<?php
namespace app\controllers;

use app\core\Controller;
use app\core\Security;
use app\models\Goal;
use app\models\GoalDeposit;
use app\models\Transaction;

class GoalController extends Controller {
    private $goalModel;
    
    public function __construct($db) {
        parent::__construct($db);
        $this->goalModel = new Goal($db);
    }
    
    public function index() {
        $this->requireAuth();
        
        $goals = $this->goalModel->findAllByUser($_SESSION['user_id']);
        $transactions = (new Transaction($this->db))->findAllByUser($_SESSION['user_id']);
        $csrf_token = Security::generateCsrfToken();
        
        $this->render('goals/index', [
            'goals' => $goals,
            'transactions' => $transactions,
            'csrf_token' => $csrf_token
        ]);
    }
    
    public function create() {
        $this->requireAuth();
        
        $csrf_token = Security::generateCsrfToken();
        $this->render('goals/create', ['csrf_token' => $csrf_token]);
    }
    
    public function store() {
        $this->requireAuth();
        
        if (!Security::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            die('Ошибка безопасности');
        }
        
        $this->goalModel->user_id = $_SESSION['user_id'];
        $this->goalModel->name = Security::sanitize($_POST['name'] ?? '');
        $this->goalModel->target_amount = floatval($_POST['target_amount'] ?? 0);
        $this->goalModel->current_amount = 0;
        $this->goalModel->deadline = $_POST['deadline'] ?? null;
        $this->goalModel->color = Security::sanitize($_POST['color'] ?? '#17a2b8');
        
        if ($this->goalModel->save()) {
            $this->redirect('/goals?success=created');
        } else {
            $this->redirect('/goals/create?error=1');
        }
    }
    
    public function edit($id) {
        $this->requireAuth();
        
        if (!$this->goalModel->findById($id) || $this->goalModel->user_id != $_SESSION['user_id']) {
            $this->redirect('/goals?error=not_found');
        }
        
        $csrf_token = Security::generateCsrfToken();
        $this->render('goals/edit', [
            'goal' => $this->goalModel,
            'csrf_token' => $csrf_token
        ]);
    }
    
    public function update($id) {
        $this->requireAuth();
        
        if (!$this->goalModel->findById($id) || $this->goalModel->user_id != $_SESSION['user_id']) {
            $this->redirect('/goals?error=not_found');
        }
        
        if (!Security::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            die('Ошибка безопасности');
        }
        
        $this->goalModel->name = Security::sanitize($_POST['name'] ?? $this->goalModel->name);
        $this->goalModel->target_amount = floatval($_POST['target_amount'] ?? $this->goalModel->target_amount);
        $this->goalModel->deadline = $_POST['deadline'] ?? $this->goalModel->deadline;
        $this->goalModel->color = Security::sanitize($_POST['color'] ?? $this->goalModel->color);
        
        if ($this->goalModel->save()) {
            $this->redirect('/goals?success=updated');
        } else {
            $this->redirect('/goals/edit/' . $id . '?error=1');
        }
    }
    
    public function delete($id) {
        $this->requireAuth();
        
        if ($this->goalModel->findById($id) && $this->goalModel->user_id == $_SESSION['user_id']) {
            $this->goalModel->delete();
            $this->redirect('/goals?success=deleted');
        } else {
            $this->redirect('/goals?error=not_found');
        }
    }
    
    public function deposit($id) {
        $this->requireAuth();
        
        if (!$this->goalModel->findById($id) || $this->goalModel->user_id != $_SESSION['user_id']) {
            $this->redirect('/goals?error=not_found');
        }
        
        if (!Security::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            die('Ошибка безопасности');
        }
        
        $amount = floatval($_POST['amount'] ?? 0);
        $transaction_id = !empty($_POST['transaction_id']) ? intval($_POST['transaction_id']) : null;
        
        if ($amount <= 0) {
            $this->redirect('/goals?error=invalid_amount');
            return;
        }
        
        $deposit = new GoalDeposit($this->db);
        $deposit->goal_id = $id;
        $deposit->transaction_id = $transaction_id;
        $deposit->amount = $amount;
        
        if ($deposit->save()) {
            $this->redirect('/goals?success=deposit');
        } else {
            $this->redirect('/goals?error=deposit_failed');
        }
    }
}