<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Новая цель</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
    <h1>Новая финансовая цель</h1>
    <form method="POST" action="/goals/create">
        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
        <div class="mb-3">
            <label>Название цели</label>
            <input type="text" name="name" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Целевая сумма</label>
            <input type="number" name="target_amount" step="0.01" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Дедлайн (необязательно)</label>
            <input type="date" name="deadline" class="form-control">
        </div>
        <div class="mb-3">
            <label>Цвет (HEX)</label>
            <input type="color" name="color" class="form-control" value="#17a2b8">
        </div>
        <button type="submit" class="btn btn-primary">Создать цель</button>
        <a href="/goals" class="btn btn-secondary">Отмена</a>
    </form>
</div>
</body>
</html>