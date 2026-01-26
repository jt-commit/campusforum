<?php
session_start();
require_once 'db.php';

$mysqli = db_connect();

/* ===============================
   MENSAGEM DE RETORNO (DELETE)
================================ */
$flash = '';
if (!empty($_GET['msg']) && $_GET['msg'] === 'deleted') {
    $flash = 'Post excluído com sucesso.';
}

/* ===============================
   PESQUISA + LISTAGEM
================================ */
$search = $_GET['q'] ?? '';
$userId = $_SESSION['user']['id'] ?? 0;

if (!empty($search)) {
    $stmt = $mysqli->prepare("
        SELECT 
            p.*,
            u.username,
            IF(sp.id IS NULL, 0, 1) AS is_saved
        FROM posts p
        JOIN users u ON u.id = p.user_id
        LEFT JOIN saved_posts sp 
            ON sp.post_id = p.id AND sp.user_id = ?
        WHERE p.title LIKE CONCAT('%', ?, '%')
           OR p.content LIKE CONCAT('%', ?, '%')
        ORDER BY p.created_at DESC
    ");
    $stmt->bind_param('iss', $userId, $search, $search);
} else {
    $stmt = $mysqli->prepare("
        SELECT 
            p.*,
            u.username,
            IF(sp.id IS NULL, 0, 1) AS is_saved
        FROM posts p
        JOIN users u ON u.id = p.user_id
        LEFT JOIN saved_posts sp 
            ON sp.post_id = p.id AND sp.user_id = ?
        ORDER BY p.created_at DESC
    ");
    $stmt->bind_param('i', $userId);
}

$stmt->execute();
$res   = $stmt->get_result();
$posts = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
?>
<!doctype html>
<html lang="pt-br">
<head>
<meta charset="utf-8">
<title>Campus Forum - Home</title>

<link rel="stylesheet" href="styles.css">

<style>
/* ===== SEARCH ===== */
.search-box input{
    width:330px;
    padding:12px 22px;
    background:#000;
    color:#fff;
    border-radius:30px;
    border:none;
}

/* ===== BOTÃO SAIR ===== */
.btn-logout{
    background:#7f1d1d;
    color:#fee2e2;
    padding:10px 18px;
    border-radius:8px;
    margin-left:12px;
    text-decoration:none;
}
.btn-logout:hover{ background:#991b1b; }

/* ===== FLASH ===== */
.flash{
    margin:20px;
    padding:12px 18px;
    background:#14532d;
    color:#dcfce7;
    border-radius:8px;
}

/* ===== BOTÃO SALVAR ===== */
.btn-save-post{
    position:absolute;
    top:14px;
    right:14px;
    background:transparent;
    border:none;
    cursor:pointer;
}
.icon-save{ width:26px; height:26px; }
.icon-save .fill{ fill:transparent; transition:.25s; }
.icon-save .stroke{ fill:none; stroke:white; stroke-width:2; }
.btn-save-post.saved .fill{ fill:white; }
</style>
</head>

<body>

<header>
    <a href="/campusforum/index.php">  <img src="campusforumlogox.png" width="150"  alt="Campus Forum"> </a>

    <div class="search-box">
        <form method="GET">
            <input
                type="text"
                name="q"
                placeholder="Pesquisar no fórum..."
                value="<?= htmlspecialchars($search) ?>"
            >
        </form>
    </div>

    <nav class="right">
        <?php if (!empty($_SESSION['user'])): ?>
            <span class="user">
                Olá, <strong><?= htmlspecialchars($_SESSION['user']['username']) ?></strong>
            </span>
            <a href="create_post.php" class="btn-novo">Criar Post</a>
            <a href="logout.php" class="btn-logout">Sair</a>
        <?php else: ?>
            <a href="login.php" class="btn-novo">Entrar</a>
            <a href="register.php" class="btn-novo">Registrar</a>
        <?php endif; ?>
    </nav>
</header>

<main>
<section>

<?php if ($flash): ?>
    <div class="flash"><?= $flash ?></div>
<?php endif; ?>

<?php if (empty($posts)): ?>
    <p style="margin-left:20px;color:white;">Nenhum post encontrado.</p>
<?php endif; ?>

<?php foreach ($posts as $p): ?>
<article class="topico" style="position:relative;">

<?php if (!empty($_SESSION['user'])): ?>
<button class="btn-save-post <?= $p['is_saved'] ? 'saved' : '' ?>"
        data-post-id="<?= $p['id'] ?>"
        title="<?= $p['is_saved'] ? 'Remover dos salvos' : 'Salvar post' ?>">

<svg class="icon-save" viewBox="0 0 24 24">
    <path class="fill"
        d="M6 2h12a1 1 0 0 1 1 1v19l-7-5-7 5V3a1 1 0 0 1 1-1z"/>
    <path class="stroke"
        d="M6 2h12a1 1 0 0 1 1 1v19l-7-5-7 5V3a1 1 0 0 1 1-1z"/>
</svg>
</button>
<?php endif; ?>

<h2>
    <a href="post.php?id=<?= $p['id'] ?>">
        <?= htmlspecialchars($p['title']) ?>
    </a>
</h2>

<p>
    <?= nl2br(htmlspecialchars(substr($p['content'],0,200))) ?>
    <?= strlen($p['content']) > 200 ? '...' : '' ?>
</p>

<div class="topico-footer">
    <span>por <?= htmlspecialchars($p['username']) ?></span>
    <span><?= date('d/m/Y H:i', strtotime($p['created_at'])) ?></span>
</div>

</article>
<?php endforeach; ?>

</section>
</main>

<footer>
    <p>&copy; <?= date('Y') ?> Campus Forum IFPE - Igarassu</p>
</footer>

<script>
document.querySelectorAll('.btn-save-post').forEach(btn=>{
    btn.addEventListener('click', async e=>{
        e.preventDefault();
        e.stopPropagation();

        const fd = new FormData();
        fd.append('post_id', btn.dataset.postId);
        fd.append('action','toggle');

        const res  = await fetch('save_post.php',{ method:'POST', body:fd });
        const data = await res.json();

        btn.classList.toggle('saved', data.saved);
        btn.title = data.saved ? 'Remover dos salvos' : 'Salvar post';
    });
});
</script>

</body>
</html>
