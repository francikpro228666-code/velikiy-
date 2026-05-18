<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Транзакции - Финансовый дневник</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Транзакции</h1>
            <a href="/transactions/create" class="btn btn-primary">
                <i class="fas fa-plus"></i> Добавить транзакцию
            </a>
        </div>
        
        <!-- Фильтры -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-3">
                        <label>С даты</label>
                        <input type="date" name="start_date" class="form-control" value="<?= htmlspecialchars($start_date) ?>">
                    </div>
                    <div class="col-md-3">
                        <label>По дату</label>
                        <input type="date" name="end_date" class="form-control" value="<?= htmlspecialchars($end_date) ?>">
                    </div>
                    <div class="col-md-3">
                        <label>Категория</label>
                        <select name="category_id" class="form-control">
                            <option value="">Все категории</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= $category->id ?>" <?= $selected_category == $category->id ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($category->name) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label>&nbsp;</label>
                        <button type="submit" class="btn btn-primary d-block w-100">Применить</button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Таблица транзакций -->
        <div class="card">
            <div class="card-body">
                <?php if (isset($_GET['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php if ($_GET['success'] == 'created'): ?>Транзакция успешно добавлена
                        <?php elseif ($_GET['success'] == 'updated'): ?>Транзакция обновлена
                        <?php elseif ($_GET['success'] == 'deleted'): ?>Транзакция удалена
                        <?php endif; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Дата</th>
                                <th>Категория</th>
                                <th>Описание</th>
                                <th>Сумма</th>
                                <th>Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($transactions)): ?>
                                <tr>
                                    <td colspan="5" class="text-center">Нет транзакций</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($transactions as $transaction): ?>
                                    <?php $category = $transaction->getCategory(); ?>
                                    <tr>
                                        <td><?= date('d.m.Y', strtotime($transaction->date)) ?></td>
                                        <td>
                                            <span style="color: <?= htmlspecialchars($category->color ?? '#000') ?>">
                                                <i class="fas <?= htmlspecialchars($category->icon ?? 'fa-tag') ?>"></i>
                                                <?= htmlspecialchars($category->name ?? 'Без категории') ?>
                                            </span>
                                        </td>
                                        <td><?= htmlspecialchars($transaction->description) ?></td>
                                        <td class="<?= ($category->type ?? 'expense') == 'income' ? 'text-success' : 'text-danger' ?> fw-bold">
                                            <?= ($category->type ?? 'expense') == 'income' ? '+' : '-' ?>
                                            <?= number_format($transaction->amount, 2) ?> ₽
                                        </td>
                                        <td>
                                            <a href="/transactions/edit/<?= $transaction->id ?>" class="btn btn-sm btn-warning">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="/transactions/delete/<?= $transaction->id ?>" 
                                               class="btn btn-sm btn-danger"
                                               onclick="return confirm('Удалить транзакцию?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
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