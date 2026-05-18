<?php
namespace app\models;

use PDO;

class Category {
    private $db;
    public $id;
    public $name;
    public $type;
    public $parent_id;
    public $icon;
    public $color;
    
    public function __construct(PDO $db) {
        $this->db = $db;
    }
    
    public function findById($id) {
        $stmt = $this->db->prepare("SELECT * FROM categories WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($data) {
            $this->hydrate($data);
            return true;
        }
        return false;
    }
    
    public function findAll($type = null) {
        $sql = "SELECT * FROM categories";
        $params = [];
        if ($type) {
            $sql .= " WHERE type = :type";
            $params['type'] = $type;
        }
        $sql .= " ORDER BY name";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $categories = [];
        foreach ($rows as $row) {
            $cat = new Category($this->db);
            $cat->hydrate($row);
            $categories[] = $cat;
        }
        return $categories;
    }
    
    public function save() {
        if ($this->id) {
            $stmt = $this->db->prepare("UPDATE categories SET name=:name, type=:type, parent_id=:parent_id, icon=:icon, color=:color WHERE id=:id");
            return $stmt->execute([
                'name' => $this->name,
                'type' => $this->type,
                'parent_id' => $this->parent_id,
                'icon' => $this->icon,
                'color' => $this->color,
                'id' => $this->id
            ]);
        } else {
            $stmt = $this->db->prepare("INSERT INTO categories (name, type, parent_id, icon, color) VALUES (:name, :type, :parent_id, :icon, :color)");
            $result = $stmt->execute([
                'name' => $this->name,
                'type' => $this->type,
                'parent_id' => $this->parent_id,
                'icon' => $this->icon,
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
        $stmt = $this->db->prepare("DELETE FROM categories WHERE id = :id");
        return $stmt->execute(['id' => $this->id]);
    }
    
    public function getTransactions() {
        $stmt = $this->db->prepare("SELECT * FROM transactions WHERE category_id = :id");
        $stmt->execute(['id' => $this->id]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $transactions = [];
        foreach ($rows as $row) {
            $t = new Transaction($this->db);
            $t->hydrate($row);
            $transactions[] = $t;
        }
        return $transactions;
    }
    
    private function hydrate($data) {
        $this->id = $data['id'];
        $this->name = $data['name'];
        $this->type = $data['type'];
        $this->parent_id = $data['parent_id'];
        $this->icon = $data['icon'];
        $this->color = $data['color'];
    }
}