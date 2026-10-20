<?php
// ============================================================
// Page : Simulateur de paiement Mobile Money
// ============================================================

require 'db.php';

$currentPage = 'simulate';

$pageTitle   = 'Simuler un paiement';

$order       = null;

$order_id    = intval($_GET['order_id'] ?? 0);

if ($order_id > 0) {
    $stmt = $conn->prepare("
        SELECT o.id, o.order_number, o.amount, o.status, o.created_at,
               c.name AS client_name, c.email AS client_email
        FROM orders o LEFT JOIN clients c ON o.client_id = c.id
        WHERE o.id = ? LIMIT 1
    ");
    $stmt->bind_param('i', $order_id);

    $stmt->execute();

    $order = $stmt->get_result()->fetch_assoc();

    $stmt->close();

    if ($order && $order['status'] === 'PENDING') {

        $createdTimestamp = strtotime($order['created_at']);

        $expiresTimestamp = $createdTimestamp + 180;

        if (time() > $expiresTimestamp) {

            $failedStatus = 'FAILED';

            $stmtFail = $conn->prepare("UPDATE orders SET status = ?, updated_at = NOW() WHERE id = ?");

            $stmtFail->bind_param('si', $failedStatus, $order_id);

            $stmtFail->execute();

            $stmtFail->close();

            $order['status'] = 'FAILED';
        }
    }
}

$pendingOrders = $conn->query("
    SELECT o.id, o.order_number, o.amount, o.created_at, c.name AS client_name
    FROM orders o LEFT JOIN clients c ON o.client_id = c.id
    WHERE o.status = 'PENDING' AND o.created_at >= (NOW() - INTERVAL 3 MINUTE)
    ORDER BY o.created_at DESC
");

$remainingSeconds = 0;
if ($order && $order['status'] === 'PENDING') {

    $createdTimestamp  = strtotime($order['created_at']);

    $expiresTimestamp  = $createdTimestamp + 180;

    $remainingSeconds  = $expiresTimestamp - time();
    
    if ($remainingSeconds < 0) $remainingSeconds = 0;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simuler un paiement — PaySimu</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', system-ui, -apple-system, sans-serif; }
        .main-content { transition: margin-left 0.3s ease-in-out; }
        @keyframes fadeUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes pulse-ring { 0% { transform: scale(0.9); opacity: 1; } 100% { transform: scale(1.5); opacity: 0; } }
        .animate-up { animation: fadeUp 0.5s ease-out; }
        .pulse-ring { animation: pulse-ring 1.5s ease-out infinite; }
    </style>
</head>
<body class="bg-[#FAFAF9] text-stone-800 min-h-screen">

    <?php include 'includes/sidebar.php'; ?>

    <div id="mainContent" class="main-content lg:ml-[280px] min-h-screen flex flex-col">

        <?php include 'includes/header.php'; ?>

        <main class="flex-1 px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
            <div class="max-w-lg mx-auto animate-up">

                <?php if (!$order): ?>

                    <!-- Sélecteur de commande -->
                    <div class="text-center mb-8">
                        <div class="w-14 h-14 mx-auto mb-4 bg-gradient-to-br from-[#D97757]/15 to-[#D97757]/5 rounded-2xl flex items-center justify-center">
                            <svg class="w-7 h-7 text-[#D97757]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                            </svg>
                        </div>
                        <h2 class="text-2xl sm:text-3xl font-bold tracking-tight mb-2">Simuler un paiement</h2>
                        <p class="text-stone-500 text-sm">Sélectionnez une commande en attente.</p>
                    </div>

                    <div class="bg-white rounded-2xl shadow-sm border border-stone-200/60 p-5 sm:p-6">
                        <?php if ($pendingOrders && $pendingOrders->num_rows > 0): ?>
                            <div class="space-y-2.5">
                                <?php while ($po = $pendingOrders->fetch_assoc()):
                                    $poRemaining = (strtotime($po['created_at']) + 180) - time();
                                    $poMin = floor($poRemaining / 60);
                                    $poSec = $poRemaining % 60;
                                ?>
                                    <a href="simulate_payment.php?order_id=<?= $po['id'] ?>"
                                       class="flex items-center justify-between p-4 bg-stone-50/80 rounded-xl
                                              hover:bg-[#D97757]/5 border border-stone-200/60 hover:border-[#D97757]/20
                                              transition-all duration-200 group">
                                        <div class="flex items-center gap-3">
                                            <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-stone-200 to-stone-100 flex items-center justify-center text-[12px] font-bold text-stone-500 group-hover:from-[#D97757]/20 group-hover:to-[#D97757]/10 group-hover:text-[#D97757] transition-all">
                                                <?= strtoupper(mb_substr($po['client_name'] ?? '?', 0, 1)) ?>
                                            </div>
                                            <div>
                                                <p class="text-[13px] font-semibold text-stone-700 group-hover:text-[#D97757] transition-colors"><?= htmlspecialchars($po['client_name']) ?></p>
                                                <p class="font-mono text-[10px] text-stone-400"><?= htmlspecialchars($po['order_number']) ?></p>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-[14px] font-bold text-stone-800"><?= number_format($po['amount'], 0, ',', ' ') ?> <span class="text-[10px] text-stone-400">FCFA</span></p>
                                            <p class="text-[10px] text-amber-600 mt-0.5 font-medium"><?= $poMin ?>:<?= str_pad($poSec, 2, '0', STR_PAD_LEFT) ?> restantes</p>
                                        </div>
                                    </a>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-10">
                                <div class="w-16 h-16 mx-auto mb-4 bg-green-50 rounded-2xl flex items-center justify-center">
                                    <svg class="w-8 h-8 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <p class="font-semibold text-stone-600">Aucune commande en attente</p>
                                <p class="text-[12px] text-stone-400 mt-1">Toutes les commandes ont été traitées.</p>
                                <a href="checkout.php" class="inline-flex mt-5 px-5 py-2.5 bg-gradient-to-r from-[#D97757] to-[#C2553A] text-white text-[12px] font-semibold rounded-xl hover:shadow-lg transition-all">
                                    Créer une commande
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>

                <?php else: ?>

                    <!-- Interface de paiement -->
                    <div class="bg-white rounded-2xl shadow-sm border border-stone-200/60 overflow-hidden">

                        <!-- Header gradient -->
                        <div class="bg-gradient-to-br from-[#D97757] via-[#C86A4A] to-[#B85A3D] px-6 sm:px-8 py-7 text-white relative overflow-hidden">
                            <!-- Motif décoratif -->
                            <div class="absolute top-0 right-0 w-40 h-40 bg-white/5 rounded-full -translate-y-1/2 translate-x-1/2"></div>
                            <div class="absolute bottom-0 left-0 w-24 h-24 bg-white/5 rounded-full translate-y-1/2 -translate-x-1/2"></div>

                            <div class="relative">
                                <div class="flex items-center gap-3 mb-5">
                                    <div class="w-11 h-11 bg-white/15 rounded-xl flex items-center justify-center backdrop-blur-sm border border-white/10">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <h2 class="text-[17px] font-bold">Mobile Money</h2>
                                        <p class="text-white/70 text-[12px]">Passerelle de paiement sécurisée</p>
                                    </div>
                                </div>

                                <?php if ($order['status'] === 'PENDING' && $remainingSeconds > 0): ?>
                                    <div class="bg-white/10 backdrop-blur-sm rounded-xl px-4 py-3.5 flex items-center gap-4 border border-white/10">
                                        <!-- Cercle animé du timer -->
                                        <div class="relative w-14 h-14 shrink-0">
                                            <svg viewBox="0 0 36 36" class="w-full h-full transform -rotate-90">
                                                <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                                                      fill="none" stroke="rgba(255,255,255,0.15)" stroke-width="2.5"/>
                                                <path id="countdownCircle"
                                                      d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                                                      fill="none" stroke="white" stroke-width="2.5"
                                                      stroke-dasharray="100, 100"
                                                      stroke-linecap="round"
                                                      style="transition: stroke-dasharray 0.5s ease;"/>
                                            </svg>
                                            <div class="absolute inset-0 flex items-center justify-center">
                                                <svg class="w-5 h-5 text-white/80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3"/>
                                                </svg>
                                            </div>
                                        </div>
                                        <div class="flex-1">
                                            <p class="text-[11px] text-white/60 font-medium uppercase tracking-wider">Temps restant</p>
                                            <p id="countdown" class="text-3xl font-extrabold font-mono tracking-wider" data-remaining="<?= $remainingSeconds ?>">--:--</p>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Détails -->
                        <div class="px-6 sm:px-8 py-6">

                            <div class="space-y-3.5 pb-6">
                                <div class="flex justify-between items-center py-2.5 px-3 bg-stone-50/80 rounded-xl">
                                    <span class="text-[12px] text-stone-400 font-medium">N° commande</span>
                                    <span class="font-mono text-[11px] bg-white px-2.5 py-1 rounded-lg border border-stone-200/80 text-stone-600"><?= htmlspecialchars($order['order_number']) ?></span>
                                </div>
                                <div class="flex justify-between items-center py-2.5 px-3 bg-stone-50/80 rounded-xl">
                                    <span class="text-[12px] text-stone-400 font-medium">Client</span>
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 rounded-md bg-gradient-to-br from-[#D97757]/20 to-[#D97757]/10 flex items-center justify-center text-[10px] font-bold text-[#D97757]">
                                            <?= strtoupper(mb_substr($order['client_name'] ?? '?', 0, 1)) ?>
                                        </div>
                                        <span class="text-[13px] font-semibold text-stone-700"><?= htmlspecialchars($order['client_name']) ?></span>
                                    </div>
                                </div>
                                <div class="flex justify-between items-center py-2.5 px-3 bg-stone-50/80 rounded-xl">
                                    <span class="text-[12px] text-stone-400 font-medium">E-mail</span>
                                    <span class="text-[12px] text-stone-600"><?= htmlspecialchars($order['client_email']) ?></span>
                                </div>
                            </div>

                            <!-- Montant -->
                            <div class="py-7 border-y border-stone-100 text-center">
                                <p class="text-[11px] text-stone-400 font-medium uppercase tracking-wider mb-2">Montant à payer</p>
                                <div class="flex items-baseline justify-center gap-1">
                                    <p class="text-5xl font-extrabold text-[#D97757] tracking-tight"><?= number_format($order['amount'], 0, ',', ' ') ?></p>
                                    <p class="text-[14px] font-semibold text-[#D97757]/60">FCFA</p>
                                </div>
                            </div>

                            <!-- Action -->
                            <div class="pt-6">
                                <?php if ($order['status'] === 'PENDING' && $remainingSeconds > 0): ?>

                                    <button id="payBtn"
                                            data-order-id="<?= $order['id'] ?>"
                                            data-amount="<?= $order['amount'] ?>"
                                            data-order-number="<?= htmlspecialchars($order['order_number']) ?>"
                                            class="w-full px-6 py-4 bg-gradient-to-r from-[#D97757] to-[#C2553A] text-white font-bold rounded-xl
                                                   hover:shadow-xl hover:shadow-[#D97757]/25 active:scale-[0.98] transition-all duration-200
                                                   disabled:opacity-50 disabled:cursor-not-allowed text-[15px] relative overflow-hidden group">
                                        <span class="relative z-10 flex items-center justify-center gap-2">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                            </svg>
                                            Payer maintenant
                                        </span>
                                        <div class="absolute inset-0 bg-white/10 translate-x-[-100%] group-hover:translate-x-[100%] transition-transform duration-700 skew-x-12"></div>
                                    </button>

                                    <div class="flex items-center justify-center gap-2 mt-4">
                                        <svg class="w-3.5 h-3.5 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                        </svg>
                                        <p class="text-[11px] text-stone-400">Paiement simulé et sécurisé</p>
                                    </div>

                                <?php elseif ($order['status'] === 'PAID'): ?>
                                    <div class="bg-green-50 border border-green-200/80 rounded-2xl p-6 text-center">
                                        <div class="w-14 h-14 mx-auto mb-3 bg-green-100 rounded-2xl flex items-center justify-center">
                                            <svg class="w-7 h-7 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                        </div>
                                        <p class="font-bold text-green-800 text-[15px]">Paiement effectué</p>
                                        <p class="text-[12px] text-green-600 mt-1">Cette commande a été payée avec succès.</p>
                                    </div>

                                <?php else: ?>
                                    <div class="bg-red-50 border border-red-200/80 rounded-2xl p-6 text-center">
                                        <div class="w-14 h-14 mx-auto mb-3 bg-red-100 rounded-2xl flex items-center justify-center">
                                            <svg class="w-7 h-7 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                        </div>
                                        <p class="font-bold text-red-800 text-[15px]">Paiement expiré</p>
                                        <p class="text-[12px] text-red-600 mt-1">Le délai de 3 minutes est dépassé.</p>
                                        <a href="checkout.php" class="inline-flex mt-4 px-5 py-2.5 bg-gradient-to-r from-[#D97757] to-[#C2553A] text-white text-[12px] font-semibold rounded-xl hover:shadow-lg transition-all">
                                            Nouvelle commande
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div id="feedback" class="mt-4 hidden"></div>

                            <div class="mt-6 text-center">
                                <a href="index.php" class="inline-flex items-center gap-1.5 text-[12px] text-stone-400 hover:text-[#D97757] transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                                    </svg>
                                    Retour au tableau de bord
                                </a>
                            </div>
                        </div>
                    </div>

                <?php endif; ?>

            </div>
        </main>
    </div>

    <script src="assets/js/main.js"></script>
    <?php $conn->close(); ?>
</body>
</html>