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
<title>Campus Forum - Login</title>

<style>
/* ======== ESTILO GERAL ======== */
body {
    margin: 0;
    font-family: "Poppins", sans-serif;
    background-color: #1a0033; /* fundo roxo escuro */
    color: #f9d76e; /* dourado */
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
}

/* ======== CONTAINER DO LOGIN ======== */
.login-container {
    background: linear-gradient(180deg, #29004b, #3d0073);
    padding: 40px 50px;
    border-radius: 18px;
    width: 350px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.5);
    text-align: center;
}

/* ======== TÍTULO ======== */
.login-container h1 {
    margin-top: 0;
    color: #f4e9ff;
    font-size: 26px;
}

/* ======== ERROS ======== */
.error {
    background-color: rgba(255, 50, 50, 0.25);
    padding: 8px;
    border-radius: 6px;
    margin-bottom: 12px;
    color: #ff9a9a;
}

/* ======== INPUTS ======== */
.login-container input {
    width: 100%;
    padding: 12px 16px;
    margin-bottom: 15px;
    border-radius: 30px;
    border: none;
    outline: none;

    background: #000;  /* preto — igual à barra de pesquisa */
    color: #fff;
    font-size: 15px;
}

.login-container input::placeholder {
    color: rgba(255,255,255,0.75);
}

/* ======== BOTÃO ======== */
button {
    width: 100%;
    padding: 12px;
    border: none;

    background: linear-gradient(180deg, #7c3aed, #5b21b6);
    color: #ffffff;
    border-radius: 10px;
    font-size: 16px;
    cursor: pointer;
    font-weight: 500;
    transition: 0.3s ease;
    box-shadow: 0 3px 6px rgba(0,0,0,0.3);
}

button:hover {
    background: linear-gradient(180deg, #9d4edd, #6a0dad);
    transform: translateY(-2px);
}

/* ======== LINK VOLTAR ======== */
.voltar {
    margin-top: 15px;
    display: inline-block;
    color: #d3aaff;
    text-decoration: none;
    font-size: 14px;
}

.voltar:hover {
    text-decoration: underline;
}
</style>

</head>
<body>

<div class="login-container">

    <h1>Login</h1>

    <?php foreach ($errors as $e): ?>
        <p class="error"><?= htmlspecialchars($e) ?></p>
    <?php endforeach; ?>

    <form method="post">
        <input type="text" name="username" placeholder="Digite seu usuário" required>
        <input type="password" name="password" placeholder="Digite sua senha" required>

        <button type="submit">Entrar</button>
    </form>

    <a href="index.php" class="voltar">← Voltar</a>

</div>

</body>
</html>

