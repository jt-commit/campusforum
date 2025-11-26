<?php
require_once 'db.php';
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST['name']);
    $email1 = trim($_POST['email1']);
    $email2 = trim($_POST['email2']);
    $password1 = $_POST['password1'];
    $password2 = $_POST['password2'];

    if ($email1 !== $email2) {
        $error = "Os emails não coincidem!";
    }
    elseif ($password1 !== $password2) {
        $error = "As senhas não coincidem!";
    }
    else {

        $password_hash = password_hash($password1, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email1]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            $error = "Email já cadastrado!";
        } else {
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
            $stmt->execute([$name, $email1, $password_hash]);

            header("Location: login.php");
            exit;
        }
    }
}
?>
<!doctype html>
<html lang="pt-br">
<head>
<meta charset="utf-8">
<title>Campus Forum - Cadastro</title>

<style>
/* ======== ESTILO GERAL ======== */
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

/* ======== CONTAINER ======== */
.form-container {
    background: linear-gradient(180deg, #29004b, #3d0073);
    padding: 40px 50px;
    border-radius: 18px;
    width: 380px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.5);
    text-align: center;
}

.form-container h1 {
    margin-top: 0;
    color: #f4e9ff;
    font-size: 26px;
}

/* ======== ERRO ======== */
.error {
    background-color: rgba(255, 50, 50, 0.25);
    padding: 8px;
    border-radius: 6px;
    margin-bottom: 12px;
    color: #ff9a9a;
}

/* ======== INPUTS ======== */
.form-container input {
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

.form-container input::placeholder {
    color: rgba(255,255,255,0.75);
}

/* ======== BOTÃO ======== */
button {
    width: 100%;
    padding: 12px;

    background: linear-gradient(180deg, #7c3aed, #5b21b6);
    color: #ffffff;

    border: none;
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

<div class="form-container">

    <h1>Criar Conta</h1>

    <?php if (!empty($error)): ?>
        <p class="error"><?= $error ?></p>
    <?php endif; ?>

    <form method="post">

        <input type="text" name="name" placeholder="Nome" required>

        <input type="email" name="email1" placeholder="Email" required>
        <input type="email" name="email2" placeholder="Confirmar Email" required>

        <input type="password" name="password1" placeholder="Senha" required>
        <input type="password" name="password2" placeholder="Confirmar Senha" required>

        <button type="submit" name="register">Cadastrar</button>
    </form>

    <a href="login.php" class="voltar">← Voltar ao login</a>
</div>

</body>
</html>

