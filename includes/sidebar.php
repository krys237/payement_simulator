<?php
// ============================================================
// Sidebar réutilisable — Design « Claude »
// Palette : crème #FAF9F5 · encre #1F1E1D · terracotta #C96442
// $currentPage doit être défini AVANT l'inclusion
// ============================================================

$currentPage = $currentPage ?? 'dashboard';

$sidebarNav = [
    [
        'section' => 'Navigation',
        'items'   => [
            ['key' => 'dashboard', 'href' => 'index.php',            'label' => 'Tableau de bord',     'icon' => 'home'],
            ['key' => 'checkout',  'href' => 'checkout.php',         'label' => 'Nouvelle commande',   'icon' => 'plus'],
            ['key' => 'simulate',  'href' => 'simulate_payment.php', 'label' => 'Simuler un paiement', 'icon' => 'card'],
        ],
    ],
    [
        'section' => 'Analyse',
        'items'   => [
            ['key' => 'stats', 'href' => 'stats.php', 'label' => 'Statistiques', 'icon' => 'chart'],
        ],
    ],
];

$sidebarIcons = [
    'home'  => '<path d="M3 10.75 12 3.5l9 7.25M5.25 9.75V19.5a1.5 1.5 0 0 0 1.5 1.5h3.5v-6h3.5v6h3.5a1.5 1.5 0 0 0 1.5-1.5V9.75"/>',
    'plus'  => '<circle cx="12" cy="12" r="8.5"/><path d="M12 8.5v7M8.5 12h7"/>',
    'card'  => '<rect x="3" y="5.5" width="18" height="13" rx="2.5"/><path d="M3 10h18M6.5 14.5h3"/>',
    'chart' => '<path d="M3.5 20.5h17M7.5 20.5v-5M12 20.5V8.5M16.5 20.5v-8"/>',
];
?>

<style>
    /* Scrollbar discrète */
    #sidebar nav::-webkit-scrollbar        { width: 6px; }
    #sidebar nav::-webkit-scrollbar-track  { background: transparent; }
    #sidebar nav::-webkit-scrollbar-thumb  { background: #E4DFD4; border-radius: 999px; }
    #sidebar nav::-webkit-scrollbar-thumb:hover { background: #D3CCBE; }

    /* État replié (desktop) */
    @media (min-width: 1024px) {
        html.sidebar-collapsed #sidebar          { transform: translateX(-100%); }
        html.sidebar-collapsed #sidebarChevron   { left: 0 !important; }
        html.sidebar-collapsed #chevronIcon      { transform: rotate(180deg); }
        html.sidebar-collapsed #pageContent      { margin-left: 0 !important; }
    }
</style>

<!-- Overlay mobile -->
<div id="sidebarOverlay"
     class="fixed inset-0 z-30 hidden bg-[#1F1E1D]/25 backdrop-blur-[2px] transition-opacity duration-300 lg:hidden"
     onclick="toggleSidebar()"></div>

