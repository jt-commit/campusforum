<?php
session_start();
require 'db.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $errors[] = 'Preencha todos os campos.';
    }

    if (empty($errors)) {

        $mysqli = db_connect();

        // Login por e-mail (username NÃO existe)
        $stmt = $mysqli->prepare(
            "SELECT id, password FROM users WHERE email = ?"
        );
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 1) {

            $stmt->bind_result($id, $hash);
            $stmt->fetch();

            if (password_verify($password, $hash)) {

                $_SESSION['user'] = [
                    'id'    => $id,
                    'email' => $email
                ];

                header("Location: index.php");
                exit;

            } else {
                $errors[] = "Senha incorreta.";
            }

        } else {
            $errors[] = "E-mail não encontrado.";
        }

        $stmt->close();
    }
}
?>
<!doctype html>
<html lang="pt-br">
<head>
<meta charset="utf-8">
<title>CampusForumFX — Login</title>

<style>
body {
    margin: 0;
    font-family: "Poppins", sans-serif;
    background-color: #1a0033;
    color: #f9d76e;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
}

.login-container {
    background: linear-gradient(180deg, #29004b, #3d0073);
    padding: 40px 50px;
    border-radius: 18px;
    width: 350px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.5);
    text-align: center;
}

.login-container h1 {
    margin-top: 0;
    color: #f4e9ff;
    font-size: 26px;
}

.error {
    background-color: rgba(255, 50, 50, 0.25);
    padding: 8px;
    border-radius: 6px;
    margin-bottom: 12px;
    color: #ff9a9a;
}

.login-container input {
    width: 100%;
    padding: 12px 16px;
    margin-bottom: 15px;
    border-radius: 30px;
    border: none;
    outline: none;
    background: #000;
    color: #fff;
    font-size: 15px;
}

.login-container input::placeholder {
    color: rgba(255,255,255,0.75);
}

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
        <input type="email" name="email" placeholder="Digite seu e-mail" required>
        <input type="password" name="password" placeholder="Digite sua senha" required>
        <button type="submit">Entrar</button>
    </form>

    <a href="index.php" class="voltar">← Voltar</a>

</div>

</body>
</html>
