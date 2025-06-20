<?php
session_start();

// Configurações do banco
$host = 'srv1664.hstgr.io';
$db   = 'u344105464_alunoprojeto';
$user = 'u344105464_ualunoprojeto';
$pass = 'Fabio0204#';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    die("Erro na conexão com o banco de dados: " . $e->getMessage());
}

// Função para validar URL
function is_valid_url($url) {
    return filter_var($url, FILTER_VALIDATE_URL) !== false;
}

// Recebe dados do formulário
$nome = trim($_POST['nome'] ?? '');
$mes = trim($_POST['mes'] ?? '');
$foto_path = null;

// Validação básica
if ($nome === '' || $mes === '') {
    $_SESSION['erro'] = "Por favor, preencha todos os campos obrigatórios.";
    header("Location: adicionar_aluno.php");
    exit;
}

// Diretório para salvar fotos
$uploadDir = __DIR__ . '/uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Processar upload do arquivo, se houver
if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
    $fileTmpPath = $_FILES['foto']['tmp_name'];
    $fileName = basename($_FILES['foto']['name']);
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    $allowedExt = ['jpg', 'jpeg', 'png', 'gif'];

    if (!in_array($fileExt, $allowedExt)) {
        $_SESSION['erro'] = "Formato de imagem não permitido. Use JPG, PNG ou GIF.";
        header("Location: adicionar_aluno.php");
        exit;
    }

    $newFileName = uniqid('foto_', true) . '.' . $fileExt;
    $destPath = $uploadDir . $newFileName;

    if (!move_uploaded_file($fileTmpPath, $destPath)) {
        $_SESSION['erro'] = "Erro ao salvar a imagem enviada.";
        header("Location: adicionar_aluno.php");
        exit;
    }

    // Caminho relativo para salvar no banco (ajuste se necessário)
    $foto_path = 'uploads/' . $newFileName;

} elseif (!empty($_POST['foto_url'])) {
    // Se enviou URL da imagem, validar e baixar
    $url = trim($_POST['foto_url']);
    if (is_valid_url($url)) {
        $imageData = @file_get_contents($url);
        if ($imageData === false) {
            $_SESSION['erro'] = "Não foi possível baixar a imagem da URL.";
            header("Location: adicionar_aluno.php");
            exit;
        }

        // Tenta pegar extensão da URL
        $pathinfo = pathinfo(parse_url($url, PHP_URL_PATH));
        $fileExt = strtolower($pathinfo['extension'] ?? 'jpg');
        $allowedExt = ['jpg', 'jpeg', 'png', 'gif'];

        if (!in_array($fileExt, $allowedExt)) {
            $_SESSION['erro'] = "Formato da imagem na URL não permitido.";
            header("Location: adicionar_aluno.php");
            exit;
        }

        $newFileName = uniqid('foto_', true) . '.' . $fileExt;
        $destPath = $uploadDir . $newFileName;

        if (file_put_contents($destPath, $imageData) === false) {
            $_SESSION['erro'] = "Erro ao salvar a imagem da URL.";
            header("Location: adicionar_aluno.php");
            exit;
        }

        $foto_path = 'uploads/' . $newFileName;

    } else {
        $_SESSION['erro'] = "URL da imagem inválida.";
        header("Location: adicionar_aluno.php");
        exit;
    }
}

// Inserção no banco
try {
    $sql = "INSERT INTO tb_alunos (nome, foto, mes) VALUES (:nome, :foto, :mes)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':nome' => $nome,
        ':foto' => $foto_path,
        ':mes' => $mes,
    ]);

    $_SESSION['sucesso'] = "Aluno adicionado com sucesso!";
    header("Location: adicionar_aluno.php");
    exit;

} catch (PDOException $e) {
    $_SESSION['erro'] = "Erro ao adicionar aluno: " . $e->getMessage();
    header("Location: adicionar_aluno.php");
    exit;
}
