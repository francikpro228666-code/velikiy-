<?php
namespace app\models;

use PDO;

class BudgetLimit {
    private $db;
    public $id;
    public $user_id;
    public $category_id;
    public $month;
    public $limit_amount;
    public $current_spent;
    public $is_active;
    
    public function __construct(PDO $db) {
        $this->db = $db;
    }
    
    public function findById($id) {
        $stmt = $this->db->prepare("SELECT * FROM budget_limits WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($data) {
            $this->hydrate($data);
            return true;
        }
        return false;
    }
    
    public function findAllByUser($user_id, $month = null) {
        $sql = "SELECT * FROM budget_limits WHERE user_id = :user_id";
        $params = ['user_id' => $user_id];
        if ($month) {
            $sql .= " AND month = :month";
            $params['month'] = $month;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $limits = [];
        foreach ($rows as $row) {
            $l = new BudgetLimit($this->db);
            $l->hydrate($row);
            $limits[] = $l;
        }
        return $limits;
    }
    
    public function save() {
        if ($this->id) {
            $stmt = $this->db->prepare("UPDATE budget_limits SET limit_amount=:limit_amount, is_active=:is_active WHERE id=:id");
            return $stmt->execute([
                'limit_amount' => $this->limit_amount,
                'is_active' => $this->is_active,
                'id' => $this->id
            ]);
        } else {
            $stmt = $this->db->prepare("INSERT INTO budget_limits (user_id, category_id, month, limit_amount, current_spent, is_active) VALUES (:user_id, :category_id, :month, :limit_amount, :current_spent, :is_active)");
            $result = $stmt->execute([
                'user_id' => $this->user_id,
                'category_id' => $this->category_id,
                'month' => $this->month,
                'limit_amount' => $this->limit_amount,
                'current_spent' => $this->current_spent ?? 0,
                'is_active' => $this->is_active ?? 1
            ]);
            if ($result) {
                $this->id = $this->db->lastInsertId();
                return true;
            }
            return false;
        }
    }
    
    public function delete() {
        if (!$this->id) return false;
        $stmt = $this->db->prepare("DELETE FROM budget_limits WHERE id = :id");
        return $stmt->execute(['id' => $this->id]);
    }
    
    public function getCategory() {
        $cat = new Category($this->db);
        $cat->findById($this->category_id);
        return $cat;
    }
    
    public function getUsagePercent() {
        if ($this->limit_amount == 0) return 0;
        return min(100, round(($this->current_spent / $this->limit_amount) * 100, 2));
    }
    
    public function isExceeded() {
        return $this->current_spent > $this->limit_amount;
    }
    
    public function findByUserAndCategory($user_id, $category_id, $month) {
        $stmt = $this->db->prepare("SELECT * FROM budget_limits WHERE user_id = :user_id AND category_id = :category_id AND month = :month");
        $stmt->execute([
            'user_id' => $user_id,
            'category_id' => $category_id,
            'month' => $month
        ]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($data) {
            $this->hydrate($data);
            return true;
        }
        return false;
    }
    
    private function hydrate($data) {
        $this->id = $data['id'];
        $this->user_id = $data['user_id'];
        $this->category_id = $data['category_id'];
        $this->month = $data['month'];
        $this->limit_amount = $data['limit_amount'];
        $this->current_spent = $data['current_spent'];
        $this->is_active = $data['is_active'];
    }
    
    // ========== МЕТОД ДЛЯ ОТЧЁТА ПО БЮДЖЕТУ (3.6) ==========
    
    /**
     * Получить отчёт по бюджету за месяц: лимит, факт, остаток, процент
     * @param int $userId
     * @param string $month (формат YYYY-MM-01)
     * @return array
     */
    public function getBudgetReport($userId, $month) {
        $sql = "SELECT c.name, c.icon, c.color, 
                       bl.limit_amount, bl.current_spent,
                       (bl.limit_amount - bl.current_spent) as remaining,
                       CASE WHEN bl.limit_amount > 0 
                            THEN ROUND((bl.current_spent / bl.limit_amount) * 100, 2)
                            ELSE 0 END as percent
                FROM budget_limits bl
                JOIN categories c ON bl.category_id = c.id
                WHERE bl.user_id = :user_id AND bl.month = :month
                ORDER BY bl.current_spent DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user_id' => $userId, 'month' => $month]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}