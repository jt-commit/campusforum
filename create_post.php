<?php
// ...existing code...
session_start();
require 'db.php';

if (empty($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$errors = [];
$title = '';
$content = '';

/* CSRF token */
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'], $token)) {
        $errors[] = 'Requisição inválida. Atualize a página e tente novamente.';
    } else {
        $title = trim((string)($_POST['title'] ?? ''));
        $content = trim((string)($_POST['content'] ?? ''));

        if ($title === '' || $content === '') {
            $errors[] = 'Preencha título e conteúdo.';
        } elseif (mb_strlen($title) > 255) {
            $errors[] = 'Título muito longo (max 255 caracteres).';
        } else {
            $mysqli = db_connect();
            $stmt = $mysqli->prepare('INSERT INTO posts (user_id, title, content) VALUES (?, ?, ?)');
            if ($stmt) {
                $stmt->bind_param('iss', $_SESSION['user']['id'], $title, $content);
                if ($stmt->execute()) {
                    // Regenera token para evitar reenvio acidental
                    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                    header('Location: index.php');
                    exit;
                } else {
                    $errors[] = 'Erro ao salvar post.';
                }
                $stmt->close();
            } else {
                $errors[] = 'Erro no banco de dados.';
            }
            $mysqli->close();
        }
    }
}
?>
<!doctype html>
<html lang="pt-br">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Criar Post</title>
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
<style>
:root{
    --bg-1: #0b0120;
    --bg-2: #2b004c;
    --card: rgba(255,255,255,0.04);
    --accent: #e9c46a;
    --accent-2: #7c3aed;
    --muted: #d9cbe9;
    --error-bg: rgba(255, 80, 80, 0.12);
    --radius: 14px;
    --glass-blur: 8px;
    --max-width: 760px;
}

*{box-sizing:border-box}
html,body{height:100%;margin:0;font-family:Inter,system-ui,-apple-system,"Segoe UI",Roboto,Arial;color:var(--muted);background:
    radial-gradient(900px 400px at 10% 10%, rgba(124,58,237,0.06), transparent),
    linear-gradient(180deg,var(--bg-1),var(--bg-2)); -webkit-font-smoothing:antialiased;}

.container{
    min-height:100vh;
    display:flex;
    align-items:center;
    justify-content:center;
    padding:32px;
}

.card{
    width:100%;
    max-width:var(--max-width);
    background: linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01));
    border-radius:calc(var(--radius) + 4px);
    padding:32px;
    box-shadow: 0 12px 40px rgba(2,0,20,0.6), inset 0 1px 0 rgba(255,255,255,0.02);
    backdrop-filter: blur(var(--glass-blur));
    border: 1px solid rgba(255,255,255,0.03);
}

.card h1{margin:0 0 14px;color:#fff;font-size:22px}
.form-row{display:flex;flex-direction:column;gap:8px;margin-bottom:14px}
label{font-size:14px;color:#f4e9ff}
input[type="text"], textarea{
    width:100%;
    padding:12px 14px;
    border-radius:10px;
    border:1px solid rgba(255,255,255,0.06);
    background:rgba(0,0,0,0.45);
    color:#fff;
    font-size:15px;
    outline:none;
    transition: box-shadow .18s ease, transform .12s ease, border-color .12s ease;
    resize:vertical;
}
input::placeholder, textarea::placeholder{color:rgba(255,255,255,0.45)}
input:focus, textarea:focus{box-shadow:0 6px 20px rgba(124,58,237,0.12);border-color:rgba(124,58,237,0.9);transform:translateY(-1px)}

.error{background:var(--error-bg);color:#ffb3b3;padding:10px 12px;border-radius:10px;margin:0 0 12px 0;font-size:14px}
.error-list{padding-left:18px;margin:0 0 12px 0}

/* Buttons */
.button-row{display:flex;gap:12px;flex-direction:column}
button.primary{
    display:inline-flex;align-items:center;justify-content:center;gap:8px;
    padding:12px 16px;border-radius:12px;border:none;background:linear-gradient(180deg,var(--accent-2),#5b21b6);
    color:#fff;font-weight:600;cursor:pointer;box-shadow:0 8px 24px rgba(92,33,150,0.28);transition:.12s;
}
button.primary:hover{transform:translateY(-3px);box-shadow:0 12px 30px rgba(92,33,150,0.36)}
a.voltar{display:inline-block;margin-top:8px;color:var(--accent);text-decoration:none;font-size:14px}
a.voltar:hover{text-decoration:underline}

/* responsive */
@media (max-width:640px){
    .card{padding:20px;border-radius:12px}
    .card h1{font-size:20px}
}
</style>
</head>
<body>
<div class="container">
  <main class="card" role="main" aria-labelledby="pageTitle">
    <h1 id="pageTitle">Criar Post</h1>

    <?php if (!empty($errors)): ?>
      <div class="error" role="alert">
        <strong>Foram encontrados os seguintes erros:</strong>
        <ul class="error-list">
          <?php foreach ($errors as $e): ?>
            <li><?php echo htmlspecialchars($e, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <form method="post" novalidate autocomplete="off" aria-describedby="helpCreate">
      <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">

      <div class="form-row">
        <label for="title">Título</label>
        <input id="title" name="title" type="text" maxlength="255" required
               value="<?php echo htmlspecialchars($title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>">
      </div>

      <div class="form-row">
        <label for="content">Conteúdo</label>
        <textarea id="content" name="content" rows="8" required><?php echo htmlspecialchars($content, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></textarea>
      </div>

      <div class="button-row">
        <button type="submit" class="primary">Publicar</button>
        <a class="voltar" href="index.php">Voltar</a>
      </div>

      <p id="helpCreate" style="position:absolute;left:-9999px;top:auto;width:1px;height:1px;overflow:hidden;">Campos obrigatórios: título e conteúdo.</p>
    </form>
  </main>
</div>
</body>
</html>
