<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Категории - Финансовый дневник</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Категории</h1>
            <a href="/categories/create" class="btn btn-primary">
                <i class="fas fa-plus"></i> Добавить категорию
            </a>
        </div>
        
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php if ($_GET['success'] == 'created'): ?>Категория успешно добавлена
                <?php elseif ($_GET['success'] == 'updated'): ?>Категория обновлена
                <?php elseif ($_GET['success'] == 'deleted'): ?>Категория удалена
                <?php endif; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['error']) && $_GET['error'] == 'has_transactions'): ?>
            <div class="alert alert-danger">Нельзя удалить категорию, у которой есть транзакции</div>
        <?php endif; ?>
        
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5><i class="fas fa-plus-circle"></i> Доходы</h5>
                    </div>
                    <div class="card-body">
                        <div class="list-group">
                            <?php foreach ($categories as $category): ?>
                                <?php if ($category->type == 'income'): ?>
                                    <div class="list-group-item">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <i class="fas <?= htmlspecialchars($category->icon ?? 'fa-tag') ?>" style="color: <?= htmlspecialchars($category->color ?? '#28a745') ?>"></i>
                                                <?= htmlspecialchars($category->name) ?>
                                                <?php if ($category->parent_id): ?>
                                                    <small class="text-muted">(подкатегория)</small>
                                                <?php endif; ?>
                                            </div>
                                            <div>
                                                <a href="/categories/edit/<?= $category->id ?>" class="btn btn-sm btn-warning">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="/categories/delete/<?= $category->id ?>" 
                                                   class="btn btn-sm btn-danger"
                                                   onclick="return confirm('Удалить категорию?')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                        <?php 
                        $incomeCount = count(array_filter($categories, function($c) { return $c->type == 'income'; }));
                        if ($incomeCount == 0): ?>
                            <p class="text-muted text-center mt-3">Нет категорий доходов</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-danger text-white">
                        <h5><i class="fas fa-minus-circle"></i> Расходы</h5>
                    </div>
                    <div class="card-body">
                        <div class="list-group">
                            <?php foreach ($categories as $category): ?>
                                <?php if ($category->type == 'expense'): ?>
                                    <div class="list-group-item">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <i class="fas <?= htmlspecialchars($category->icon ?? 'fa-tag') ?>" style="color: <?= htmlspecialchars($category->color ?? '#dc3545') ?>"></i>
                                                <?= htmlspecialchars($category->name) ?>
                                                <?php if ($category->parent_id): ?>
                                                    <small class="text-muted">(подкатегория)</small>
                                                <?php endif; ?>
                                            </div>
                                            <div>
                                                <a href="/categories/edit/<?= $category->id ?>" class="btn btn-sm btn-warning">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="/categories/delete/<?= $category->id ?>" 
                                                   class="btn btn-sm btn-danger"
                                                   onclick="return confirm('Удалить категорию?')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                        <?php 
                        $expenseCount = count(array_filter($categories, function($c) { return $c->type == 'expense'; }));
                        if ($expenseCount == 0): ?>
                            <p class="text-muted text-center mt-3">Нет категорий расходов</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="mt-3">
            <a href="/dashboard" class="btn btn-secondary">На дашборд</a>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>