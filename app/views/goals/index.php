<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Мои цели</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
    <h1>Финансовые цели</h1>
    <a href="/goals/create" class="btn btn-primary mb-3">Новая цель</a>
    <table class="table table-bordered">
        <thead>
            <tr><th>Название</th><th>Нужно</th><th>Накоплено</th><th>%</th><th>Дедлайн</th><th>Действия</th></tr>
        </thead>
        <tbody>
            <?php foreach ($goals as $goal): ?>
            <tr>
                <td><?= htmlspecialchars($goal->name) ?></td>
                <td><?= $goal->target_amount ?> ₽</td>
                <td><?= $goal->current_amount ?> ₽</td>
                <td><?= $goal->getProgressPercent() ?>%</td>
                <td><?= $goal->deadline ?? '—' ?></td>
                <td>
                    <a href="/goals/edit/<?= $goal->id ?>" class="btn btn-sm btn-warning">Изменить</a>
                    <a href="/goals/delete/<?= $goal->id ?>" class="btn btn-sm btn-danger" onclick="return confirm('Удалить?')">Удалить</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <a href="/dashboard" class="btn btn-secondary">На дашборд</a>
</div>
</body>
</html>