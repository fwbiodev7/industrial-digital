
<?php
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(0);

// Conexão MySQL
$host = 'srv1664.hstgr.io';
$db = 'u344105464_alunoprojeto';
$user = 'u344105464_ualunoprojeto';
$pass = 'Fabio0204#';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
  die("Erro na conexão: " . $conn->connect_error);
}

$meses = ['maio', 'junho', 'julho'];
$mesSelecionado = $_GET['mes'] ?? 'maio';
if (!in_array($mesSelecionado, $meses)) {
 $mesSelecionado = 'maio';
}

$sqlAlunos = "SELECT * FROM tb_alunos WHERE mes = ? ORDER BY nome";
$stmtAlunos = $conn->prepare($sqlAlunos);
if (!$stmtAlunos) {
 die("Erro na consulta alunos: " . $conn->error);
}
$stmtAlunos->bind_param('s', $mesSelecionado);
$stmtAlunos->execute();
$resultAlunos = $stmtAlunos->get_result();

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
<link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet"
<link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet"/>


<title>Industrial Digital - Trabalhos</title>
 <link rel="icon" type="image/png" href="uploads/faviconind.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <style>
        /* CSS para o botão de engrenagem e outros ajustes finos */
        .btn-gear { /* ... seu estilo para engrenagem ... */
            display: inline-flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            border-radius: 0.75rem; /* rounded-xl */
            height: 2.5rem; /* h-10 */
            width: 2.5rem; /* size-10 para ser quadrado */
            background-color: #293542; /* bg-[#293542] */
            color: white; /* text-white */
            font-size: 0.875rem; /* text-sm */
            font-weight: 700; /* font-bold */
            line-height: normal;
            letter-spacing: 0.015em;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.2s ease;
            text-decoration: none;
        }

        .btn-gear:hover {
            background-color: #3d4a5c; /* Um tom ligeiramente mais claro */
            transform: translateY(-2px);
        }

        .btn-gear svg {
            width: 1.25rem; /* 20px */
            height: 1.25rem; /* 20px */
            vertical-align: middle;
        }

        /* Estilos para o tema escuro (padrão) */
        body {
            background-color:rgb(14, 16, 19);
            color: #f8f9fa;
        }

        header {
            background-color:rgb(6, 7, 8);
            border-bottom-color:rgb(0, 0, 0);
        }

        .text-white {
            color: #f8f9fa;
        }

        .border-b-[#293542] {
            border-color: #495057;
        }

        .bg-[#293542] {
            background-color:rgb(15, 15, 15);
            color: #fff;
        }

        .card-aluno {
            background-color: transparent;
            box-shadow: none;
        }

        .card-aluno p {
            color: #e0e0e0;
        }

        footer {
            background-color:rgb(0, 0, 0) !important;
            color: #adb5bd !important;
        }

        .dark footer a {
            color: #66aaff !important;
        }
        .dark .bg-[#d2e2f3] {
            background-color:rgb(17, 17, 17), 0);
            color: #f8f9fa;
        }
    </style>
