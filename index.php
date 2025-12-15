<?php
session_start();
require 'db.php';
$mysqli = db_connect();

// ----- PESQUISA -----
$search = $_GET['q'] ?? '';

if (!empty($search)) {
    $stmt = $mysqli->prepare("
        SELECT p.*, u.username 
        FROM posts p
        JOIN users u ON p.user_id = u.id
        WHERE p.title LIKE CONCAT('%', ?, '%')
           OR p.content LIKE CONCAT('%', ?, '%')
        ORDER BY p.created_at DESC
    ");
    $stmt->bind_param("ss", $search, $search);
    $stmt->execute();
    $res = $stmt->get_result();

} else {
    $res = $mysqli->query("
        SELECT p.*, u.username
        FROM posts p
        JOIN users u ON p.user_id = u.id
        ORDER BY p.created_at DESC
    ");
}

$posts = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
?>
<!doctype html>
<html lang="pt-br">
<head>
<meta charset="utf-8">
<title>Campus Forum - Home</title>
<link rel="stylesheet" href="styles.css">

<style>
.search-box form { margin-left: 20px; }
.search-box input {
  width: 330px;
  padding: 12px 22px;
  background: #000;
  color: #fff;
  border: none;
  outline: none;
  border-radius: 30px;
  font-size: 16px;
  transition: 0.25s ease;
}
.search-box input:focus {
  background: #111;
  box-shadow: 0 0 10px rgba(255, 255, 255, 0.25);
}
.search-box input::placeholder {
  color: rgba(255,255,255,0.7);
}
</style>

</head>
<body>

<header>

  <img src="/campusforum/campusforumlogox.png" width="150" alt="Campus Forum">

  <div class="search-box">
    <form method="GET" action="">
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
      <span class="user">Olá, <strong><?= htmlspecialchars($_SESSION['user']['username']) ?></strong></span>
      <a href="create_post.php" class="btn-novo">Criar Post</a>
      <a href="logout.php" class="logout">Sair</a>
    <?php else: ?>
      <a href="login.php" class="btn-novo">Entrar</a>
      <a href="register.php" class="btn-novo">Registrar</a>
    <?php endif; ?>
  </nav>

</header>

<main>
  <section>

    <?php if (empty($posts)): ?>
      <p style="color:white; margin-left:20px;">Nenhum post encontrado.</p>
    <?php endif; ?>

    <?php foreach ($posts as $p): ?>
      <article class="topico">
        <div class="topico-header">
          <img src="img/post.png" alt="Post">
          <div>
            <h2>
              <a href="post.php?id=<?= $p['id'] ?>">
                <?= htmlspecialchars($p['title']) ?>
              </a>
            </h2>
            <span class="badge">Post</span>
          </div>
        </div>

        <p>
          <?= nl2br(htmlspecialchars(substr($p['content'], 0, 400))) ?>
          <?php if (strlen($p['content']) > 400) echo '...'; ?>
        </p>

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
