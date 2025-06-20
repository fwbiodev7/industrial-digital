<?php
// Configurações do banco (coloque as suas)
$host = 'srv1664.hstgr.io';
$db   = 'u344105464_alunoprojeto';
$user = 'u344105464_ualunoprojeto';
$pass = 'Fabio0204#';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Erro na conexão: " . $conn->connect_error);
}

$sql = "SELECT id, nome, foto FROM tb_alunos ORDER BY nome";
$result = $conn->query($sql);

if (!$result) {
    die("Erro na consulta: " . $conn->error);
}

echo "<h1>Verificação de fotos dos alunos</h1>";
echo "<table border='1' cellpadding='6' cellspacing='0'>";
echo "<tr><th>ID</th><th>Nome</th><th>Foto (caminho)</th><th>Status</th></tr>";

while ($row = $result->fetch_assoc()) {
    $foto = $row['foto'];
    $fotoNome = basename($foto);
    $caminhoServidor = __DIR__ . '/uploads/' . $fotoNome;

    if (file_exists($caminhoServidor) && !empty($fotoNome)) {
        $status = "<span style='color: green;'>Existe ✅</span>";
    } else {
        $status = "<span style='color: red;'>Faltando ❌</span>";
    }

    echo "<tr>
        <td>{$row['id']}</td>
        <td>" . htmlspecialchars($row['nome']) . "</td>
        <td>" . htmlspecialchars($foto) . "</td>
        <td>$status</td>
    </tr>";
}

echo "</table>";

$conn->close();
