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

    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600&family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', system-ui, -apple-system, sans-serif; }
        .font-serif { font-family: 'Fraunces', ui-serif, Georgia, serif; }
        .main-content { transition: margin-left 0.3s ease-in-out; }
        html.sidebar-collapsed #mainContent { margin-left: 0 !important; }
        @keyframes fadeUp { from { opacity: 0; transform: translateY(16px); } to { opacity: 1; transform: translateY(0); } }
        .animate-up { animation: fadeUp 0.5s ease-out; }
    </style>
</head>
<body class="bg-[#FAF9F5] text-[#1F1E1D] min-h-screen antialiased">

    <?php include 'includes/sidebar.php'; ?>

    <div id="mainContent" class="main-content lg:ml-[272px] min-h-screen flex flex-col">

        <?php include 'includes/header.php'; ?>

        <main class="flex-1 px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
            <div class="max-w-xl mx-auto animate-up">

                <!-- En-tête -->
                <div class="mb-8 text-center">
                    <div class="w-14 h-14 mx-auto mb-4 bg-[#FBEFE9] rounded-2xl flex items-center justify-center
                                ring-1 ring-[#EFD5C7] shadow-[0_1px_3px_rgba(201,100,66,0.10)]">
                        <svg class="w-7 h-7 text-[#C96442]" fill="none" stroke="currentColor" stroke-width="1.7"
                             stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="8.5"/>
                            <path d="M12 8.5v7M8.5 12h7"/>
                        </svg>
                    </div>
                    <h2 class="font-serif text-[24px] sm:text-[28px] font-medium tracking-tight text-[#1F1E1D] mb-1.5">
                        Créer une commande
                    </h2>
                    <p class="text-[13px] text-[#8A8578]">Renseignez les informations du client et le montant.</p>
                </div>

                <?php if ($message): ?>
                    <div class="mb-6 p-4 rounded-xl text-[13px] flex items-start gap-3
                                <?= $messageType === 'error'
                                    ? 'bg-[#F7EDEA] text-[#8B3A2A] border border-[#E8D5CE]'
                                    : 'bg-[#EEF1E5] text-[#4F6038] border border-[#DDE4CC]' ?>">
                        <span class="text-base leading-none mt-0.5">
                            <?= $messageType === 'error' ? '⚠️' : '✓' ?>
                        </span>
                        <span><?= htmlspecialchars($message) ?></span>
                    </div>
                <?php endif; ?>

                <!-- Formulaire -->
                <div class="bg-white rounded-2xl border border-[#EAE6DD] p-6 sm:p-8
                            shadow-[0_1px_3px_rgba(31,30,29,0.04)]">
                    <form method="POST" class="space-y-5">

                        <div>
                            <label class="block text-[10.5px] font-semibold text-[#A8A29A] uppercase tracking-[0.14em] mb-2">
                                Nom complet
                            </label>
                            <input type="text" name="name" required
                                   value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
                                   class="w-full px-4 py-3 bg-[#FBF9F4] border border-[#EAE6DD] rounded-xl text-[14px] text-[#1F1E1D]
                                          focus:outline-none focus:ring-2 focus:ring-[#C96442]/20 focus:border-[#C96442]/50 focus:bg-white
                                          placeholder:text-[#C9C3B8] transition-all duration-200"
                                   placeholder="Jean Dupont">
                        </div>

                        <div>
                            <label class="block text-[10.5px] font-semibold text-[#A8A29A] uppercase tracking-[0.14em] mb-2">
                                Adresse e-mail
                            </label>

                            <input type="email" name="email" required
                            
                                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                                   class="w-full px-4 py-3 bg-[#FBF9F4] border border-[#EAE6DD] rounded-xl text-[14px] text-[#1F1E1D]
                                          focus:outline-none focus:ring-2 focus:ring-[#C96442]/20 focus:border-[#C96442]/50 focus:bg-white
                                          placeholder:text-[#C9C3B8] transition-all duration-200"
                                   placeholder="jean@exemple.com">
                        </div>

                        <div>
                            <label class="block text-[10.5px] font-semibold text-[#A8A29A] uppercase tracking-[0.14em] mb-2">
                                Montant (FCFA)
                            </label>
                            <div class="relative">
                                <input type="number" name="amount" step="1" min="100" required
                                       value="<?= htmlspecialchars($_POST['amount'] ?? '') ?>"
                                       class="w-full px-4 py-3 pr-16 bg-[#FBF9F4] border border-[#EAE6DD] rounded-xl text-[14px] text-[#1F1E1D]
                                              focus:outline-none focus:ring-2 focus:ring-[#C96442]/20 focus:border-[#C96442]/50 focus:bg-white
                                              placeholder:text-[#C9C3B8] transition-all duration-200"
                                       placeholder="10 000">
                                <span class="absolute right-4 top-1/2 -translate-y-1/2 text-[11px] font-semibold text-[#A8A29A] pointer-events-none">
                                    FCFA
                                </span>
                            </div>
                        </div>

                        <div class="pt-3">
                            <button type="submit"
                                    class="group w-full inline-flex items-center justify-center gap-2 px-6 py-3.5
                                           bg-[#C96442] text-white text-[14px] font-semibold rounded-xl
                                           shadow-[0_1px_3px_rgba(201,100,66,0.35)]
                                           hover:bg-[#B85636] hover:shadow-[0_6px_16px_rgba(201,100,66,0.30)]
                                           active:scale-[0.98] transition-all duration-200">
                                Créer et procéder au paiement
                                <svg class="w-4 h-4 transition-transform duration-200 group-hover:translate-x-0.5"
                                     fill="none" stroke="currentColor" stroke-width="2.2"
                                     stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                    <path d="M9 5l7 7-7 7"/>
                                </svg>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Note -->
                <div class="mt-6 bg-[#FBF4E4] border border-[#EDE1C4] rounded-2xl p-4 flex gap-3">
                    <div class="w-8 h-8 bg-[#F5E9CC] rounded-xl flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4 text-[#A07E28]" fill="none" stroke="currentColor" stroke-width="1.7"
                             stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <path d="M12 9v2.5M12 15h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-[12px] font-semibold text-[#8A6A1F]">Délai de paiement : 3 minutes</p>
                        <p class="text-[11.5px] text-[#8A6A1F]/80 mt-1 leading-relaxed">
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