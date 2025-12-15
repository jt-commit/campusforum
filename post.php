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


<?php
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
<style>
/* ======== ESTILO GERAL ======== */
body {
    margin: 0;
    font-family: "Poppins", sans-serif;
    background-color: #1a0033;
    color: #f9d76e;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
}

/* ======== CONTAINER DO LOGIN ======== */
.login-container {
    background: linear-gradient(180deg, #29004b, #3d0073);
    padding: 40px 50px;
    border-radius: 18px;
    width: 350px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.5);
    text-align: center;
}

/* ======== TÍTULO ======== */
.login-container h1 {
    margin-top: 0;
    color: #f4e9ff;
    font-size: 26px;
}

/* ======== ERROS ======== */
.error {
    background-color: rgba(255, 50, 50, 0.25);
    padding: 8px;
    border-radius: 6px;
    margin-bottom: 12px;
    color: #ff9a9a;
}

/* ======== INPUTS ======== */
.login-container input {
    width: 100%;
    padding: 12px 16px;
    margin-bottom: 15px;
    border-radius: 30px;
    border: none;
    outline: none;
    background: #000;
    color: #fff;
    font-size: 15px;
}

.login-container input::placeholder {
    color: rgba(255,255,255,0.75);
}

/* ======== BOTÃO ======== */
button {
    width: 100%;
    padding: 12px;
    border: none;

    background: linear-gradient(180deg, #7c3aed, #5b21b6);
    color: #ffffff;
    border-radius: 10px;
    font-size: 16px;
    cursor: pointer;
    font-weight: 500;
    transition: 0.3s ease;
    box-shadow: 0 3px 6px rgba(0,0,0,0.3);
}

button:hover {
    background: linear-gradient(180deg, #9d4edd, #6a0dad);
    transform: translateY(-2px);
}

/* ======== LINK VOLTAR ======== */
.voltar {
    margin-top: 15px;
    display: inline-block;
    color: #d3aaff;
    text-decoration: none;
    font-size: 14px;
}

.voltar:hover {
    text-decoration: underline;
}
</style>
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
