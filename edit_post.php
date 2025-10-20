<?php
session_start();
require 'db.php';

$id = intval($_GET['id'] ?? 0);
$mysqli = db_connect();

// Pegar o post
$stmt = $mysqli->prepare('SELECT * FROM posts WHERE id=?');
$stmt->bind_param('i', $id);
$stmt->execute();
$res = $stmt->get_result();
$post = $res->fetch_assoc();

if (!$post) {
    http_response_code(404);
    echo 'Post não encontrado';
    exit;
}

// Só o autor pode editar
if (empty($_SESSION['user']) || $_SESSION['user']['id'] !== $post['user_id']) {
    http_response_code(403);
    echo 'Acesso negado';
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
        $stmt = $mysqli->prepare('UPDATE posts SET title=?, content=? WHERE id=?');
        $stmt->bind_param('ssi', $title, $content, $id);
        if ($stmt->execute()) {
            header('Location: post.php?id=' . $id);
            exit;
        } else {
            $errors[] = 'Erro ao atualizar o post';
        }
    }
}
?>
<!doctype html>
<html lang="pt-br">
<head>
<meta charset="utf-8">
<title>Editar Post</title>
<link rel="stylesheet" href="styles.css">
</head>
<body>
<h1>Editar Post</h1>
<?php foreach ($errors as $e) echo "<p class='error'>" . htmlspecialchars($e) . "</p>"; ?>
<form method="post">
<label>Título:<br><input name="title" value="<?= htmlspecialchars($post['title']) ?>" required></label><br>
<label>Conteúdo:<br><textarea name="content" rows="8" required><?= htmlspecialchars($post['content']) ?></textarea></label><br>
<button>Salvar</button>
</form>
<p><a href="post.php?id=<?= $id ?>">Voltar</a></p>
</body>
</html>
