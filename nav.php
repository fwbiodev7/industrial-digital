<?php
session_start();

$meses = ['maio', 'junho', 'julho'];
$mesSelecionado = $_GET['mes'] ?? 'maio';
if (!in_array($mesSelecionado, $meses)) {
    $mesSelecionado = 'maio';
}
?>

<div class="flex flex-1 justify-between items-center gap-8">
    <!-- LOGO -->
    <div>
        <img src="uploads/logoind.png"
             alt="Industrial Digital Logo"
             class="h-20 w-auto max-w-[140px] transition-transform duration-300 hover:scale-105" />
    </div>

    <!-- BOTÕES + MESES -->
    <div class="flex items-center gap-2">
        <!-- Botão Projetos -->
        <a href="projetos.php"
           class="flex items-center justify-center h-10 px-4 rounded-xl bg-[#d2e2f3] text-[#14191f] text-sm font-bold transition duration-300 transform hover:scale-105">
            Projetos
        </a>

        <!-- Botão Adicionar Aluno -->
        <a href="adicionar_aluno.php?mes=<?= htmlspecialchars($mesSelecionado) ?>"
           class="flex items-center justify-center h-10 px-4 rounded-xl bg-[#293542] text-white text-sm font-bold transition duration-300 transform hover:scale-105">
            Adicionar Aluno
        </a>

        <!-- Meses como Botões -->
        <?php foreach ($meses as $mes): ?>
            <a href="?mes=<?= $mes ?>"
               class="flex items-center justify-center h-10 px-4 rounded-xl text-sm font-bold transition duration-300 transform hover:scale-105
               <?= $mes === $mesSelecionado ? 'bg-white text-[#14191f]' : 'bg-[#1f2937] text-white' ?>">
                <?= ucfirst($mes) ?>
            </a>
        <?php endforeach; ?>

     <button id="btnTema"
        class="flex items-center justify-center h-10 px-3 rounded-xl bg-[#293542] text-white text-sm font-bold transition duration-300 transform hover:scale-105">
</button>

<a href="gerenciar_relacionamentos.php"
   class="flex items-center justify-center h-10 px-3 rounded-xl bg-[#293542] text-white text-sm font-bold transition duration-300 transform hover:scale-105"
   title="Gerenciar Relacionamentos">
    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <circle cx="12" cy="12" r="3"></circle>
        <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09a1.65 1.65 0 0 0-1-1.51 1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0 1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09a1.65 1.65 0 0 0 1.51-1 1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0 .33 1.82V21"></path>
    </svg>
</a>

        <!-- Foto de perfil -->
        <div class="bg-center bg-no-repeat aspect-square bg-cover rounded-full size-10"
             style='background-image: url("https://lh3.googleusercontent.com/aida-public/AB6AXu...");'>
        </div>
    </div>
</div>
