<?php
// ============================================================
// Page : Création d'une commande et d'un client
// ============================================================

require 'db.php';

$currentPage = 'checkout';

$pageTitle   = 'Nouvelle commande';

$message     = '';

$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name   = trim($_POST['name']   ?? '');

    $email  = trim($_POST['email']  ?? '');

    $amount = floatval($_POST['amount'] ?? 0);


    if ($name === '' || $email === '' || $amount <= 0) {

        $message     = 'Veuillez remplir tous les champs correctement.';

        $messageType = 'error';
    } else {
        $stmt = $conn->prepare("SELECT id FROM clients WHERE email = ? LIMIT 1");

        $stmt->bind_param('s', $email);

        $stmt->execute();

        $res = $stmt->get_result();

        if ($res->num_rows > 0) {

            $clientRow = $res->fetch_assoc();

            $client_id = $clientRow['id'];
        } else {
            $stmt2 = $conn->prepare("INSERT INTO clients (name, email) VALUES (?, ?)");

            $stmt2->bind_param('ss', $name, $email);

            $stmt2->execute();

            $client_id = $conn->insert_id;

            $stmt2->close();
        }
        $stmt->close();

        $order_number = 'CMD-' . strtoupper(bin2hex(random_bytes(4))) . '-' . time();

        $status       = 'PENDING';

        $stmt3 = $conn->prepare("INSERT INTO orders (order_number, client_id, amount, status, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())");
        
        $stmt3->bind_param('sids', $order_number, $client_id, $amount, $status);

        if ($stmt3->execute()) {

            $newOrderId = $conn->insert_id;

            $stmt3->close();

            header("Location: simulate_payment.php?order_id=" . $newOrderId);

            exit;
        } else {
            $message     = 'Erreur lors de la création de la commande.';

            $messageType = 'error';

            $stmt3->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouvelle commande — PaySimu</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', system-ui, -apple-system, sans-serif; }
        .main-content { transition: margin-left 0.3s ease-in-out; }
        @keyframes fadeUp { from { opacity: 0; transform: translateY(16px); } to { opacity: 1; transform: translateY(0); } }
        .animate-up { animation: fadeUp 0.5s ease-out; }
    </style>
</head>
<body class="bg-[#FAFAF9] text-stone-800 min-h-screen">

    <?php include 'includes/sidebar.php'; ?>

    <div id="mainContent" class="main-content lg:ml-[280px] min-h-screen flex flex-col">

        <?php include 'includes/header.php'; ?>

        <main class="flex-1 px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
            <div class="max-w-xl mx-auto animate-up">

                <!-- En-tête -->
                <div class="mb-8 text-center">
                    <div class="w-14 h-14 mx-auto mb-4 bg-gradient-to-br from-[#D97757]/15 to-[#D97757]/5 rounded-2xl flex items-center justify-center">
                        <svg class="w-7 h-7 text-[#D97757]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h2 class="text-2xl sm:text-3xl font-bold tracking-tight mb-2">Créer une commande</h2>
                    <p class="text-stone-500 text-sm">Renseignez les informations du client et le montant.</p>
                </div>

                <?php if ($message): ?>
                    <div class="mb-6 p-4 rounded-xl text-sm flex items-start gap-3
                                <?= $messageType === 'error'
                                    ? 'bg-red-50 text-red-800 border border-red-200'
                                    : 'bg-green-50 text-green-800 border border-green-200' ?>">
                        <span class="text-lg"><?= $messageType === 'error' ? '⚠️' : '✅' ?></span>
                        <span><?= htmlspecialchars($message) ?></span>
                    </div>
                <?php endif; ?>

                <!-- Formulaire -->
                <div class="bg-white rounded-2xl shadow-sm border border-stone-200/60 p-6 sm:p-8">
                    <form method="POST" class="space-y-5">

                        <div>
                            <label class="block text-[12px] font-semibold text-stone-500 uppercase tracking-wider mb-2">Nom complet</label>
                            <input type="text" name="name" required
                                   value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
                                   class="w-full px-4 py-3 bg-stone-50/80 border border-stone-200 rounded-xl text-[14px]
                                          focus:outline-none focus:ring-2 focus:ring-[#D97757]/30 focus:border-[#D97757]/50 focus:bg-white
                                          placeholder:text-stone-300 transition-all duration-200"
                                   placeholder="Jean Dupont">
                        </div>

                        <div>
                            <label class="block text-[12px] font-semibold text-stone-500 uppercase tracking-wider mb-2">Adresse e-mail</label>
                            <input type="email" name="email" required
                                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                                   class="w-full px-4 py-3 bg-stone-50/80 border border-stone-200 rounded-xl text-[14px]
                                          focus:outline-none focus:ring-2 focus:ring-[#D97757]/30 focus:border-[#D97757]/50 focus:bg-white
                                          placeholder:text-stone-300 transition-all duration-200"
                                   placeholder="jean@exemple.com">
                        </div>

                        <div>
                            <label class="block text-[12px] font-semibold text-stone-500 uppercase tracking-wider mb-2">Montant (FCFA)</label>
                            <div class="relative">
                                <input type="number" name="amount" step="1" min="100" required
                                       value="<?= htmlspecialchars($_POST['amount'] ?? '') ?>"
                                       class="w-full px-4 py-3 pr-16 bg-stone-50/80 border border-stone-200 rounded-xl text-[14px]
                                              focus:outline-none focus:ring-2 focus:ring-[#D97757]/30 focus:border-[#D97757]/50 focus:bg-white
                                              placeholder:text-stone-300 transition-all duration-200"
                                       placeholder="10 000">
                                <span class="absolute right-4 top-1/2 -translate-y-1/2 text-[12px] font-semibold text-stone-400">FCFA</span>
                            </div>
                        </div>

                        <div class="pt-3">
                            <button type="submit"
                                    class="w-full px-6 py-3.5 bg-gradient-to-r from-[#D97757] to-[#C2553A] text-white font-semibold rounded-xl
                                           hover:shadow-lg hover:shadow-[#D97757]/25 active:scale-[0.98] transition-all duration-200 text-[14px]">
                                Créer et procéder au paiement →
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Note -->
                <div class="mt-6 bg-amber-50/60 border border-amber-200/60 rounded-2xl p-4 flex gap-3">
                    <div class="w-8 h-8 bg-amber-100 rounded-xl flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-[12px] font-bold text-amber-800">Délai de paiement : 3 minutes</p>
                        <p class="text-[11px] text-amber-700/80 mt-1 leading-relaxed">
                            Après la création, vous disposerez de 3 minutes pour effectuer le paiement. Passé ce délai, la commande sera automatiquement marquée comme échouée.
                        </p>
                    </div>
                </div>

            </div>
        </main>
    </div>

    <script src="assets/js/main.js"></script>
    <?php $conn->close(); ?>
</body>
</html>