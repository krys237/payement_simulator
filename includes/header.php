<?php
// ============================================================
// En-tête responsive avec fil d'ariane et badge
// ============================================================
?>

<header class="sticky top-0 z-20 bg-[#FAFAF9]/80 backdrop-blur-xl border-b border-stone-200/60">
    
    <div class="flex items-center justify-between px-4 sm:px-6 lg:px-8 py-3.5">

        <!-- Gauche : Hamburger + Titre -->
        <div class="flex items-center gap-3">
            <!-- Bouton hamburger mobile -->
            <button id="menuBtn"
                    onclick="toggleSidebar()"
                    class="lg:hidden p-2 -ml-2 rounded-xl text-stone-500 hover:bg-stone-100 hover:text-stone-800 transition-all duration-200 active:scale-95">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>

            <!-- Fil d'ariane + titre -->
            <div class="flex flex-col">
                <div class="hidden sm:flex items-center gap-1.5 text-[11px] text-stone-400 mb-0.5">
                    <span>PaySimu</span>
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                    <span class="text-stone-600"><?= htmlspecialchars($pageTitle ?? 'Page') ?></span>
                </div>
                <h2 class="text-[15px] sm:text-[17px] font-semibold tracking-tight text-stone-800">
                    <?= htmlspecialchars($pageTitle ?? 'Tableau de bord') ?>
                </h2>
            </div>
        </div>

        <!-- Droite : Horodatage + Avatar -->
        <div class="flex items-center gap-3">
            <div class="hidden sm:flex items-center gap-2 px-3 py-1.5 bg-stone-100/80 rounded-lg">
                <svg class="w-3.5 h-3.5 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="text-[12px] font-medium text-stone-500"><?= date('d/m/Y H:i') ?></span>
            </div>
            <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-[#D97757] to-[#C2553A] flex items-center justify-center text-white text-xs font-bold shadow-sm">
                A
            </div>
        </div>
    </div>
</header>