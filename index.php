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
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600&family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', system-ui, -apple-system, sans-serif; }
        .font-serif { font-family: 'Fraunces', ui-serif, Georgia, serif; }
        .scrollbar-thin::-webkit-scrollbar { width: 5px; height: 5px; }
        .scrollbar-thin::-webkit-scrollbar-thumb { background: #E4DFD4; border-radius: 999px; }
        .scrollbar-thin::-webkit-scrollbar-thumb:hover { background: #D3CCBE; }
        .scrollbar-thin::-webkit-scrollbar-track { background: transparent; }

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
        html.sidebar-collapsed #mainContent {
            margin-left: 0 !important;
        }
    </style>
</head>
<body class="bg-[#FAF9F5] text-[#1F1E1D] min-h-screen antialiased">

    <?php include 'includes/sidebar.php'; ?>

    <div id="mainContent" class="main-content lg:ml-[272px] min-h-screen flex flex-col">

        <?php include 'includes/header.php'; ?>

        <main class="flex-1 px-4 sm:px-6 lg:px-8 py-6 sm:py-8">

            <!-- Cartes de statistiques -->
            <div class="grid grid-cols-2 xl:grid-cols-4 gap-3 sm:gap-4 mb-8">

                <!-- Total commandes -->
                <div class="stat-card group bg-white rounded-2xl border border-[#EAE6DD] p-4 sm:p-5
                            shadow-[0_1px_3px_rgba(31,30,29,0.04)]
                            hover:shadow-[0_4px_14px_rgba(31,30,29,0.08)] hover:border-[#D3CCBE]
                            transition-all duration-300">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-[10px] sm:text-[10.5px] font-semibold text-[#A8A29A] uppercase tracking-[0.14em]">Commandes</span>
                        <div class="w-9 h-9 bg-[#F3F0E8] rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-[18px] h-[18px] text-[#6B665D]" fill="none" stroke="currentColor" stroke-width="1.7"
                                 stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                <path d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                        </div>
                    </div>
                    <p class="font-serif text-2xl sm:text-3xl font-medium tracking-tight text-[#1F1E1D]"><?= $totalOrders ?></p>
                    <p class="text-[11px] text-[#A8A29A] mt-1">Total cumulé</p>
                </div>

                <!-- Payées -->
                <div class="stat-card group bg-white rounded-2xl border border-[#EAE6DD] p-4 sm:p-5
                            shadow-[0_1px_3px_rgba(31,30,29,0.04)]
                            hover:shadow-[0_4px_14px_rgba(201,100,66,0.10)] hover:border-[#E8D5CE]
                            transition-all duration-300">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-[10px] sm:text-[10.5px] font-semibold text-[#A8A29A] uppercase tracking-[0.14em]">Payées</span>
                        <div class="w-9 h-9 bg-[#FBEFE9] rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-[18px] h-[18px] text-[#C96442]" fill="none" stroke="currentColor" stroke-width="1.7"
                                 stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                    <p class="font-serif text-2xl sm:text-3xl font-medium tracking-tight text-[#C96442]"><?= $totalPaid ?></p>
                    <p class="text-[11px] text-[#A8A29A] mt-1">Transactions réussies</p>
                </div>

                <!-- En attente -->
                <div class="stat-card group bg-white rounded-2xl border border-[#EAE6DD] p-4 sm:p-5
                            shadow-[0_1px_3px_rgba(31,30,29,0.04)]
                            hover:shadow-[0_4px_14px_rgba(31,30,29,0.08)] hover:border-[#D3CCBE]
                            transition-all duration-300">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-[10px] sm:text-[10.5px] font-semibold text-[#A8A29A] uppercase tracking-[0.14em]">En attente</span>
                        <div class="w-9 h-9 bg-[#FBF4E4] rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-[18px] h-[18px] text-[#A07E28]" fill="none" stroke="currentColor" stroke-width="1.7"
                                 stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="8.5"/>
                                <path d="M12 7.5v4.5l3 1.75"/>
                            </svg>
                        </div>
                    </div>
                    <p class="font-serif text-2xl sm:text-3xl font-medium tracking-tight text-[#1F1E1D]"><?= $totalPending ?></p>
                    <p class="text-[11px] text-[#A8A29A] mt-1">À traiter</p>
                </div>

                <!-- Revenus -->
                <div class="stat-card group bg-white rounded-2xl border border-[#EAE6DD] p-4 sm:p-5
                            shadow-[0_1px_3px_rgba(31,30,29,0.04)]
                            hover:shadow-[0_4px_14px_rgba(201,100,66,0.10)] hover:border-[#E8D5CE]
                            transition-all duration-300">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-[10px] sm:text-[10.5px] font-semibold text-[#A8A29A] uppercase tracking-[0.14em]">Revenus</span>
                        <div class="w-9 h-9 bg-[#C96442] rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300 shadow-[0_1px_3px_rgba(201,100,66,0.35)]">
                            <svg class="w-[18px] h-[18px] text-white" fill="none" stroke="currentColor" stroke-width="1.7"
                                 stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                <path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                    <p class="font-serif text-xl sm:text-2xl font-medium tracking-tight text-[#1F1E1D]">
                        <?= number_format($totalRevenue, 0, ',', ' ') ?>
                        <span class="font-sans text-[11px] font-medium text-[#A8A29A] ml-1">FCFA</span>
                    </p>
                    <p class="text-[11px] text-[#A8A29A] mt-1">Total collecté</p>
                </div>
            </div>

            <!-- Tableau des commandes -->
            <div class="bg-white rounded-2xl border border-[#EAE6DD] overflow-hidden
                        shadow-[0_1px_3px_rgba(31,30,29,0.04)]"
                 style="animation: fadeUp 0.5s ease-out 0.25s backwards;">

                <div class="px-4 sm:px-6 py-4 border-b border-[#EAE6DD] flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <h3 class="font-serif text-[16px] sm:text-[17px] font-medium tracking-tight text-[#1F1E1D]">Commandes récentes</h3>
                        <p class="text-[12px] text-[#A8A29A] mt-0.5">Suivi en temps réel de toutes les commandes</p>
                    </div>
                    <a href="checkout.php"
                       class="inline-flex items-center justify-center gap-2 px-4 py-2
                              bg-[#C96442] text-white text-[12px] font-semibold rounded-xl
                              shadow-[0_1px_3px_rgba(201,100,66,0.35)]
                              hover:bg-[#B85636] hover:shadow-[0_4px_12px_rgba(201,100,66,0.30)]
                              active:scale-[0.97] transition-all duration-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2"
                             stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <path d="M12 4v16m8-8H4"/>
                        </svg>
                        Nouvelle commande
                    </a>
                </div>

                <!-- Desktop -->
                <div class="hidden md:block overflow-x-auto scrollbar-thin">
                    <table class="w-full text-sm">
                        <thead class="bg-[#FAF9F5]">
                            <tr class="text-left text-[#A8A29A] uppercase text-[10px] tracking-[0.12em] font-semibold">
                                <th class="px-6 py-3 border-b border-[#EAE6DD]">N° Commande</th>
                                <th class="px-6 py-3 border-b border-[#EAE6DD]">Client</th>
                                <th class="px-6 py-3 border-b border-[#EAE6DD]">Montant</th>
                                <th class="px-6 py-3 border-b border-[#EAE6DD]">Statut</th>
                                <th class="px-6 py-3 border-b border-[#EAE6DD]">Paiement</th>
                                <th class="px-6 py-3 border-b border-[#EAE6DD]">Date</th>
                                <th class="px-6 py-3 border-b border-[#EAE6DD] text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#F0ECE3]">
                        <?php if ($result && $result->num_rows > 0): ?>
                            <?php $rowIndex = 0; while ($row = $result->fetch_assoc()): $rowIndex++; ?>
                                <tr class="table-row hover:bg-[#FBF9F4] transition-colors duration-150" style="animation-delay: <?= $rowIndex * 0.03 ?>s;">
                                    <td class="px-6 py-3.5">
                                        <span class="font-mono text-[11px] bg-[#F3F0E8] text-[#6B665D] px-2 py-1 rounded-md ring-1 ring-[#EAE5D9]">
                                            <?= htmlspecialchars($row['order_number']) ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-3.5">
                                        <div class="flex items-center gap-2.5">
                                            <div class="w-7 h-7 rounded-lg bg-[#F3F0E8] flex items-center justify-center text-[11px] font-semibold text-[#A8A29A]">
                                                <?= strtoupper(mb_substr($row['client_name'] ?? '?', 0, 1)) ?>
                                            </div>
                                            <div>
                                                <p class="text-[13px] font-medium text-[#1F1E1D] leading-tight"><?= htmlspecialchars($row['client_name'] ?? '—') ?></p>
                                                <p class="text-[11px] text-[#A8A29A]"><?= htmlspecialchars($row['client_email'] ?? '') ?></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-3.5">
                                        <span class="text-[13px] font-semibold text-[#1F1E1D]"><?= number_format($row['amount'], 0, ',', ' ') ?></span>
                                        <span class="text-[10px] text-[#A8A29A] ml-0.5">FCFA</span>
                                    </td>
                                    <td class="px-6 py-3.5">
                                        <?php
                                        $statusConfig = [
                                            'PAID'      => ['bg-[#FBEFE9] text-[#A24C2F] border-[#EFD5C7]', 'Payée', '●'],
                                            'PENDING'   => ['bg-[#FBF4E4] text-[#8A6A1F] border-[#EDE1C4]', 'En attente', '◉'],
                                            'FAILED'    => ['bg-[#F7EDEA] text-[#8B3A2A] border-[#E8D5CE]', 'Échouée', '✕'],
                                            'CANCELLED' => ['bg-[#F3F0E8] text-[#8A8578] border-[#E4DFD4]', 'Annulée', '—'],
                                        ];
                                        $sc = $statusConfig[$row['status']] ?? ['bg-[#F3F0E8] text-[#6B665D] border-[#E4DFD4]', $row['status'], '?'];
                                        ?>
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-medium border <?= $sc[0] ?>">
                                            <span class="text-[8px]"><?= $sc[2] ?></span>
                                            <?= $sc[1] ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-3.5">
                                        <?php if ($row['transaction_id']): ?>
                                            <p class="font-mono text-[10px] text-[#6B665D] leading-tight"><?= htmlspecialchars($row['transaction_id']) ?></p>
                                            <p class="text-[10px] text-[#A8A29A] mt-0.5"><?= htmlspecialchars($row['provider']) ?></p>
                                        <?php else: ?>
                                            <span class="text-[11px] text-[#C9C3B8]">Aucun</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-3.5 text-[12px] text-[#6B665D] tabular-nums"><?= date('d/m/Y H:i', strtotime($row['created_at'])) ?></td>
                                    <td class="px-6 py-3.5 text-right">
                                        <?php if ($row['status'] === 'PENDING'): ?>
                                            <a href="simulate_payment.php?order_id=<?= $row['id'] ?>"
                                               class="inline-flex items-center gap-1 px-3 py-1.5
                                                      bg-[#C96442] text-white text-[11px] font-semibold rounded-lg
                                                      shadow-[0_1px_3px_rgba(201,100,66,0.30)]
                                                      hover:bg-[#B85636] hover:shadow-[0_3px_10px_rgba(201,100,66,0.30)]
                                                      active:scale-95 transition-all duration-200">
                                                Payer
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.2"
                                                     stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                                    <path d="M9 5l7 7-7 7"/>
                                                </svg>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-[#C9C3B8] text-[11px]">—</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="px-6 py-20 text-center">
                                    <div class="w-16 h-16 mx-auto mb-4 bg-[#FBEFE9] rounded-2xl flex items-center justify-center">
                                        <svg class="w-8 h-8 text-[#C96442]" fill="none" stroke="currentColor" stroke-width="1.6"
                                             stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                            <path d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                        </svg>
                                    </div>
                                    <p class="font-serif text-[16px] font-medium text-[#1F1E1D]">Aucune commande pour le moment</p>
                                    <p class="text-[13px] text-[#A8A29A] mt-1">Créez votre première commande pour commencer.</p>
                                    <a href="checkout.php"
                                       class="inline-flex mt-5 px-5 py-2.5 bg-[#C96442] text-white text-[13px] font-semibold rounded-xl
                                              shadow-[0_1px_3px_rgba(201,100,66,0.35)] hover:bg-[#B85636] hover:shadow-[0_4px_12px_rgba(201,100,66,0.30)] transition-all">
                                        Créer une commande
                                    </a>
                                </td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Mobile -->
                <div class="md:hidden divide-y divide-[#F0ECE3]">
                    <?php
                    if ($result && $result->num_rows > 0) {
                        $result->data_seek(0);
                        $mobileIndex = 0;
                        while ($row = $result->fetch_assoc()):
                            $mobileIndex++;
                            $sc = $statusConfig[$row['status']] ?? ['bg-[#F3F0E8] text-[#6B665D] border-[#E4DFD4]', $row['status'], '?'];
                    ?>
                        <div class="p-4 hover:bg-[#FBF9F4] transition-colors duration-150" style="animation: fadeUp 0.3s ease-out <?= $mobileIndex * 0.05 ?>s backwards;">
                            <div class="flex items-start justify-between mb-3">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-lg bg-[#F3F0E8] flex items-center justify-center text-[12px] font-semibold text-[#A8A29A]">
                                        <?= strtoupper(mb_substr($row['client_name'] ?? '?', 0, 1)) ?>
                                    </div>
                                    <div>
                                        <p class="text-[13px] font-medium text-[#1F1E1D]"><?= htmlspecialchars($row['client_name'] ?? '—') ?></p>
                                        <p class="font-mono text-[10px] text-[#A8A29A] mt-0.5"><?= htmlspecialchars($row['order_number']) ?></p>
                                    </div>
                                </div>
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-medium border <?= $sc[0] ?>">
                                    <span class="text-[7px]"><?= $sc[2] ?></span>
                                    <?= $sc[1] ?>
                                </span>
                            </div>
                            <div class="flex items-end justify-between">
                                <div>
                                    <p class="font-serif text-lg font-medium text-[#1F1E1D]">
                                        <?= number_format($row['amount'], 0, ',', ' ') ?>
                                        <span class="font-sans text-[11px] font-medium text-[#A8A29A]">FCFA</span>
                                    </p>
                                    <p class="text-[11px] text-[#A8A29A] mt-0.5 tabular-nums"><?= date('d/m/Y H:i', strtotime($row['created_at'])) ?></p>
                                </div>
                                <?php if ($row['status'] === 'PENDING'): ?>
                                    <a href="simulate_payment.php?order_id=<?= $row['id'] ?>"
                                       class="px-3.5 py-2 bg-[#C96442] text-white text-[11px] font-semibold rounded-xl
                                              shadow-[0_1px_3px_rgba(201,100,66,0.30)]
                                              hover:bg-[#B85636] hover:shadow-[0_4px_10px_rgba(201,100,66,0.30)]
                                              active:scale-95 transition-all">
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
                            <div class="w-14 h-14 mx-auto mb-3 bg-[#FBEFE9] rounded-2xl flex items-center justify-center">
                                <svg class="w-7 h-7 text-[#C96442]" fill="none" stroke="currentColor" stroke-width="1.6"
                                     stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                    <path d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                </svg>
                            </div>
                            <p class="font-serif font-medium text-[#1F1E1D]">Aucune commande</p>
                            <a href="checkout.php"
                               class="inline-flex mt-4 px-4 py-2 bg-[#C96442] text-white text-[12px] font-semibold rounded-xl
                                      shadow-[0_1px_3px_rgba(201,100,66,0.35)] hover:bg-[#B85636] transition-all">
                                Créer
                            </a>
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