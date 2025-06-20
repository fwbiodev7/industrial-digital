<?php
require_once 'banco.php';

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $res = excluirProjeto($id);
    if ($res === true) {
        header('Location: index.php?msg=Projeto excluído com sucesso');
        exit;
    } else {
        echo "Erro ao excluir projeto: " . $res;
    }
} else {
    header('Location: index.php');
    exit;
}
?>