</head>
<body class="dark">
 <div class="relative flex size-full min-h-screen flex-col bg-body group/design-root overflow-x-hidden" style='font-family: "Work Sans", "Noto Sans", sans-serif;'>
  <div class="layout-container flex h-full grow flex-col">
   <header class="flex items-center justify-between whitespace-nowrap border-b border-solid border-b-[#293542] px-2 sm:px-4 md:px-10 py-3">
    <div class="flex items-center gap-4 text-white">
     <div class="size-4">
     </div>
    </div>
    <?php include_once 'nav.php'; ?>
   </header>
   <div class="px-4 sm:px-6 lg:px-40 flex flex-1 justify-center py-5">
    <div class="layout-content-container flex flex-col max-w-[960px] flex-1">
     <h2 class="text-white text-2xl font-bold leading-tight tracking-[-0.015em] mb-8 text-center">Industrial Digital - Trabalhos de <?= ucfirst($mesSelecionado) ?></h2>

    <div class="grid grid-cols-[repeat(auto-fit,minmax(150px,1fr))] sm:grid-cols-[repeat(auto-fit,minmax(180px,1fr))] md:grid-cols-[repeat(auto-fit,minmax(200px,1fr))] gap-2 sm:gap-3 md:gap-4 p-2 sm:p-4">
    <?php if ($resultAlunos && $resultAlunos->num_rows > 0): ?>
        <?php while ($aluno = $resultAlunos->fetch_assoc()): ?>
        <div class="flex flex-col gap-2 sm:gap-3 pb-2 sm:pb-3 card-aluno">
          <div
            class="w-full bg-center bg-no-repeat aspect-square bg-cover rounded-xl"
            style='background-image: url("<?= htmlspecialchars($aluno['foto']) ?>");'
          ></div>
          <p class="text-white text-base font-bold leading-normal text-center"><?= htmlspecialchars($aluno['nome']) ?></p>
 <a href="ver_projetos.php?aluno_id=<?= $aluno['id'] ?>
        "class="flex items-center justify-center h-10 px-4 rounded-xl bg-[#d2e2f3] text-[#14191f] text-sm font-bold transition duration-300 transform hover:scale-105">
          Ver Projetos
        </a>
        </a>
        </div>
        <?php endwhile; ?>
     <?php else: ?>
        <p class="text-white text-center col-span-full">Nenhum aluno encontrado para o mês de <?= ucfirst($mesSelecionado) ?>.</p>
     <?php endif; ?>
     </div>
    </div>
   </div>
  </div>
 </div>

<script>
    const btnTema = document.getElementById('btnTema');
    const body = document.body;

    // Função para atualizar o cookie do tema
    function setCookie(name, value, days) {
        let expires = "";
        if (days) {
            const date = new Date();
            date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
            expires = "; expires=" + date.toUTCString();
        }
        document.cookie = name + "=" + (value || "") + expires + "; path=/";
    }

    // Função para obter o cookie do tema
    function getCookie(name) {
        const nameEQ = name + "=";
        const ca = document.cookie.split(';');
        for(let i=0; i < ca.length; i++) {
            let c = ca[i];
            while (c.charAt(0) === ' ') c = c.substring(1, c.length);
            if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length);
        }
        return null;
    }

    // Inicializa o tema com base no cookie ou padrão
    const savedTheme = getCookie('theme');
    if (savedTheme === 'dark') {
        body.classList.add('dark');
        btnTema.innerHTML = '<div class="text-white" data-icon="Moon" data-size="20px" data-weight="regular"><svg xmlns="http://www.w3.org/2000/svg" width="20px" height="20px" fill="currentColor" viewBox="0 0 256 256"><path d="M128,32a96,96,0,1,0,96,96A96.11,96.11,0,0,0,128,32ZM40,128a88,88,0,1,1,88,88A88.1,88.1,0,0,1,40,128Z"></path></svg></div>';
    } else {
        body.classList.remove('dark');
        btnTema.innerHTML = '<div class="text-white" data-icon="Sun" data-size="20px" data-weight="regular"><svg xmlns="http://www.w3.org/2000/svg" width="20px" height="20px" fill="currentColor" viewBox="0 0 256 256"><path d="M120,40V16a8,8,0,0,1,16,0V40a8,8,0,0,1-16,0Zm72,88a64,64,0,1,1-64-64A64.07,64.07,0,0,1,192,128Zm-16,0a48,48,0,1,0-48,48A48.05,48.05,0,0,0,176,128ZM58.34,69.66A8,8,0,0,0,69.66,58.34l-16-16A8,8,0,0,0,42.34,53.66Zm0,116.68-16,16a8,8,0,0,0,11.32,11.32l16-16a8,8,0,0,0-11.32-11.32ZM192,72a8,8,0,0,0,5.66-2.34l16-16a8,8,0,0,0-11.32-11.32l-16,16A8,8,0,0,0,192,72Zm5.66,114.34a8,8,0,0,0-11.32,11.32l16,16a8,8,0,0,0,11.32-11.32ZM48,128a8,8,0,0,0-8-8H16a8,8,0,0,0,0,16H40A8,8,0,0,0,48,128Zm80,80a8,8,0,0,0-8,8v24a8,8,0,0,0,16,0V216A8,8,0,0,0,128,208Zm112-88H216a8,8,0,0,0,0,16h24a8,8,0,0,0,0-16Z"></path></svg></div>';
            setCookie('theme', 'light', 365);
    }
});
</script>

<footer class="bg-[#343a40] text-white py-5 text-center mt-10 font-bold">
    <div class="flex justify-center gap-4 items-center flex-wrap">
        <a href="https://instagram.com/fwbio7" target="_blank" class="text-white text-decoration-none transition-colors duration-300 hover:text-blue-400">
            <i class="ri-instagram-line ri-lg"></i> </a>
        <a href="https://github.com/fwbio1sistemas" target="_blank" class="text-white text-decoration-none transition-colors duration-300 hover:text-blue-400">
            <i class="ri-github-simple-fill ri-lg"></i> </a>
        <p class="mt-2 text-sm">© <?php echo date('Y'); ?> Desenvolvido por Fabio e Edelzio - Todos os direitos reservados</p>
    </div>
</footer>