<?php
namespace app\models;

use PDO;

class Transaction {
    private $db;
    public $id;
    public $user_id;
    public $category_id;
    public $amount;
    public $date;
    public $description;
    public $created_at;
    public $is_recurring;
    
    public function __construct(PDO $db) {
        $this->db = $db;
    }
    
    public function findById($id) {
        $stmt = $this->db->prepare("SELECT * FROM transactions WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($data) {
            $this->hydrate($data);
            return true;
        }
        return false;
    }
    
    public function findAllByUser($user_id, $start_date = null, $end_date = null, $category_id = null) {
        $sql = "SELECT * FROM transactions WHERE user_id = :user_id";
        $params = ['user_id' => $user_id];
        
        if ($start_date) {
            $sql .= " AND date >= :start_date";
            $params['start_date'] = $start_date;
        }
        if ($end_date) {
            $sql .= " AND date <= :end_date";
            $params['end_date'] = $end_date;
        }
        if ($category_id) {
            $sql .= " AND category_id = :category_id";
            $params['category_id'] = $category_id;
        }
        $sql .= " ORDER BY date DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $transactions = [];
        foreach ($rows as $row) {
            $t = new Transaction($this->db);
            $t->hydrate($row);
            $transactions[] = $t;
        }
        return $transactions;
    }
    
    public function save() {
        if ($this->id) {
            $stmt = $this->db->prepare("UPDATE transactions SET user_id=:user_id, category_id=:category_id, amount=:amount, date=:date, description=:description, is_recurring=:is_recurring WHERE id=:id");
            return $stmt->execute([
                'user_id' => $this->user_id,
                'category_id' => $this->category_id,
                'amount' => $this->amount,
                'date' => $this->date,
                'description' => $this->description,
                'is_recurring' => $this->is_recurring,
                'id' => $this->id
            ]);
        } else {
            $stmt = $this->db->prepare("INSERT INTO transactions (user_id, category_id, amount, date, description, is_recurring) VALUES (:user_id, :category_id, :amount, :date, :description, :is_recurring)");
            $result = $stmt->execute([
                'user_id' => $this->user_id,
                'category_id' => $this->category_id,
                'amount' => $this->amount,
                'date' => $this->date,
                'description' => $this->description,
                'is_recurring' => $this->is_recurring
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
        $stmt = $this->db->prepare("DELETE FROM transactions WHERE id = :id");
        return $stmt->execute(['id' => $this->id]);
    }
    
    public function getCategory() {
        $cat = new Category($this->db);
        $cat->findById($this->category_id);
        return $cat;
    }
    
    public function getUser() {
        $user = new User($this->db);
        $user->findById($this->user_id);
        return $user;
    }
    
    public function hydrate($data) {
        $this->id = $data['id'];
        $this->user_id = $data['user_id'];
        $this->category_id = $data['category_id'];
        $this->amount = $data['amount'];
        $this->date = $data['date'];
        $this->description = $data['description'];
        $this->created_at = $data['created_at'];
        $this->is_recurring = $data['is_recurring'];
    }
}