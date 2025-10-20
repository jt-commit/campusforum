<?php
session_start();
require 'db.php';
$mysqli = db_connect();
$res = $mysqli->query('SELECT p.*, u.username FROM posts p JOIN users u ON p.user_id=u.id ORDER BY p.created_at DESC');
$posts = $res->fetch_all(MYSQLI_ASSOC);
?>
<!doctype html>
<html lang="pt-br">
<head>
<meta charset="utf-8">
<title>Campus Forum - Home</title>
<link rel="stylesheet" href="styles.css">
</head>
<body>
<header><h1>Campus Forum (PHP)</h1>
<nav>
<?php if(!empty($_SESSION['user'])): ?>
    Olá, <?=htmlspecialchars($_SESSION['user']['username'])?> | <a href="create_post.php">Criar post</a> | <a href="logout.php">Sair</a>
<?php else: ?>
    <a href="login.php">Entrar</a> | <a href="register.php">Registrar</a>
<?php endif; ?>
</nav>
</header>
<main>
<?php foreach($posts as $p): ?>
<article class="post">
  <h2><a href="post.php?id=<?= $p['id'] ?>"><?= htmlspecialchars($p['title']) ?></a></h2>
  <p class="meta">por <?=htmlspecialchars($p['username'])?> em <?= $p['created_at'] ?></p>
  <p><?= nl2br(htmlspecialchars(substr($p['content'],0,400))) ?><?php if(strlen($p['content'])>400) echo '...'; ?></p>
</article>
<?php endforeach; ?>
</main>
<footer></footer>
</body>
</html>

