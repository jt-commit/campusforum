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

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<link rel="stylesheet" href="styles.css">
<link rel="stylesheet" href="index_style.css">

<style>
body { font-family: 'Poppins', sans-serif; }

/* BOTÃO SALVAR */
.btn-save-post {
    position: absolute;
    top: 14px;
    right: 14px;
    background-color: transparent !important;
    border: none !important;
    box-shadow: none !important;
    padding: 0 !important;
    cursor: pointer;
    z-index: 10;
}
.btn-save-post:focus,
.btn-save-post:active {
    outline: none;
    background: transparent;
}
.icon-save {
    width: 26px;
    height: 26px;
    display: block;
}
.icon-save .fill {
    fill: transparent;
    transition: 0.25s ease;
}
.icon-save .stroke {
    fill: none;
    stroke: white;
    stroke-width: 2;
}
.btn-save-post:hover .icon-save {
    transform: scale(1.1);
}
.btn-save-post.saved .fill {
    fill: white;
}
.nav-user {
    display: flex;
    align-items: center;
    gap: 12px;
}

.nav-avatar {
    width: 42px;
    height: 42px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #7b3fe4;
    transition: 0.25s;
}

.nav-avatar:hover {
    transform: scale(1.1);
}

</style>
</head>

<body>

<header>
    <a href="/campusforumatl/index.php">
        <img src="/campusforumatl/campusforumlogox.png" width="140">
    </a>

    <div class="search-box">
        <form method="get">
            <input type="text" name="q" placeholder="Pesquisar no fórum..."
                   value="<?= htmlspecialchars($search) ?>">
        </form>
    </div>

    <nav class="nav-user">
<?php if (!empty($_SESSION['user'])): ?>
    <?php
    $uid = $_SESSION['user']['id'];
    $q = $mysqli->prepare("SELECT avatar FROM users WHERE id = ?");
    $q->bind_param("i", $uid);
    $q->execute();
    $u = $q->get_result()->fetch_assoc();

    $avatar = !empty($u['avatar'])
        ? "/campusforumatl/uploads/avatars/".$u['avatar']
        : "https://ui-avatars.com/api/?name=".urlencode($_SESSION['user']['username'])."&background=7b3fe4&color=fff";
    ?>

    <a href="perfil.php" class="avatar-link">
        <img src="<?= htmlspecialchars($avatar) ?>" class="nav-avatar">
    </a>

    <a href="salvos.php" class="btn-novo">⭐</a>
    <a href="create_pergunta.php" class="btn-novo">+</a>
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
        ? '/campusforumatl/uploads/posts/'.$post['image']
        : '';
    $saved = in_array($post['id'], $favoritos);
?>
<article class="post-card<?= $img ? ' has-image' : '' ?>">

<?php if ($img): ?>
<div class="post-image">
    <img src="<?= htmlspecialchars($img) ?>">
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
        <button class="btn-save-post <?= $saved ? 'saved' : '' ?>"
                data-post-id="<?= $post['id'] ?>">
            <svg class="icon-save" viewBox="0 0 24 24">
                <path class="fill"
                      d="M6 2h12a1 1 0 0 1 1 1v19l-7-5-7 5z"/>
                <path class="stroke"
                      d="M6 2h12a1 1 0 0 1 1 1v19l-7-5-7 5z"/>
            </svg>
        </button>
        <?php endif; ?>
    </div>
</div>
</article>
<?php endforeach; ?>

</main>

<?php if (empty($posts)): ?>
<div style="text-align: center; padding: 40px; color: #aaa;">
    <p>Nenhum post encontrado<?= !empty($search) ? ' para "' . htmlspecialchars($search) . '"' : '' ?></p>
</div>
<?php endif; ?>

<script>
document.querySelectorAll('.btn-save-post').forEach(btn=>{
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
