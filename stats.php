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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', system-ui, -apple-system, sans-serif; }
        .main-content { transition: margin-left 0.3s ease-in-out; }
        @keyframes fadeUp { from { opacity: 0; transform: translateY(16px); } to { opacity: 1; transform: translateY(0); } }
        .stat-card { animation: fadeUp 0.5s ease-out backwards; }
        .stat-card:nth-child(1) { animation-delay: 0.05s; }
        .stat-card:nth-child(2) { animation-delay: 0.10s; }
        .stat-card:nth-child(3) { animation-delay: 0.15s; }
        .stat-card:nth-child(4) { animation-delay: 0.20s; }
    </style>
</head>
<body class="bg-[#FAFAF9] text-stone-800 min-h-screen">

    <?php include 'includes/sidebar.php'; ?>

    <div id="mainContent" class="main-content lg:ml-[280px] min-h-screen flex flex-col">

        <?php include 'includes/header.php'; ?>

        <main class="flex-1 px-4 sm:px-6 lg:px-8 py-6 sm:py-8">

            <!-- En-tête -->
            <div class="mb-8">
                <h2 class="text-2xl sm:text-3xl font-bold tracking-tight mb-2">Statistiques</h2>
                <p class="text-stone-500 text-sm">Vue d'ensemble de l'activité de la plateforme.</p>
            </div>

            <!-- Grille résumé -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-8">
                <div class="stat-card group bg-white rounded-2xl border border-stone-200/60 p-4 sm:p-5 shadow-sm hover:shadow-md hover:border-stone-300/60 transition-all duration-300">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[10px] sm:text-[11px] font-semibold text-stone-400 uppercase tracking-wider">Clients</span>
                        <div class="w-8 h-8 bg-blue-50 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                            <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </div>
                    </div>
                    <p class="text-2xl sm:text-3xl font-extrabold"><?= $totalClients ?></p>
                </div>
                <div class="stat-card group bg-white rounded-2xl border border-stone-200/60 p-4 sm:p-5 shadow-sm hover:shadow-md hover:border-green-200 transition-all duration-300">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[10px] sm:text-[11px] font-semibold text-stone-400 uppercase tracking-wider">Taux succès</span>
                        <div class="w-8 h-8 bg-green-50 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                            <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                        </div>
                    </div>
                    <p class="text-2xl sm:text-3xl font-extrabold text-green-700"><?= $successRate ?>%</p>
                </div>
                <div class="stat-card group bg-white rounded-2xl border border-stone-200/60 p-4 sm:p-5 shadow-sm hover:shadow-md transition-all duration-300">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[10px] sm:text-[11px] font-semibold text-stone-400 uppercase tracking-wider">Transactions</span>
                        <div class="w-8 h-8 bg-purple-50 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                            <svg class="w-4 h-4 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/></svg>
                        </div>
                    </div>
                    <p class="text-2xl sm:text-3xl font-extrabold"><?= $totalPayments ?></p>
                </div>
                <div class="stat-card group bg-white rounded-2xl border border-stone-200/60 p-4 sm:p-5 shadow-sm hover:shadow-md hover:border-[#D97757]/30 transition-all duration-300">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[10px] sm:text-[11px] font-semibold text-stone-400 uppercase tracking-wider">Revenus</span>
                        <div class="w-8 h-8 bg-[#D97757]/10 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                            <svg class="w-4 h-4 text-[#D97757]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                    </div>
                    <p class="text-xl sm:text-2xl font-extrabold text-[#D97757]"><?= number_format($revenue, 0, ',', ' ') ?> <span class="text-[11px] font-semibold text-[#D97757]/60">FCFA</span></p>
                </div>
            </div>

            <!-- Graphiques -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">

                <!-- Répartition -->
                <div class="bg-white rounded-2xl border border-stone-200/60 p-5 sm:p-6 shadow-sm" style="animation: fadeUp 0.5s ease-out 0.25s backwards;">
                    <h3 class="text-[14px] font-bold mb-5">Répartition des commandes</h3>
                    <div class="space-y-4">
                        <?php
                        $bars = [
                            ['Payées',    $totalPaid,      'bg-green-500',  'text-green-700'],
                            ['En attente',$totalPending,   'bg-amber-500',  'text-amber-700'],
                            ['Échouées',  $totalFailed,    'bg-red-500',    'text-red-700'],
                            ['Annulées',  $totalCancelled, 'bg-stone-400',  'text-stone-600'],
                        ];
                        foreach ($bars as $bar):
                            $pct = $totalOrders > 0 ? ($bar[1] / $totalOrders * 100) : 0;
                        ?>
                        <div>
                            <div class="flex justify-between items-center text-[12px] mb-1.5">
                                <span class="font-medium <?= $bar[3] ?>"><?= $bar[0] ?></span>
                                <span class="font-bold <?= $bar[3] ?>"><?= $bar[1] ?> <span class="font-normal text-stone-400">(<?= round($pct) ?>%)</span></span>
                            </div>
                            <div class="w-full bg-stone-100 rounded-full h-2.5 overflow-hidden">
                                <div class="<?= $bar[2] ?> h-2.5 rounded-full transition-all duration-1000 ease-out" style="width: <?= $pct ?>%"></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Derniers paiements -->
                <div class="bg-white rounded-2xl border border-stone-200/60 p-5 sm:p-6 shadow-sm" style="animation: fadeUp 0.5s ease-out 0.3s backwards;">
                    <h3 class="text-[14px] font-bold mb-5">Derniers paiements</h3>
                    <?php if ($recentPayments && $recentPayments->num_rows > 0): ?>
                        <div class="space-y-2.5 max-h-72 overflow-y-auto pr-1 scrollbar-thin">
                            <?php while ($rp = $recentPayments->fetch_assoc()): ?>
                                <div class="flex items-center justify-between p-3 bg-stone-50/80 rounded-xl hover:bg-stone-100/60 transition-colors">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-lg flex items-center justify-center text-[10px] font-bold
                                                    <?= $rp['status'] === 'SUCCESS' ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600' ?>">
                                            <?= $rp['status'] === 'SUCCESS' ? '✓' : '✕' ?>
                                        </div>
                                        <div>
                                            <p class="text-[12px] font-semibold text-stone-700"><?= htmlspecialchars($rp['client_name'] ?? '—') ?></p>
                                            <p class="font-mono text-[9px] text-stone-400"><?= htmlspecialchars($rp['transaction_id']) ?></p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-[12px] font-bold text-stone-800"><?= number_format($rp['amount'], 0, ',', ' ') ?> <span class="text-[9px] text-stone-400">FCFA</span></p>
                                        <span class="text-[10px] <?= $rp['status'] === 'SUCCESS' ? 'text-green-600' : 'text-red-600' ?> font-medium"><?= $rp['status'] === 'SUCCESS' ? 'Réussi' : 'Échoué' ?></span>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-10">
                            <div class="w-12 h-12 mx-auto mb-3 bg-stone-100 rounded-xl flex items-center justify-center">
                                <svg class="w-6 h-6 text-stone-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                            </div>
                            <p class="text-[13px] text-stone-400">Aucun paiement enregistré.</p>
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