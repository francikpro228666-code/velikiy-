<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Бюджет - Финансовый дневник</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
<div class="container mt-4">
    <h1>Бюджет на месяц</h1>
    <form method="GET" class="mb-4">
        <label>Выберите месяц:</label>
        <input type="month" name="month" class="form-control w-25 d-inline-block" value="<?= htmlspecialchars($selected_month ?? date('Y-m')) ?>">
        <button type="submit" class="btn btn-primary">Показать</button>
    </form>
    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
        <input type="hidden" name="month" value="<?= htmlspecialchars($selected_month ?? date('Y-m-01')) ?>">
        <table class="table table-bordered">
            <thead>
                <tr><th>Категория</th><th>Лимит</th><th>Потрачено</th><th>Остаток</th><th>%</th></tr>
            </thead>
            <tbody>
                <?php foreach ($categories as $cat): ?>
                <tr>
                    <td><?= htmlspecialchars($cat->name) ?></td>
                    <td><input type="number" name="limits[<?= $cat->id ?>]" value="<?= $budgets[$cat->id]->limit_amount ?? 0 ?>" class="form-control" step="100"></td>
                    <td><?= ($budgets[$cat->id]->current_spent ?? 0) ?> ₽</td>
                    <td><?= (($budgets[$cat->id]->limit_amount ?? 0) - ($budgets[$cat->id]->current_spent ?? 0)) ?> ₽</td>
                    <td><?= round((($budgets[$cat->id]->current_spent ?? 0) / max(1, ($budgets[$cat->id]->limit_amount ?? 1))) * 100, 2) ?>%</td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <button type="submit" class="btn btn-primary">Сохранить лимиты</button>
    </form>
    <a href="/dashboard" class="btn btn-secondary mt-3">На дашборд</a>
</div>
</body>
</html>