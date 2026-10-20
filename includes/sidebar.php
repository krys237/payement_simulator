<?php
// ============================================================
// Sidebar réutilisable — Design amélioré avec chevron
// Variable $currentPage définie avant l'inclusion
// ============================================================
?>

<!-- Overlay mobile -->
<div id="sidebarOverlay"
     class="fixed inset-0 bg-black/50 backdrop-blur-sm z-30 hidden lg:hidden transition-all duration-300"
     onclick="toggleSidebar()">
</div>

<!-- Sidebar -->
<aside id="sidebar"
       class="fixed top-0 left-0 h-full w-[280px] bg-white/95 backdrop-blur-xl border-r border-stone-200/80 z-40
              transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out
              flex flex-col shadow-2xl lg:shadow-none">

    <!-- Logo & Branding -->
    <div class="px-6 py-5 border-b border-stone-100/80">
        <div class="flex items-center gap-3">
            <div class="relative">
                <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-[#D97757] to-[#C2553A] flex items-center justify-center text-white font-bold text-lg shadow-lg shadow-[#D97757]/25">
                    P
                </div>
                <div class="absolute -bottom-0.5 -right-0.5 w-3.5 h-3.5 bg-green-400 rounded-full border-2 border-white"></div>
            </div>
            <div>
                <h1 class="text-[17px] font-bold tracking-tight text-stone-800">PaySimu</h1>
                <p class="text-[11px] text-stone-400 font-medium tracking-wide uppercase">Simulation de paiement</p>
            </div>
        </div>
    </div>

    <!-- Navigation principale -->
    <nav class="flex-1 px-3 py-5 space-y-1 overflow-y-auto scrollbar-thin">

        <p class="px-3 mb-3 text-[10px] font-bold text-stone-400/80 uppercase tracking-[0.15em]">Navigation</p>

        <!-- Tableau de bord -->
        <a href="index.php"
           class="group flex items-center gap-3 px-3 py-2.5 rounded-xl text-[13px] font-medium transition-all duration-200
                  <?= ($currentPage === 'dashboard')
                      ? 'bg-gradient-to-r from-[#D97757]/10 to-[#D97757]/5 text-[#D97757] shadow-sm shadow-[#D97757]/5 border border-[#D97757]/10'
                      : 'text-stone-500 hover:bg-stone-50 hover:text-stone-800' ?>">
            <div class="w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-200
                        <?= ($currentPage === 'dashboard') ? 'bg-[#D97757]/15' : 'bg-stone-100 group-hover:bg-stone-200' ?>">
                <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                          d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-4 0a1 1 0 01-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 01-1 1h-2z"/>
                </svg>
            </div>
            Tableau de bord
            <?php if ($currentPage === 'dashboard'): ?>
                <div class="ml-auto w-1.5 h-1.5 rounded-full bg-[#D97757]"></div>
            <?php endif; ?>
        </a>

        <!-- Nouvelle commande -->
        <a href="checkout.php"
           class="group flex items-center gap-3 px-3 py-2.5 rounded-xl text-[13px] font-medium transition-all duration-200
                  <?= ($currentPage === 'checkout')
                      ? 'bg-gradient-to-r from-[#D97757]/10 to-[#D97757]/5 text-[#D97757] shadow-sm shadow-[#D97757]/5 border border-[#D97757]/10'
                      : 'text-stone-500 hover:bg-stone-50 hover:text-stone-800' ?>">
            <div class="w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-200
                        <?= ($currentPage === 'checkout') ? 'bg-[#D97757]/15' : 'bg-stone-100 group-hover:bg-stone-200' ?>">
                <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                          d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            Nouvelle commande
            <?php if ($currentPage === 'checkout'): ?>
                <div class="ml-auto w-1.5 h-1.5 rounded-full bg-[#D97757]"></div>
            <?php endif; ?>
        </a>

        <!-- Simuler un paiement -->
        <a href="simulate_payment.php"
           class="group flex items-center gap-3 px-3 py-2.5 rounded-xl text-[13px] font-medium transition-all duration-200
                  <?= ($currentPage === 'simulate')
                      ? 'bg-gradient-to-r from-[#D97757]/10 to-[#D97757]/5 text-[#D97757] shadow-sm shadow-[#D97757]/5 border border-[#D97757]/10'
                      : 'text-stone-500 hover:bg-stone-50 hover:text-stone-800' ?>">
            <div class="w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-200
                        <?= ($currentPage === 'simulate') ? 'bg-[#D97757]/15' : 'bg-stone-100 group-hover:bg-stone-200' ?>">
                <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                          d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                </svg>
            </div>
            Simuler un paiement
            <?php if ($currentPage === 'simulate'): ?>
                <div class="ml-auto w-1.5 h-1.5 rounded-full bg-[#D97757]"></div>
            <?php endif; ?>
        </a>

        <div class="pt-5 pb-2">
            <p class="px-3 mb-3 text-[10px] font-bold text-stone-400/80 uppercase tracking-[0.15em]">Analyse</p>
        </div>

        <!-- Statistiques -->
        <a href="stats.php"
           class="group flex items-center gap-3 px-3 py-2.5 rounded-xl text-[13px] font-medium transition-all duration-200
                  <?= ($currentPage === 'stats')
                      ? 'bg-gradient-to-r from-[#D97757]/10 to-[#D97757]/5 text-[#D97757] shadow-sm shadow-[#D97757]/5 border border-[#D97757]/10'
                      : 'text-stone-500 hover:bg-stone-50 hover:text-stone-800' ?>">
            <div class="w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-200
                        <?= ($currentPage === 'stats') ? 'bg-[#D97757]/15' : 'bg-stone-100 group-hover:bg-stone-200' ?>">
                <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                          d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
            </div>
            Statistiques
            <?php if ($currentPage === 'stats'): ?>
                <div class="ml-auto w-1.5 h-1.5 rounded-full bg-[#D97757]"></div>
            <?php endif; ?>
        </a>

    </nav>

    <!-- Card d'information en bas -->
    <div class="p-3 border-t border-stone-100/80">
        <div class="bg-gradient-to-br from-stone-50 to-stone-100/50 rounded-2xl p-4 border border-stone-200/50">
            <div class="flex items-start gap-3">
                <div class="w-8 h-8 bg-green-100 rounded-xl flex items-center justify-center shrink-0">
                    <span class="relative flex h-2.5 w-2.5">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-500 opacity-60"></span>
                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-green-500"></span>
                    </span>
                </div>
                <div>
                    <p class="text-[12px] font-semibold text-stone-700">Mode Simulation</p>
                    <p class="text-[11px] text-stone-500 mt-0.5 leading-relaxed">Aucune transaction réelle n'est effectuée sur cette plateforme.</p>
                </div>
            </div>
        </div>
    </div>
</aside>

<!-- ============================================================ -->
<!-- CHEVRON — Bouton repliable entre sidebar et contenu (desktop) -->
<!-- ============================================================ -->
<button id="sidebarChevron"
        onclick="toggleDesktopSidebar()"
        class="hidden lg:flex fixed top-1/2 -translate-y-1/2 z-50 w-6 h-12 items-center justify-center
               bg-white border border-stone-200 rounded-r-lg shadow-md
               hover:bg-stone-50 hover:shadow-lg transition-all duration-300 group"
        style="left: 280px;"
        title="Réduire la sidebar">
    <svg id="chevronIcon" class="w-4 h-4 text-stone-400 group-hover:text-[#D97757] transition-all duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
    </svg>
</button>