<?php
require_once 'banco.php';

// Mostra o nome do banco atual
$db = $conn->query("SELECT DATABASE()")->fetch_row()[0];
echo "<p><strong>Banco conectado:</strong> $db</p>";

// Verifica se a tabela existe
$tabela = $conn->query("SHOW TABLES LIKE 'tb_alunos'");
if ($tabela->num_rows > 0) {
    echo "<p><strong>Tabela 'tb_alunos' encontrada.</strong></p>";

    // Mostra os registros
    $res = $conn->query("SELECT * FROM tb_alunos");
    if ($res->num_rows > 0) {
        echo "<h3>Alunos cadastrados:</h3><ul>";
        while ($linha = $res->fetch_assoc()) {
            echo "<li>ID: {$linha['id']} | Nome: {$linha['nome']}</li>";
        }
        echo "</ul>";
    } else {
        echo "<p style='color:red;'>A tabela existe, mas não há alunos cadastrados.</p>";
    }
} else {
    echo "<p style='color:red;'>A tabela 'tb_alunos' não existe no banco atual.</p>";
}
?>
