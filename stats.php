<?php
// ============================================================
// Page : Statistiques
// ============================================================

require 'db.php';

include 'includes/expire_orders.php';

$currentPage = 'stats';

$pageTitle   = 'Statistiques';

$totalClients = 0;

$res1 = $conn->query("SELECT COUNT(*) as total FROM clients");

if ($res1) { $row1 = $res1->fetch_assoc(); $totalClients = $row1['total']; }


$totalOrders = 0; $totalPaid = 0; $totalPending = 0; $totalFailed = 0; $totalCancelled = 0; $revenue = 0;

$res2 = $conn->query("SELECT status, COUNT(*) as cnt, COALESCE(SUM(amount), 0) as total_amount FROM orders GROUP BY status");

if ($res2) {
    while ($row2 = $res2->fetch_assoc()) {

        $totalOrders += $row2['cnt'];

        if ($row2['status'] === 'PAID')      { $totalPaid      = $row2['cnt']; $revenue = $row2['total_amount']; }

        if ($row2['status'] === 'PENDING')   { $totalPending   = $row2['cnt']; }

        if ($row2['status'] === 'FAILED')    { $totalFailed    = $row2['cnt']; }

        if ($row2['status'] === 'CANCELLED') { $totalCancelled = $row2['cnt']; }
    }
}

$totalPayments = 0; $successPayments = 0; $failedPayments = 0;

$res3 = $conn->query("SELECT status, COUNT(*) as cnt FROM payments GROUP BY status");

if ($res3) {

    while ($row3 = $res3->fetch_assoc()) {

        $totalPayments += $row3['cnt'];

        if ($row3['status'] === 'SUCCESS') { $successPayments = $row3['cnt']; }

        if ($row3['status'] === 'FAILED')  { $failedPayments  = $row3['cnt']; }
    }
}

$successRate = ($totalOrders > 0) ? round(($totalPaid / $totalOrders) * 100, 1) : 0;

