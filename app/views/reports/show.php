<!DOCTYPE html>
<html>
<head>
    <title>Отчёт</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
    <h2>Результат отчёта</h2>
    
    <?php if ($report_type == 'transactions'): ?>
        <h3>Доходы и расходы за <?= $start_date ?> – <?= $end_date ?></h3>
        <p>Всего доходов: <?= $summary['total_income'] ?> ₽</p>
        <p>Всего расходов: <?= $summary['total_expense'] ?> ₽</p>
        <p>Баланс: <?= $summary['balance'] ?> ₽</p>
        
        <h4>Доходы по категориям</h4>
        <table class="table table-bordered">
            <tr><th>Категория</th><th>Сумма</th></tr>
            <?php foreach ($grouped['income'] as $cat): ?>
            <tr>
                <td><?= htmlspecialchars($cat['name']) ?></td>
                <td><?= $cat['total'] ?> ₽</td>
            </tr>
            <?php endforeach; ?>
        </table>
        
        <h4>Расходы по категориям</h4>
        <table class="table table-bordered">
            <tr><th>Категория</th><th>Сумма</th></tr>
            <?php foreach ($grouped['expense'] as $cat): ?>
            <tr>
                <td><?= htmlspecialchars($cat['name']) ?></td>
                <td><?= $cat['total'] ?> ₽</td>
            </tr>
            <?php endforeach; ?>
        </table>
    <?php elseif ($report_type == 'budget'): ?>
        <h3>Бюджет за <?= $month ?></h3>
        <table class="table table-bordered">
            <tr><th>Категория</th><th>Лимит</th><th>Потрачено</th><th>Остаток</th><th>%</th></tr>
            <?php foreach ($budgetReport as $item): ?>
            <tr>
                <td><?= htmlspecialchars($item['name']) ?></td>
                <td><?= $item['limit_amount'] ?> ₽</td>
                <td><?= $item['current_spent'] ?> ₽</td>
                <td><?= $item['remaining'] ?> ₽</td>
                <td><?= $item['percent'] ?>%</td>
            </tr>
            <?php endforeach; ?>
        </table>
    <?php elseif ($report_type == 'goals'): ?>
        <h3>Финансовые цели</h3>
        <table class="table table-bordered">
            <tr><th>Название</th><th>Нужно</th><th>Накоплено</th><th>%</th><th>Статус</th></tr>
            <?php foreach ($goalsReport as $goal): ?>
            <tr>
                <td><?= htmlspecialchars($goal['name']) ?></td>
                <td><?= $goal['target_amount'] ?> ₽</td>
                <td><?= $goal['current_amount'] ?> ₽</td>
                <td><?= $goal['percent'] ?>%</td>
                <td><?= $goal['is_completed'] ? 'Выполнена' : 'В процессе' ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
    
    <hr>
    <form method="POST" action="/reports/exportExcel" style="display:inline-block;">
        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
        <input type="hidden" name="report_type" value="<?= $report_type ?>">
        <?php if ($report_type == 'transactions'): ?>
            <input type="hidden" name="start_date" value="<?= $start_date ?>">
            <input type="hidden" name="end_date" value="<?= $end_date ?>">
        <?php elseif ($report_type == 'budget'): ?>
            <input type="hidden" name="month" value="<?= $month ?>">
        <?php endif; ?>
        <button type="submit" class="btn btn-success">Скачать Excel (XLSX)</button>
    </form>
    
    <form method="POST" action="/reports/exportWord" style="display:inline-block;">
        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
        <input type="hidden" name="report_type" value="<?= $report_type ?>">
        <?php if ($report_type == 'transactions'): ?>
            <input type="hidden" name="start_date" value="<?= $start_date ?>">
            <input type="hidden" name="end_date" value="<?= $end_date ?>">
        <?php elseif ($report_type == 'budget'): ?>
            <input type="hidden" name="month" value="<?= $month ?>">
        <?php endif; ?>
        <button type="submit" class="btn btn-primary">Скачать Word (DOCX)</button>
    </form>
    
    <a href="/reports" class="btn btn-secondary">Новый отчёт</a>
</div>
</body>
</html>