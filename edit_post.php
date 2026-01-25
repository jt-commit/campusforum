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
    background: #000;
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
