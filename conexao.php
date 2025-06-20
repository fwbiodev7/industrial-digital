<?php
// Configurações do banco de dados
$host = 'srv1664.hstgr.io';
$db   = 'u344105464_alunoprojeto';
$user = 'u344105464_ualunoprojeto';
$pass = 'Fabio0204#';

// Criar conexão
$conn = new mysqli($host, $user, $pass, $db);

// Verificar conexão
if ($conn->connect_error) {
    die("Erro na conexão com o banco de dados: " . $conn->connect_error);
}

// Configurar charset para evitar problemas de acentuação
$conn->set_charset("utf8mb4");
?>
