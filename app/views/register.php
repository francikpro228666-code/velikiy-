<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрация - Финансовый дневник</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3>Регистрация</h3>
                    </div>
                    <div class="card-body">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                        <?php endif; ?>
                        
                        <form method="POST" action="/register">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                            
                            <div class="mb-3">
                                <label>Имя пользователя</label>
                                <input type="text" name="username" class="form-control" required>
                            </div>
                            
                            <div class="mb-3">
                                <label>Email</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                            
                            <div class="mb-3">
                                <label>Пароль</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            
                            <div class="mb-3">
                                <label>Полное имя</label>
                                <input type="text" name="full_name" class="form-control">
                            </div>
                            
                            <div class="mb-3">
                                <label>Валюта</label>
                                <select name="currency" class="form-control">
                                    <option value="RUB">Рубль (RUB)</option>
                                    <option value="USD">Доллар (USD)</option>
                                    <option value="EUR">Евро (EUR)</option>
                                </select>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100">Зарегистрироваться</button>
                            <div class="text-center mt-3">
                                <a href="/login">Уже есть аккаунт? Войти</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>