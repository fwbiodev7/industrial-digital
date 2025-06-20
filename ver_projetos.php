<?php
// ====================
// 1. Configuração e conexão com o banco
// ====================
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$host = 'srv1664.hstgr.io';
$db   = 'u344105464_alunoprojeto';
$user = 'u344105464_ualunoprojeto';
$pass = 'Fabio0204#';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Falha na conexão com o banco: " . $conn->connect_error);
}

// ====================
// 2. Funções para buscar alunos e projetos
// ====================

// Modificação para buscar alunos únicos pelo nome
function buscarAlunosUnicos($conn) {
    $sql = "SELECT id, nome FROM tb_alunos GROUP BY nome ORDER BY nome";
    $result = $conn->query($sql);
    $alunos = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $alunos[] = $row;
        }
    }
    return $alunos;
}

// Função para buscar projetos com termo de pesquisa
function buscarProjetosAluno($conn, $aluno_id = null, $termo_pesquisa = '') {
    $sql = "SELECT p.id AS projeto_id, p.nome AS projeto_nome, p.descricao, p.status, a.id AS aluno_id, a.nome AS aluno_nome
            FROM tb_projetos p
            INNER JOIN tb_relacionamentos r ON p.id = r.projeto_id
            INNER JOIN tb_alunos a ON r.aluno_id = a.id
            WHERE 1=1"; // Cláusula WHERE inicial para facilitar a adição de condições
    
    $params = [];
    $types = "";

    // Adiciona condição para aluno_id se ele for fornecido (não nulo)
    if ($aluno_id !== null) {
        $sql .= " AND r.aluno_id = ?";
        $params[] = $aluno_id;
        $types .= "i";
    }

    if (!empty($termo_pesquisa)) {
        // Adiciona a condição de pesquisa pelo nome ou descrição do projeto
        $sql .= " AND (p.nome LIKE ? OR p.descricao LIKE ?)";
        $params[] = '%' . $termo_pesquisa . '%';
        $params[] = '%' . $termo_pesquisa . '%';
        $types .= "ss"; // Garante que 'ss' seja adicionado para os dois parâmetros de string
    }

    // Adiciona ORDER BY para organizar os projetos dentro de cada aluno
    $sql .= " ORDER BY p.nome ASC";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Erro no prepare(): " . $conn->error);
    }
    
    if (!empty($params)) { // Só faz o bind_param se houver parâmetros
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $projetos = [];
    while ($row = $result->fetch_assoc()) {
        $projetos[] = $row;
    }
    $stmt->close();
    return $projetos;
}

// ====================
// 3. Recebe o filtro via GET e o termo de pesquisa
// ====================
$aluno_selecionado_id = isset($_GET['aluno_id']) ? intval($_GET['aluno_id']) : null;
$termo_pesquisa = isset($_GET['pesquisar_projeto']) ? trim($_GET['pesquisar_projeto']) : '';

// ====================
// 4. Lógica para exibir alunos e seus projetos (com pesquisa)
// ====================
$alunos_para_exibir = [];