$recentPayments = $conn->query("
    SELECT p.transaction_id, p.amount, p.status, p.provider, p.paid_at, p.created_at,
           o.order_number, c.name AS client_name
    FROM payments p LEFT JOIN orders o ON p.order_id = o.id LEFT JOIN clients c ON o.client_id = c.id
    ORDER BY p.created_at DESC LIMIT 10
");
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistiques — PaySimu</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600&family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', system-ui, -apple-system, sans-serif; }
        .font-serif { font-family: 'Fraunces', ui-serif, Georgia, serif; }
        .main-content { transition: margin-left 0.3s ease-in-out; }
        html.sidebar-collapsed #mainContent { margin-left: 0 !important; }
        .scrollbar-thin::-webkit-scrollbar { width: 5px; height: 5px; }
        .scrollbar-thin::-webkit-scrollbar-thumb { background: #E4DFD4; border-radius: 999px; }
        .scrollbar-thin::-webkit-scrollbar-thumb:hover { background: #D3CCBE; }
        .scrollbar-thin::-webkit-scrollbar-track { background: transparent; }
        @keyframes fadeUp { from { opacity: 0; transform: translateY(16px); } to { opacity: 1; transform: translateY(0); } }
        .stat-card { animation: fadeUp 0.5s ease-out backwards; }
        .stat-card:nth-child(1) { animation-delay: 0.05s; }
        .stat-card:nth-child(2) { animation-delay: 0.10s; }
        .stat-card:nth-child(3) { animation-delay: 0.15s; }
        .stat-card:nth-child(4) { animation-delay: 0.20s; }
    </style>
</head>
<body class="bg-[#FAF9F5] text-[#1F1E1D] min-h-screen antialiased">

    <?php include 'includes/sidebar.php'; ?>

    <div id="mainContent" class="main-content lg:ml-[272px] min-h-screen flex flex-col">

        <?php include 'includes/header.php'; ?>

        <main class="flex-1 px-4 sm:px-6 lg:px-8 py-6 sm:py-8">

            <!-- En-tête -->
            <div class="mb-8">
                <h2 class="font-serif text-[22px] sm:text-[26px] font-medium tracking-tight text-[#1F1E1D] mb-1.5">Statistiques</h2>
                <p class="text-[13px] text-[#8A8578]">Vue d'ensemble de l'activité de la plateforme.</p>
            </div>

            <!-- Grille résumé -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-8">

                <!-- Clients -->
                <div class="stat-card group bg-white rounded-2xl border border-[#EAE6DD] p-4 sm:p-5
                            shadow-[0_1px_3px_rgba(31,30,29,0.04)]
                            hover:shadow-[0_4px_14px_rgba(31,30,29,0.08)] hover:border-[#D3CCBE]
                            transition-all duration-300">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-[10px] sm:text-[10.5px] font-semibold text-[#A8A29A] uppercase tracking-[0.14em]">Clients</span>
                        <div class="w-8 h-8 bg-[#F3F0E8] rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-4 h-4 text-[#6B665D]" fill="none" stroke="currentColor" stroke-width="1.7"
                                 stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                <path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </div>
                    </div>
                    <p class="font-serif text-2xl sm:text-3xl font-medium tracking-tight text-[#1F1E1D]"><?= $totalClients ?></p>
                    <p class="text-[11px] text-[#A8A29A] mt-1">Comptes enregistrés</p>
                </div>

                <!-- Taux succès -->
                <div class="stat-card group bg-white rounded-2xl border border-[#EAE6DD] p-4 sm:p-5
                            shadow-[0_1px_3px_rgba(31,30,29,0.04)]
                            hover:shadow-[0_4px_14px_rgba(122,143,79,0.10)] hover:border-[#E0E4D2]
                            transition-all duration-300">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-[10px] sm:text-[10.5px] font-semibold text-[#A8A29A] uppercase tracking-[0.14em]">Taux succès</span>
                        <div class="w-8 h-8 bg-[#EEF1E5] rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-4 h-4 text-[#7A8F4F]" fill="none" stroke="currentColor" stroke-width="1.7"
                                 stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                <path d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                            </svg>
                        </div>
                    </div>
                    <p class="font-serif text-2xl sm:text-3xl font-medium tracking-tight text-[#5F7038]"><?= $successRate ?><span class="text-base font-sans font-medium text-[#7A8F4F]/70 ml-0.5">%</span></p>
                    <p class="text-[11px] text-[#A8A29A] mt-1">Commandes payées</p>
                </div>

                <!-- Transactions -->
                <div class="stat-card group bg-white rounded-2xl border border-[#EAE6DD] p-4 sm:p-5
                            shadow-[0_1px_3px_rgba(31,30,29,0.04)]
                            hover:shadow-[0_4px_14px_rgba(31,30,29,0.08)] hover:border-[#D3CCBE]
                            transition-all duration-300">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-[10px] sm:text-[10.5px] font-semibold text-[#A8A29A] uppercase tracking-[0.14em]">Transactions</span>
                        <div class="w-8 h-8 bg-[#F3F0E8] rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-4 h-4 text-[#6B665D]" fill="none" stroke="currentColor" stroke-width="1.7"
                                 stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                <path d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                            </svg>
                        </div>
                    </div>
                    <p class="font-serif text-2xl sm:text-3xl font-medium tracking-tight text-[#1F1E1D]"><?= $totalPayments ?></p>
                    <p class="text-[11px] text-[#A8A29A] mt-1">Tentatives de paiement</p>
                </div>

                <!-- Revenus -->
                <div class="stat-card group bg-white rounded-2xl border border-[#EAE6DD] p-4 sm:p-5
                            shadow-[0_1px_3px_rgba(31,30,29,0.04)]
                            hover:shadow-[0_4px_14px_rgba(201,100,66,0.10)] hover:border-[#E8D5CE]
                            transition-all duration-300">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-[10px] sm:text-[10.5px] font-semibold text-[#A8A29A] uppercase tracking-[0.14em]">Revenus</span>
                        <div class="w-8 h-8 bg-[#C96442] rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300 shadow-[0_1px_3px_rgba(201,100,66,0.35)]">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="1.7"
                                 stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                <path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                    <p class="font-serif text-xl sm:text-2xl font-medium tracking-tight text-[#C96442]">
                        <?= number_format($revenue, 0, ',', ' ') ?>
                        <span class="font-sans text-[11px] font-medium text-[#C96442]/70 ml-0.5">FCFA</span>
                    </p>
                    <p class="text-[11px] text-[#A8A29A] mt-1">Total collecté</p>
                </div>
            </div>

            <!-- Graphiques -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">

                <!-- Répartition -->
                <div class="bg-white rounded-2xl border border-[#EAE6DD] p-5 sm:p-6
                            shadow-[0_1px_3px_rgba(31,30,29,0.04)]"
                     style="animation: fadeUp 0.5s ease-out 0.25s backwards;">
                    <div class="flex items-center justify-between mb-5">
                        <h3 class="font-serif text-[16px] font-medium tracking-tight text-[#1F1E1D]">Répartition des commandes</h3>
                        <span class="text-[10.5px] font-medium text-[#A8A29A] tabular-nums"><?= $totalOrders ?> total</span>
                    </div>

                    <div class="space-y-4">
                        <?php
                        $bars = [
                            ['Payées',     $totalPaid,      'bg-[#C96442]', 'text-[#A24C2F]', 'bg-[#FBEFE9]'],
                            ['En attente', $totalPending,   'bg-[#C6A052]', 'text-[#8A6A1F]', 'bg-[#FBF4E4]'],
                            ['Échouées',   $totalFailed,    'bg-[#B8573F]', 'text-[#8B3A2A]', 'bg-[#F7EDEA]'],
                            ['Annulées',   $totalCancelled, 'bg-[#B5AFA4]', 'text-[#6B665D]', 'bg-[#F3F0E8]'],
                        ];
                        foreach ($bars as $bar):
                            $pct = $totalOrders > 0 ? ($bar[1] / $totalOrders * 100) : 0;
                        ?>
                        <div>
                            <div class="flex justify-between items-center text-[12px] mb-1.5">
                                <span class="font-medium <?= $bar[3] ?>"><?= $bar[0] ?></span>
                                <span class="font-semibold tabular-nums <?= $bar[3] ?>">
                                    <?= $bar[1] ?>
                                    <span class="font-normal text-[#A8A29A] ml-1">(<?= round($pct) ?>%)</span>
                                </span>
                            </div>
                            <div class="w-full <?= $bar[4] ?> rounded-full h-2 overflow-hidden">
                                <div class="<?= $bar[2] ?> h-2 rounded-full transition-all duration-1000 ease-out" style="width: <?= $pct ?>%"></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Derniers paiements -->
                <div class="bg-white rounded-2xl border border-[#EAE6DD] p-5 sm:p-6
                            shadow-[0_1px_3px_rgba(31,30,29,0.04)]"
                     style="animation: fadeUp 0.5s ease-out 0.3s backwards;">
                    <div class="flex items-center justify-between mb-5">
                        <h3 class="font-serif text-[16px] font-medium tracking-tight text-[#1F1E1D]">Derniers paiements</h3>
                        <span class="text-[10.5px] font-medium text-[#A8A29A]">10 plus récents</span>
                    </div>

                    <?php if ($recentPayments && $recentPayments->num_rows > 0): ?>
                        <div class="space-y-2 max-h-72 overflow-y-auto pr-1 scrollbar-thin">
                            <?php while ($rp = $recentPayments->fetch_assoc()): ?>
                                <div class="flex items-center justify-between p-2.5 rounded-xl
                                            border border-transparent
                                            hover:bg-[#FBF9F4] hover:border-[#F0ECE3]
                                            transition-colors duration-150">
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        <div class="w-8 h-8 rounded-lg flex items-center justify-center text-[11px] font-semibold shrink-0
                                                    <?= $rp['status'] === 'SUCCESS'
                                                        ? 'bg-[#EEF1E5] text-[#5F7038]'
                                                        : 'bg-[#F7EDEA] text-[#8B3A2A]' ?>">
                                            <?= $rp['status'] === 'SUCCESS' ? '✓' : '✕' ?>
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-[12.5px] font-medium text-[#1F1E1D] truncate"><?= htmlspecialchars($rp['client_name'] ?? '—') ?></p>
                                            <p class="font-mono text-[9.5px] text-[#A8A29A] truncate"><?= htmlspecialchars($rp['transaction_id']) ?></p>
                                        </div>
                                    </div>
                                    <div class="text-right shrink-0 ml-2">
                                        <p class="text-[12px] font-semibold text-[#1F1E1D] tabular-nums">
                                            <?= number_format($rp['amount'], 0, ',', ' ') ?>
                                            <span class="text-[9px] text-[#A8A29A] font-normal">FCFA</span>
                                        </p>
                                        <span class="text-[10px] font-medium <?= $rp['status'] === 'SUCCESS' ? 'text-[#5F7038]' : 'text-[#8B3A2A]' ?>">
                                            <?= $rp['status'] === 'SUCCESS' ? 'Réussi' : 'Échoué' ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-12">
                            <div class="w-12 h-12 mx-auto mb-3 bg-[#F3F0E8] rounded-xl flex items-center justify-center">
                                <svg class="w-6 h-6 text-[#C9C3B8]" fill="none" stroke="currentColor" stroke-width="1.6"
                                     stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                    <path d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                            </div>
                            <p class="font-serif text-[14px] font-medium text-[#1F1E1D]">Aucun paiement enregistré</p>
                            <p class="text-[12px] text-[#A8A29A] mt-1">Les transactions apparaîtront ici.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </main>
    </div>

    <script src="assets/js/main.js"></script>
    <?php $conn->close(); ?>
</body>
</html>