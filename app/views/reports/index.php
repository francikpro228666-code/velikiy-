<!DOCTYPE html>
<html>
<head>
    <title>Отчёты - Финансовый дневник</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
    <h1>Генерация отчётов</h1>
    <form method="POST" action="/reports/generate">
        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
        
        <div class="mb-3">
            <label>Тип отчёта:</label>
            <select name="report_type" id="report_type" class="form-control" onchange="togglePeriod()">
                <option value="transactions">Доходы и расходы за период</option>
                <option value="budget">Бюджет за месяц</option>
                <option value="goals">Финансовые цели</option>
            </select>
        </div>
        
        <div id="period_block" class="mb-3">
            <label>Дата начала:</label>
            <input type="date" name="start_date" class="form-control" value="<?= date('Y-m-01') ?>">
            <label>Дата окончания:</label>
            <input type="date" name="end_date" class="form-control" value="<?= date('Y-m-t') ?>">
        </div>
        
        <div id="month_block" class="mb-3" style="display:none;">
            <label>Месяц:</label>
            <input type="month" name="month" class="form-control" value="<?= date('Y-m') ?>">
        </div>
        
        <button type="submit" class="btn btn-primary">Показать отчёт</button>
    </form>
</div>
<script>
function togglePeriod() {
    var type = document.getElementById('report_type').value;
    if (type === 'budget') {
        document.getElementById('period_block').style.display = 'none';
        document.getElementById('month_block').style.display = 'block';
    } else {
        document.getElementById('period_block').style.display = 'block';
        document.getElementById('month_block').style.display = 'none';
    }
}
togglePeriod();
</script>
</body>
</html>