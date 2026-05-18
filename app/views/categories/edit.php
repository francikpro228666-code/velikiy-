<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Редактирование категории</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
<div class="container mt-4">
    <h1>Редактировать категорию</h1>
    <form method="POST" action="/categories/edit/<?= $category->id ?>">
        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
        
        <div class="mb-3">
            <label>Название</label>
            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($category->name) ?>" required>
        </div>
        
        <div class="mb-3">
            <label>Тип</label>
            <select name="type" class="form-control">
                <option value="expense" <?= $category->type == 'expense' ? 'selected' : '' ?>>Расход</option>
                <option value="income" <?= $category->type == 'income' ? 'selected' : '' ?>>Доход</option>
            </select>
        </div>
        
        <div class="mb-3">
            <label>Родительская категория</label>
            <select name="parent_id" class="form-control">
                <option value="">Нет</option>
                <?php foreach ($parentCategories as $cat): ?>
                    <?php if ($cat->id != $category->id): ?>
                        <option value="<?= $cat->id ?>" <?= $category->parent_id == $cat->id ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat->name) ?>
                        </option>
                    <?php endif; ?>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="mb-3">
            <label>Иконка (FontAwesome)</label>
            <input type="text" name="icon" class="form-control" value="<?= htmlspecialchars($category->icon ?? 'fa-tag') ?>">
        </div>
        
        <div class="mb-3">
            <label>Цвет (HEX)</label>
            <input type="color" name="color" class="form-control" value="<?= htmlspecialchars($category->color ?? '#000000') ?>">
        </div>
        
        <button type="submit" class="btn btn-primary">Сохранить</button>
        <a href="/categories" class="btn btn-secondary">Отмена</a>
    </form>
</div>
</body>
</html>