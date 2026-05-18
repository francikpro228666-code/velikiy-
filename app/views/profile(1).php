<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Профиль - Финансовый дневник</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-3">
                <div class="list-group">
                    <a href="/dashboard" class="list-group-item list-group-item-action">
                        <i class="fas fa-tachometer-alt"></i> Дашборд
                    </a>
                    <a href="/transactions" class="list-group-item list-group-item-action">
                        <i class="fas fa-exchange-alt"></i> Транзакции
                    </a>
                    <a href="/categories" class="list-group-item list-group-item-action">
                        <i class="fas fa-tags"></i> Категории
                    </a>
                    <a href="/profile" class="list-group-item list-group-item-action active">
                        <i class="fas fa-user"></i> Профиль
                    </a>
                    <a href="/logout" class="list-group-item list-group-item-action text-danger">
                        <i class="fas fa-sign-out-alt"></i> Выход
                    </a>
                </div>
            </div>
            
            <div class="col-md-9">
                <div class="card">
                    <div class="card-header">
                        <h4><i class="fas fa-user-circle"></i> Мой профиль</h4>
                    </div>
                    <div class="card-body">
                        <?php if (isset($_GET['success'])): ?>
                            <div class="alert alert-success">Профиль успешно обновлен!</div>
                        <?php endif; ?>
                        
                        <?php if (isset($_GET['error'])): ?>
                            <div class="alert alert-danger">Ошибка при обновлении профиля</div>
                        <?php endif; ?>
                        
                        <form method="POST" action="/profile">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                            
                            <div class="mb-3">
                                <label>Имя пользователя</label>
                                <input type="text" class="form-control" value="<?= htmlspecialchars($user->username) ?>" disabled>
                                <small class="text-muted">Имя пользователя нельзя изменить</small>
                            </div>
                            
                            <div class="mb-3">
                                <label>Email</label>
                                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user->email) ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label>Полное имя</label>
                                <input type="text" name="full_name" class="form-control" value="<?= htmlspecialchars($user->full_name) ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label>Валюта по умолчанию</label>
                                <select name="currency" class="form-control">
                                    <option value="RUB" <?= $user->currency == 'RUB' ? 'selected' : '' ?>>Рубль (RUB)</option>
                                    <option value="USD" <?= $user->currency == 'USD' ? 'selected' : '' ?>>Доллар (USD)</option>
                                    <option value="EUR" <?= $user->currency == 'EUR' ? 'selected' : '' ?>>Евро (EUR)</option>
                                </select>
                            </div>
                            
                            <hr>
                            <h6>Сменить пароль</h6>
                            <div class="mb-3">
                                <label>Новый пароль</label>
                                <input type="password" name="password" class="form-control" placeholder="Оставьте пустым, если не хотите менять">
                            </div>
                            <div class="mb-3">
                                <label>Подтверждение пароля</label>
                                <input type="password" name="confirm_password" class="form-control">
                            </div>
                            
                            <div class="mb-3">
                                <label>Дата регистрации</label>
                                <input type="text" class="form-control" value="<?= date('d.m.Y', strtotime($user->created_at)) ?>" disabled>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">Сохранить изменения</button>
                            <a href="/dashboard" class="btn btn-secondary">Отмена</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>