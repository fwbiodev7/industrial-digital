<?php
require_once 'banco.php';

$sql = "SELECT id, nome FROM tb_alunos";
$result = $conn->query($sql);

if (!$result) {
    die("Erro na consulta: " . $conn->error);
}

if ($result->num_rows > 0) {
    echo "<h3>Alunos encontrados:</h3><ul>";
    while ($row = $result->fetch_assoc()) {
        echo "<li>ID: {$row['id']} - Nome: {$row['nome']}</li>";
    }
    echo "</ul>";
} else {
    echo "Nenhum aluno cadastrado.";
}
?>
