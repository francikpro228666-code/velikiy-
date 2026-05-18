<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Дашборд - Финансовый дневник</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .stat-card {
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            color: white;
            transition: transform 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .stat-card.income { background: linear-gradient(135deg, #28a745, #20c997); }
        .stat-card.expense { background: linear-gradient(135deg, #dc3545, #fd7e14); }
        .stat-card.balance { background: linear-gradient(135deg, #17a2b8, #6f42c1); }
        .progress {
            height: 10px;
            border-radius: 5px;
        }
        .navbar-brand {
            font-weight: bold;
            font-size: 1.5rem;
        }
        .sidebar {
            min-height: 100vh;
            background-color: #f8f9fa;
            border-right: 1px solid #dee2e6;
        }
        .main-content {
            padding: 20px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-2 d-none d-md-block sidebar">
                <div class="position-sticky pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="/dashboard">
                                <i class="fas fa-tachometer-alt"></i> Дашборд
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/transactions">
                                <i class="fas fa-exchange-alt"></i> Транзакции
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/categories">
                                <i class="fas fa-tags"></i> Категории
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/profile">
                                <i class="fas fa-user"></i> Профиль
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/logout">
                                <i class="fas fa-sign-out-alt"></i> Выход
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>
            
            <!-- Main content -->
            <main class="col-md-10 ms-sm-auto px-md-4 main-content">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1>Финансовый дашборд</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <span class="text-muted">Привет, <?= htmlspecialchars($_SESSION['username'] ?? 'Пользователь') ?>!</span>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-4">
                        <div class="stat-card income">
                            <h5><i class="fas fa-arrow-up"></i> Доходы</h5>
                            <h2><?= number_format($total_income, 2) ?> ₽</h2>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-card expense">
                            <h5><i class="fas fa-arrow-down"></i> Расходы</h5>
                            <h2><?= number_format($total_expense, 2) ?> ₽</h2>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-card balance">
                            <h5><i class="fas fa-wallet"></i> Баланс</h5>
                            <h2><?= number_format($balance, 2) ?> ₽</h2>
                        </div>
                    </div>
                </div>
                
                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="fas fa-chart-line"></i> Бюджет на месяц</h5>
                            </div>
                            <div class="card-body">
                                <?php if (empty($budgets)): ?>
                                    <p class="text-muted">Нет установленных бюджетных лимитов</p>
                                    <a href="/budgets" class="btn btn-primary btn-sm">Установить лимиты</a>
                                <?php else: ?>
                                    <?php foreach ($budgets as $budget): ?>
                                        <?php $category = $budget->getCategory(); ?>
                                        <div class="mb-3">
                                            <div class="d-flex justify-content-between">
                                                <span>
                                                    <i class="fas <?= htmlspecialchars($category->icon ?? 'fa-tag') ?>"></i>
                                                    <?= htmlspecialchars($category->name) ?>
                                                </span>
                                                <span><?= number_format($budget->current_spent, 2) ?> / <?= number_format($budget->limit_amount, 2) ?> ₽</span>
                                            </div>
                                            <div class="progress">
                                                <?php $percent = $budget->getUsagePercent(); ?>
                                                <div class="progress-bar <?= $percent > 90 ? 'bg-danger' : ($percent > 70 ? 'bg-warning' : 'bg-success') ?>" 
                                                     style="width: <?= $percent ?>%"></div>
                                            </div>
                                            <small class="text-muted"><?= $percent ?>% использовано</small>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="fas fa-bullseye"></i> Финансовые цели</h5>
                            </div>
                            <div class="card-body">
                                <?php if (empty($goals)): ?>
                                    <p class="text-muted">Нет активных целей</p>
                                    <a href="/goals/create" class="btn btn-primary btn-sm">Создать цель</a>
                                <?php else: ?>
                                    <?php foreach ($goals as $goal): ?>
                                        <div class="mb-3">
                                            <div class="d-flex justify-content-between">
                                                <span><?= htmlspecialchars($goal->name) ?></span>
                                                <span><?= number_format($goal->current_amount, 2) ?> / <?= number_format($goal->target_amount, 2) ?> ₽</span>
                                            </div>
                                            <div class="progress">
                                                <div class="progress-bar bg-info" style="width: <?= $goal->getProgressPercent() ?>%"></div>
                                            </div>
                                            <small class="text-muted"><?= $goal->getProgressPercent() ?>% выполнено</small>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="fas fa-history"></i> Последние транзакции</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Дата</th>
                                                <th>Категория</th>
                                                <th>Описание</th>
                                                <th>Сумма</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($recent_transactions as $transaction): ?>
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
                                                    <td class="<?= ($category->type ?? 'expense') == 'income' ? 'text-success' : 'text-danger' ?>">
                                                        <?= ($category->type ?? 'expense') == 'income' ? '+' : '-' ?>
                                                        <?= number_format($transaction->amount, 2) ?> ₽
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                            <?php if (empty($recent_transactions)): ?>
                                                <tr>
                                                    <td colspan="4" class="text-center">Нет транзакций</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <a href="/transactions" class="btn btn-outline-primary">Все транзакции</a>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>