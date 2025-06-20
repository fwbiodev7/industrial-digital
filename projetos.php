<?php
session_start();
require_once 'conexao.php';

// Verifica se a conexão está OK
if (!$conn) {
    die("Erro na conexão com o banco de dados.");
}

// Processar exclusão de projeto
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['excluir_id'])) {
    $idExcluir = intval($_POST['excluir_id']);
    if ($idExcluir > 0) {
        $sqlDelete = "DELETE FROM tb_projetos WHERE id = ?";
        $stmt = $conn->prepare($sqlDelete);
        if ($stmt) {
            $stmt->bind_param('i', $idExcluir);
            $stmt->execute();
            if ($stmt->affected_rows > 0) {
                $_SESSION['sucesso'] = "Projeto excluído com sucesso.";
            } else {
                $_SESSION['erro'] = "Projeto não encontrado ou já excluído.";
            }
            $stmt->close();
        } else {
            $_SESSION['erro'] = "Erro ao preparar a exclusão.";
        }
        header("Location: projetos.php");
        exit;
    }
}

// Buscar todos os projetos com o nome do aluno associado
$sql = "SELECT p.id AS projeto_id, p.nome AS projeto_nome, p.descricao, p.status, p.mes,
             a.id AS aluno_id, a.nome AS aluno_nome
             FROM tb_projetos p
             LEFT JOIN tb_alunos a ON p.aluno_id = a.id
             ORDER BY p.id DESC";

$res = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8" />
    <title>Lista de Projetos</title>
    <link rel="preconnect" href="https://fonts.gstatic.com/" crossorigin="" />
    <link
        rel="stylesheet"
        as="style"
        onload="this.rel='stylesheet'"
        href="https://fonts.googleapis.com/css2?display=swap&family=Noto+Sans%3Awght%40400%3B500%3B700%3B900&family=Work+Sans%3Awght%40400%3B500%3B700%3B900"
    />
    <link rel="icon" type="image/x-icon" href="data:image/x-icon;base64," />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <style>
        /* Estilos CSS do index.php aplicados aqui para consistência */
        body {
            background-color: rgb(14, 16, 19); /* Fundo muito escuro */
            color: #f8f9fa; /* Cor do texto padrão para o body */
        }

        header {
            background-color: rgb(6, 7, 8); /* Cabeçalho ainda mais escuro */
            border-bottom-color: rgb(0, 0, 0); /* Borda preta */
        }

        .text-white {
            color: #f8f9fa; /* Garante que elementos com text-white sejam bem claros */
        }

        .border-b-[#293542] { /* Esta classe se refere à borda do header, será sobrescrita pela acima */
            border-color: #495057;
        }

        .bg-[#293542] { /* Cor de fundo para botões específicos, como o de tema */
            background-color: rgb(15, 15, 15);
            color: #fff;
        }

        .card-projeto { /* Nomeei a classe para os cards de projetos */
            background-color: rgb(32, 37, 44); /* Fundo transparente como os cards de aluno no index.php */
            box-shadow: none; /* Remove qualquer sombra padrão */
        }

        .card-projeto p, .card-projeto strong { /* Cor para textos dentro dos cards de projeto */
            color: #e0e0e0;
        }

        /* Estilos para o botão "Adicionar Projeto" baseado no index.php */
        .btn-add-projeto {
            background-color: transparent; /* Fundo transparente */
            border: 1px solid #d2e2f3; /* Borda da cor do texto */
            color: #d2e2f3; /* Cor do texto padrão */
            transition: background-color 0.3s ease, color 0.3s ease, transform 0.2s ease;
        }

        .btn-add-projeto:hover {
            background-color: #d2e2f3; /* Fundo preenchido no hover */
            color: #14191f; /* Texto escuro no hover */
            transform: scale(1.05);
        }

        /* Botão de exclusão para combinar com o estilo geral */
        .btn-excluir {
            background-color: #dc3545; /* Vermelho padrão */
            color: white;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }

        .btn-excluir:hover {
            background-color: #c82333; /* Vermelho mais escuro no hover */
            transform: scale(1.03);
        }

        /* Mensagens de sucesso e erro - cores do index.php não são tão escuras, usando cores Tailwind mais escuras que combinam */
        .bg-green-50, .dark .bg-green-700 {
            background-color: rgba(16, 185, 129, 0.2); /* Tom verde escuro transparente */
            color: #34d399; /* Cor do texto verde claro */
        }
        .bg-red-50, .dark .bg-red-700 {
            background-color: rgba(239, 68, 68, 0.2); /* Tom vermelho escuro transparente */
            color: #f87171; /* Cor do texto vermelho claro */
        }
    </style>
