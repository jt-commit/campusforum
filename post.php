<?php
session_start();
require_once 'db.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

$mysqli = db_connect();

/* =========================
   BUSCA DO POST
========================= */
$postId = intval($_GET['id'] ?? 0);

$stmt = $mysqli->prepare(
    "SELECT p.id, p.title, p.content, p.created_at, p.user_id, u.email
     FROM posts p
     JOIN users u ON p.user_id = u.id
     WHERE p.id = ?"
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
   INSERIR COMENTÁRIO
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_SESSION['user'])) {

    $content = trim($_POST['content'] ?? '');

    if ($content !== '') {
        $stmt = $mysqli->prepare(
            "INSERT INTO comments (post_id, user_id, content)
             VALUES (?, ?, ?)"
        );
        $stmt->bind_param(
            "iis",
            $postId,
            $_SESSION['user']['id'],
            $content
        );
        $stmt->execute();
        $stmt->close();

        header("Location: post.php?id=" . $postId);
        exit;
    }
}

/* =========================
   LISTAR COMENTÁRIOS
========================= */
$stmt = $mysqli->prepare(
    "SELECT c.content, c.created_at, u.email
     FROM comments c
     JOIN users u ON c.user_id = u.id
     WHERE c.post_id = ?
     ORDER BY c.created_at ASC"
);
$stmt->bind_param("i", $postId);
$stmt->execute();
$comments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!doctype html>
<html lang="pt-br">
<head>
<meta charset="utf-8">
<title><?= htmlspecialchars($post['title']) ?> — CampusForumFX</title>

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
    display: flex;
    justify-content: space-between;
    align-items: center;
}

header a {
    color: #d3aaff;
    text-decoration: none;
    margin-left: 15px;
}

main {
    max-width: 900px;
    margin: 40px auto;
    padding: 0 20px;
}

article {
    background: #2d0059;
    padding: 30px;
    border-radius: 18px;
    box-shadow: 0 6px 20px rgba(0,0,0,0.4);
}

article h1 {
    margin-top: 0;
    color: #f4e9ff;
}

.meta {
    font-size: 14px;
    color: #d3aaff;
    margin-bottom: 20px;
}

.content {
    line-height: 1.6;
    color: #fff;
}

section.comments {
    margin-top: 40px;
}

.comment {
    background: #240046;
    padding: 15px 18px;
    border-radius: 12px;
    margin-bottom: 15px;
}

.comment strong {
    color: #f4e9ff;
}

textarea {
    width: 100%;
    min-height: 100px;
    padding: 12px;
    border-radius: 12px;
    border: none;
    outline: none;
    background: #000;
    color: #fff;
}

button {
    margin-top: 10px;
    padding: 10px 22px;
    border: none;
    border-radius: 10px;
    background: linear-gradient(180deg, #7c3aed, #5b21b6);
    color: #fff;
    cursor: pointer;
}

button:hover {
    background: linear-gradient(180deg, #9d4edd, #6a0dad);
}
</style>
</head>
<body>

<header>
    <a href="index.php">← Voltar ao fórum</a>

    <nav>
        <?php if (!empty($_SESSION['user'])): ?>
            <span><?= htmlspecialchars($_SESSION['user']['email']) ?></span>
            <a href="logout.php">Sair</a>
        <?php else: ?>
            <a href="login.php">Entrar</a>
            <a href="register.php">Registrar</a>
        <?php endif; ?>
    </nav>
</header>

<main>

<article>
    <h1><?= htmlspecialchars($post['title']) ?></h1>
    <p class="meta">
        por <?= htmlspecialchars($post['email']) ?> em
        <?= date('d/m/Y H:i', strtotime($post['created_at'])) ?>
    </p>

    <div class="content">
        <?= nl2br(htmlspecialchars($post['content'])) ?>
    </div>

    <?php if (!empty($_SESSION['user']) && $_SESSION['user']['id'] === $post['user_id']): ?>
        <p>
            <a href="edit_post.php?id=<?= $post['id'] ?>">Editar</a> |
            <a href="delete_post.php?id=<?= $post['id'] ?>"
               onclick="return confirm('Tem certeza que deseja excluir este post?')">
               Excluir
            </a>
        </p>
    <?php endif; ?>
</article>

<section class="comments">
    <h2>Comentários</h2>

    <?php foreach ($comments as $c): ?>
        <div class="comment">
            <strong><?= htmlspecialchars($c['email']) ?></strong><br>
            <small><?= date('d/m/Y H:i', strtotime($c['created_at'])) ?></small>
            <div><?= nl2br(htmlspecialchars($c['content'])) ?></div>
        </div>
    <?php endforeach; ?>

    <?php if (!empty($_SESSION['user'])): ?>
        <form method="post">
            <textarea name="content" placeholder="Escreva seu comentário..." required></textarea>
            <button type="submit">Enviar comentário</button>
        </form>
    <?php else: ?>
        <p><a href="login.php">Entre</a> para comentar.</p>
    <?php endif; ?>
</section>

</main>

</body>
</html>
