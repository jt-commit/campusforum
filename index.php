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

// Obter posts salvos do usuário (se logado)
$saved_posts = [];
if (!empty($_SESSION['user'])) {
    $user_id = $_SESSION['user']['id'];
    // Verificar se a tabela saved_posts existe
    $table_check = $mysqli->query("SHOW TABLES LIKE 'saved_posts'");
    if ($table_check && $table_check->num_rows > 0) {
        $saved_res = $mysqli->query("SELECT post_id FROM saved_posts WHERE user_id = $user_id");
        if ($saved_res) {
            while ($row = $saved_res->fetch_assoc()) {
                $saved_posts[$row['post_id']] = true;
            }
        }
    }
}
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


/* BOTÃO (nunca pode ter fundo) */
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



</style>

</head>
<body>

<header>

  <img src="C:/xampp/htdocs/campusforum/campusforumlogox.png" width="150" alt="Campus Forum">

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
        <?php if (!empty($_SESSION['user'])): ?>
          <button class="btn-save-post <?= isset($saved_posts[$p['id']]) ? 'saved' : '' ?>" 
        data-post-id="<?= $p['id'] ?>">

<svg class="icon-save" viewBox="0 0 24 24">
    <path class="fill"
        d="M6 2h12a1 1 0 0 1 1 1v19l-7-5-7 5V3a1 1 0 0 1 1-1z"/>
    <path class="stroke"
        d="M6 2h12a1 1 0 0 1 1 1v19l-7-5-7 5V3a1 1 0 0 1 1-1z"/>
</svg>

</button>


        <?php endif; ?>

        <div class="topico-imagem">
          <?php if (!empty($p['image'])): ?>
            <img src="<?= htmlspecialchars($p['image']) ?>" alt="Imagem de <?= htmlspecialchars($p['title']) ?>">
          <?php else: ?>
            <div style="width:100%; height:100%; background:linear-gradient(180deg, #3c006d, #120028); display:flex; align-items:center; justify-content:center; color:#bca7ff; font-size:12px; border-radius:12px;">
              Sem imagem
            </div>
          <?php endif; ?>
        </div>

        <div class="topico-conteudo">
          <div class="topico-header">
            <h2>
              <a href="post.php?id=<?= $p['id'] ?>">
                <?= htmlspecialchars($p['title']) ?>
              </a>
            </h2>
          </div>

          <p class="topico-descricao">
            <?= htmlspecialchars(substr($p['content'], 0, 200)) ?>
            <?php if (strlen($p['content']) > 200) echo '...'; ?>
          </p>

          <div class="topico-footer">
            <div class="autor">
              <span><?= htmlspecialchars($p['username']) ?></span>
            </div>
            <div class="data">
              <?= date('d/m/Y H:i', strtotime($p['created_at'])) ?>
            </div>
          </div>
        </div>
      </article>
    <?php endforeach; ?>

  </section>
</main>

<footer>
  <p>&copy; <?= date('Y') ?> Campus Forum IFPE - Igarassu</p>
</footer>

<script>
document.querySelectorAll('.btn-save-post').forEach(btn => {
    btn.addEventListener('click', async function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const postId = this.dataset.postId;
        const btn = this;
        
        if (!postId) {
            alert('Erro: Post ID inválido');
            return;
        }
        
        const formData = new FormData();
        formData.append('post_id', postId);
        formData.append('action', 'toggle');
        
        try {
            const response = await fetch('save_post.php', {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            });
            
            let data;
            try {
                data = await response.json();
            } catch (e) {
                console.error('Erro ao parsear JSON:', e);
                alert('Erro ao processar resposta do servidor');
                return;
            }
            
            if (response.ok && data.saved !== undefined) {
                if (data.saved) {
                    btn.classList.add('saved');
                    btn.title = 'Remover dos salvos';
                } else {
                    btn.classList.remove('saved');
                    btn.title = 'Salvar post';
                }
            } else {
                console.error('Erro:', data);
                alert('Erro ao salvar post: ' + (data.error || 'Desconhecido'));
            }
        } catch (error) {
            console.error('Erro na requisição:', error);
            alert('Erro ao processar sua solicitação: ' + error.message);
        }
    });
});
</script>

</body>
</html>