if ($aluno_selecionado_id !== null) {
    // Caso 1: Um aluno específico foi selecionado
    $stmt = $conn->prepare("SELECT id, nome FROM tb_alunos WHERE id = ?");
    $stmt->bind_param("i", $aluno_selecionado_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $aluno_info = $result->fetch_assoc();
    $stmt->close();

    if ($aluno_info) {
        $aluno_info['projetos'] = buscarProjetosAluno($conn, $aluno_info['id'], $termo_pesquisa);
        $alunos_para_exibir[] = $aluno_info;
    }
} else {
    // Caso 2: Nenhum aluno específico selecionado (exibir todos ou pesquisar globalmente)
    
    $projetos_encontrados = buscarProjetosAluno($conn, null, $termo_pesquisa);
    
    // Agrupa os projetos encontrados por aluno
    $alunos_temp = [];
    foreach ($projetos_encontrados as $proj) {
        $aluno_id = $proj['aluno_id'];
        if (!isset($alunos_temp[$aluno_id])) {
            $alunos_temp[$aluno_id] = [
                'id' => $aluno_id,
                'nome' => $proj['aluno_nome'],
                'projetos' => []
            ];
        }
        $alunos_temp[$aluno_id]['projetos'][] = $proj;
    }

    // Se não há termo de pesquisa, garante que todos os alunos cadastrados apareçam,
    // mesmo que não tenham projetos ou projetos que não foram capturados pela busca global.
    // Isso evita "duplicação" de cards de aluno para os que teriam 0 projetos após a filtragem.
    if (empty($termo_pesquisa)) {
        $todos_alunos_cadastrados = buscarAlunosUnicos($conn);
        foreach ($todos_alunos_cadastrados as $aluno_cad) {
            if (!isset($alunos_temp[$aluno_cad['id']])) {
                // Se o aluno não tinha nenhum projeto encontrado na pesquisa global (ou nenhum projeto mesmo),
                // adicione-o com uma lista de projetos vazia (ou chame a função para buscar especificamente).
                $aluno_cad['projetos'] = buscarProjetosAluno($conn, $aluno_cad['id'], ''); // Busca todos os projetos dele sem termo de pesquisa
                $alunos_temp[$aluno_cad['id']] = $aluno_cad;
            }
        }
    }
    
    // Converte o array associativo em indexado e ordena por nome do aluno
    $alunos_para_exibir = array_values($alunos_temp);
    usort($alunos_para_exibir, function($a, $b) {
        return strcmp($a['nome'], $b['nome']);
    });
}

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8" />
    <title>Projetos Relacionados a Alunos</title>
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
        /* Estilos CSS do index.php e projetos.php aplicados aqui para consistência */
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

        .card-aluno, .card-projeto { /* Classes para os cards, com fundo transparente */
            background-color: rgba(0, 0, 0, 0.1);
            box-shadow: none;
            border: 1px solid rgba(0, 0, 0, 0.1); /* Uma borda sutil para definir os cards */
        }

        .card-aluno p, .card-projeto p, .card-projeto strong {
            color: #e0e0e0; /* Cor para textos dentro dos cards */
        }

        /* Estilos para botões */
        .btn-default {
            background-color: transparent; /* Fundo transparente */
            border: 1px solid #d2e2f3; /* Borda da cor do texto */
            color: #d2e2f3; /* Cor do texto padrão */
            transition: background-color 0.3s ease, color 0.3s ease, transform 0.2s ease;
        }

        .btn-default:hover {
            background-color: #d2e2f3; /* Fundo preenchido no hover */
            color: #14191f; /* Texto escuro no hover */
            transform: scale(1.05);
        }

        /* Botões de ação como "Ver Projetos" e "Pesquisar" */
        .btn-action {
            background-color: #d2e2f3; /* Cor azul claro do index.php para botões de ação */
            color: #14191f; /* Texto escuro */
            transition: background-color 0.3s ease, transform 0.2s ease;
        }

        .btn-action:hover {
            background-color: #a4c7e6; /* Um azul um pouco mais escuro no hover */
            transform: scale(1.05);
        }

        /* Estilo para campos de input (genérico, pode ser sobrescrito) */
        .input-field {
            background-color: rgb(0, 0, 0);
            border: 1px solid rgb(0, 0, 0);
            color: #f8f9fa;
            padding: 0.5rem 0.75rem;
            border-radius: 0.375rem; /* rounded-md */
            width: 100%;
        }
        .input-field::placeholder {
            color: #a0a0a0;
        }
        .input-field:focus {
            border-color: #d2e2f3;
            outline: none;
            box-shadow: 0 0 0 3px rgba(210, 226, 243, 0.5);
        }

        /* ESTILOS PARA BARRA DE PESQUISA ARREDONDADA (pílula) */
        .search-bar-header {
            /* REMOVIDO: width: 100%; e max-width: 300px; para permitir controle via Tailwind */
            display: flex;
            align-items: center;
            border-radius: 9999px; /* Mantém o formato de pílula */
            border: 1px solid rgba(255, 255, 255, 0.2); /* Borda sutil */
            overflow: hidden; /* Garante que os cantos arredondados funcionem bem */
            background-color: #2e3b4d; /* Fundo mais escuro para contraste */
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3); /* Sombra suave para profundidade */
            transition: all 0.3s ease-in-out; /* Transição para efeitos de hover/focus */
        }

        .search-bar-header:focus-within {
            border-color: #60a5fa; /* Borda azul ao focar (Tailwind blue-400) */
            box-shadow: 0 0 0 3px rgba(96, 165, 250, 0.4); /* Anel de foco azul */
        }

        .search-bar-header .input-field {
            border: none; /* Remove bordas individuais do input */
            border-radius: 0; /* Remove arredondamentos individuais do input */
            background-color: transparent; /* Fundo transparente para o input */
            padding: 0.5rem 1rem; /* Aumentado o padding para mais espaço */
            flex-grow: 1; /* Permite que o input preencha o espaço disponível */
            color: #e0e0e0; /* Cor do texto clara */
            font-size: 1rem; /* Tamanho da fonte */
        }

        .search-bar-header .input-field::placeholder {
            color: #a0a0a0; /* Cor do placeholder */
            opacity: 0.8; /* Transparência do placeholder */
        }

        .search-bar-header .input-field:focus {
            outline: none; /* Remove o outline padrão ao focar */
            box-shadow: none; /* Remove a sombra de foco padrão */
        }

        .search-bar-header .search-button {
            border: none; /* Remove bordas individuais do botão */
            border-radius: 0; /* Remove arredondamentos individuais do botão */
            background-color: #2e3b4d; /* Cor de fundo do botão (Tailwind blue-500) */
            color: white; /* Cor da lupa */
            padding: 0.5rem 1rem; /* Espaçamento interno */
            flex-shrink: 0; /* Impede que o botão encolha */
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background-color 0.2s ease, transform 0.2s ease;
            /* As bordas arredondadas do lado direito do botão */
            border-top-right-radius: 9999px; 
            border-bottom-right-radius: 9999px;
        }

        .search-bar-header .search-button:hover {
            background-color:rgb(33, 42, 58); /* Cor mais escura ao passar o mouse (Tailwind blue-600) */
            transform: scale(1.02); /* Leve aumento ao passar o mouse */
        }

        /* Oculta o texto do botão "Pesquisar" para deixar apenas a lupa */
        .search-button .search-text {
            display: none;
        }
        .search-button svg {
            margin-right: 0; /* Garante que não haja margem extra se o texto não aparecer */
        }

    </style>
