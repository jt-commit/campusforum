<?php
session_start();
require 'db.php';

if (empty($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$mysqli = db_connect();
$uid = $_SESSION['user']['id'];

/* BUSCAR AVATAR DO USUÁRIO */
$stmt = $mysqli->prepare("SELECT avatar FROM users WHERE id = ?");
$stmt->bind_param("i", $uid);
$stmt->execute();
$res = $stmt->get_result();
$user_data = $res->fetch_assoc();
$avatar = $user_data['avatar'] ?? '';
if (empty($avatar)) {
    $avatar = 'https://ui-avatars.com/api/?name='.urlencode($_SESSION['user']['username']).'&background=7c3aed&color=fff';
}

/* BUSCAR POSTS SALVOS */
$stmt = $mysqli->prepare("
    SELECT p.*, u.username
    FROM saved_posts s
    JOIN posts p ON p.id = s.post_id
    JOIN users u ON u.id = p.user_id
    WHERE s.user_id = ?
    ORDER BY s.saved_at DESC
");
$stmt->bind_param("i", $uid);
$stmt->execute();
$res = $stmt->get_result();
$posts = $res->fetch_all(MYSQLI_ASSOC);
?>
<!doctype html>
<html lang="pt-br">
<head>
<meta charset="utf-8">
<title>Salvos - Campus Forum</title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<link rel="stylesheet" href="styles.css">
<link rel="stylesheet" href="index_style.css">

<style>
body { font-family: 'Poppins', sans-serif; }

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

/* MESMO CSS DO BOTÃO DO INDEX */
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
</style>
</head>

<body>

<header>
    <a href="/campusforumatl/index.php">
        <img src="/campusforumatl/campusforumlogox.png" width="140">
    </a>

    <div class="search-box"></div>

    <nav class="nav-user">
        <?php if (!empty($_SESSION['user'])): ?>
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

<h2 style="text-align:center;margin:20px 0;">
    ⭐ Minhas Perguntas Salvas
</h2>

<?php if (empty($posts)): ?>
<p style="text-align:center;">Você ainda não salvou nenhuma pergunta.</p>
<?php endif; ?>

<?php foreach ($posts as $post):
$img = !empty($post['image']) ? '/campusforumatl/uploads/posts/'.$post['image'] : '';
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
        <?= nl2br(htmlspecialchars(mb_strimwidth($post['content'], 0, 300, '...'))) ?>
    </p>

    <div class="post-actions">
        <a href="pergunta.php?id=<?= $post['id'] ?>">Ler mais</a>

        <button class="btn-save-post saved"
                data-post-id="<?= $post['id'] ?>">
            <svg class="icon-save" viewBox="0 0 24 24">
                <path class="fill"
                      d="M6 2h12a1 1 0 0 1 1 1v19l-7-5-7 5z"/>
                <path class="stroke"
                      d="M6 2h12a1 1 0 0 1 1 1v19l-7-5-7 5z"/>
            </svg>
        </button>
    </div>
</div>
</article>

<?php endforeach; ?>

</main>

<script>
document.querySelectorAll('.btn-save-post').forEach(btn=>{
    btn.addEventListener('click', async ()=>{
        const res = await fetch('save_post.php', {
            method:'POST',
            headers:{'Content-Type':'application/json'},
            body:JSON.stringify({ post_id: btn.dataset.postId })
        });
        const data = await res.json();
        if (!data.saved) {
            btn.closest('.post-card').remove();
        }
    });
});
</script>

</body>
</html>
