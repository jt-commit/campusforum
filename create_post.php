<?php
session_start();
require 'db.php';

// Apenas usuários logados podem criar post
if (empty($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$errors = [];
$title = '';
$content = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title   = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');

    if ($title === '' || $content === '') {
        $errors[] = 'Preencha o título e o conteúdo.';
    }

    if (empty($errors)) {
        $mysqli = db_connect();

        $stmt = $mysqli->prepare(
            'INSERT INTO posts (user_id, title, content, created_at)
             VALUES (?, ?, ?, NOW())'
        );

        $stmt->bind_param(
            'iss',
            $_SESSION['user']['id'],
            $title,
            $content
        );

        if ($stmt->execute()) {
            header('Location: index.php');
            exit;
        } else {
            $errors[] = 'Erro ao salvar o post.';
        }
    }
}
?>
<!doctype html>
<html lang="pt-br">
<head>
<meta charset="utf-8">
<title>Criar Post</title>
<link rel="stylesheet" href="styles.css">
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

.post-container {
    background: linear-gradient(180deg, #29004b, #3d0073);
    padding: 40px 50px;
    border-radius: 18px;
    width: 420px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.5);
}

h1 {
    margin-top: 0;
    color: #f4e9ff;
    text-align: center;
}

.error {
    background-color: rgba(255, 50, 50, 0.25);
    padding: 8px;
    border-radius: 6px;
    margin-bottom: 12px;
    color: #ff9a9a;
}

input, textarea {
    width: 100%;
    padding: 12px 16px;
    margin-bottom: 15px;
    border-radius: 14px;
    border: none;
    outline: none;
    background: #000;
    color: #fff;
    font-size: 15px;
}

textarea {
    resize: vertical;
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
    display: block;
    text-align: center;
    margin-top: 15px;
    color: #d3aaff;
    text-decoration: none;
}

.voltar:hover {
    text-decoration: underline;
}
</style>
</head>
<body>

<div class="post-container">
    <h1>Criar Post</h1>

    <?php foreach ($errors as $e): ?>
        <div class="error"><?= htmlspecialchars($e) ?></div>
    <?php endforeach; ?>

    <form method="post">
        <input
            type="text"
            name="title"
            placeholder="Título do post"
            value="<?= htmlspecialchars($title) ?>"
            required
        >

        <textarea
            name="content"
            rows="8"
            placeholder="Conteúdo do post"
            required
        ><?= htmlspecialchars($content) ?></textarea>

        <button type="submit">Publicar</button>
    </form>

    <a class="voltar" href="index.php">← Voltar</a>
</div>

</body>
</html>