<!-- Sidebar -->
<aside id="sidebar"
       class="fixed top-0 left-0 z-40 flex h-full w-[272px] -translate-x-full flex-col
              border-r border-[#EAE6DD] bg-[#FAF9F5]
              transition-transform duration-300 ease-out lg:translate-x-0">

    <!-- Logo & Branding -->
    <div class="flex items-center gap-3 border-b border-[#EDE9E0] px-5 py-[18px]">
        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-[13px] bg-[#C96442]
                    shadow-[0_2px_8px_rgba(201,100,66,0.28)]">
            <span class="font-serif text-[17px] font-medium leading-none text-white">P</span>
        </div>
        <div class="min-w-0">
            <h1 class="font-serif text-[16px] font-medium leading-tight tracking-[-0.01em] text-[#1F1E1D]">
                PaySimu
            </h1>
            <p class="mt-0.5 text-[10.5px] font-medium uppercase tracking-[0.12em] text-[#A8A29A]">
                Simulation de paiement
            </p>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 overflow-y-auto px-3 py-4">

        <?php foreach ($sidebarNav as $group): ?>

            <p class="px-3 pb-2 text-[10px] font-semibold uppercase tracking-[0.16em] text-[#A8A29A]">
                <?= $group['section'] ?>
            </p>

            <div class="mb-4 space-y-0.5">
                <?php foreach ($group['items'] as $item):
                    $active = ($currentPage === $item['key']);
                ?>
                    <a href="<?= $item['href'] ?>"
                       <?= $active ? 'aria-current="page"' : '' ?>
                       class="group relative flex items-center gap-3 rounded-xl px-3 py-2.5 text-[13.5px]
                              transition-colors duration-150
                              <?= $active
                                  ? 'bg-white font-medium text-[#1F1E1D] shadow-[0_1px_2px_rgba(31,30,29,0.06)] ring-1 ring-[#EAE6DD]'
                                  : 'font-normal text-[#6B665D] hover:bg-[#F1EEE6] hover:text-[#1F1E1D]' ?>">

                        <?php if ($active): ?>
                            <span class="absolute -left-3 top-1/2 h-5 w-[3px] -translate-y-1/2 rounded-full bg-[#C96442]"></span>
                        <?php endif; ?>

                        <svg class="h-[18px] w-[18px] shrink-0 transition-colors duration-150
                                    <?= $active ? 'text-[#C96442]' : 'text-[#A8A29A] group-hover:text-[#6B665D]' ?>"
                             fill="none" stroke="currentColor" stroke-width="1.6"
                             stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <?= $sidebarIcons[$item['icon']] ?>
                        </svg>

                        <span class="truncate"><?= $item['label'] ?></span>
                    </a>
                <?php endforeach; ?>
            </div>

        <?php endforeach; ?>

    </nav>

    <!-- Carte d'information -->
    <div class="border-t border-[#EDE9E0] p-3">
        <div class="rounded-2xl border border-[#EAE5D9] bg-[#F3F0E8] p-3.5">
            <div class="flex items-start gap-2.5">
                <span class="relative mt-[5px] flex h-2 w-2 shrink-0">
                    <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-[#C96442] opacity-60"></span>
                    <span class="relative inline-flex h-2 w-2 rounded-full bg-[#C96442]"></span>
                </span>
                <div>
                    <p class="text-[12px] font-medium text-[#1F1E1D]">Mode simulation</p>
                    <p class="mt-1 text-[11px] leading-relaxed text-[#7A756C]">
                        Aucune transaction réelle n'est effectuée sur cette plateforme.
                    </p>
                </div>
            </div>
        </div>
        <p class="mt-3 text-center text-[10px] text-[#B5AFA4]">PaySimu · v2.0</p>
    </div>
</aside>

<!-- ============================================================ -->
<!-- CHEVRON — Bouton repliable (desktop)                          -->
<!-- ============================================================ -->
<button id="sidebarChevron"
        onclick="toggleDesktopSidebar()"
        title="Replier la sidebar"
        class="group fixed top-1/2 z-50 hidden h-11 w-6 -translate-y-1/2 items-center justify-center
               rounded-r-lg border border-l-0 border-[#EAE6DD] bg-white
               shadow-[2px_0_8px_rgba(31,30,29,0.06)]
               transition-all duration-300 hover:bg-[#F6EDE7] lg:flex"
        style="left: 272px;">
    <svg id="chevronIcon"
         class="h-3.5 w-3.5 text-[#B5AFA4] transition-all duration-300 group-hover:text-[#C96442]"
         fill="none" stroke="currentColor" stroke-width="2"
         stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
        <path d="M15 19l-7-7 7-7"/>
    </svg>
</button>

<script>
    // À supprimer si ces fonctions existent déjà dans ta page
    window.toggleSidebar = window.toggleSidebar || function () {
        const sb = document.getElementById('sidebar');
        const ov = document.getElementById('sidebarOverlay');
        const open = sb.classList.toggle('-translate-x-full') === false;
        sb.classList.toggle('translate-x-0', open);
        ov.classList.toggle('hidden', !open);
    };

    window.toggleDesktopSidebar = window.toggleDesktopSidebar || function () {
        document.documentElement.classList.toggle('sidebar-collapsed');
    };
</script>