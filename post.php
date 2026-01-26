<?php
session_start();
require 'db.php';

$mysqli = db_connect();

/* =======================
   POST
======================= */
$post_id = (int)($_GET['id'] ?? 0);
if ($post_id <= 0) die('Post inválido');

$stmt = $mysqli->prepare("
    SELECT p.*, u.username
    FROM posts p
    JOIN users u ON u.id = p.user_id
    WHERE p.id = ?
");
$stmt->bind_param('i', $post_id);
$stmt->execute();
$post = $stmt->get_result()->fetch_assoc();

if (!$post) die('Post não encontrado');

/* =======================
   POST SALVO
======================= */
$is_saved = false;
if (!empty($_SESSION['user'])) {
    $uid = $_SESSION['user']['id'];
    $chk = $mysqli->prepare("SELECT 1 FROM saved_posts WHERE user_id=? AND post_id=?");
    $chk->bind_param('ii', $uid, $post_id);
    $chk->execute();
    $chk->store_result();
    $is_saved = $chk->num_rows > 0;
}

/* =======================
   PESQUISA (HEADER)
======================= */
$search = $_GET['q'] ?? '';
?>
<!doctype html>
<html lang="pt-br">
<head>
<meta charset="utf-8">
<title><?= htmlspecialchars($post['title']) ?> - Campus Forum</title>
<link rel="stylesheet" href="styles.css">

<style>
.post-container{
    max-width:900px;
    margin:40px auto;
    background:#120028;
    border-radius:16px;
    padding:30px;
    position:relative;
    color:#f9d76e;
}

.post-meta{
    display:flex;
    justify-content:space-between;
    font-size:13px;
    color:#bca7ff;
    margin-bottom:15px;
}

.post-edited{
    font-size:12px;
    color:#ffb703;
    margin-bottom:20px;
}

.post-image img{
    width:100%;
    border-radius:12px;
    margin-bottom:20px;
}

.post-actions{
    margin-top:25px;
    border-top:1px solid #3c006d;
    padding-top:15px;
}

.post-actions a{
    color:#b085ff;
    margin-right:15px;
    text-decoration:none;
}

.post-actions a:hover{
    text-decoration:underline;
}

/* BOTÃO SALVAR */
.btn-save-post{
    position:absolute;
    top:20px;
    right:20px;
    background:none;
    border:none;
    cursor:pointer;
}

.icon-save{ width:26px; height:26px; }
.icon-save .fill{ fill:transparent; transition:.2s; }
.icon-save .stroke{ fill:none; stroke:white; stroke-width:2; }
.btn-save-post.saved .fill{ fill:white; }

/* SEARCH */
.search-box input{
    width:300px;
    padding:10px 20px;
    background:#000;
    color:#fff;
    border-radius:30px;
    border:none;
}
</style>
</head>

<body>

<header>
    <a href="/campusforum/index.php">
        <img src="campusforumlogox.png" width="140">   <!--Inserido link para home page -->

    </a>

<div class="search-box">
<form method="get" action="index.php">
    <input type="text" name="q" placeholder="Pesquisar no fórum..." value="<?= htmlspecialchars($search) ?>">
</form>
</div>

<nav class="right">
<?php if (!empty($_SESSION['user'])): ?>
    <span>Olá, <strong><?= htmlspecialchars($_SESSION['user']['username']) ?></strong></span>
    <a href="create_post.php" class="btn-novo">Criar Post</a>
    <a href="logout.php" class="logout">Sair</a>
<?php else: ?>
    <a href="login.php" class="btn-novo">Entrar</a>
    <a href="register.php" class="btn-novo">Registrar</a>
<?php endif; ?>
</nav>

</header>

<main>

<div class="post-container">

<?php if (!empty($_SESSION['user'])): ?>
<button class="btn-save-post <?= $is_saved ? 'saved' : '' ?>" data-post-id="<?= $post_id ?>">
<svg class="icon-save" viewBox="0 0 24 24">
    <path class="fill" d="M6 2h12a1 1 0 0 1 1 1v19l-7-5-7 5V3a1 1 0 0 1 1-1z"/>
    <path class="stroke" d="M6 2h12a1 1 0 0 1 1 1v19l-7-5-7 5V3a1 1 0 0 1 1-1z"/>
</svg>
</button>
<?php endif; ?>

<h1><?= htmlspecialchars($post['title']) ?></h1>

<div class="post-meta">
<span>por <?= htmlspecialchars($post['username']) ?></span>
<span><?= date('d/m/Y H:i', strtotime($post['created_at'])) ?></span>
</div>

<?php if (!empty($post['updated_at'])): ?>
<div class="post-edited">
✏️ Editado em <?= date('d/m/Y H:i', strtotime($post['updated_at'])) ?>
</div>
<?php endif; ?>

<?php if (!empty($post['image'])): ?>
<div class="post-image">
<img src="<?= htmlspecialchars($post['image']) ?>">
</div>
<?php endif; ?>

<div>
<?= nl2br(htmlspecialchars($post['content'])) ?>
</div>

<?php if (!empty($_SESSION['user']) && $_SESSION['user']['id'] === $post['user_id']): ?>
<div class="post-actions">
<a href="edit_post.php?id=<?= $post_id ?>">✏️ Editar</a>
<a href="delete_post.php?id=<?= $post_id ?>" onclick="return confirm('Excluir este post?')">🗑️ Excluir</a>
</div>
<?php endif; ?>

</div>

</main>

<footer style="text-align:center;color:#777;margin:40px 0;">
&copy; <?= date('Y') ?> Campus Forum
</footer>

<script>
document.querySelector('.btn-save-post')?.addEventListener('click', async e => {
    const btn = e.currentTarget;
    const postId = btn.dataset.postId;

    const res = await fetch('save_post.php', {
        method:'POST',
        headers:{'Content-Type':'application/json'},
        body:JSON.stringify({post_id:postId})
    });

    const data = await res.json();
    btn.classList.toggle('saved', data.saved);
});
</script>

</body>
</html>
