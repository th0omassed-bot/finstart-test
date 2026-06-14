<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';

$error = '';

if (is_admin_logged_in()) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login'] ?? '');
    $password = (string)($_POST['password'] ?? '');
    $passwordHash = hash('sha256', $password);

    if (hash_equals(ADMIN_LOGIN, $login) && hash_equals(ADMIN_PASSWORD_SHA256, $passwordHash)) {
        session_regenerate_id(true);
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_login'] = $login;
        csrf_token();

        header('Location: index.php');
        exit;
    }

    sleep(1);
    $error = 'Неверный логин или пароль.';
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Вход в админку — FinStart</title>
  <link rel="stylesheet" href="assets/admin.css" />
</head>
<body class="login-page">
  <main class="login-card">
    <h1>FinStart Admin</h1>
    <p>Закрытая панель управления сайтом.</p>

    <?php if ($error !== ''): ?>
      <div class="alert error"><?= h($error) ?></div>
    <?php endif; ?>

    <form method="POST">
      <label>
        Логин
        <input type="text" name="login" autocomplete="username" required autofocus />
      </label>

      <label>
        Пароль
        <input type="password" name="password" autocomplete="current-password" required />
      </label>

      <button type="submit">Войти</button>
    </form>
  </main>
</body>
</html>
