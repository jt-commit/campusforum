<?php
// ...existing code...
session_start();
require 'db.php';

if (empty($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

// Verificar se o user_id é válido
if (empty($_SESSION['user']['id']) || !is_numeric($_SESSION['user']['id'])) {
    header('Location: login.php');
    exit;
}

$errors = [];
$title = '';
$content = '';

// Criar pasta de uploads se não existir
$uploads_dir = __DIR__ . '/uploads';
if (!is_dir($uploads_dir)) {
    mkdir($uploads_dir, 0755, true);
}

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
        $image_path = null;

        if ($title === '' || $content === '') {
            $errors[] = 'Preencha título e conteúdo.';
        } elseif (mb_strlen($title) > 255) {
            $errors[] = 'Título muito longo (max 255 caracteres).';
        } else {
            // Processar upload de imagem
            if (!empty($_FILES['image']['name'])) {
                $file = $_FILES['image'];
                $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                $max_size = 5 * 1024 * 1024; // 5MB

                if (!in_array($file['type'], $allowed_types)) {
                    $errors[] = 'Tipo de imagem não permitido. Use JPG, PNG, GIF ou WebP.';
                } elseif ($file['size'] > $max_size) {
                    $errors[] = 'Imagem muito grande (máximo 5MB).';
                } elseif ($file['error'] !== UPLOAD_ERR_OK) {
                    $errors[] = 'Erro no upload da imagem.';
                } else {
                    // Gerar nome único para a imagem
                    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                    $filename = 'post_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
                    $filepath = $uploads_dir . '/' . $filename;

                    if (move_uploaded_file($file['tmp_name'], $filepath)) {
                        $image_path = 'uploads/' . $filename;
                    } else {
                        $errors[] = 'Não foi possível salvar a imagem.';
                    }
                }
            }

            // Inserir post no banco de dados apenas se não houver erros
            if (empty($errors)) {
                $mysqli = db_connect();
                
                // Validar que o usuário existe no banco antes de inserir
                $check_user = $mysqli->prepare('SELECT id FROM users WHERE id = ?');
                $check_user->bind_param('i', $_SESSION['user']['id']);
                $check_user->execute();
                $check_user->store_result();
                
                if ($check_user->num_rows === 0) {
                    $errors[] = 'Sessão inválida. Faça login novamente.';
                    $check_user->close();
                } else {
                    $check_user->close();
                    
                    $user_id = (int)$_SESSION['user']['id'];
                    $stmt = $mysqli->prepare('INSERT INTO posts (user_id, title, content, image) VALUES (?, ?, ?, ?)');
                    if ($stmt) {
                        $stmt->bind_param('isss', $user_id, $title, $content, $image_path);
                        if ($stmt->execute()) {
                            // Regenera token para evitar reenvio acidental
                            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                            $mysqli->close();
                            header('Location: index.php');
                            exit;
                        } else {
                            $errors[] = 'Erro ao salvar post: ' . htmlspecialchars($stmt->error);
                        }
                        $stmt->close();
                    } else {
                        $errors[] = 'Erro no banco de dados: ' . htmlspecialchars($mysqli->error);
                    }
                }
                $mysqli->close();
            }
        }
    }
}
?>
<!doctype html>
<html lang="pt-br">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Criar Pergunta</title>
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
<style>
:root{
    --bg-1: #0f051e;
    --bg-2: #1a0d2e;
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
    background: linear-gradient(180deg, rgba(255,255,255,0.08), rgba(255,255,255,0.05));
    border-radius:calc(var(--radius) + 4px);
    padding:32px;
    box-shadow: 0 12px 40px rgba(2,0,20,0.8), inset 0 1px 0 rgba(255,255,255,0.04);
    backdrop-filter: blur(var(--glass-blur));
    border: 1px solid rgba(255,255,255,0.12);
}

.card h1{margin:0 0 14px;color:#fff;font-size:22px}
.form-row{display:flex;flex-direction:column;gap:8px;margin-bottom:14px}
label{font-size:14px;color:#f4e9ff}
input[type="text"], textarea{
    width:100%;
    padding:12px 14px;
    border-radius:10px;
    border:1px solid rgba(255,255,255,0.12);
    background:rgba(0,0,0,0.65);
    color:#fff;
    font-size:15px;
    outline:none;
    transition: box-shadow .18s ease, transform .12s ease, border-color .12s ease;
    resize:vertical;
}
input[type="file"] {
    width:100%;
    padding:10px 14px;
    border-radius:10px;
    border:2px dashed rgba(255,255,255,0.4);
    background:rgba(0,0,0,0.5);
    color:#f4e9ff;
    font-size:14px;
    cursor:pointer;
    transition: border-color .18s ease;
}
input[type="file"]:hover {
    border-color:rgba(124,58,237,0.6);
}
.image-preview {
    margin-top:8px;
    max-width:100%;
    border-radius:10px;
    max-height:300px;
    display:none;
}
.image-preview.show {
    display:block;
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
/* ======== VOLTAR ======== */
.voltar {
    display: inline-block;
    margin-top: 15px;
    color: #d3aaff;
    text-decoration: none;
    font-size: 14px;
    text-align: center;
}

.voltar:hover {
    text-decoration: underline;
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

    <form method="post" enctype="multipart/form-data" novalidate autocomplete="off" aria-describedby="helpCreate">
      <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">

      <div class="form-row">
        <label for="title">Assunto</label>
        <input id="title" name="title" type="text" maxlength="255" required
               value="<?php echo htmlspecialchars($title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>">
      </div>

      <div class="form-row">
        <label for="content">Pergunta</label>
        <textarea id="content" name="content" rows="8" required><?php echo htmlspecialchars($content, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></textarea>
      </div>

      <div class="form-row">
        <label for="image">Imagem de Referência (Opcional)</label>
        <input id="image" name="image" type="file" accept="image/jpeg,image/png,image/gif,image/webp">
        <small style="color:rgba(255,255,255,0.5);margin-top:4px;display:block;">Formatos aceitos: JPG, PNG, GIF, WebP (máximo 5MB)</small>
        <img id="imagePreview" class="image-preview" alt="Prévia da imagem">
      </div>

      <div class="button-row">
        <button type="submit" class="primary">Publicar</button>
        <a class="voltar" href="index.php">← Voltar para o Fórum</a>
      </div>

      <p id="helpCreate" style="position:absolute;left:-9999px;top:auto;width:1px;height:1px;overflow:hidden;">Campos obrigatórios: título e conteúdo.</p>
    </form>
  </main>
</div>
<script>
document.getElementById('image').addEventListener('change', function(e) {
    const file = e.target.files[0];
    const preview = document.getElementById('imagePreview');
    
    if (file) {
        const reader = new FileReader();
        reader.onload = function(event) {
            preview.src = event.target.result;
            preview.classList.add('show');
        }
        reader.readAsDataURL(file);
    } else {
        preview.classList.remove('show');
    }
});
</script>
</body>
</html>
