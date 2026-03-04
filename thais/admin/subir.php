<?php
// ====== CONFIG ======
$PASSWORD = '15mandarinas';
$maxSizeMB = 12;
$allowed = ['jpg','jpeg','png','webp']; // recomendado

// Login simple por sesión
session_start();
if (isset($_POST['pass'])) {
  if (hash_equals($PASSWORD, $_POST['pass'])) {
    $_SESSION['ok'] = true;
  } else {
    $error = 'Contraseña incorrecta';
  }
}
if (isset($_GET['logout'])) {
  session_destroy();
  header("Location: subir.php");
  exit;
}

function safeName($name) {
  $name = strtolower($name);
  $name = preg_replace('/[^a-z0-9\-_\.]+/','-',$name);
  $name = trim($name,'-');
  if ($name === '') $name = 'foto';
  return $name;
}

$uploaded = [];
$uploadError = null;

if (!empty($_SESSION['ok']) && isset($_POST['categoria']) && !empty($_FILES['fotos'])) {
  $cat = $_POST['categoria'] === 'naturaleza' ? 'naturaleza' : 'familia';
  $targetDir = dirname(__DIR__) . "/img/$cat";

  if (!is_dir($targetDir)) {
    $uploadError = "No existe la carpeta destino: img/$cat";
  } else {
    $count = count($_FILES['fotos']['name']);
    for ($i=0; $i<$count; $i++) {
      if ($_FILES['fotos']['error'][$i] !== UPLOAD_ERR_OK) continue;

      $tmp = $_FILES['fotos']['tmp_name'][$i];
      $orig = $_FILES['fotos']['name'][$i];
      $size = $_FILES['fotos']['size'][$i];

      if ($size > $maxSizeMB * 1024 * 1024) continue;

      $ext = strtolower(pathinfo($orig, PATHINFO_EXTENSION));
      if (!in_array($ext, $allowed, true)) continue;

      // Verificación extra: que sea imagen real
      $info = @getimagesize($tmp);
      if ($info === false) continue;

      $base = pathinfo($orig, PATHINFO_FILENAME);
      $base = safeName($base);

      // nombre único
      $final = $base . '-' . date('Ymd-His') . '-' . bin2hex(random_bytes(3)) . '.' . $ext;
      $dest = $targetDir . '/' . $final;

      if (move_uploaded_file($tmp, $dest)) {
        $uploaded[] = "img/$cat/$final";
      }
    }
  }
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Subir fotos · Admin</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,600&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">

<style>
  :root{
    --bg:#fbfaf7;
    --soft:#f3f0e8;
    --text:#1f2320;
    --muted:#5b615b;
    --brand:#2f5d50;
    --brand2:#8aa07a;
    --line:rgba(0,0,0,.08);
    --shadow:0 18px 42px rgba(0,0,0,.08);
    --radius:18px;
    --radius2:26px;
  }

  *{box-sizing:border-box}
  body{
    margin:0;
    font-family:Inter,system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;
    background:var(--bg);
    color:var(--text);
    line-height:1.65;
  }

  .wrap{max-width:740px;margin:44px auto;padding:18px}
  .top{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:14px;
  }
  .top h2{
    margin:0;
    font-family:Fraunces,serif;
    letter-spacing:-0.02em;
  }

  .card{
    background:rgba(255,255,255,.85);
    border:1px solid var(--line);
    border-radius:var(--radius);
    padding:18px;
    box-shadow:0 10px 24px rgba(0,0,0,.05);
  }

  label{display:block;font-weight:800;margin:12px 0 6px}

  input,select{
    width:100%;
    padding:12px;
    border-radius:12px;
    border:1px solid rgba(0,0,0,.14);
    font:inherit;
    background:#fff;
  }

  button{
    margin-top:14px;
    padding:12px 18px;
    border:0;
    border-radius:999px;
    font-weight:800;
    cursor:pointer;
    background:linear-gradient(135deg,var(--brand),var(--brand2));
    color:#fff;
    box-shadow:0 14px 30px rgba(47,93,80,.18);
    transition:filter .2s ease;
  }
  button:hover{filter:brightness(.97)}

  a{color:inherit;text-decoration:none}
  .top a{
    padding:8px 10px;
    border-radius:999px;
    font-weight:700;
    color:var(--muted);
  }
  .top a:hover{background:rgba(47,93,80,.08);color:var(--text)}

  small{color:var(--muted)}
  code{
    background:rgba(47,93,80,.08);
    border:1px solid rgba(47,93,80,.18);
    padding:2px 6px;
    border-radius:10px;
    font-family:ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,"Liberation Mono","Courier New",monospace;
  }

  .ok{
    margin-top:14px;
    background:rgba(47,93,80,.08);
    border:1px solid rgba(47,93,80,.18);
    padding:12px;
    border-radius:14px;
  }

  .err{
    margin-top:14px;
    background:rgba(160,60,60,.08);
    border:1px solid rgba(160,60,60,.22);
    padding:12px;
    border-radius:14px;
  }

  ul{margin:8px 0 0;padding-left:18px}
</style>
</head>
<body>
  <div class="wrap">
    <div class="top">
      <h2 style="margin:0;">Panel de subida</h2>
      <?php if (!empty($_SESSION['ok'])): ?>
        <a href="?logout=1">Salir</a>
      <?php endif; ?>
    </div>

    <div class="card">
      <?php if (empty($_SESSION['ok'])): ?>
        <form method="post">
          <label>Contraseña</label>
          <input type="password" name="pass" required>
          <button type="submit">Entrar</button>
          <?php if (!empty($error)): ?><div class="err"><?= htmlspecialchars($error) ?></div><?php endif; ?>
          <p><small>Ruta del panel: <code>/admin/subir.php</code></small></p>
        </form>
      <?php else: ?>
        <form method="post" enctype="multipart/form-data">
          <label>Categoría</label>
          <select name="categoria">
            <option value="familia">Familia</option>
            <option value="naturaleza">Naturaleza</option>
          </select>

          <label>Fotos (puedes seleccionar varias)</label>
          <input type="file" name="fotos[]" accept="image/*" multiple required>

          <button type="submit">Subir</button>

          <p><small>Al subir, aparecen automáticamente en “Ver todas”.</small></p>
        </form>

        <?php if ($uploadError): ?>
          <div class="err"><?= htmlspecialchars($uploadError) ?></div>
        <?php endif; ?>

        <?php if (!empty($uploaded)): ?>
          <div class="ok">
            <strong>Subidas:</strong>
            <ul>
              <?php foreach ($uploaded as $u): ?>
                <li><?= htmlspecialchars($u) ?></li>
              <?php endforeach; ?>
            </ul>
            <p>
              Ver galerías:
              <a href="../galeria-familia.php" target="_blank">Familia</a> ·
              <a href="../galeria-naturaleza.php" target="_blank">Naturaleza</a>
            </p>
          </div>
        <?php endif; ?>
      <?php endif; ?>
    </div>
  </div>
</body>
</html>
