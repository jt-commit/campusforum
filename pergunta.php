<?php
session_start();
require 'db.php';

$mysqli = db_connect();

/* =======================
   POST
======================= */
$post_id = (int)($_GET['id'] ?? 0);
if ($post_id <= 0) {
    die('Post inválido');
}

$stmt = $mysqli->prepare("
    SELECT p.*, u.username
    FROM posts p
    JOIN users u ON u.id = p.user_id
    WHERE p.id = ?
");
$stmt->bind_param('i', $post_id);
$stmt->execute();
$result = $stmt->get_result();
$post = $result->fetch_assoc();

if (!$post) {
    die('Post não encontrado');
}

/* =======================
   POST SALVO
======================= */
$is_saved = false;
if (!empty($_SESSION['user'])) {
    $uid = $_SESSION['user']['id'];
    $chk = $mysqli->prepare("
        SELECT 1 
        FROM saved_posts 
        WHERE user_id = ? AND post_id = ?
    ");
    $chk->bind_param('ii', $uid, $post_id);
    $chk->execute();
    $chk->store_result();
    $is_saved = $chk->num_rows > 0;
}

/* =======================
   PESQUISA
======================= */
$search = $_GET['q'] ?? '';

/* =======================
   CAMINHO DA IMAGEM
======================= */
$image_path = '';
if (!empty($post['image'])) {
    $image_path = '/campusforum/uploads/posts/' . $post['image'];
}

/* =======================
   INSERIR COMENTÁRIO
======================= */
if (!empty($_SESSION['user']) && $_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['comment'])) {
    $comment_content = trim($_POST['comment']);
    if ($comment_content !== '') {
        $ins = $mysqli->prepare("INSERT INTO comments (post_id, user_id, content) VALUES (?, ?, ?)");
        $ins->bind_param('iis', $post_id, $_SESSION['user']['id'], $comment_content);
        $ins->execute();
        // Redireciona para evitar reenvio do formulário
        header("Location: pergunta.php?id=" . $post_id);
        exit;
    }
}

/* =======================
   EXCLUIR COMENTÁRIO
======================= */
if (!empty($_SESSION['user']) && isset($_GET['delete_comment'])) {
    $comment_id = (int)$_GET['delete_comment'];
    $del = $mysqli->prepare("DELETE FROM comments WHERE id = ? AND user_id = ?");
    $del->bind_param('ii', $comment_id, $_SESSION['user']['id']);
    $del->execute();
    header("Location: pergunta.php?id=" . $post_id);
    exit;
}

/* =======================
   BUSCAR COMENTÁRIOS
======================= */
$cstmt = $mysqli->prepare("
    SELECT c.*, u.username
    FROM comments c
    JOIN users u ON u.id = c.user_id
    WHERE c.post_id = ?
    ORDER BY c.created_at ASC
");
$cstmt->bind_param('i', $post_id);
$cstmt->execute();
$comments = $cstmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!doctype html>
<html lang="pt-br">
<head>
<meta charset="utf-8">
<title><?= htmlspecialchars($post['title']) ?> - Campus Forum</title>
<link rel="stylesheet" href="styles.css">
<link rel="stylesheet" href="index_style.css">
<link rel="stylesheet" href="post_style.css">

<style>
.post-container{
    max-width:900px;
    margin:40px auto;
    background:linear-gradient(180deg, #6e22c3, #3c006d);
    border-radius:16px;
    padding:30px;
    position:relative;
    color:#ffffff;
}
.post-meta{
    display:flex;
    justify-content:space-between;
    font-size:13px;
    color:#ffffff;
    margin-bottom:15px;
}
.post-edited{
    font-size:12px;
    color:#ffffff;
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
    color:#ffffff;
    margin-right:15px;
    text-decoration:none;
}
.post-actions a:hover{
    text-decoration:underline;
}
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
.search-box input{
    width:75%;
    padding:14px 24px;
    background:#000;
    color:#fff;
    border-radius:30px;
    border:none;
}
.btn-logout{
    background:#b91c1c;        /* vermelho */
    color:#fff !important;
    padding:8px 14px;
    border-radius:10px;
    text-decoration:none;
    font-weight:600;
    transition:.2s;
}
</style>
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
<!-- POST -->
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

    <?php if ($image_path): ?>
    <div class="post-image">
        <img src="<?= htmlspecialchars($image_path) ?>" alt="Imagem do post">
    </div>
    <?php endif; ?>

    <div>
        <?= nl2br(htmlspecialchars($post['content'])) ?>
    </div>

    <?php if (!empty($_SESSION['user']) && $_SESSION['user']['id'] === $post['user_id']): ?>
    <div class="post-actions">
        <a href="edit_post.php?id=<?= $post_id ?>">
            <img src="lapis2.png" alt="Editar" style="width:20px;height:20px;vertical-align:middle;margin-right:6px;transform:translateY(1px);">Editar
        </a>
        <a href="#" onclick="deletePost(<?= $post_id ?>); return false;" style="margin-left:10px;">
            <img src="lixeira.png" alt="Excluir" style="width:20px;height:20px;vertical-align:middle;margin-right:6px;transform:translateY(1px);">Excluir
        </a>
    </div>
    <?php endif; ?>
</div> <!-- Fim post-container -->

<!-- COMENTÁRIOS -->
<div class="comments-container" style="max-width:900px;margin:20px auto 60px auto;">
    <h2>Comentários</h2>

    <?php if (!empty($_SESSION['user'])): ?>
    <form method="post" style="margin-bottom:20px;">
        <textarea name="comment" rows="3" 
                  style="width:100%;padding:10px;border-radius:8px;border:1px solid #444;background:#0b0018;color:#fff;" 
                  placeholder="Escreva seu comentário..."></textarea>
        <button type="submit" 
                style="margin-top:10px;padding:8px 16px;border:none;border-radius:8px;background:#6d28d9;color:#fff;cursor:pointer;">
            Comentar
        </button>
    </form>
    <?php else: ?>
        <p>Você precisa <a href="login.php">entrar</a> para comentar.</p>
    <?php endif; ?>

    <?php if ($comments): ?>
        <?php foreach ($comments as $c): ?>
            <div class="comment" style="padding:12px 16px;margin-bottom:10px;border-radius:8px;background:#1b002f;color:#f4e9ff;position:relative;">
                <strong><?= htmlspecialchars($c['username']) ?></strong> 
                <span style="font-size:12px;color:#aaa;">- <?= date('d/m/Y H:i', strtotime($c['created_at'])) ?></span>
                <p style="margin:6px 0;"><?= nl2br(htmlspecialchars($c['content'])) ?></p>
                <?php if (!empty($_SESSION['user']) && $_SESSION['user']['id'] === $c['user_id']): ?>
                          <a href="pergunta.php?id=<?= $post_id ?>&delete_comment=<?= $c['id'] ?>" 
                              style="position:absolute;top:10px;right:10px;"
                              onclick="return confirm('Excluir este comentário?')">
                                <img src="lixeira.png" alt="Excluir" style="width:18px;height:18px;vertical-align:middle;margin-right:4px;transform:translateY(2px);">
                          </a>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>Nenhum comentário ainda. Seja o primeiro!</p>
    <?php endif; ?>
</div>



<script>
function deletePost(id) {
    if (!confirm('Excluir esta pergunta?')) return;

    var form = new URLSearchParams();
    form.append('id', id);

    fetch('delete_post.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: form.toString()
    })
    .then(function(res){ return res.json(); })
    .then(function(data){
        if (data.success) {
            window.location.href = 'index.php';
        } else {
            alert(data.message || 'Erro ao excluir');
        }
    })
    .catch(function(){ alert('Erro ao excluir'); });
}
</script>



