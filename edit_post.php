<?php
session_start();
require_once 'db.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

/* =========================
   AUTENTICAÇÃO
========================= */
if (empty($_SESSION['user'])) {
    http_response_code(403);
    echo "Acesso negado.";
    exit;
}

$postId = intval($_GET['id'] ?? 0);

if ($postId <= 0) {
    http_response_code(400);
    echo "ID inválido.";
    exit;
}

$mysqli = db_connect();

/* =========================
   BUSCAR POST
========================= */
$stmt = $mysqli->prepare(
    "SELECT id, title, content, user_id
     FROM posts
     WHERE id = ?"
);
$stmt->bind_param("i", $postId);
$stmt->execute();
$result = $stmt->get_result();
$post = $result->fetch_assoc();
$stmt->close();

if (!$post) {
    http_response_code(404);
    echo "Post não encontrado.";
    exit;
}

/* =========================
   AUTORIZAÇÃO
========================= */
if ($_SESSION['user']['id'] !== $post['user_id']) {
    http_response_code(403);
    echo "Você não tem permissão para editar este post.";
    exit;
}

$errors = [];

/* =========================
   ATUALIZAR POST
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $title   = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');

    if ($title === '' || $content === '') {
        $errors[] = "Título e conteúdo são obrigatórios.";
    }

    if (empty($errors)) {
        $stmt = $mysqli->prepare(
            "UPDATE posts
             SET title = ?, content = ?
             WHERE id = ?"
        );
        $stmt->bind_param("ssi", $title, $content, $postId);

        if ($stmt->execute()) {
            $stmt->close();
            header("Location: post.php?id=" . $postId);
            exit;
        }

        $stmt->close();
        $errors[] = "Erro ao atualizar o post.";
    }
}
?>
<!doctype html>
<html lang="pt-br">
<head>
<meta charset="utf-8">
<title>Editar Post — CampusForumFX</title>

<style>
body {
    margin: 0;
    font-family: "Poppins", sans-serif;
    background: #1a0033;
    color: #f9d76e;
}

header {
    background: #29004b;
    padding: 15px 30px;
}

header a {
    color: #d3aaff;
    text-decoration: none;
}

main {
    max-width: 800px;
    margin: 40px auto;
    padding: 0 20px;
}

.container {
    background: #2d0059;
    padding: 30px;
    border-radius: 18px;
    box-shadow: 0 6px 20px rgba(0,0,0,0.4);
}

h1 {
    margin-top: 0;
    color: #f4e9ff;
}

.error {
    background: rgba(255, 0, 50, 0.25);
    padding: 10px 14px;
    border-radius: 10px;
    margin-bottom: 15px;
    color: #ffb3b3;
}

input, textarea {
    width: 100%;
    padding: 14px 16px;
    margin-bottom: 15px;
    border-radius: 12px;
    border: none;
    outline: none;
    background: #000;
    color: #fff;
    font-size: 15px;
}

textarea {
    min-height: 180px;
    resize: vertical;
}

button {
    padding: 12px 26px;
    border: none;
    border-radius: 12px;
    background: linear-gradient(180deg, #7c3aed, #5b21b6);
    color: #fff;
    font-size: 16px;
    cursor: pointer;
}

button:hover {
    background: linear-gradient(180deg, #9d4edd, #6a0dad);
}
</style>
</head>
<body>

<header>
    <a href="post.php?id=<?= $postId ?>">← Voltar ao post</a>
</header>

<main>
<div class="container">

    <h1>Editar Post</h1>

    <?php foreach ($errors as $e): ?>
        <div class="error"><?= htmlspecialchars($e) ?></div>
    <?php endforeach; ?>

    <form method="post">
        <input
            type="text"
            name="title"
            placeholder="Título do post"
            value="<?= htmlspecialchars($post['title']) ?>"
            required
        >

        <textarea
            name="content"
            placeholder="Conteúdo do post"
            required
        ><?= htmlspecialchars($post['content']) ?></textarea>

        <button type="submit">Salvar alterações</button>
    </form>

</div>
</main>

</body>
</html>
