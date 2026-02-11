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
    $image_path = '/campusforumatl/uploads/posts/' . $post['image'];
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
    SELECT c.*, u.username, 
    COALESCE(COUNT(DISTINCT cl.id), 0) as likes,
    COALESCE(COUNT(DISTINCT cd.id), 0) as dislikes
    FROM comments c
    JOIN users u ON u.id = c.user_id
    LEFT JOIN comment_likes cl ON cl.comment_id = c.id
    LEFT JOIN comment_dislikes cd ON cd.comment_id = c.id
    WHERE c.post_id = ?
    GROUP BY c.id
    ORDER BY c.created_at ASC
");
$cstmt->bind_param('i', $post_id);
$cstmt->execute();
$comments = $cstmt->get_result()->fetch_all(MYSQLI_ASSOC);

/* =======================
   LIKES E DISLIKES DO USUÁRIO
======================= */
$user_likes = [];
$user_dislikes = [];
if (!empty($_SESSION['user'])) {
    $uid = $_SESSION['user']['id'];
    $like_stmt = $mysqli->prepare("
        SELECT comment_id FROM comment_likes 
        WHERE comment_id IN (SELECT id FROM comments WHERE post_id = ?) 
        AND user_id = ?
    ");
    $like_stmt->bind_param('ii', $post_id, $uid);
    $like_stmt->execute();
    $like_result = $like_stmt->get_result();
    while ($row = $like_result->fetch_assoc()) {
        $user_likes[$row['comment_id']] = true;
    }
    
    $dislike_stmt = $mysqli->prepare("
        SELECT comment_id FROM comment_dislikes 
        WHERE comment_id IN (SELECT id FROM comments WHERE post_id = ?) 
        AND user_id = ?
    ");
    $dislike_stmt->bind_param('ii', $post_id, $uid);
    $dislike_stmt->execute();
    $dislike_result = $dislike_stmt->get_result();
    while ($row = $dislike_result->fetch_assoc()) {
        $user_dislikes[$row['comment_id']] = true;
    }
}
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
    html, body {
        height: 100%;
        margin: 0;
        background-color: #1a0d2e;
        -webkit-font-smoothing: antialiased;
    }
    
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


/* Remove qualquer efeito padrão do navegador */
.btn-save-post:focus,
.btn-save-post:active {
    outline: none;
    background: transparent;
}

/* SVG */
.icon-save {
    width: 26px;
    height: 26px;
    display: block;
}

/* Parte interna (preenchimento) */
.icon-save .fill {
    fill: transparent;
    transition: 0.25s ease;
}

/* Contorno */
.icon-save .stroke {
    fill: none;
    stroke: white;
    stroke-width: 2;
}

/* Hover */
.btn-save-post:hover .icon-save {
    transform: scale(1.1);
}

/* Quando estiver salvo → SÓ o ícone fica branco */
.btn-save-post.saved .fill {
    fill: white;
}
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
.btn-logout{
    background:#b91c1c;        /* vermelho */
    color:#fff !important;
    padding:8px 14px;
    border-radius:10px;
    text-decoration:none;
    font-weight:600;
    transition:.2s;
}
.voltar-link a {
            color: #d3aaff;
            text-decoration: none;
            font-size: 14px;
            transition: 0.3s;
        }

        .voltar-link a:hover {
            color: #1ac022;
            text-decoration: underline;
        }

        .btn-like {
            background: none;
            border: none;
            color: #d3aaff;
            cursor: pointer;
            font-size: 14px;
            padding: 4px 8px;
            border-radius: 4px;
            transition: 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-like:hover {
            background: rgba(211, 170, 255, 0.1);
            color: #1ac022;
        }

        .btn-like.liked {
            color: #ff6b9d;
        }

        .btn-like.liked:hover {
            background: rgba(255, 107, 157, 0.1);
        }

        .btn-dislike {
            background: none;
            border: none;
            color: #d3aaff;
            cursor: pointer;
            font-size: 14px;
            padding: 4px 8px;
            border-radius: 4px;
            transition: 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-dislike:hover {
            background: rgba(211, 170, 255, 0.1);
            color: #ff6b6b;
        }

        .btn-dislike.disliked {
            color: #ff6b6b;
        }

        .btn-dislike.disliked:hover {
            background: rgba(255, 107, 107, 0.1);
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
<header>
    <a href="/campusforumatl/index.php">
        <img src="/campusforumatl/campusforumlogox.png" width="140" alt="Campus Forum">
    </a>

    <div class="search-box"></div>

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
            <img src="/campusforumatl/lapis2.png" alt="Editar" style="width:20px;height:20px;vertical-align:middle;margin-right:6px;transform:translateY(1px);">Editar
        </a>
        <a href="#" onclick="deletePost(<?= $post_id ?>); return false;" style="margin-left:10px;">
            <img src="/campusforumatl/lixeira.png" alt="Excluir" style="width:20px;height:20px;vertical-align:middle;margin-right:6px;transform:translateY(1px);">Excluir
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
            <div class="comment" style="padding:12px 16px;margin-bottom:10px;border-radius:8px;background:#2d1b4e;color:#f4e9ff;position:relative;">
                <strong><?= htmlspecialchars($c['username']) ?></strong> 
                <span style="font-size:12px;color:#aaa;">- <?= date('d/m/Y H:i', strtotime($c['created_at'])) ?></span>
                <p style="margin:6px 0;"><?= nl2br(htmlspecialchars($c['content'])) ?></p>
                
                <div style="display:flex;gap:10px;margin-top:10px;align-items:center;">
                    <?php if (!empty($_SESSION['user'])): ?>
                        <button class="btn-like <?= isset($user_likes[$c['id']]) ? 'liked' : '' ?>" 
                                data-comment-id="<?= $c['id'] ?>"
                                title="<?= isset($user_likes[$c['id']]) ? 'Remover like' : 'Curtir' ?>">
                            <span style="font-size:20px;">👍</span>
                            <span class="like-count"><?= intval($c['likes']) ?></span>
                        </button>

                        <button class="btn-dislike <?= isset($user_dislikes[$c['id']]) ? 'disliked' : '' ?>" 
                                data-comment-id="<?= $c['id'] ?>"
                                title="<?= isset($user_dislikes[$c['id']]) ? 'Remover dislike' : 'Não gostei' ?>">
                            <span style="font-size:20px;">👎</span>
                            <span class="dislike-count"><?= intval($c['dislikes']) ?></span>
                        </button>
                    <?php else: ?>
                        <span style="font-size:16px;color:#aaa;">👍 <?= intval($c['likes']) ?></span>
                        <span style="font-size:16px;color:#aaa;">👎 <?= intval($c['dislikes']) ?></span>
                    <?php endif; ?>
                
                    <?php if (!empty($_SESSION['user']) && $_SESSION['user']['id'] === $c['user_id']): ?>
                        <a href="pergunta.php?id=<?= $post_id ?>&delete_comment=<?= $c['id'] ?>" 
                            onclick="return confirm('Excluir este coment\u00e1rio?')"
                            style="margin-left:auto;">
                            <img src="/campusforumatl/lixeira.png" alt="Excluir" style="width:18px;height:18px;vertical-align:middle;margin-right:4px;transform:translateY(2px);">
                        </a>
                    <?php endif; ?>
                </div>
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

// Like comentários
document.querySelectorAll('.btn-like').forEach(function(botao) {
    botao.addEventListener('click', function() {
        var comentarioId = parseInt(this.getAttribute('data-comment-id'));
        var isLiked = this.classList.contains('liked');
        var action = isLiked ? 'unlike' : 'like';
        var form = new FormData();
        
        form.append('comment_id', comentarioId);
        form.append('action', action);

        fetch('like_comment.php', {
            method: 'POST',
            body: form
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (data.success) {
                botao.classList.toggle('liked');
                botao.querySelector('.like-count').textContent = data.likes;
            } else {
                alert(data.message || 'Erro ao fazer like');
            }
        })
        .catch(function() { alert('Erro ao processar like'); });
    });
});

// Dislike comentários
document.querySelectorAll('.btn-dislike').forEach(function(botao) {
    botao.addEventListener('click', function() {
        var comentarioId = parseInt(this.getAttribute('data-comment-id'));
        var isDisliked = this.classList.contains('disliked');
        var action = isDisliked ? 'undislike' : 'dislike';
        var form = new FormData();
        
        form.append('comment_id', comentarioId);
        form.append('action', action);

        fetch('dislike_comment.php', {
            method: 'POST',
            body: form
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (data.success) {
                botao.classList.toggle('disliked');
                botao.querySelector('.dislike-count').textContent = data.dislikes;
            } else {
                alert(data.message || 'Erro ao fazer dislike');
            }
        })
        .catch(function() { alert('Erro ao processar dislike'); });
    });
});
</script>
<main class="voltar-link">
  <a href="index.php">← Voltar ao Fórum</a>
</main>
</script>



