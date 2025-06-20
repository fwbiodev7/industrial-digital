<?php
session_start();

$meses = ['maio', 'junho', 'julho'];
$mesSelecionado = $_GET['mes'] ?? 'maio';
if (!in_array($mesSelecionado, $meses)) {
    $mesSelecionado = 'maio';
}

// Verifica se tem alunos no mês e se o id foi passado
if (isset($_SESSION['alunos'][$mesSelecionado]) && isset($_GET['id'])) {
    $idAluno = intval($_GET['id']);

    // Verifica se o índice existe
    if (isset($_SESSION['alunos'][$mesSelecionado][$idAluno])) {
        // Remove o aluno do array
        unset($_SESSION['alunos'][$mesSelecionado][$idAluno]);
        // Reorganiza índices para evitar buracos
        $_SESSION['alunos'][$mesSelecionado] = array_values($_SESSION['alunos'][$mesSelecionado]);
    }
}

// Redireciona de volta para a página principal com o mês selecionado
header("Location: index.php?mes=" . $mesSelecionado);
exit;
