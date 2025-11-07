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


<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="utf-8">
<title>Login</title>
<link rel="stylesheet" href="styles.css">


</head>
<body>
    <form method="post">
<center>
        <h1>Login</h1>

        <?php foreach ($errors as $e): ?>
            <p class="error"><?= htmlspecialchars($e) ?></p>
        <?php endforeach; ?>
 
        <label>Usuário:</label>
        <input type="text" name="username" placeholder="Digite seu usuário" required>
<br>
<br>
        <label>Senha:</label>
        <input type="password" name="password" class="btn-novo" placeholder="Digite sua senha" required>
<br>

<br>
        <button class="btn-novo" >Entrar</button>
        <a href="index.php">
<br>

          <- Voltar</a>

</center>
    </form>
</body>
</html>

