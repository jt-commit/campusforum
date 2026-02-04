<?php
session_start();
require 'db.php';

$mysqli = db_connect();

/* =======================
   PESQUISA
======================= */
$search = $_GET['q'] ?? '';

if ($search) {
    $stmt = $mysqli->prepare("
        SELECT p.*, u.username
        FROM posts p
        JOIN users u ON u.id = p.user_id
        WHERE p.title LIKE CONCAT('%', ?, '%')
           OR p.content LIKE CONCAT('%', ?, '%')
        ORDER BY p.created_at DESC
    ");
    $stmt->bind_param('ss', $search, $search);
    $stmt->execute();
    $res = $stmt->get_result();
} else {
    $res = $mysqli->query("
        SELECT p.*, u.username
        FROM posts p
        JOIN users u ON u.id = p.user_id
        ORDER BY p.created_at DESC
    ");
}

$posts = $res->fetch_all(MYSQLI_ASSOC);

/* =======================
   FAVORITOS
======================= */
$favoritos = [];
if (!empty($_SESSION['user'])) {
    $uid = $_SESSION['user']['id'];
    $q = $mysqli->prepare("SELECT post_id FROM saved_posts WHERE user_id = ?");
    $q->bind_param('i', $uid);
    $q->execute();
    $r = $q->get_result();
    while ($row = $r->fetch_assoc()) {
        $favoritos[] = $row['post_id'];
    }
}
?>
<!doctype html>
<html lang="pt-br">
<head>
<meta charset="utf-8">
<title>Campus Forum</title>
<link rel="stylesheet" href="styles.css">
<link rel="stylesheet" href="index_style.css">
</head>

<body>

<header>
    <a href="/campusforum/index.php">
        <img src="/campusforum/campusforumlogox.png" width="140" alt="Campus Forum">
    </a>

    <div class="search-box">
        <form method="get" class="search-form">
            <button type="submit" class="search-icon" aria-label="Pesquisar">
                <img src="/campusforum/lupa1.png" alt="">
            </button>
            <input type="text" name="q" placeholder="Pesquisar no fórum..."
                   value="<?= htmlspecialchars($search) ?>">
        </form>
    </div>

    <nav>
        <?php if (!empty($_SESSION['user'])): ?>
            <span>Olá, <strong><?= htmlspecialchars($_SESSION['user']['username']) ?></strong></span>
            <a href="create_pergunta.php" class="btn-novo">Perguntar</a>
           <a href="logout.php" class="btn-logout">Sair</a>


        <?php else: ?>
            <a href="login.php" class="btn-novo">Entrar</a>
            <a href="register.php" class="btn-novo">Registrar</a>
        <?php endif; ?>
    </nav>
</header>

<main>

<?php foreach ($posts as $post): 
    $img = !empty($post['image'])
        ? '/campusforum/uploads/posts/'.$post['image']
        : '';
    $saved = in_array($post['id'], $favoritos);
?>
<article class="post-card<?= $img ? ' has-image' : '' ?>">

<?php if ($img): ?>
<div class="post-image">
    <img src="<?= htmlspecialchars($img) ?>" alt="Imagem do post">
    <div class="post-overlay"></div>

    <div class="post-title">
        <h2>
                <a href="pergunta.php?id=<?= $post['id'] ?>">
                <?= htmlspecialchars($post['title']) ?>
            </a>
        </h2>
    </div>
</div>
<?php endif; ?>

<div class="post-body">

    <span class="post-meta">
        por <?= htmlspecialchars($post['username']) ?> •
        <?= date('d/m/Y H:i', strtotime($post['created_at'])) ?>
    </span>

    <p>
        <?= nl2br(htmlspecialchars(mb_strimwidth($post['content'], 0, 350, '...'))) ?>
    </p>

    <div class="post-actions">
        <a href="pergunta.php?id=<?= $post['id'] ?>">Ler mais</a>

        <?php if (!empty($_SESSION['user'])): ?>
        <button class="btn-save <?= $saved ? 'saved' : '' ?>"
                data-post-id="<?= $post['id'] ?>">
            <svg class="icon-save" viewBox="0 0 24 24">
                <path class="fill"
                      d="M6 2h12a1 1 0 0 1 1 1v19l-7-5-7 5V3a1 1 0 0 1 1-1z"/>
                <path class="stroke"
                      d="M6 2h12a1 1 0 0 1 1 1v19l-7-5-7 5V3a1 1 0 0 1 1-1z"/>
            </svg>
        </button>
        <?php endif; ?>
    </div>

</div>
</article>
<?php endforeach; ?>

</main>

<script>
document.querySelectorAll('.btn-save').forEach(btn=>{
    btn.addEventListener('click', async ()=>{
        const res = await fetch('save_post.php', {
            method:'POST',
            headers:{'Content-Type':'application/json'},
            body:JSON.stringify({ post_id: btn.dataset.postId })
        });
        const data = await res.json();
        btn.classList.toggle('saved', data.saved);
    });
});
</script>

</body>
</html>