</head>
<body>
    <header class="flex items-center justify-between whitespace-nowrap border-b border-solid border-b-[#293542] px-4 sm:px-6 lg:px-10 py-3 flex-wrap gap-4">
        <div class="flex items-center gap-4 text-white">
            <div class="size-4">
                <svg viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M24 4H6V17.3333V30.6667H24V44H42V30.6667V17.3333H24V4Z" fill="currentColor"></path>
                </svg>
            </div>
            <h2 class="text-white text-lg font-bold leading-tight tracking-[-0.015em]">Projetos dos Alunos</h2>
        </div>
        
        <form method="GET" action="ver_projetos.php" class="flex gap-0 search-bar-header w-full sm:w-auto md:w-[500px] lg:w-[600px] xl:w-[700px] ml-auto">
            <?php if ($aluno_selecionado_id): ?>
                <input type="hidden" name="aluno_id" value="<?php echo htmlspecialchars($aluno_selecionado_id); ?>">
            <?php endif; ?>
            <input
                type="text"
                name="pesquisar_projeto"
                placeholder="Pesquisar projetos..."
                value="<?php echo htmlspecialchars($termo_pesquisa); ?>"
                class="flex-1 input-field"
            >
            <button type="submit" class="px-3 py-2 flex items-center justify-center search-button">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 256 256">
                    <path d="M229.66,218.34l-50.07-50.06a88.19,88.19,0,1,0-11.31,11.31l50.06,50.07a8,8,0,0,0,11.32-11.32ZM40,112a72,72,0,1,1,72,72A72.08,72.08,0,0,1,40,112Z"></path>
                </svg>
                <span class="search-text">Pesquisar</span>
            </button>
        </form>
    </header>

    <div class="flex flex-1 justify-end gap-4 sm:gap-8 order-2 sm:order-none">
        <div class="flex gap-2">
        </div>
    </div>

    <div class="px-4 sm:px-6 lg:px-40 flex flex-1 justify-center py-5">
        <div class="layout-content-container flex flex-col max-w-[960px] flex-1">
            <?php if ($aluno_selecionado_id): ?>
                <div class="pb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <a href="ver_projetos.php" class="inline-flex items-center rounded-md px-3 py-2 text-sm font-semibold shadow-sm btn-action">
                        Voltar para todos os alunos
                    </a>
                </div>
            <?php endif; ?>

            <?php if (count($alunos_para_exibir) > 0): ?>
                <div class="flex flex-col gap-8">
                    <?php foreach ($alunos_para_exibir as $aluno): ?>
                        <div class="bg-[#1e293b] rounded-xl p-4 sm:p-6 card-aluno">
                            <h2 class="text-white text-2xl font-bold mb-2">
                                <?php echo htmlspecialchars($aluno['nome']); ?>
                            </h2>
                            <p class="text-gray-400 text-sm mb-4">Projetos:</p>
                            <div class="grid grid-cols-[repeat(auto-fit,minmax(280px,1fr))] gap-4 sm:gap-6">
                                <?php
                                $projetos_do_aluno_atual = isset($aluno['projetos']) ? $aluno['projetos'] : [];

                                if (count($projetos_do_aluno_atual) === 0):
                                ?>
                                    <p class="text-gray-400 text-sm col-span-full">
                                        Nenhum projeto encontrado para este aluno
                                        <?php if (!empty($termo_pesquisa)): ?>
                                            com o termo "<?php echo htmlspecialchars($termo_pesquisa); ?>"
                                        <?php endif; ?>.
                                    </p>
                                <?php else: ?>
                                    <?php foreach ($projetos_do_aluno_atual as $proj): ?>
                                        <div class="rounded-xl p-4 sm:p-6 card-projeto">
                                            <h3 class="text-white text-xl font-semibold mb-2"><?php echo htmlspecialchars($proj['projeto_nome']); ?></h3>
                                            <p class="text-gray-300 text-sm mb-2">
                                                <strong>Descrição:</strong> <?php echo nl2br(htmlspecialchars($proj['descricao'])); ?>
                                            </p>
                                            <p class="text-gray-300 text-sm">
                                                <strong>Status:</strong> <?php echo htmlspecialchars($proj['status']); ?>
                                            </p>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-white text-lg">Nenhum aluno cadastrado ou projeto encontrado com o termo "<?php echo htmlspecialchars($termo_pesquisa); ?>".</p>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Adaptação do script de tema do index.php e projetos.php
        const toggleBtn = document.getElementById('toggle-theme'); // Use um ID para o botão de tema
        const body = document.body;

        // Função para salvar preferência de tema no localStorage
        function setTheme(dark) {
            if (dark) {
                body.classList.add('dark');
                // Altera o ícone do sol para lua
                if (toggleBtn) { // Verifique se o botão existe antes de tentar manipulá-lo
                    toggleBtn.querySelector('svg path').setAttribute('d', 'M128,32a96,96,0,1,0,96,96A96.11,96.11,0,0,0,128,32ZM40,128a88,88,0,1,1,88,88A88.1,88.1,0,0,1,40,128Z');
                }
            } else {
                body.classList.remove('dark');
                // Altera o ícone da lua para sol
                if (toggleBtn) {
                    toggleBtn.querySelector('svg path').setAttribute('d', 'M120,40V16a8,8,0,0,1,16,0V40a8,8,0,0,1-16,0Zm72,88a64,64,0,1,1-64-64A64.07,64.07,0,0,1,192,128Zm-16,0a48,48,0,1,0-48,48A48.05,48.05,0,0,0,176,128ZM58.34,69.66A8,8,0,0,0,69.66,58.34l-16-16A8,8,0,0,0,42.34,53.66Zm0,116.68-16,16a8,8,0,0,0,11.32,11.32l16-16a8,8,0,0,0-11.32-11.32ZM192,72a8,8,0,0,0,5.66-2.34l16-16a8,8,0,0,0-11.32-11.32l-16,16A8,8,0,0,0,192,72Zm5.66,114.34a8,8,0,0,0-11.32,11.32l16,16a8,8,0,0,0,11.32-11.32ZM48,128a8,8,0,0,0-8-8H16a8,8,0,0,0,0,16H40A8,8,0,0,0,48,128Zm80,80a8,8,0,0,0-8,8v24a8,8,0,0,0,16,0V216A8,8,0,0,0,128,208Zm112-88H216a8,8,0,0,0,0,16h24a8,8,0,0,0,0-16Z');
                }
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
        if (toggleBtn) {
            toggleBtn.addEventListener('click', () => {
                const isDark = body.classList.contains('dark');
                setTheme(!isDark);
            });
        }
    </script>
    <footer class="bg-black text-white py-5 text-center mt-10 font-bold">
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

<?php $conn->close(); ?>