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

<header>

<!--Cabeçalho com logo do campusforum logoX porque é um png com fundo transparente -->
  <img src="\campusforum\campusforumlogox.png" width="150" height="auto">

 
<div class="search-box">
  <input type="text" placeholder="Pesquisar no fórum...">
</div>
 <nav class="right">
    <?php if (!empty($_SESSION['user'])): ?>
      <span class="user">Olá, <strong><?= htmlspecialchars($_SESSION['user']['username']) ?></strong></span>
      <a href="create_post.php" class="btn-novo">Criar Post</a>
      <a href="logout.php" class="logout">Sair</a>
    <?php else: ?>
      <a href="login.php" class="btn-novo">Entrar</a>
      <a href="register.php" class="btn-novo">Registrar</a>
    <?php endif; ?>
  </nav>
</header>




    <?php foreach ($posts as $p): ?>
      <article class="topico">
        <div class="topico-header">
          <img src="img/post.png" alt="Post">
          <div>
            <h2><a href="post.php?id=<?= $p['id'] ?>"><?= htmlspecialchars($p['title']) ?></a></h2>
            <span class="badge">Post</span>
          </div>
        </div>
        <p><?= nl2br(htmlspecialchars(substr($p['content'], 0, 400))) ?><?php if (strlen($p['content']) > 400) echo '...'; ?></p>

        <div class="topico-footer">
          <div class="autor">
            <img src="img/user.png" alt="Autor">
            <span><?= htmlspecialchars($p['username']) ?></span>
          </div>
          <div class="estatisticas">
            <span><?= date('d/m/Y H:i', strtotime($p['created_at'])) ?></span>
          </div>
        </div>
      </article>
    <?php endforeach; ?>
  </section>
</main>

<footer>
  <p>&copy; <?= date('Y') ?> Campus Forum IFPE - Igarassu</p>
</footer>

</body>
</html>

