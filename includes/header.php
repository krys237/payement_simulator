<?php
// ============================================================
// En-tête responsive — Design « Claude »
// Palette : crème #FAF9F5 · encre #1F1E1D · terracotta #C96442
// $pageTitle doit être défini AVANT l'inclusion (optionnel)
// ============================================================

$pageTitle = $pageTitle ?? 'Tableau de bord';
?>

<style>
    /* Petite touche typographique pour le titre (optionnel) */
    #appHeader h2 { letter-spacing: -0.01em; }
</style>

<header id="appHeader"
        class="sticky top-0 z-20 border-b border-[#EAE6DD] bg-[#FAF9F5]/85 backdrop-blur-xl">

    <div class="flex items-center justify-between gap-4 px-4 py-3 sm:px-6 lg:px-8">

        <!-- Gauche : Hamburger + Fil d'ariane + Titre -->
        <div class="flex min-w-0 items-center gap-2.5">

            <!-- Bouton hamburger mobile -->
            <button id="menuBtn"
                    onclick="toggleSidebar()"
                    aria-label="Ouvrir le menu"
                    class="-ml-2 rounded-lg p-2 text-[#6B665D] transition-colors duration-150
                           hover:bg-[#F1EEE6] hover:text-[#1F1E1D]
                           active:scale-95 lg:hidden">
                <svg class="h-[19px] w-[19px]" fill="none" stroke="currentColor" stroke-width="1.8"
                     stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                    <path d="M4 7h16M4 12h16M4 17h16"/>
                </svg>
            </button>

            <!-- Fil d'ariane + titre -->
            <div class="flex min-w-0 flex-col">

                <!-- Fil d'ariane (desktop) -->
                <nav class="mb-0.5 hidden items-center gap-1.5 text-[11px] text-[#A8A29A] sm:flex"
                     aria-label="Fil d'ariane">
                    <span>PaySimu</span>
                    <svg class="h-2.5 w-2.5 text-[#C9C3B8]" fill="none" stroke="currentColor" stroke-width="2"
                         stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                        <path d="M9 5l7 7-7 7"/>
                    </svg>
                    <span class="truncate font-medium text-[#6B665D]">
                        <?= htmlspecialchars($pageTitle) ?>
                    </span>
                </nav>

                <!-- Titre de la page -->
                <h2 class="truncate font-serif text-[16px] font-medium text-[#1F1E1D] sm:text-[18px]">
                    <?= htmlspecialchars($pageTitle) ?>
                </h2>
            </div>
        </div>

        <!-- Droite : Horodatage + Avatar -->
        <div class="flex shrink-0 items-center gap-2.5">

            <!-- Horodatage (desktop) -->
            <div class="hidden items-center gap-2 rounded-lg border border-[#EAE6DD] bg-[#F3F0E8]
                        px-3 py-1.5 sm:flex">
                <svg class="h-3.5 w-3.5 text-[#A8A29A]" fill="none" stroke="currentColor"
                     stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"
                     viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="8.5"/>
                    <path d="M12 7.5v4.5l3 1.75"/>
                </svg>
                <span class="text-[11.5px] font-medium tabular-nums text-[#6B665D]">
                    <?= date('d/m/Y H:i') ?>
                </span>
            </div>

            <!-- Séparateur vertical -->
            <span class="hidden h-5 w-px bg-[#EAE6DD] sm:block"></span>

            <!-- Avatar -->
            <button type="button"
                    aria-label="Menu du compte"
                    class="flex h-8 w-8 items-center justify-center rounded-[10px]
                           bg-[#C96442] font-serif text-[13px] font-medium text-white
                           shadow-[0_1px_3px_rgba(201,100,66,0.35)]
                           ring-1 ring-[#C96442]/20 transition-transform duration-150
                           hover:scale-[1.03] active:scale-95">
                A
            </button>
        </div>
    </div>
</header>