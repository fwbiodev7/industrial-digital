<?php
session_start();
include("conexao.php");

// Criar relacionamento
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['aluno_id']) && isset($_POST['projeto_id'])) {
    $aluno_id = $_POST['aluno_id'];
    $projeto_id = $_POST['projeto_id'];

    $verifica = $conn->prepare("SELECT * FROM tb_relacionamentos WHERE aluno_id = ? AND projeto_id = ?");
    $verifica->bind_param("ii", $aluno_id, $projeto_id);
    $verifica->execute();
    $resultado = $verifica->get_result();

    if ($resultado->num_rows == 0) {
        $inserir = $conn->prepare("INSERT INTO tb_relacionamentos (aluno_id, projeto_id) VALUES (?, ?)");
        $inserir->bind_param("ii", $aluno_id, $projeto_id);
        $inserir->execute();
    }
}

// Excluir relacionamento
if (isset($_GET['excluir'])) {
    $id_excluir = $_GET['excluir'];
    $excluir = $conn->prepare("DELETE FROM tb_relacionamentos WHERE id = ?");
    $excluir->bind_param("i", $id_excluir);
    $excluir->execute();
}

// Buscar dados
$alunos = $conn->query("SELECT * FROM tb_alunos ORDER BY id");
$projetos = $conn->query("SELECT * FROM tb_projetos ORDER BY id");

$relacionamentos = $conn->query("
    SELECT r.id, a.id AS aluno_id, a.nome AS aluno_nome, p.nome AS projeto_nome
    FROM tb_relacionamentos r
    JOIN tb_alunos a ON r.aluno_id = a.id
    JOIN tb_projetos p ON r.projeto_id = p.id
    ORDER BY r.id DESC
");
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Gerenciar Relacionamentos</title>
    <style>
        :root {
            --bg: #121212;
            --card: #1e1e1e;
            --text: #e0e0e0;
            --primary: #4caf50;
            --primary-hover: #43a047;
            --danger: #e53935;
            --danger-hover: #c62828;
            --border: #333;
        }

        body {
            background-color: var(--bg);
            color: var(--text);
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
            padding: 40px;
        }

        h1 {
            text-align: center;
            margin-bottom: 30px;
            font-size: 32px;
        }

        form {
            background-color: var(--card);
            padding: 20px;
            max-width: 600px;
            margin: 0 auto 40px auto;
            border-radius: 12px;
            box-shadow: 0 0 10px rgba(0,0,0,0.3);
        }

        label {
            display: block;
            margin-top: 15px;
            margin-bottom: 5px;
        }

        select, button {
            width: 100%;
            padding: 12px;
            margin-top: 5px;
            border-radius: 8px;
            border: 1px solid var(--border);
            background-color: #2a2a2a;
            color: var(--text);
            font-size: 15px;
        }

        button {
            background-color: var(--primary);
            color: white;
            font-weight: bold;
            cursor: pointer;
            margin-top: 20px;
        }

        button:hover {
            background-color: var(--primary-hover);
        }

        table {
            width: 100%;
            max-width: 1000px;
            margin: auto;
            border-collapse: collapse;
            background-color: var(--card);
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 0 10px rgba(0,0,0,0.3);
        }

        th, td {
            padding: 14px;
            text-align: center;
            border-bottom: 1px solid var(--border);
        }

        th {
            background-color: #292929;
            font-weight: bold;
        }

        tr:hover {
            background-color: #252525;
        }

        .excluir-btn {
            background-color: var(--danger);
            color: white;
            padding: 8px 14px;
            border: none;
            border-radius: 6px;
            text-decoration: none;
            font-size: 14px;
        }

        .excluir-btn:hover {
            background-color: var(--danger-hover);
        }
    </style>
</head>
<body>

    <h1>Gerenciar Relacionamentos</h1>

    <form method="POST">
        <label for="aluno_id">Selecionar Aluno</label>
        <select name="aluno_id" required>
            <option value="">-- Selecione um aluno --</option>
            <?php while ($aluno = $alunos->fetch_assoc()): ?>
                <option value="<?= $aluno['id'] ?>">[ID <?= $aluno['id'] ?>] <?= htmlspecialchars($aluno['nome']) ?></option>
            <?php endwhile; ?>
        </select>

        <label for="projeto_id">Selecionar Projeto</label>
        <select name="projeto_id" required>
            <option value="">-- Selecione um projeto --</option>
            <?php while ($projeto = $projetos->fetch_assoc()): ?>
                <option value="<?= $projeto['id'] ?>"><?= htmlspecialchars($projeto['nome']) ?></option>
            <?php endwhile; ?>
        </select>

        <button type="submit">Criar Relacionamento</button>
    </form>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>ID do Aluno</th>
                <th>Nome do Aluno</th>
                <th>Projeto</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($rel = $relacionamentos->fetch_assoc()): ?>
                <tr>
                    <td><?= $rel['id'] ?></td>
                    <td><?= $rel['aluno_id'] ?></td>
                    <td><?= htmlspecialchars($rel['aluno_nome']) ?></td>
                    <td><?= htmlspecialchars($rel['projeto_nome']) ?></td>
                    <td>
                        <a class="excluir-btn" href="?excluir=<?= $rel['id'] ?>" onclick="return confirm('Tem certeza que deseja excluir este relacionamento?')">Excluir</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

</body>
</html>
