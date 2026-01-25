<?php
require_once 'db.php';
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();

$conn = db_connect(); // ← IMPORTANTE

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = trim($_POST['username'] ?? '');
    $email1 = trim($_POST['email1'] ?? '');
    $email2 = trim($_POST['email2'] ?? '');
    $password1 = $_POST['password1'] ?? '';
    $password2 = $_POST['password2'] ?? '';

    // Validar preenchimento
    if (empty($username) || empty($email1) || empty($email2) || empty($password1) || empty($password2)) {
        $error = "Preencha todos os campos!";
    } elseif ($email1 !== $email2) {
        $error = "Os emails não coincidem!";
    }
    elseif ($password1 !== $password2) {
        $error = "As senhas não coincidem!";
    }
    else {

        $password_hash = password_hash($password1, PASSWORD_DEFAULT);

        // Verificar se email já existe
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email1);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error = "Email já cadastrado!";
        } else {

            // Inserir usuário
            $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $username, $email1, $password_hash);
            $stmt->execute();

            header("Location: login.php");
            exit;
        }

        $stmt->close();
    }
}
?>
<!doctype html>
<html lang="pt-br">
<head>
<meta charset="utf-8">
<title>Campus Forum - Cadastro</title>

<style>/* ================================ */
/*  ESTILO GERAL DA PÁGINA         */
/* ================================ */

body {
    margin: 0;
    padding: 0;
    font-family: "Poppins", sans-serif;
    background: linear-gradient(180deg, #1a0033, #3d0073);
    color: #f9d76e;

    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
}

/* ================================ */
/*  CONTAINER DO FORM              */
/* ================================ */

.form-container {
    background: rgba(0, 0, 0, 0.35);
    backdrop-filter: blur(8px);

    padding: 40px 50px;
    width: 380px;

    border-radius: 20px;
    text-align: center;
    box-shadow: 0 8px 25px rgba(0,0,0,0.4);
}

.form-container h1 {
    margin-top: 0;
    padding-bottom: 5px;
    font-size: 28px;
    color: #f4e9ff;
}

/* ================================ */
/*  CAIXA DE ERROS                 */
/* ================================ */

.error {
    background: rgba(255, 0, 50, 0.25);
    padding: 10px 14px;
    border-radius: 10px;
    margin-bottom: 15px;

    color: #ffb3b3;
    font-weight: 500;
    font-size: 14px;
}

/* ================================ */
/*  CAMPOS DO FORM                 */
/* ================================ */

.form-container input {
    width: 100%;
    padding: 14px 18px;
    margin-bottom: 15px;

    background: #000;
    color: #fff;

    border: none;
    outline: none;

    border-radius: 30px;
    font-size: 15px;

    transition: 0.25s ease;
}

.form-container input::placeholder {
    color: rgba(255,255,255,0.7);
}

.form-container input:focus {
    background: #111;
    box-shadow: 0 0 10px rgba(255,255,255,0.2);
}

/* ================================ */
/*  BOTÃO                          */
/* ================================ */

button {
    width: 100%;
    padding: 12px;

    background: linear-gradient(180deg, #7c3aed, #5b21b6);
    color: #fff;

    border: none;
    border-radius: 12px;

    font-size: 17px;
    cursor: pointer;
    font-weight: 500;

    transition: 0.3s ease;
}

button:hover {
    transform: translateY(-2px);
    background: linear-gradient(180deg, #9d4edd, #6a0dad);
}

/* ================================ */
/*  LINK VOLTAR                    */
/* ================================ */

.voltar {
    display: block;
    margin-top: 15px;

    color: #d3aaff;
    text-decoration: none;
    font-size: 14px;

    transition: 0.2s ease;
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

        <input type="text" name="username" placeholder="Nome" required>

        <input type="email" name="email1" placeholder="Email" required>
        <input type="email" name="email2" placeholder="Confirmar Email" required>

        <input type="password" name="password1" placeholder="Senha" required>
        <input type="password" name="password2" placeholder="Confirmar Senha" required>

        <button type="submit">Cadastrar</button>
    </form>

    <a href="login.php" class="voltar">← Voltar ao login</a>
</div>

</body>
</html>

