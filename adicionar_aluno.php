<?php
// Desativa a exibição de erros para o ambiente de produção
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(0);

// Conexão MySQL
$host = 'srv1664.hstgr.io';
$db   = 'u344105464_alunoprojeto';
$user = 'u344105464_ualunoprojeto';
$pass = 'Fabio0204#';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die('Erro de conexão: ' . $conn->connect_error);
}

$meses = ['maio', 'junho', 'julho'];
$mesSelecionado = $_GET['mes'] ?? 'maio';
if (!in_array($mesSelecionado, $meses)) {
    $mesSelecionado = 'maio';
}

$erro = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $mes = $_POST['mes'] ?? 'maio';
    if (!in_array($mes, $meses)) $mes = 'maio';

    $projetos = $_POST['projetos'] ?? [];

    if ($nome === '') {
        $erro = 'O nome do aluno é obrigatório.';
    } elseif (!isset($_FILES['foto']) || $_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
        $erro = 'A foto do aluno é obrigatória e deve ser válida.';
    } else {
        $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
        $extPermitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (!in_array($ext, $extPermitidas)) {
            $erro = 'Formato da foto não permitido. Use jpg, png, gif ou webp.';
        } else {
            if (!is_dir('uploads')) mkdir('uploads', 0755, true);
            $novoNome = uniqid('foto_') . '.' . $ext;
            $destino = 'uploads/' . $novoNome;
            if (move_uploaded_file($_FILES['foto']['tmp_name'], $destino)) {
                $stmt = $conn->prepare("INSERT INTO tb_alunos (nome, foto, mes) VALUES (?, ?, ?)");
                if (!$stmt) die("Erro no prepare: " . $conn->error);
                $stmt->bind_param('sss', $nome, $destino, $mes);
                if ($stmt->execute()) {
                    $idAluno = $stmt->insert_id;
                    $stmt->close();

                    if (!empty($projetos)) {
                        $stmtProjeto = $conn->prepare("INSERT INTO tb_projetos (nome, descricao, status, mes, id_aluno) VALUES (?, '', ?, ?, ?)");
                        if ($stmtProjeto) {
                            foreach ($projetos as $proj) {
                                $projNome = trim($proj['nome'] ?? '');
                                $projStatus = trim($proj['status'] ?? '');
                                if ($projNome !== '') {
                                    $stmtProjeto->bind_param('sssi', $projNome, $projStatus, $mes, $idAluno);
                                    $stmtProjeto->execute();
                                }
                            }
                            $stmtProjeto->close();
                        }
                    }

                    header("Location: index.php?mes=" . urlencode($mes));
                    exit();
                } else {
                    $erro = 'Erro ao salvar no banco: ' . $stmt->error;
                }
            } else {
                $erro = 'Erro ao mover o arquivo.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <link rel="preconnect" href="https://fonts.gstatic.com/" crossorigin="" />
    <link
        rel="stylesheet"
        as="style"
        onload="this.rel='stylesheet'"
        href="https://fonts.googleapis.com/css2?display=swap&family=Noto+Sans%3Awght%40400%3B500%3B700%3B900&family=Work+Sans%3Awght%40400%3B500%3B700%3B900"
    />

    <title>Adicionar Aluno - Industrial Digital</title>
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

        /* Fundo principal do formulário e cards de projeto */
        .bg-dark-card {
            background-color: rgb(15, 15, 15); /* Fundo dos cards e formulário */
        }

        /* Cores para o botão primário (Salvar Aluno) */
        .btn-primary-custom {
            background-color: #d2e2f3; /* Mantido como no index para contraste */
            color: #14191f;
        }

        .btn-primary-custom:hover {
            background-color: #c0d1e0;
            transform: scale(1.02);
        }

        /* Cores para o botão de adicionar projeto */
        .btn-add-projeto-custom {
            background-color: #20252a; /* Um tom de cinza escuro para o botão */
            color: white;
        }

        .btn-add-projeto-custom:hover {
            background-color: #2c333a; /* Ligeiramente mais claro no hover */
            transform: translateY(-1px);
        }

        /* Estilo para campos de input e select (ainda mais escuros) */
        input[type="text"],
        input[type="file"],
        select {
            background-color: rgb(20, 20, 20); /* Fundo dos inputs ainda mais escuro */
            border: 1px solid rgb(50, 50, 50); /* Borda mais escura */
            color: #e0e0e0; /* Texto nos inputs */
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            font-size: 1rem;
            width: 100%;
            appearance: none;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        input[type="text"]:focus,
        input[type="file"]:focus,
        select:focus {
            border-color: #63b3ed;
            outline: none;
            box-shadow: 0 0 0 3px rgba(99, 179, 237, 0.5);
        }

        /* Estilo para labels */
        label {
            color: #e0e0e0;
            font-weight: 600;
            margin-bottom: 0.5rem;
            display: block;
        }

        /* Estilo para a caixa de erro */
        .alert-erro {
            background-color: #6b2e35; /* Fundo vermelho escuro para erro */
            color: #f9d7da; /* Texto claro para erro */
            border: 1px solid #a94a53; /* Borda vermelha escura */
            padding: 1rem;
            border-radius: 0.75rem;
            margin-bottom: 1.5rem;
            text-align: center;
            font-weight: 600;
        }
        /* Estilo para o separador */
        .separator {
            border-color: rgb(50, 50, 50); /* Separador mais escuro */
        }

        /* Estilo para card de projeto individual */
        .projeto-item {
            background-color: rgb(20, 20, 20); /* Fundo do item de projeto mais escuro */
            border: 1px solid rgb(50, 50, 50); /* Borda do item de projeto mais escura */
            border-radius: 0.75rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.4); /* Sombra mais intensa */
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .projeto-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 10px rgba(0, 0, 0, 0.6); /* Sombra ainda mais intensa no hover */
        }

        .btn-remover-custom {
            background-color: #dc2626; /* Vermelho padrão para remover */
            color: white;
            padding: 0.4rem 0.8rem;
            border-radius: 0.5rem;
            transition: background-color 0.2s ease;
        }

        .btn-remover-custom:hover {
            background-color: #b91c1c; /* Vermelho mais escuro no hover */
        }

        /* Estilo para o link "Voltar" */
        .voltar-link {
            color: #63b3ed; /* Mantido como um tom de azul para links */
            text-decoration: none;
            transition: color 0.2s ease;
        }

        .voltar-link:hover {
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
                    <h2 class="text-white text-2xl sm:text-3xl font-bold leading-tight tracking-[-0.015em] mb-8 text-center">Adicionar Aluno - <?= ucfirst($mesSelecionado); ?></h2>

                    <?php if ($erro): ?>
                        <div class="alert-erro mb-6"><?= htmlspecialchars($erro); ?></div>
                    <?php endif; ?>

                    <form action="adicionar_aluno.php" method="post" enctype="multipart/form-data" novalidate class="bg-dark-card p-6 rounded-xl shadow-lg flex flex-col gap-5">
                        <div>
                            <label for="nome" class="block text-sm font-semibold mb-2">Nome do aluno</label>
                            <input type="text" name="nome" id="nome" maxlength="255" required
                                value="<?= isset($_POST['nome']) ? htmlspecialchars($_POST['nome']) : ''; ?>"
                                placeholder="Ex: João Silva"
                                class="w-full px-4 py-2 rounded-lg" />
                        </div>

                        <div>
                            <label for="foto" class="block text-sm font-semibold mb-2">Foto do aluno</label>
                            <input type="file" name="foto" id="foto" accept="image/*" required
                                class="w-full px-4 py-2 rounded-lg" />
                        </div>

                        <div>
                            <label for="mes" class="block text-sm font-semibold mb-2">Mês</label>
                            <select name="mes" id="mes" required
                                class="w-full px-4 py-2 rounded-lg">
                                <?php foreach ($meses as $m): ?>
                                    <option value="<?= $m; ?>" <?= ($mesSelecionado === $m) ? 'selected' : ''; ?>>
                                        <?= ucfirst($m); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <hr class="border-t separator my-4">

                        <h5 class="text-white text-lg font-semibold mb-3">Projetos</h5>
                        <div id="projetos-container" class="space-y-4"></div>

                        <button type="button" id="btn-add-projeto" class="flex items-center justify-center h-10 px-4 rounded-xl btn-add-projeto-custom text-sm font-bold transition duration-300 transform hover:scale-105">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 256 256" class="mr-2"><path d="M224,128a8,8,0,0,1-8,8H136v80a8,8,0,0,1-16,0V136H40a8,8,0,0,1,0-16H120V40a8,8,0,0,1,16,0v80h80A8,8,0,0,1,224,128Z"></path></svg>
                            Adicionar Projeto
                        </button>

                        <button type="submit" class="flex items-center justify-center h-10 px-4 rounded-xl btn-primary-custom text-sm font-bold transition duration-300 transform hover:scale-105">
                            Salvar Aluno
                        </button>
                    </form>

                    <a href="index.php?mes=<?= urlencode($mesSelecionado); ?>" class="voltar-link mt-8 text-center text-blue-400 font-semibold flex items-center justify-center gap-2 hover:underline">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 256 256"><path d="M128,24A104,104,0,1,0,232,128,104.11,104.11,0,0,0,128,24Zm0,192a88,88,0,1,1,88-88A88.1,88.1,0,0,1,128,216Zm-36.26-92.26L96,120.34l31.54-31.54a8,8,0,0,1,11.32,11.32L119.31,128l3.53,3.54a8,8,0,0,1-11.32,11.32L96,135.66l-4.26,4.26a8,8,0,0,1-11.31-11.31l4.26-4.26Z"></path></svg>
                        Voltar para a lista
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        const projetosContainer = document.getElementById('projetos-container');
        const btnAddProjeto = document.getElementById('btn-add-projeto');
        let projetoIndex = 0;

        function criarProjeto(nome = '', status = '') {
            const div = document.createElement('div');
            div.className = 'projeto-item p-4'; // Tailwind classes for styling
            div.innerHTML = `
                <div class="flex flex-col gap-3">
                    <div class="flex justify-between items-center mb-2">
                        <h6 class="text-white text-md font-bold">Projeto ${projetoIndex + 1}</h6>
                        <button type="button" class="btn-remover-custom" title="Remover projeto">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 256 256"><path d="M216,48H176V40a24,24,0,0,0-24-24H104A24,24,0,0,0,80,40v8H40a8,8,0,0,0,0,16H216a8,8,0,0,0,0-16ZM88,40a8,8,0,0,1,8-8h64a8,8,0,0,1,8,8v8H88ZM200,64H56V216a16,16,0,0,0,16,16H184a16,16,0,0,0,16-16ZM72,208V80H184V208Zm40-104V184a8,8,0,0,1-16,0V104a8,8,0,0,1,16,0Zm48,0V184a8,8,0,0,1-16,0V104a8,8,0,0,1,16,0Z"></path></svg>
                        </button>
                    </div>
                    <label for="projeto-nome-${projetoIndex}" class="block text-sm font-semibold mb-1">Nome do projeto</label>
                    <input type="text" class="w-full px-4 py-2 rounded-lg" id="projeto-nome-${projetoIndex}" name="projetos[${projetoIndex}][nome]" placeholder="Ex: App de Reciclagem" value="${nome}" required>

                    <label for="projeto-status-${projetoIndex}" class="block text-sm font-semibold mb-1">Status do projeto</label>
                    <input type="text" class="w-full px-4 py-2 rounded-lg" id="projeto-status-${projetoIndex}" name="projetos[${projetoIndex}][status]" placeholder="Ex: Em andamento" value="${status}" required>
                </div>
            `;

            div.querySelector('.btn-remover-custom').onclick = () => {
                div.remove();
                atualizarIndices();
            };

            projetosContainer.appendChild(div);
            projetoIndex++;
        }

        function atualizarIndices() {
            const cards = projetosContainer.querySelectorAll('.projeto-item');
            projetoIndex = 0;
            cards.forEach(card => {
                card.querySelector('h6').textContent = `Projeto ${projetoIndex + 1}`;

                const inputNome = card.querySelector('input[name^="projetos"][name$="[nome]"]');
                const inputStatus = card.querySelector('input[name^="projetos"][name$="[status]"]');

                inputNome.name = `projetos[${projetoIndex}][nome]`;
                inputNome.id = `projeto-nome-${projetoIndex}`;
                card.querySelector('label[for^="projeto-nome"]').setAttribute('for', `projeto-nome-${projetoIndex}`);

                inputStatus.name = `projetos[${projetoIndex}][status]`;
                inputStatus.id = `projeto-status-${projetoIndex}`;
                card.querySelector('label[for^="projeto-status"]').setAttribute('for', `projeto-status-${projetoIndex}`);

                projetoIndex++;
            });
        }

        // Se a página foi submetida com projetos, carrega eles na tela
        <?php if (!empty($_POST['projetos']) && is_array($_POST['projetos'])): ?>
            const projetosPost = <?= json_encode($_POST['projetos']); ?>;
            projetosPost.forEach(proj => criarProjeto(proj.nome || '', proj.status || ''));
        <?php else: ?>
            criarProjeto(); // Adiciona um projeto vazio por padrão
        <?php endif; ?>

        btnAddProjeto.addEventListener('click', () => {
            criarProjeto();
            window.scrollTo({ top: document.body.scrollHeight, behavior: 'smooth' });
        });
    </script>
</body>
</html>