</head>
<body class="dark">
    <div class="relative flex size-full min-h-screen flex-col bg-body group/design-root overflow-x-hidden" style='font-family: "Work Sans", "Noto Sans", sans-serif;'>
        <div class="layout-container flex h-full grow flex-col">
            <header class="flex items-center justify-between whitespace-nowrap border-b border-solid border-b-[#293542] px-4 sm:px-6 lg:px-10 py-3">
                <div class="flex items-center gap-4 text-white">
                    <div class="size-4">
                        <svg viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M24 4H6V17.3333V30.6667H24V44H42V30.6667V17.3333H24V4Z" fill="currentColor"></path>
                        </svg>
                    </div>
                    <h2 class="text-white text-lg font-bold leading-tight tracking-[-0.015em]">Lista de Projetos</h2>
                </div>
                <div class="flex flex-1 justify-end gap-4 sm:gap-8">
                    <div class="flex gap-2">
                      <a href="adicionar_projeto.php"
           class="flex items-center justify-center h-10 px-4 rounded-xl bg-[#d2e2f3] text-[#14191f] text-sm font-bold transition duration-300 transform hover:scale-105">
            Adicionar Projetos
        </a>
                        
                    </div>
                </div>
            </header>
            <div class="px-4 sm:px-6 lg:px-40 flex flex-1 justify-center py-5">
                <div class="layout-content-container flex flex-col max-w-[1100px] flex-1">
                    <?php if (isset($_SESSION['sucesso'])): ?>
                        <div class="rounded-md bg-green-50 dark:bg-green-700 p-4 mb-4">
                            <div class="flex">
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-green-800 dark:text-green-400"><?= htmlspecialchars($_SESSION['sucesso']) ?></h3>
                                </div>
                            </div>
                        </div>
                        <?php unset($_SESSION['sucesso']); ?>
                    <?php endif; ?>
                    <?php if (isset($_SESSION['erro'])): ?>
                        <div class="rounded-md bg-red-50 dark:bg-red-700 p-4 mb-4">
                            <div class="flex">
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-red-800 dark:text-red-400"><?= htmlspecialchars($_SESSION['erro']) ?></h3>
                                </div>
                            </div>
                        </div>
                        <?php unset($_SESSION['erro']); ?>
                    <?php endif; ?>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
                        <?php if (!$res || $res->num_rows === 0): ?>
                            <p class="text-white text-lg">Nenhum projeto cadastrado.</p>
                        <?php else: ?>
                            <?php while ($projeto = $res->fetch_assoc()): ?>
                                <div class="rounded-xl p-4 sm:p-6 flex flex-col card-projeto">
                                    <div>
                                        <h3 class="text-white text-xl font-semibold mb-2"><?= htmlspecialchars($projeto['projeto_nome']) ?></h3>
                                        <a href="ver_projetos.php?aluno_id=<?= intval($projeto['aluno_id']) ?>" class="inline-block text-blue-400 hover:text-blue-500 text-sm font-semibold mb-2">
                                            <?= htmlspecialchars($projeto['aluno_nome'] ?: 'Aluno não informado') ?>
                                        </a>
                                        <p class="text-white text-sm mb-4">
                                            <strong>Descrição:</strong> <?= nl2br(htmlspecialchars($projeto['descricao'])) ?>
                                        </p>
                                        <p class="text-white text-sm mb-2">
                                            <strong>Status:</strong> <?= htmlspecialchars($projeto['status']) ?>
                                        </p>
                                        <p class="text-white text-sm">
                                            <strong>Mês:</strong> <?= htmlspecialchars($projeto['mes']) ?>
                                        </p>
                                    </div>
                                    <div class="mt-auto">
                                        <form method="post" onsubmit="return confirm('Confirma a exclusão do projeto <?= htmlspecialchars(addslashes($projeto['projeto_nome'])) ?>?');">
                                            <input type="hidden" name="excluir_id" value="<?= $projeto['projeto_id'] ?>">
                                            <button type="submit" class="inline-flex items-center justify-center rounded-md px-3 py-2 text-sm font-semibold w-full mt-4 btn-excluir">
                                                Excluir
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Adaptação do script de tema do index.php
        const toggleBtn = document.getElementById('toggle-theme'); // Usando toggle-theme para o botão de tema
        const body = document.body;

        // Função para salvar preferência de tema no localStorage
        function setTheme(dark) {
            if (dark) {
                body.classList.add('dark');
                toggleBtn.querySelector('span').textContent = 'Modo Claro';
                toggleBtn.querySelector('svg path').setAttribute('d', 'M128,32a96,96,0,1,0,96,96A96.11,96.11,0,0,0,128,32ZM40,128a88,88,0,1,1,88,88A88.1,88.1,0,0,1,40,128Z'); // Ícone da lua
            } else {
                body.classList.remove('dark');
                toggleBtn.querySelector('span').textContent = 'Modo Escuro';
                toggleBtn.querySelector('svg path').setAttribute('d', 'M120,40V16a8,8,0,0,1,16,0V40a8,8,0,0,1-16,0Zm72,88a64,64,0,1,1-64-64A64.07,64.07,0,0,1,192,128Zm-16,0a48,48,0,1,0-48,48A48.05,48.05,0,0,0,176,128ZM58.34,69.66A8,8,0,0,0,69.66,58.34l-16-16A8,8,0,0,0,42.34,53.66Zm0,116.68-16,16a8,8,0,0,0,11.32,11.32l16-16a8,8,0,0,0-11.32-11.32ZM192,72a8,8,0,0,0,5.66-2.34l16-16a8,8,0,0,0-11.32-11.32l-16,16A8,8,0,0,0,192,72Zm5.66,114.34a8,8,0,0,0-11.32,11.32l16,16a8,8,0,0,0,11.32-11.32ZM48,128a8,8,0,0,0-8-8H16a8,8,0,0,0,0,16H40A8,8,0,0,0,48,128Zm80,80a8,8,0,0,0-8,8v24a8,8,0,0,0,16,0V216A8,8,0,0,0,128,208Zm112-88H216a8,8,0,0,0,0,16h24a8,8,0,0,0,0-16Z'); // Ícone do sol
            }
            localStorage.setItem('theme', dark ? 'dark' : 'light');
        }

        // Checa preferencia salva ou padrão do sistema
        const savedTheme = localStorage.getItem('theme');
        const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;

        if (savedTheme === 'dark' || (!savedTheme && prefersDark)) {
            setTheme(true);
        } else {
            setTheme(false);
        }

        // Evento do botão para alternar tema
        toggleBtn.addEventListener('click', () => {
            const isDark = body.classList.contains('dark');
            setTheme(!isDark);
        });
    </script>

    <footer class="bg-[#343a40] text-white py-5 text-center mt-10 font-bold">
        <div class="flex justify-center gap-4 items-center flex-wrap">
            <a href="https://instagram.com/fwbio7" target="_blank" class="text-white text-decoration-none transition-colors duration-300 hover:text-blue-400">
                <svg xmlns="http://www.w3.org/2000/svg" fill="white" width="28" height="28" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 1.17.056 1.97.24 2.427.403.563.198.965.438 1.387.86.422.422.662.824.86 1.387.163.457.347 1.256.403 2.427.058 1.266.07 1.646.07 4.85s-.012 3.584-.07 4.85c-.056 1.17-.24 1.97-.403 2.427a3.904 3.904 0 0 1-.86 1.387 3.904 3.904 0 0 1-1.387-.86c-.457.163-1.256.347-2.427-.403-1.266.058-1.646.07-4.85.07s-3.584-.012-4.85-.07c-1.17-.056-1.97-.24-2.427-.403a3.904 3.904 0 0 1-1.387-.86 3.904 3.904 0 0 1-.86-1.387c-.163-.457-.347-.1256-.403-2.427-.058-1.266-.07-1.646-.07-4.85s.012-3.584.07-4.85c.056-1.17.24-1.97.403-2.427a3.904 3.904 0 0 1 .86-1.387 3.904 3.904 0 0 1 1.387-.86c.457-.163 1.256-.347 2.427-.403 1.266-.058 1.646-.07 4.85-.07zM12 0C8.741 0 8.332.013 7.052.07 5.773.127 4.802.304 4.002.611c-.845.328-1.56.77-2.275 1.485a6.48 6.48 0 0 0-1.486 2.275C.304 5.198.127 6.169.07 7.448.013 8.728 0 9.137 0 12c0 2.863.013 3.272.07 4.552.057 1.279.234 2.25.611 3.05.328.845.77 1.56 1.485 2.275a6.48 6.48 0 0 0 2.275 1.486c.8.377 1.771.554 3.05.611 1.28.057 1.689.07 4.552.07s3.272-.013 4.552-.07c1.279-.057 2.25-.234 3.05-.611a6.48 6.48 0 0 0 2.275-1.486 6.48 6.48 0 0 0 1.486-2.275c.377-.8.554-1.771.611-3.05.057-1.28.07-1.689.07-4.552s-.013-3.272-.07-4.552c-.057-1.279-.234-2.25-.611-3.05a6.48 6.48 0 0 0-1.486-2.275 6.48 6.48 0 0 0-2.275-1.486c-.8-.307-1.771-.484-3.05-.541C15.272.013 14.863 0 12 0zm0 5.838a6.162 6.162 0 1 0 0 12.324 6.162 6.162 0 0 0 0-12.324zm0 10.18a4.018 4.018 0 1 1 0-8.036 4.018 4.018 0 0 1 0 8.036zm6.406-11.845a1.44 1.44 0 1 1-2.88 0 1.44 1.44 0 0 1 2.88 0z"/></svg>
            </a>
            <a href="https://github.com/edelziolopes" target="_blank" class="text-white text-decoration-none transition-colors duration-300 hover:text-blue-400">
                <svg xmlns="http://www.w3.org/2000/svg" fill="white" width="28" height="28" viewBox="0 0 24 24"><path d="M12 .5C5.648.5.5 5.648.5 12c0 5.082 3.292 9.385 7.862 10.905.574.105.783-.25.783-.556 0-.274-.01-1.003-.015-1.97-3.2.695-3.877-1.54-3.877-1.54-.523-1.33-1.277-1.683-1.277-1.683-.044-.714.08-.7.08-.7 1.156.082 1.764 1.187 1.764 1.187 1.026 1.758 2.694 1.25 3.35.956.104-.744.402-1.25.731-1.538-2.554-.29-5.24-1.278-5.24-5.688 0-1.256.448-2.285 1.183-3.09-.119-.289-.513-1.448.111-3.02 0 0 .966-.31 3.162 1.18a10.91 10.91 0 0 1 2.881-.388c.977.004 1.963.133 2.881.388 2.196-1.49 3.16-1.18 3.16-1.18.626 1.572.232 2.731.114 3.02.736.805 1.181 1.834 1.181 3.09 0 4.422-2.692 5.394-5.258 5.678.413.356.78 1.065.78 2.148 0 1.552-.014 2.804-.014 3.184 0 .31.206.667.789.554C20.708 21.38 24 17.08 24 12c0-6.352-5.148-11.5-12-11.5z"/></svg>
            </a>
            <a href="https://industrialdigital.com.br/portifoliof" target="_blank" class="text-white text-decoration-none transition-colors duration-300 hover:text-blue-400">
                <svg xmlns="http://www.w3.org/2000/svg" fill="white" width="28" height="28" viewBox="0 0 24 24"><path d="M10.9 2C6.6 2 3 5.6 3 9.9c0 3.3 2.1 6.2 5.2 7.5l.3.1-.2.5c-.1.3-.5 1.1-.6 1.3-.2.4-.1.7.1.9.3.3.6.2.9.1.2-.1 2.1-1.1 2.5-1.3l.2-.1.2.1c1 .3 2 .5 3.1.5 4.3 0 7.9-3.6 7.9-7.9S15.2 2 10.9 2zm.1 2c4 0 7.2 3.2 7.2 7.2 0 4-3.2 7.2-7.2 7.2-1.1 0-2.2-.3-3.1-.7-.2-.1-.6 0-.2.1-1.2.6-2 1l.4-1c.1-.3 0-.6-.2-.8-.1-.1-.2-.2-.4-.3C4.5 15.4 3 13 3 9.9 3 6 6.2 3 10.9 3z"/></svg>
            </a>
        </div>
        <p class="mt-2 text-sm">© <?php echo date('Y'); ?> Desenvolvido por Fabio e Edelzio - Todos os direitos reservados</p>
    </footer>
</body>
</html>