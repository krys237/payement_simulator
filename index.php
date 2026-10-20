<?php
// ============================================================
// Page : Tableau de bord
// ============================================================

require 'db.php';

include 'includes/expire_orders.php';

$currentPage = 'dashboard';

$pageTitle   = 'Tableau de bord';

// Statistiques
$totalOrders = 0; $totalPaid = 0; $totalPending = 0; $totalFailed = 0; $totalRevenue = 0;

$statsResult = $conn->query("SELECT status, COUNT(*) as total, SUM(amount) as sum_amount FROM orders GROUP BY status");

if ($statsResult) {

    while ($statRow = $statsResult->fetch_assoc()) {

        $totalOrders += $statRow['total'];

        if ($statRow['status'] === 'PAID')    { $totalPaid    = $statRow['total']; $totalRevenue = $statRow['sum_amount']; }
        if ($statRow['status'] === 'PENDING') { $totalPending = $statRow['total']; }
        if ($statRow['status'] === 'FAILED')  { $totalFailed  = $statRow['total']; }
    }
}

// Liste des commandes
$sql = "SELECT o.id, o.order_number, o.amount, o.status, o.created_at,
               c.name AS client_name, c.email AS client_email,
               p.transaction_id, p.status AS payment_status, p.provider, p.paid_at
        FROM orders o
        LEFT JOIN clients c ON o.client_id = c.id
        LEFT JOIN payments p ON p.order_id = o.id
        ORDER BY o.created_at DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord — PaySimu</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', system-ui, -apple-system, sans-serif; }
        .scrollbar-thin::-webkit-scrollbar { width: 4px; height: 4px; }
        .scrollbar-thin::-webkit-scrollbar-thumb { background: #d6d3d1; border-radius: 4px; }
        .stat-card { animation: fadeUp 0.5s ease-out backwards; }
        .stat-card:nth-child(1) { animation-delay: 0.05s; }
        .stat-card:nth-child(2) { animation-delay: 0.10s; }
        .stat-card:nth-child(3) { animation-delay: 0.15s; }
        .stat-card:nth-child(4) { animation-delay: 0.20s; }
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(16px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .table-row { animation: fadeIn 0.3s ease-out backwards; }
        @keyframes fadeIn {
            from { opacity: 0; }
            to   { opacity: 1; }
        }
        /* Transition du contenu principal quand sidebar réduite */
        .main-content {
            transition: margin-left 0.3s ease-in-out;
        }
    </style>
</head>
<body class="bg-[#FAFAF9] text-stone-800 min-h-screen">

    <?php include 'includes/sidebar.php'; ?>

    <div id="mainContent" class="main-content lg:ml-[280px] min-h-screen flex flex-col">

        <?php include 'includes/header.php'; ?>

        <main class="flex-1 px-4 sm:px-6 lg:px-8 py-6 sm:py-8">

            <!-- Cartes de statistiques -->
            <div class="grid grid-cols-2 xl:grid-cols-4 gap-3 sm:gap-4 mb-8">

                <!-- Total commandes -->
                <div class="stat-card group bg-white rounded-2xl border border-stone-200/60 p-4 sm:p-5 shadow-sm hover:shadow-md hover:border-stone-300/60 transition-all duration-300">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-[11px] sm:text-xs font-semibold text-stone-400 uppercase tracking-wider">Commandes</span>
                        <div class="w-9 h-9 bg-gradient-to-br from-stone-100 to-stone-50 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-[18px] h-[18px] text-stone-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                        </div>
                    </div>
                    <p class="text-2xl sm:text-3xl font-extrabold tracking-tight"><?= $totalOrders ?></p>
                    <p class="text-[11px] text-stone-400 mt-1">Total cumulé</p>
                </div>

                <!-- Payées -->
                <div class="stat-card group bg-white rounded-2xl border border-stone-200/60 p-4 sm:p-5 shadow-sm hover:shadow-md hover:border-green-200 transition-all duration-300">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-[11px] sm:text-xs font-semibold text-stone-400 uppercase tracking-wider">Payées</span>
                        <div class="w-9 h-9 bg-gradient-to-br from-green-100 to-green-50 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-[18px] h-[18px] text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                    <p class="text-2xl sm:text-3xl font-extrabold tracking-tight text-green-700"><?= $totalPaid ?></p>
                    <p class="text-[11px] text-green-500 mt-1">Transactions réussies</p>
                </div>

                <!-- En attente -->
                <div class="stat-card group bg-white rounded-2xl border border-stone-200/60 p-4 sm:p-5 shadow-sm hover:shadow-md hover:border-amber-200 transition-all duration-300">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-[11px] sm:text-xs font-semibold text-stone-400 uppercase tracking-wider">En attente</span>
                        <div class="w-9 h-9 bg-gradient-to-br from-amber-100 to-amber-50 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-[18px] h-[18px] text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                    <p class="text-2xl sm:text-3xl font-extrabold tracking-tight text-amber-600"><?= $totalPending ?></p>
                    <p class="text-[11px] text-amber-500 mt-1">À traiter</p>
                </div>

                <!-- Revenus -->
                <div class="stat-card group bg-white rounded-2xl border border-stone-200/60 p-4 sm:p-5 shadow-sm hover:shadow-md hover:border-[#D97757]/30 transition-all duration-300">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-[11px] sm:text-xs font-semibold text-stone-400 uppercase tracking-wider">Revenus</span>
                        <div class="w-9 h-9 bg-gradient-to-br from-[#D97757]/15 to-[#D97757]/5 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-[18px] h-[18px] text-[#D97757]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                    <p class="text-xl sm:text-2xl font-extrabold tracking-tight text-[#D97757]"><?= number_format($totalRevenue, 0, ',', ' ') ?></p>
                    <p class="text-[11px] text-[#D97757]/70 mt-1">FCFA collectés</p>
                </div>
            </div>

            <!-- Tableau des commandes -->
            <div class="bg-white rounded-2xl shadow-sm border border-stone-200/60 overflow-hidden" style="animation: fadeUp 0.5s ease-out 0.25s backwards;">

                <div class="px-4 sm:px-6 py-4 border-b border-stone-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <h3 class="text-[15px] font-bold text-stone-800">Commandes récentes</h3>
                        <p class="text-[12px] text-stone-400 mt-0.5">Suivi en temps réel de toutes les commandes</p>
                    </div>
                    <a href="checkout.php"
                       class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-gradient-to-r from-[#D97757] to-[#C2553A] text-white text-[12px] font-semibold rounded-xl hover:shadow-lg hover:shadow-[#D97757]/20 active:scale-[0.97] transition-all duration-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                        </svg>
                        Nouvelle commande
                    </a>
                </div>

                <!-- Desktop -->
                <div class="hidden md:block overflow-x-auto scrollbar-thin">
                    <table class="w-full text-sm">
                        <thead class="bg-stone-50/60">
                            <tr class="text-left text-stone-400 uppercase text-[10px] tracking-wider font-semibold">
                                <th class="px-6 py-3">N° Commande</th>
                                <th class="px-6 py-3">Client</th>
                                <th class="px-6 py-3">Montant</th>
                                <th class="px-6 py-3">Statut</th>
                                <th class="px-6 py-3">Paiement</th>
                                <th class="px-6 py-3">Date</th>
                                <th class="px-6 py-3 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100/80">
                        <?php if ($result && $result->num_rows > 0): ?>
                            <?php $rowIndex = 0; while ($row = $result->fetch_assoc()): $rowIndex++; ?>
                                <tr class="table-row hover:bg-stone-50/50 transition-colors duration-150" style="animation-delay: <?= $rowIndex * 0.03 ?>s;">
                                    <td class="px-6 py-3.5">
                                        <span class="font-mono text-[11px] bg-stone-100 text-stone-600 px-2 py-1 rounded-md"><?= htmlspecialchars($row['order_number']) ?></span>
                                    </td>
                                    <td class="px-6 py-3.5">
                                        <div class="flex items-center gap-2.5">
                                            <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-stone-200 to-stone-100 flex items-center justify-center text-[11px] font-bold text-stone-500">
                                                <?= strtoupper(mb_substr($row['client_name'] ?? '?', 0, 1)) ?>
                                            </div>
                                            <div>
                                                <p class="text-[13px] font-medium text-stone-700 leading-tight"><?= htmlspecialchars($row['client_name'] ?? '—') ?></p>
                                                <p class="text-[11px] text-stone-400"><?= htmlspecialchars($row['client_email'] ?? '') ?></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-3.5">
                                        <span class="text-[13px] font-bold text-stone-800"><?= number_format($row['amount'], 0, ',', ' ') ?></span>
                                        <span class="text-[10px] text-stone-400 ml-0.5">FCFA</span>
                                    </td>
                                    <td class="px-6 py-3.5">
                                        <?php
                                        $statusConfig = [
                                            'PAID'      => ['bg-green-50 text-green-700 border-green-200', 'Payée', '●'],
                                            'PENDING'   => ['bg-amber-50 text-amber-700 border-amber-200', 'En attente', '◉'],
                                            'FAILED'    => ['bg-red-50 text-red-700 border-red-200', 'Échouée', '✕'],
                                            'CANCELLED' => ['bg-stone-100 text-stone-500 border-stone-200', 'Annulée', '—'],
                                        ];
                                        $sc = $statusConfig[$row['status']] ?? ['bg-stone-100 text-stone-600 border-stone-200', $row['status'], '?'];
                                        ?>
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-semibold border <?= $sc[0] ?>">
                                            <span class="text-[8px]"><?= $sc[2] ?></span>
                                            <?= $sc[1] ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-3.5">
                                        <?php if ($row['transaction_id']): ?>
                                            <p class="font-mono text-[10px] text-stone-500 leading-tight"><?= htmlspecialchars($row['transaction_id']) ?></p>
                                            <p class="text-[10px] text-stone-400 mt-0.5"><?= htmlspecialchars($row['provider']) ?></p>
                                        <?php else: ?>
                                            <span class="text-[11px] text-stone-300">Aucun</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-3.5 text-[12px] text-stone-500"><?= date('d/m/Y H:i', strtotime($row['created_at'])) ?></td>
                                    <td class="px-6 py-3.5 text-right">
                                        <?php if ($row['status'] === 'PENDING'): ?>
                                            <a href="simulate_payment.php?order_id=<?= $row['id'] ?>"
                                               class="inline-flex items-center gap-1 px-3 py-1.5 bg-gradient-to-r from-[#D97757] to-[#C2553A] text-white text-[11px] font-semibold rounded-lg hover:shadow-md hover:shadow-[#D97757]/20 active:scale-95 transition-all duration-200">
                                                Payer
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                                                </svg>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-stone-300 text-[11px]">—</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="px-6 py-20 text-center">
                                    <div class="w-16 h-16 mx-auto mb-4 bg-stone-100 rounded-2xl flex items-center justify-center">
                                        <svg class="w-8 h-8 text-stone-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                        </svg>
                                    </div>
                                    <p class="font-semibold text-stone-600">Aucune commande pour le moment</p>
                                    <p class="text-[13px] text-stone-400 mt-1">Créez votre première commande pour commencer.</p>
                                    <a href="checkout.php" class="inline-flex mt-5 px-5 py-2.5 bg-gradient-to-r from-[#D97757] to-[#C2553A] text-white text-[13px] font-semibold rounded-xl hover:shadow-lg hover:shadow-[#D97757]/20 transition-all">Créer une commande</a>
                                </td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Mobile -->
                <div class="md:hidden divide-y divide-stone-100/80">
                    <?php
                    if ($result && $result->num_rows > 0) {
                        $result->data_seek(0);
                        $mobileIndex = 0;
                        while ($row = $result->fetch_assoc()):
                            $mobileIndex++;
                            $sc = $statusConfig[$row['status']] ?? ['bg-stone-100 text-stone-600 border-stone-200', $row['status'], '?'];
                    ?>
                        <div class="p-4 hover:bg-stone-50/50 transition-colors duration-150" style="animation: fadeUp 0.3s ease-out <?= $mobileIndex * 0.05 ?>s backwards;">
                            <div class="flex items-start justify-between mb-3">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-stone-200 to-stone-100 flex items-center justify-center text-[12px] font-bold text-stone-500">
                                        <?= strtoupper(mb_substr($row['client_name'] ?? '?', 0, 1)) ?>
                                    </div>
                                    <div>
                                        <p class="text-[13px] font-semibold text-stone-700"><?= htmlspecialchars($row['client_name'] ?? '—') ?></p>
                                        <p class="font-mono text-[10px] text-stone-400 mt-0.5"><?= htmlspecialchars($row['order_number']) ?></p>
                                    </div>
                                </div>
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-semibold border <?= $sc[0] ?>">
                                    <span class="text-[7px]"><?= $sc[2] ?></span>
                                    <?= $sc[1] ?>
                                </span>
                            </div>
                            <div class="flex items-end justify-between">
                                <div>
                                    <p class="text-lg font-extrabold text-stone-800"><?= number_format($row['amount'], 0, ',', ' ') ?> <span class="text-[11px] font-medium text-stone-400">FCFA</span></p>
                                    <p class="text-[11px] text-stone-400 mt-0.5"><?= date('d/m/Y H:i', strtotime($row['created_at'])) ?></p>
                                </div>
                                <?php if ($row['status'] === 'PENDING'): ?>
                                    <a href="simulate_payment.php?order_id=<?= $row['id'] ?>"
                                       class="px-3.5 py-2 bg-gradient-to-r from-[#D97757] to-[#C2553A] text-white text-[11px] font-semibold rounded-xl hover:shadow-lg active:scale-95 transition-all">
                                        Payer →
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php
                        endwhile;
                    } else {
                    ?>
                        <div class="p-10 text-center">
                            <div class="text-4xl mb-3">📭</div>
                            <p class="font-medium text-stone-700">Aucune commande</p>
                            <a href="checkout.php" class="inline-flex mt-4 px-4 py-2 bg-gradient-to-r from-[#D97757] to-[#C2553A] text-white text-[12px] font-semibold rounded-xl transition-all">Créer</a>
                        </div>
                    <?php } ?>
                </div>
            </div>

        </main>
    </div>

    <script src="assets/js/main.js"></script>
    <?php $conn->close(); ?>
</body>
</html>