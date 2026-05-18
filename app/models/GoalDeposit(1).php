<?php
namespace app\models;

use PDO;

class GoalDeposit {
    private $db;
    public $id;
    public $goal_id;
    public $transaction_id;
    public $amount;
    public $deposited_at;
    
    public function __construct(PDO $db) {
        $this->db = $db;
    }
    
    public function findById($id) {
        $stmt = $this->db->prepare("SELECT * FROM goal_deposits WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($data) {
            $this->hydrate($data);
            return true;
        }
        return false;
    }
    
    public function findAllByGoal($goal_id) {
        $stmt = $this->db->prepare("SELECT * FROM goal_deposits WHERE goal_id = :goal_id ORDER BY deposited_at DESC");
        $stmt->execute(['goal_id' => $goal_id]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $deposits = [];
        foreach ($rows as $row) {
            $d = new GoalDeposit($this->db);
            $d->hydrate($row);
            $deposits[] = $d;
        }
        return $deposits;
    }
    
    public function save() {
        if ($this->id) {
            $stmt = $this->db->prepare("UPDATE goal_deposits SET goal_id=:goal_id, transaction_id=:transaction_id, amount=:amount WHERE id=:id");
            return $stmt->execute([
                'goal_id' => $this->goal_id,
                'transaction_id' => $this->transaction_id,
                'amount' => $this->amount,
                'id' => $this->id
            ]);
        } else {
            $this->db->beginTransaction();
            try {
                $stmt = $this->db->prepare("INSERT INTO goal_deposits (goal_id, transaction_id, amount) VALUES (:goal_id, :transaction_id, :amount)");
                $result = $stmt->execute([
                    'goal_id' => $this->goal_id,
                    'transaction_id' => $this->transaction_id,
                    'amount' => $this->amount
                ]);
                if (!$result) throw new \Exception("Ошибка вставки");
                $this->id = $this->db->lastInsertId();
                
                $goal = new Goal($this->db);
                if ($goal->findById($this->goal_id)) {
                    $goal->deposit($this->amount);
                }
                
                $this->db->commit();
                return true;
            } catch (\Exception $e) {
                $this->db->rollBack();
                return false;
            }
        }
    }
    
    public function delete() {
        if (!$this->id) return false;
        $this->db->beginTransaction();
        try {
            $goal = new Goal($this->db);
            if ($goal->findById($this->goal_id)) {
                $goal->current_amount -= $this->amount;
                if ($goal->current_amount < 0) $goal->current_amount = 0;
                $goal->is_completed = false;
                $goal->save();
            }
            
            $stmt = $this->db->prepare("DELETE FROM goal_deposits WHERE id = :id");
            $stmt->execute(['id' => $this->id]);
            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
    
    public function getTransaction() {
        $t = new Transaction($this->db);
        $t->findById($this->transaction_id);
        return $t;
    }
    
    public function getGoal() {
        $g = new Goal($this->db);
        $g->findById($this->goal_id);
        return $g;
    }
    
    private function hydrate($data) {
        $this->id = $data['id'];
        $this->goal_id = $data['goal_id'];
        $this->transaction_id = $data['transaction_id'];
        $this->amount = $data['amount'];
        $this->deposited_at = $data['deposited_at'];
    }
}