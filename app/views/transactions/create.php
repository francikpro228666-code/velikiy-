<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Добавить транзакцию</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3>Добавить транзакцию</h3>
                        <a href="/transactions" class="btn btn-secondary btn-sm float-end">Назад</a>
                    </div>
                    <div class="card-body">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                        <?php endif; ?>
                        
                        <form method="POST" action="/transactions/create">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                            
                            <div class="mb-3">
                                <label>Сумма</label>
                                <input type="number" step="0.01" name="amount" class="form-control" required>
                            </div>
                            
                            <div class="mb-3">
                                <label>Категория</label>
                                <select name="category_id" class="form-control" required>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?= $category->id ?>">
                                            <?= htmlspecialchars($category->name) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label>Дата</label>
                                <input type="date" name="date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label>Описание</label>
                                <textarea name="description" class="form-control" rows="3"></textarea>
                            </div>
                            
                            <div class="mb-3 form-check">
                                <input type="checkbox" name="is_recurring" class="form-check-input" id="is_recurring">
                                <label class="form-check-label" for="is_recurring">Регулярная транзакция</label>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">Сохранить</button>
                            <a href="/transactions" class="btn btn-secondary">Отмена</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>