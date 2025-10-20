<?php
session_start();
require 'db.php';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $u = trim($_POST['username'] ?? '');
    $p = $_POST['password'] ?? '';

    if ($u === '' || $p === '') {
        $errors[] = 'Preencha usuário e senha';
    }

    if (empty($errors)) {
        $mysqli = db_connect();
        $stmt = $mysqli->prepare('SELECT id, password FROM users WHERE username=?');
        $stmt->bind_param('s', $u);
        $stmt->execute();
        $stmt->bind_result($id, $hash);

        if ($stmt->fetch()) {
            if (password_verify($p, $hash)) {
                $_SESSION['user'] = ['id' => $id, 'username' => $u];
                header('Location: index.php');
                exit;
            } else {
                $errors[] = 'Senha incorreta';
            }
        } else {
            $errors[] = 'Usuário não encontrado';
        }
    }
}
?>
<!doctype html>
<html lang="pt-br">
<head>
<meta charset="utf-8">
<title>Login</title>
<link rel="stylesheet" href="styles.css">
</head>
<body>
<h1>Login</h1>
<?php foreach ($errors as $e) echo "<p class='error'>" . htmlspecialchars($e) . "</p>"; ?>
<form method="post">
<label>Usuário: <input name="username" required></label><br>
<label>Senha: <input type="password" name="password" required></label><br>
<button>Entrar</button>
</form>
<p><a href="index.php">Voltar</a></p>
</body>
</html>
