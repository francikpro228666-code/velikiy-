<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Добавление категории</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
<div class="container mt-4">
    <h1>Добавить категорию</h1>
    <form method="POST" action="/categories/create">
        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
        
        <div class="mb-3">
            <label>Название</label>
            <input type="text" name="name" class="form-control" required>
        </div>
        
        <div class="mb-3">
            <label>Тип</label>
            <select name="type" class="form-control">
                <option value="expense">Расход</option>
                <option value="income">Доход</option>
            </select>
        </div>
        
        <div class="mb-3">
            <label>Родительская категория (опционально)</label>
            <select name="parent_id" class="form-control">
                <option value="">Нет</option>
                <?php foreach ($parentCategories as $cat): ?>
                    <option value="<?= $cat->id ?>"><?= htmlspecialchars($cat->name) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="mb-3">
            <label>Иконка (FontAwesome, например fa-tag)</label>
            <input type="text" name="icon" class="form-control" value="fa-tag">
        </div>
        
        <div class="mb-3">
            <label>Цвет (HEX)</label>
            <input type="color" name="color" class="form-control" value="#000000">
        </div>
        
        <button type="submit" class="btn btn-primary">Сохранить</button>
        <a href="/categories" class="btn btn-secondary">Отмена</a>
    </form>
</div>
</body>
</html>