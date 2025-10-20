<?php
session_start();
require 'db.php';
if (empty($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');

    if ($title === '' || $content === '') {
        $errors[] = 'Preencha título e conteúdo';
    }

    if (empty($errors)) {
        $mysqli = db_connect();
        $stmt = $mysqli->prepare('INSERT INTO posts(user_id, title, content) VALUES(?,?,?)');
        $stmt->bind_param('iss', $_SESSION['user']['id'], $title, $content);

        if ($stmt->execute()) {
            header('Location: index.php');
            exit;
        } else {
            $errors[] = 'Erro ao salvar post';
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
</head>
<body>
<h1>Criar Post</h1>
<?php foreach ($errors as $e) echo "<p class='error'>" . htmlspecialchars($e) . "</p>"; ?>
<form method="post">
<label>Título: <input name="title" required></label><br>
<label>Conteúdo:<br><textarea name="content" rows="8" required></textarea></label><br>
<button>Publicar</button>
</form>
<p><a href="index.php">Voltar</a></p>
</body>
</html>
