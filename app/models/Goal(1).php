<?php
namespace app\models;

use PDO;

class Goal {
    private $db;
    public $id;
    public $user_id;
    public $name;
    public $target_amount;
    public $current_amount;
    public $deadline;
    public $created_at;
    public $is_completed;
    public $color;
    
    public function __construct(PDO $db) {
        $this->db = $db;
    }
    
    public function findById($id) {
        $stmt = $this->db->prepare("SELECT * FROM goals WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($data) {
            $this->hydrate($data);
            return true;
        }
        return false;
    }
    
    public function findAllByUser($user_id, $onlyActive = false) {
        $sql = "SELECT * FROM goals WHERE user_id = :user_id";
        if ($onlyActive) {
            $sql .= " AND is_completed = 0";
        }
        $sql .= " ORDER BY deadline ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user_id' => $user_id]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $goals = [];
        foreach ($rows as $row) {
            $g = new Goal($this->db);
            $g->hydrate($row);
            $goals[] = $g;
        }
        return $goals;
    }
    
    public function save() {
        if ($this->id) {
            $stmt = $this->db->prepare("UPDATE goals SET name=:name, target_amount=:target_amount, current_amount=:current_amount, deadline=:deadline, is_completed=:is_completed, color=:color WHERE id=:id");
            return $stmt->execute([
                'name' => $this->name,
                'target_amount' => $this->target_amount,
                'current_amount' => $this->current_amount,
                'deadline' => $this->deadline,
                'is_completed' => $this->is_completed,
                'color' => $this->color,
                'id' => $this->id
            ]);
        } else {
            $stmt = $this->db->prepare("INSERT INTO goals (user_id, name, target_amount, current_amount, deadline, color) VALUES (:user_id, :name, :target_amount, :current_amount, :deadline, :color)");
            $result = $stmt->execute([
                'user_id' => $this->user_id,
                'name' => $this->name,
                'target_amount' => $this->target_amount,
                'current_amount' => $this->current_amount ?? 0,
                'deadline' => $this->deadline,
                'color' => $this->color
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
        $stmt = $this->db->prepare("DELETE FROM goals WHERE id = :id");
        return $stmt->execute(['id' => $this->id]);
    }
    
    public function deposit($amount) {
        if ($amount <= 0) return false;
        $this->current_amount += $amount;
        if ($this->current_amount >= $this->target_amount) {
            $this->is_completed = true;
        }
        $stmt = $this->db->prepare("UPDATE goals SET current_amount = :current, is_completed = :completed WHERE id = :id");
        return $stmt->execute([
            'current' => $this->current_amount,
            'completed' => $this->is_completed ? 1 : 0,
            'id' => $this->id
        ]);
    }
    
    public function getProgressPercent() {
        if ($this->target_amount == 0) return 0;
        return min(100, round($this->current_amount / $this->target_amount * 100, 2));
    }
    
    // ========== МЕТОД ДЛЯ ОТЧЁТА ПО ЦЕЛЯМ (3.6) ==========
    
    /**
     * Получить все цели пользователя для отчёта
     * @param int $userId
     * @return array
     */
    public function getGoalsReport($userId) {
        $sql = "SELECT name, target_amount, current_amount, deadline, is_completed,
                       ROUND((current_amount / target_amount) * 100, 2) as percent
                FROM goals
                WHERE user_id = :user_id
                ORDER BY deadline ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    private function hydrate($data) {
        $this->id = $data['id'];
        $this->user_id = $data['user_id'];
        $this->name = $data['name'];
        $this->target_amount = $data['target_amount'];
        $this->current_amount = $data['current_amount'];
        $this->deadline = $data['deadline'];
        $this->created_at = $data['created_at'];
        $this->is_completed = $data['is_completed'];
        $this->color = $data['color'];
    }
}