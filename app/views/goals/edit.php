<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Редактирование цели</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
    <h1>Редактировать цель</h1>
    <form method="POST" action="/goals/edit/<?= $goal->id ?>">
        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
        
        <div class="mb-3">
            <label>Название</label>
            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($goal->name) ?>" required>
        </div>
        
        <div class="mb-3">
            <label>Целевая сумма</label>
            <input type="number" name="target_amount" step="0.01" class="form-control" value="<?= $goal->target_amount ?>" required>
        </div>
        
        <div class="mb-3">
            <label>Текущая сумма</label>
            <input type="number" name="current_amount" step="0.01" class="form-control" value="<?= $goal->current_amount ?>">
            <small class="text-muted">Обычно меняется через пополнение, но можно отредактировать вручную</small>
        </div>
        
        <div class="mb-3">
            <label>Дедлайн</label>
            <input type="date" name="deadline" class="form-control" value="<?= $goal->deadline ?>">
        </div>
        
        <div class="mb-3">
            <label>Цвет</label>
            <input type="color" name="color" class="form-control" value="<?= htmlspecialchars($goal->color ?? '#17a2b8') ?>">
        </div>
        
        <div class="mb-3 form-check">
            <input type="checkbox" name="is_completed" class="form-check-input" id="completed" <?= $goal->is_completed ? 'checked' : '' ?>>
            <label class="form-check-label" for="completed">Отметить как выполненную</label>
        </div>
        
        <button type="submit" class="btn btn-primary">Сохранить изменения</button>
        <a href="/goals" class="btn btn-secondary">Отмена</a>
    </form>
</div>
</body>
</html>