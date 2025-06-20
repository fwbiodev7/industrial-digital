<?php
require_once 'conexao.php';

// --- ALUNOS ---

function adicionarAluno($nome, $foto, $mes) {
    global $conn;
    $sql = "INSERT INTO tb_alunos (nome, foto, mes) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return "Erro na preparação da query adicionarAluno: " . $conn->error;
    }
    $stmt->bind_param("sss", $nome, $foto, $mes);
    if ($stmt->execute()) {
        return true;
    } else {
        return "Erro ao adicionar aluno: " . $stmt->error;
    }
}

function listarAlunos() {
    global $conn;
    $sql = "SELECT * FROM tb_alunos ORDER BY mes, nome";
    $result = $conn->query($sql);
    if (!$result) {
        return false;
    }
    $alunos = [];
    while ($row = $result->fetch_assoc()) {
        $alunos[] = $row;
    }
    return $alunos;
}

function excluirAluno($id) {
    global $conn;

    // Excluir projetos do aluno antes
    $sqlProjetos = "DELETE FROM tb_projetos WHERE aluno_id = ?";
    $stmtProjetos = $conn->prepare($sqlProjetos);
    if (!$stmtProjetos) {
        return "Erro na preparação da query excluirAluno (projetos): " . $conn->error;
    }
    $stmtProjetos->bind_param("i", $id);
    $stmtProjetos->execute();

    // Excluir aluno
    $sql = "DELETE FROM tb_alunos WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return "Erro na preparação da query excluirAluno (aluno): " . $conn->error;
    }
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        return true;
    } else {
        return "Erro ao excluir aluno: " . $stmt->error;
    }
}
function buscarAlunos($conn) {
    $sql = "SELECT id, nome FROM tb_alunos ORDER BY nome";
    $result = $conn->query($sql);
    $alunos = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $alunos[] = $row;
        }
    }
    return $alunos;
}
// --- PROJETOS ---

function adicionarProjeto($aluno_id, $nomeProjeto, $descricao, $status, $mes) {
    global $conn;
    $sql = "INSERT INTO tb_projetos (aluno_id, nome, descricao, status, mes) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return "Erro na preparação da query adicionarProjeto: " . $conn->error;
    }
    $stmt->bind_param("issss", $aluno_id, $nomeProjeto, $descricao, $status, $mes);
    if ($stmt->execute()) {
        return true;
    } else {
        return "Erro ao adicionar projeto: " . $stmt->error;
    }
}

function listarProjetosDoAluno($aluno_id) {
    global $conn;
    $sql = "SELECT * FROM tb_projetos WHERE aluno_id = ? ORDER BY mes";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return "Erro na preparação da query listarProjetosDoAluno: " . $conn->error;
    }
    $stmt->bind_param("i", $aluno_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if (!$result) {
        return false;
    }
    $projetos = [];
    while ($row = $result->fetch_assoc()) {
        $projetos[] = $row;
    }
    return $projetos;
}

function excluirProjeto($id) {
    global $conn;
    $sql = "DELETE FROM tb_projetos WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return "Erro na preparação da query excluirProjeto: " . $conn->error;
    }
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        return true;
    } else {
        return "Erro ao excluir projeto: " . $stmt->error;
    }
}
function buscarProjetosAluno($conn, $aluno_id) {
    $sql = "SELECT p.id AS projeto_id, p.nome AS projeto_nome, p.descricao, p.status 
            FROM tb_projetos p
            INNER JOIN tb_relacionamentos r ON p.id = r.projeto_id
            WHERE r.aluno_id = ?";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Erro no prepare: " . $conn->error);
    }
    
    $stmt->bind_param("i", $aluno_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $projetos = [];
    while ($row = $result->fetch_assoc()) {
        $projetos[] = $row;
    }
    
    $stmt->close();
    return $projetos;
}

?>
