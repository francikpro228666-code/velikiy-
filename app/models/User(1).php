<?php
namespace app\models;

use PDO;

class User {
    private $db;
    public $id;
    public $username;
    public $email;
    public $password;
    public $full_name;
    public $currency;
    public $created_at;
    
    public function __construct(PDO $db) {
        $this->db = $db;
    }
    
    public function findById($id) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($data) {
            $this->hydrate($data);
            return true;
        }
        return false;
    }
    
    public function findByEmail($email) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($data) {
            $this->hydrate($data);
            return $this;
        }
        return null;
    }
    
    public function save() {
        if ($this->id) {
            $sql = "UPDATE users SET username = :username, email = :email, full_name = :full_name, currency = :currency";
            $params = [
                'username' => $this->username,
                'email' => $this->email,
                'full_name' => $this->full_name,
                'currency' => $this->currency,
                'id' => $this->id
            ];
            if (!empty($this->password)) {
                $sql .= ", password = :password";
                $params['password'] = $this->password;
            }
            $sql .= " WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
        } else {
            $stmt = $this->db->prepare("INSERT INTO users (username, email, password, full_name, currency) VALUES (:username, :email, :password, :full_name, :currency)");
            $result = $stmt->execute([
                'username' => $this->username,
                'email' => $this->email,
                'password' => $this->password,
                'full_name' => $this->full_name,
                'currency' => $this->currency ?? 'RUB'
            ]);
            if ($result) {
                $this->id = $this->db->lastInsertId();
                return true;
            }
            return false;
        }
    }
    
    private function hydrate($data) {
        $this->id = $data['id'];
        $this->username = $data['username'];
        $this->email = $data['email'];
        $this->password = $data['password'];
        $this->full_name = $data['full_name'];
        $this->currency = $data['currency'];
        $this->created_at = $data['created_at'];
    }
}