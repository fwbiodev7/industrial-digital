<?php 
session_start();
require_once 'conexao.php';

// Criação de relacionamento
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aluno_id'], $_POST['projeto_id'])) {
    $aluno_id = intval($_POST['aluno_id']);
    $projeto_id = intval($_POST['projeto_id']);

    // Verifica se já existe
    $check = $conn->prepare("SELECT id FROM tb_relacionamentos WHERE aluno_id = ? AND projeto_id = ?");
    $check->bind_param("ii", $aluno_id, $projeto_id);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $_SESSION['erro'] = "Esse relacionamento já existe.";
    } else {
        $insert = $conn->prepare("INSERT INTO tb_relacionamentos (aluno_id, projeto_id) VALUES (?, ?)");
        $insert->bind_param("ii", $aluno_id, $projeto_id);
        if ($insert->execute()) {
            $_SESSION['sucesso'] = "Relacionamento criado com sucesso!";
        } else {
            $_SESSION['erro'] = "Erro ao criar relacionamento.";
        }
        $insert->close();
    }

    $check->close();
    header("Location: relacionamentos.php");
    exit;
}

// Exclusão
if (isset($_GET['remover'])) {
    $idRemover = intval($_GET['remover']);
    $del = $conn->prepare("DELETE FROM tb_relacionamentos WHERE id = ?");
    $del->bind_param("i", $idRemover);
    $del->execute();
    if ($del->affected_rows > 0) {
        $_SESSION['sucesso'] = "Relacionamento removido com sucesso.";
    } else {
        $_SESSION['erro'] = "Erro ao remover relacionamento.";
    }
    $del->close();
    header("Location: relacionamentos.php");
    exit;
}

// Buscar dados para o formulário e a tabela
$alunos = $conn->query("SELECT id, nome FROM tb_alunos ORDER BY nome");
$projetos = $conn->query("SELECT id, nome FROM tb_projetos ORDER BY nome");

$relacionamentos = $conn->query("
    SELECT r.id, a.nome AS nome_aluno, p.nome AS nome_projeto
    FROM tb_relacionamentos r
    JOIN tb_alunos a ON r.aluno_id = a.id
    JOIN tb_projetos p ON r.projeto_id = p.id
    ORDER BY a.nome, p.nome
");
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8" />
    <title>Relacionamentos</title>
    <style>
        /* Reset simples */
        * {
            box-sizing: border-box;
        }
        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #121212;
            color: #e0e0e0;
            padding: 2rem 1rem;
        }
        h1, h2 {
            text-align: center;
            font-weight: 600;
            margin-bottom: 1rem;
            color: #fff;
        }
        form, table {
            max-width: 700px;
            margin: 1.5rem auto;
            background-color: #1e1e1e;
            padding: 1.5rem 2rem;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgb(0 0 0 / 0.7);
            border: 1px solid #333;
        }
        label {
            display: block;
            margin-top: 1rem;
            font-weight: 500;
            color: #ccc;
        }
        select, button {
            width: 100%;
            padding: 0.8rem 1rem;
            border-radius: 6px;
            border: 1px solid #444;
            background-color: #2c2c2c;
            color: #ddd;
            font-size: 1rem;
            margin-top: 0.4rem;
            transition: background-color 0.3s, border-color 0.3s;
        }
        select:focus, button:focus {
            outline: none;
            border-color: #6a9cff;
            background-color: #3a3f58;
        }
        button {
            margin-top: 1.8rem;
            background-color: #4caf50;
            border: none;
            font-weight: 700;
            cursor: pointer;
            color: white;
            box-shadow: 0 3px 8px rgba(76, 175, 80, 0.6);
        }
        button:hover {
            background-color: #388e3c;
            box-shadow: 0 4px 14px rgba(56, 142, 60, 0.8);
        }
        .message {
            max-width: 700px;
            margin: 1rem auto;
            padding: 1rem;
            border-radius: 8px;
            text-align: center;
            font-weight: 600;
            font-size: 0.95rem;
        }
        .success {
            background-color: #2e7d32;
            color: #c8facc;
            border: 1px solid #1b5e20;
            box-shadow: 0 0 8px #1b5e20;
        }
        .error {
            background-color: #b71c1c;
            color: #ffbcbc;
            border: 1px solid #7f0000;
            box-shadow: 0 0 8px #7f0000;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 2rem;
            color: #ddd;
        }
        th, td {
            padding: 0.9rem 1rem;
            border-bottom: 1px solid #333;
        }
        th {
            background-color: #3a3f58;
            font-weight: 700;
            text-align: left;
        }
        tr:hover {
            background-color: #2c2c2c;
        }
        .btn-remove {
            background-color: #e53935;
            color: white;
            padding: 0.35rem 0.75rem;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 600;
            transition: background-color 0.3s;
        }
        .btn-remove:hover {
            background-color: #ab000d;
        }

        /* Nav já com margin-bottom para separar */
        nav {
            max-width: 700px;
            margin: 0 auto 2rem auto;
        }
    </style>
</head>
<body>

<?php require_once 'nav.php'; ?>

<h1>Central de Relacionamentos de Alunos e Projetos</h1>

<?php if (isset($_SESSION['sucesso'])): ?>
    <div class="message success"><?= htmlspecialchars($_SESSION['sucesso']) ?></div>
    <?php unset($_SESSION['sucesso']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['erro'])): ?>
    <div class="message error"><?= htmlspecialchars($_SESSION['erro']) ?></div>
    <?php unset($_SESSION['erro']); ?>
<?php endif; ?>

<form method="post" novalidate>
    <label for="aluno_id">Aluno:</label>
    <select name="aluno_id" id="aluno_id" required>
        <option value="">-- Selecione um aluno --</option>
        <?php while ($a = $alunos->fetch_assoc()): ?>
            <option value="<?= $a['id'] ?>"><?= htmlspecialchars($a['nome']) ?></option>
        <?php endwhile; ?>
    </select>

    <label for="projeto_id">Projeto:</label>
    <select name="projeto_id" id="projeto_id" required>
        <option value="">-- Selecione um projeto --</option>
        <?php while ($p = $projetos->fetch_assoc()): ?>
            <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nome']) ?></option>
        <?php endwhile; ?>
    </select>

    <button type="submit">Relacionar</button>
</form>

<h2>Relacionamentos existentes</h2>
<table>
    <thead>
        <tr>
            <th>Aluno</th>
            <th>Projeto</th>
            <th>Ação</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($relacionamentos && $relacionamentos->num_rows > 0): ?>
            <?php while ($r = $relacionamentos->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($r['nome_aluno']) ?></td>
                    <td><?= htmlspecialchars($r['nome_projeto']) ?></td>
                    <td>
                        <a class="btn-remove" href="relacionamentos.php?remover=<?= $r['id'] ?>" onclick="return confirm('Tem certeza que deseja remover este relacionamento?')">Remover</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="3" style="text-align:center; color:#777;">Nenhum relacionamento cadastrado ainda.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

</body>
</html>
