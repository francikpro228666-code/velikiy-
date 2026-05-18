<?php
namespace app\controllers;

use app\core\Controller;
use app\core\Security;
use app\models\Category;

class CategoryController extends Controller {
    private $categoryModel;
    
    public function __construct($db) {
        parent::__construct($db);
        $this->categoryModel = new Category($db);
    }
    
    public function index() {
        $this->requireAuth();
        
        $categories = $this->categoryModel->findAll();
        
        $this->render('categories/index', ['categories' => $categories]);
    }
    
    public function create() {
        $this->requireAuth();
        
        $csrf_token = Security::generateCsrfToken();
        $parentCategories = $this->categoryModel->findAll();
        
        $this->render('categories/create', [
            'parentCategories' => $parentCategories,
            'csrf_token' => $csrf_token
        ]);
    }
    
    public function store() {
        $this->requireAuth();
        
        // Проверка CSRF
        if (!Security::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            die('Ошибка безопасности');
        }
        
        // Валидация
        $name = Security::sanitize($_POST['name'] ?? '');
        if (empty($name)) {
            $this->redirect('/categories/create?error=empty_name');
            return;
        }
        
        $type = $_POST['type'] ?? 'expense';
        if (!in_array($type, ['income', 'expense'])) {
            $type = 'expense';
        }
        
        $parent_id = !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;
        $icon = Security::sanitize($_POST['icon'] ?? '');
        $color = Security::sanitize($_POST['color'] ?? '#000000');
        
        $this->categoryModel->name = $name;
        $this->categoryModel->type = $type;
        $this->categoryModel->parent_id = $parent_id;
        $this->categoryModel->icon = $icon;
        $this->categoryModel->color = $color;
        
        if ($this->categoryModel->save()) {
            $this->redirect('/categories?success=created');
        } else {
            $this->redirect('/categories/create?error=save_failed');
        }
    }
    
    public function edit($id) {
        $this->requireAuth();
        
        $id = Security::validateId($id) ? (int)$id : 0;
        
        if (!$id || !$this->categoryModel->findById($id)) {
            $this->redirect('/categories?error=not_found');
        }
        
        $csrf_token = Security::generateCsrfToken();
        $parentCategories = $this->categoryModel->findAll();
        
        $this->render('categories/edit', [
            'category' => $this->categoryModel,
            'parentCategories' => $parentCategories,
            'csrf_token' => $csrf_token
        ]);
    }
    
    public function update($id) {
        $this->requireAuth();
        
        $id = Security::validateId($id) ? (int)$id : 0;
        
        if (!$id || !$this->categoryModel->findById($id)) {
            $this->redirect('/categories?error=not_found');
        }
        
        // Проверка CSRF
        if (!Security::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            die('Ошибка безопасности');
        }
        
        $this->categoryModel->name = Security::sanitize($_POST['name'] ?? $this->categoryModel->name);
        $this->categoryModel->type = $_POST['type'] ?? $this->categoryModel->type;
        $this->categoryModel->parent_id = !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;
        $this->categoryModel->icon = Security::sanitize($_POST['icon'] ?? $this->categoryModel->icon);
        $this->categoryModel->color = Security::sanitize($_POST['color'] ?? $this->categoryModel->color);
        
        if ($this->categoryModel->save()) {
            $this->redirect('/categories?success=updated');
        } else {
            $this->redirect('/categories/edit/' . $id . '?error=save_failed');
        }
    }
    
    public function delete($id) {
        $this->requireAuth();
        
        $id = Security::validateId($id) ? (int)$id : 0;
        
        if (!$id || !$this->categoryModel->findById($id)) {
            $this->redirect('/categories?error=not_found');
        }
        
        if ($this->categoryModel->delete()) {
            $this->redirect('/categories?success=deleted');
        } else {
            $this->redirect('/categories?error=has_transactions');
        }
    }
}