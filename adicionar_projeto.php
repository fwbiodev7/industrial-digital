<?php
session_start();
// Desativa a exibição de erros para o ambiente de produção
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(0);

require_once 'banco.php'; // Sua conexão MySQLi

// Busca os alunos para popular o select
$sqlAlunos = "SELECT id, nome FROM tb_alunos ORDER BY nome";
$resultAlunos = $conn->query($sqlAlunos);
if (!$resultAlunos) {
    die("Erro ao buscar alunos: " . $conn->error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome']);
    $descricao = trim($_POST['descricao']);
    $status = trim($_POST['status']);
    $mes = trim($_POST['mes']);
    $aluno_id = isset($_POST['aluno_id']) ? intval($_POST['aluno_id']) : 0;

    if (empty($nome)) {
        $_SESSION['erro'] = "O nome do projeto é obrigatório.";
        header('Location: adicionar_projeto.php');
        exit;
    }
    if ($aluno_id <= 0) {
        $_SESSION['erro'] = "Selecione um aluno válido.";
        header('Location: adicionar_projeto.php');
        exit;
    }

    // Insere projeto incluindo aluno_id
    $sql = "INSERT INTO tb_projetos (nome, descricao, status, mes, id_aluno) VALUES (?, ?, ?, ?, ?)"; // Ajustado para id_aluno
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        $_SESSION['erro'] = "Erro na preparação da consulta: " . $conn->error;
        header('Location: adicionar_projeto.php');
        exit;
    }
    $stmt->bind_param('ssssi', $nome, $descricao, $status, $mes, $aluno_id);

    if ($stmt->execute()) {
        $_SESSION['sucesso'] = "Projeto cadastrado com sucesso!";
        header('Location: projetos.php'); // Redireciona para a listagem de projetos após sucesso
        exit;
    } else {
        $_SESSION['erro'] = "Erro ao cadastrar projeto: " . $stmt->error;
        header('Location: adicionar_projeto.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <link rel="preconnect" href="https://fonts.gstatic.com/" crossorigin="" />
    <link
        rel="stylesheet"
        as="style"
        onload="this.rel='stylesheet'"
        href="https://fonts.googleapis.com/css2?display=swap&family=Noto+Sans%3Awght%40400%3B500%3B700%3B900&family=Work+Sans%3Awght%40400%3B500%3B700%3B900"
    />

    <title>Adicionar Projeto - Industrial Digital</title>
    <link rel="icon" type="image/png" href="uploads/faviconind.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <style>
        /* Estilos base (ainda mais escuros) */
        body {
            background-color: rgb(10, 12, 14); /* Base ainda mais escura */
            color: #f0f0f0; /* Texto levemente mais claro para contraste */
            font-family: "Work Sans", "Noto Sans", sans-serif;
        }

        header {
            background-color: rgb(0, 0, 0); /* Preto puro para o cabeçalho */
            border-bottom-color: rgb(0, 0, 0); /* Sem borda visível no header */
        }

        .text-white {
            color: #f0f0f0; /* Garante consistência com o novo texto */
        }

        /* Fundo principal do formulário */
        .bg-dark-card {
            background-color: rgb(15, 15, 15); /* Fundo do formulário */
        }

        /* Cores para o botão de enviar */
        .btn-primary-custom {
            background-color: #d2e2f3; /* Cor do botão "Ver Projetos" do index */
            color: #14191f; /* Cor do texto do botão "Ver Projetos" do index */
        }

        .btn-primary-custom:hover {
            background-color: #c0d1e0; /* Um pouco mais escuro no hover */
            transform: scale(1.02); /* Pequeno efeito de escala no hover */
        }

        /* Estilo para campos de input, select e textarea (ainda mais escuros) */
        input[type="text"],
        input[type="month"],
        select,
        textarea {
            background-color: rgb(20, 20, 20); /* Fundo dos inputs ainda mais escuro */
            border: 1px solid rgb(50, 50, 50); /* Borda mais escura */
            color: #e0e0e0; /* Texto nos inputs */
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            font-size: 1rem;
            width: 100%;
            appearance: none; /* Remove estilos padrão para select */
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        input[type="text"]:focus,
        input[type="month"]:focus,
        select:focus,
        textarea:focus {
            border-color: #63b3ed;
            outline: none;
            box-shadow: 0 0 0 3px rgba(99, 179, 237, 0.5);
        }

        /* Estilo para labels */
        label {
            color:rgb(10, 10, 10);
            font-weight: 600;
            margin-bottom: 0.5rem;
            display: block;
        }

        /* Estilo para a caixa de erro */
        .alert-error { /* Renomeado de .error para consistência */
            background-color: #6b2e35; /* Fundo vermelho escuro para erro */
            color: #f9d7da; /* Texto claro para erro */
            border: 1px solid #a94a53; /* Borda vermelha escura */
            padding: 1rem;
            border-radius: 0.75rem;
            margin-bottom: 1.5rem;
            text-align: center;
            font-weight: 600;
        }
        /* Estilo para a caixa de sucesso */
        .alert-success { /* Renomeado de .success para consistência */
            background-color: #0f5132; /* Fundo verde escuro para sucesso */
            color: #d1e7dd; /* Texto claro para sucesso */
            border: 1px solid #0a3622; /* Borda verde escura */
            padding: 1rem;
            border-radius: 0.75rem;
            margin-bottom: 1.5rem;
            text-align: center;
            font-weight: 600;
        }

        /* Estilo para o link "Voltar" */
        .back-link-custom {
            color: #63b3ed; /* Cor de link azul clara */
            text-decoration: none;
            transition: color 0.2s ease;
        }

        .back-link-custom:hover {
            color: #4299e1;
            text-decoration: underline;
        }
    </style>
</head>
<body class="dark">
    <div class="relative flex min-h-screen flex-col bg-body group/design-root overflow-x-hidden font-[Work_Sans,_Noto_Sans,_sans-serif]">
        <div class="layout-container flex h-full grow flex-col">
            <header class="flex items-center justify-between whitespace-nowrap border-b border-solid border-b-black px-4 sm:px-6 md:px-10 py-3">
                <div class="flex items-center gap-4 text-white">
                    <div class="size-4">
                        <svg viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M24 4H6V17.3333V30.6667H24V44H42V30.6667V17.3333H24V4Z" fill="currentColor"></path>
                        </svg>
                    </div>
                </div>
            </header>

            <div class="px-4 py-5 flex-1 flex justify-center">
                <div class="layout-content-container flex flex-col w-full max-w-xl">
                    <h2 class="text-white text-2xl sm:text-3xl font-bold leading-tight tracking-[-0.015em] mb-8 text-center">Adicionar Projeto</h2>

                    <?php if (isset($_SESSION['erro'])): ?>
                        <div class="alert-error mb-6"><?= htmlspecialchars($_SESSION['erro']) ?></div>
                        <?php unset($_SESSION['erro']); ?>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['sucesso'])): ?>
                        <div class="alert-success mb-6"><?= htmlspecialchars($_SESSION['sucesso']) ?></div>
                        <?php unset($_SESSION['sucesso']); ?>
                    <?php endif; ?>

                    <form method="post" autocomplete="off" novalidate class="bg-dark-card p-6 rounded-xl shadow-lg flex flex-col gap-5">
                        <div>
                            <label for="nome" class="block text-white font-bold mb-2">Nome do Projeto <span class="text-red-500">*</span></label>
                            <input
                                type="text"
                                id="nome"
                                name="nome"
                                required
                                maxlength="255"
                                placeholder="Digite o nome do projeto"
                                value="<?= htmlspecialchars($_POST['nome'] ?? '') ?>"
                                class="w-full px-4 py-2 rounded-lg bg-black text-white font-semibold"
                            />
                        </div>

<div>
    <label for="descricao" class="block text-white font-bold mb-2">Descrição</label>
    <textarea
        id="descricao"
        name="descricao"
        rows="4"
        placeholder="Descrição do projeto"
        class="w-full px-4 py-2 rounded-lg bg-black text-white font-semibold"
    ><?= htmlspecialchars($_POST['descricao'] ?? '') ?></textarea>
</div>


<div>
    <label for="status" class="block text-white font-bold mb-2">Status</label>
    <input
        type="text"
        id="status"
        name="status"
        maxlength="50"
        placeholder="Ex: Em andamento, Concluído"
        value="<?= htmlspecialchars($_POST['status'] ?? '') ?>"
        class="w-full px-4 py-2 rounded-lg bg-black text-white font-semibold"
    />
</div>



<div>
    <label for="mes" class="block text-white font-bold mb-2">Mês/Ano</label>
    <input
        type="month"
        id="mes"
        name="mes"
        value="<?= htmlspecialchars($_POST['mes'] ?? '') ?>"
        class="w-full px-4 py-2 rounded-lg bg-black text-white font-semibold"
    />
</div>



<div>
    <label for="aluno_id" class="block text-white font-bold mb-2">Aluno <span class="text-red-500">*</span></label>
    <select id="aluno_id" name="aluno_id" required
        class="w-full px-4 py-2 rounded-lg bg-black text-white font-semibold">
        <option value="" disabled <?= !isset($_POST['aluno_id']) ? 'selected' : '' ?>>Selecione um aluno</option>
        <?php
            if ($resultAlunos->num_rows > 0) {
                mysqli_data_seek($resultAlunos, 0); // Resetar ponteiro de resultado
                while ($row = $resultAlunos->fetch_assoc()) {
                    $selected = (isset($_POST['aluno_id']) && $_POST['aluno_id'] == $row['id']) ? 'selected' : '';
                    echo '<option value="' . htmlspecialchars($row['id']) . '" ' . $selected . '>'
                        . htmlspecialchars($row['nome']) . '</option>';
                }
            } else {
                echo '<option value="">Nenhum aluno cadastrado</option>';
            }
        ?>
    </select>
</div>

                        <button type="submit" class="flex items-center justify-center h-10 px-4 rounded-xl btn-primary-custom text-sm font-bold transition duration-300 transform hover:scale-105">
                            Cadastrar Projeto
                        </button>
                    </form>

                <div class="flex justify-center">
    <a href="projetos.php" class="inline-block border-2 border-white text-white font-bold py-2 px-3 rounded-lg hover:bg-white hover:text-black transition duration-300 ease-in-out w-auto">
        Voltar para os Projetos
    </a>
</div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>