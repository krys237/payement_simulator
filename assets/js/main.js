// ============================================================
// Script principal — Sidebar, chevron, paiement, compte à rebours
// ============================================================

document.addEventListener('DOMContentLoaded', () => {

    // ========================================================
    // 1. SIDEBAR MOBILE — Ouverture / Fermeture
    // ========================================================
    const sidebar        = document.getElementById('sidebar');

    const sidebarOverlay = document.getElementById('sidebarOverlay');

    window.toggleSidebar = () => {

        if (!sidebar || !sidebarOverlay) return;
        const isOpen = !sidebar.classList.contains('-translate-x-full');
        if (isOpen) {
            sidebar.classList.add('-translate-x-full');
            sidebarOverlay.classList.add('hidden');
            document.body.style.overflow = '';
        } else {
            sidebar.classList.remove('-translate-x-full');
            sidebarOverlay.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }
    };

    window.addEventListener('resize', () => {
        if (window.innerWidth >= 1024 && sidebar) {

            if (!document.body.dataset.sidebarCollapsed) {

                sidebar.classList.remove('-translate-x-full');
            }
            if (sidebarOverlay) sidebarOverlay.classList.add('hidden');
            document.body.style.overflow = '';
        }
    });


    // ========================================================
    // 2. CHEVRON — Replier / Déplier la sidebar (desktop)
    // ========================================================
    const chevronBtn   = document.getElementById('sidebarChevron');

    const chevronIcon  = document.getElementById('chevronIcon');

    const mainContent  = document.getElementById('mainContent');
    let sidebarCollapsed = false;

    window.toggleDesktopSidebar = () => {
        if (!sidebar || !chevronBtn || !mainContent) return;

        sidebarCollapsed = !sidebarCollapsed;

        if (sidebarCollapsed) {
            // Replier
            sidebar.style.transform = 'translateX(-100%)';

            chevronBtn.style.left = '0px';
            mainContent.style.marginLeft = '0px';
            chevronIcon.style.transform = 'rotate(180deg)';
            document.body.dataset.sidebarCollapsed = 'true';
        } else {
            // Déplier
            sidebar.style.transform = 'translateX(0)';
            chevronBtn.style.left = '280px';
            mainContent.style.marginLeft = '280px';
            chevronIcon.style.transform = 'rotate(0deg)';
            delete document.body.dataset.sidebarCollapsed;
        }
    };


    // ========================================================
    // 3. COMPTE À REBOURS
    // ========================================================
    const countdownEl     = document.getElementById('countdown');
    const countdownCircle = document.getElementById('countdownCircle');
    const payBtn          = document.getElementById('payBtn');
    const feedback        = document.getElementById('feedback');
    let countdownInterval = null;

    if (countdownEl) {
        let remaining   = parseInt(countdownEl.dataset.remaining, 10) || 0;
        const totalTime = 180;

        const renderCountdown = () => {

            const minutes = Math.floor(remaining / 60);

            const seconds = remaining % 60;
            
            countdownEl.textContent =
                String(minutes).padStart(2, '0') + ':' +
                String(seconds).padStart(2, '0');

            // Cercle SVG
            if (countdownCircle) {
                const pct = (remaining / totalTime) * 100;
                countdownCircle.setAttribute('stroke-dasharray', `${pct}, 100`);
            }

            // Changement de couleur final
            if (remaining <= 30) {
                countdownEl.classList.add('text-red-200');
            }
        };

        renderCountdown();

        countdownInterval = setInterval(() => {
            remaining--;
            if (remaining <= 0) {
                remaining = 0;
                renderCountdown();
                clearInterval(countdownInterval);
                handleExpiration();
                return;
            }
            renderCountdown();
        }, 1000);
    }

    const handleExpiration = () => {
        if (payBtn) {
            payBtn.disabled  = true;
            payBtn.innerHTML = `
                <span class="flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Délai expiré
                </span>
            `;
            payBtn.classList.remove('bg-gradient-to-r', 'from-[#D97757]', 'to-[#C2553A]', 'hover:shadow-xl');
            payBtn.classList.add('bg-stone-400', 'cursor-not-allowed');
        }
        if (feedback) {
            feedback.classList.remove('hidden');
            feedback.className = 'mt-4 p-4 rounded-xl bg-red-50 text-red-800 border border-red-200 text-sm';
            feedback.innerHTML = `
                <div class="font-bold mb-1">⏱ Temps expiré</div>
                <p class="text-[12px]">Le délai de 3 minutes est dépassé. Rechargement...</p>
            `;
        }
        setTimeout(() => window.location.reload(), 2500);
    };


    // ========================================================
    // 4. PAIEMENT — Envoi fetch() au webhook
    // ========================================================
    if (payBtn) {
        payBtn.addEventListener('click', async () => {

            const orderId     = payBtn.dataset.orderId;
            const amount      = payBtn.dataset.amount;
            const orderNumber = payBtn.dataset.orderNumber;

            payBtn.disabled = true;
            payBtn.innerHTML = `
                <span class="inline-flex items-center justify-center gap-2">
                    <svg class="animate-spin h-5 w-5" viewBox="0 0 24 24" fill="none">
                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" class="opacity-25"/>
                        <path fill="currentColor" class="opacity-75" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
                    </svg>
                    Traitement en cours...
                </span>
            `;

            if (feedback) {
                feedback.classList.remove('hidden');
                feedback.className = 'mt-4 p-4 rounded-xl bg-amber-50 text-amber-800 border border-amber-200 text-[13px]';
                feedback.innerHTML = `
                    <div class="flex items-center gap-2">
                        <svg class="animate-spin h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none">
                            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" class="opacity-25"/>
                            <path fill="currentColor" class="opacity-75" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
                        </svg>
                        Communication avec la passerelle de paiement...
                    </div>
                `;
            }

            try {
                const response = await fetch('api/v1/payment/callback.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        order_id:     parseInt(orderId, 10),
                        order_number: orderNumber,
                        amount:       parseFloat(amount),
                        provider:     'MOBILE_MONEY'
                    })
                });

                const text = await response.text();
                let result;
                try { result = JSON.parse(text); }
                catch (e) { throw new Error('Réponse invalide : ' + text.substring(0, 200)); }

                if (result.success) {
                    if (countdownInterval) clearInterval(countdownInterval);

                    payBtn.innerHTML = `
                        <span class="flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Paiement effectué !
                        </span>
                    `;
                    payBtn.classList.remove('from-[#D97757]', 'to-[#C2553A]');
                    payBtn.classList.add('from-green-600', 'to-green-700');

                    if (feedback) {
                        feedback.className = 'mt-4 p-5 rounded-xl bg-green-50 text-green-800 border border-green-200 text-[13px]';
                        feedback.innerHTML = `
                            <div class="font-bold mb-2 flex items-center gap-2">
                                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Paiement réussi !
                            </div>
                            <div class="space-y-1 text-[12px] bg-green-100/50 rounded-lg p-3">
                                <p>Transaction : <span class="font-mono font-bold">${result.transaction_id}</span></p>
                                <p>Montant : <span class="font-bold">${parseFloat(result.amount).toLocaleString('fr-FR')} FCFA</span></p>
                                <p>Date : <span class="font-medium">${result.paid_at}</span></p>
                            </div>
                            <p class="mt-3 text-[11px] text-green-600">Redirection dans 2 secondes...</p>
                        `;
                    }

                    setTimeout(() => { window.location.href = 'index.php'; }, 2000);

                } else {
                    payBtn.disabled  = false;
                    payBtn.innerHTML = `
                        <span class="flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            Réessayer
                        </span>
                    `;

                    if (feedback) {
                        feedback.className = 'mt-4 p-4 rounded-xl bg-red-50 text-red-800 border border-red-200 text-[13px]';
                        feedback.innerHTML = `
                            <div class="font-bold mb-1">✗ Échec du paiement</div>
                            <p class="text-[12px]">${result.message || 'Erreur inconnue.'}</p>
                        `;
                    }

                    if (result.expired) {
                        setTimeout(() => window.location.reload(), 2000);
                    }
                }

            } catch (error) {
                payBtn.disabled  = false;
                payBtn.innerHTML = 'Réessayer';

                if (feedback) {
                    feedback.className = 'mt-4 p-4 rounded-xl bg-red-50 text-red-800 border border-red-200 text-[13px]';
                    feedback.innerHTML = `
                        <div class="font-bold mb-1">✗ Erreur de connexion</div>
                        <p class="text-[12px] font-mono">${error.message}</p>
                    `;
                }
            }
        });
    }

});