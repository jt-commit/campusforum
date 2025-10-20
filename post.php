<?php
session_start();
require 'db.php';
$id = intval($_GET['id'] ?? 0);
$mysqli = db_connect();
$stmt = $mysqli->prepare('SELECT p.*, u.username FROM posts p JOIN users u ON p.user_id=u.id WHERE p.id=?');
$stmt->bind_param('i', $id);
$stmt->execute();
$res = $stmt->get_result();
$post = $res->fetch_assoc();
if (!$post) { 
    http_response_code(404); 
    echo 'Post não encontrado'; 
    exit; 
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_SESSION['user'])) {
    $content = trim($_POST['content'] ?? '');
    if ($content !== '') {
        $ins = $mysqli->prepare('INSERT INTO comments(post_id, user_id, content) VALUES(?,?,?)');
        $ins->bind_param('iis', $id, $_SESSION['user']['id'], $content);
        $ins->execute();
        header('Location: post.php?id='.$id); 
        exit;
    }
}

$comments = $mysqli->query('SELECT c.*, u.username FROM comments c JOIN users u ON c.user_id=u.id WHERE c.post_id='.$id.' ORDER BY c.created_at ASC')->fetch_all(MYSQLI_ASSOC);
?>
<!doctype html>
<html lang="pt-br">
<head>
<meta charset="utf-8">
<title><?=htmlspecialchars($post['title'])?></title>
<link rel="stylesheet" href="styles.css">
</head>
<body>
<article>
  <h1><?=htmlspecialchars($post['title'])?></h1>
  <p class="meta">por <?=htmlspecialchars($post['username'])?> em <?= $post['created_at'] ?></p>
  <div class="content"><?= nl2br(htmlspecialchars($post['content'])) ?></div>

  <?php if(!empty($_SESSION['user']) && $_SESSION['user']['id'] === $post['user_id']): ?>
    <p>
      <a href="edit_post.php?id=<?= $post['id'] ?>">Editar</a> | 
      <a href="delete_post.php?id=<?= $post['id'] ?>" onclick="return confirm('Tem certeza que deseja excluir este post?')">Excluir</a>
    </p>
  <?php endif; ?>
</article>

<section>
  <h2>Comentários</h2>
  <?php foreach($comments as $c): ?>
    <div class="comment">
      <strong><?=htmlspecialchars($c['username'])?></strong> em <?= $c['created_at'] ?>
      <div><?= nl2br(htmlspecialchars($c['content'])) ?></div>
    </div>
  <?php endforeach; ?>

  <?php if(!empty($_SESSION['user'])): ?>
    <form method="post">
      <label>Seu comentário:<br><textarea name="content" required></textarea></label><br>
      <button>Enviar</button>
    </form>
  <?php else: ?>
    <p><a href="login.php">Entrar</a> para comentar.</p>
  <?php endif; ?>
</section>

<p><a href="index.php">Voltar</a></p>
</body>
</html>
