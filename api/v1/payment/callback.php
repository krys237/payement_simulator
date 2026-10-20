<?php
// ============================================================
// Endpoint API : Webhook de paiement simulé
// ============================================================

header('Content-Type: application/json; charset=utf-8');

header('Access-Control-Allow-Origin: *');

header('Access-Control-Allow-Methods: POST, OPTIONS');

header('Access-Control-Allow-Headers: Content-Type');

// Gérer la pré-requête OPTIONS (CORS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {

    http_response_code(200);

    exit;
}

// Connexion directe
$host   = 'localhost';

$user   = 'root';

$pass   = 'darel';

$dbname = 'payment_simulation';

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {

    http_response_code(500);

    echo json_encode(['success' => false, 'message' => 'Erreur de connexion : ' . $conn->connect_error]);

    exit;
}

$conn->set_charset('utf8mb4');

// Lecture du JSON
$input = file_get_contents('php://input');

$data  = json_decode($input, true);

// Debug : si JSON invalide
if (!$data) {

    http_response_code(400);

    echo json_encode([

        'success' => false,

        'message' => 'Données JSON invalides.',

        'raw_input' => $input
    ]);
    $conn->close();
    exit;
}

if (!isset($data['order_id'])) {

    http_response_code(400);

    echo json_encode(['success' => false, 'message' => 'order_id manquant.']);

    $conn->close();
    
    exit;
}

$order_id       = intval($data['order_id']);

$provider       = isset($data['provider']) ? trim($data['provider']) : 'MOBILE_MONEY';

$transaction_id = 'TXN-' . strtoupper(bin2hex(random_bytes(6))) . '-' . time();

// Transaction atomique
$conn->begin_transaction();

try {

    // Verrouiller la commande en lecture
    $stmt = $conn->prepare("SELECT id, status, amount, created_at FROM orders WHERE id = ? FOR UPDATE");

    $stmt->bind_param('i', $order_id);

    $stmt->execute();

    $res = $stmt->get_result();

    if ($res->num_rows === 0) {

        throw new Exception('Commande introuvable (ID: ' . $order_id . ').');
    }

    $order = $res->fetch_assoc();

    $stmt->close();

    // Vérifier le statut
    if ($order['status'] !== 'PENDING') {

        throw new Exception('Commande déjà traitée (statut : ' . $order['status'] . ').');
    }

    // Vérifier l'expiration (3 minutes)
    $createdTimestamp = strtotime($order['created_at']);

    $expiresTimestamp = $createdTimestamp + 180;

    $now              = time();

    if ($now > $expiresTimestamp) {
        // Marquer comme FAILED
        $failedStatus = 'FAILED';

        $stmtExpire = $conn->prepare("UPDATE orders SET status = ?, updated_at = NOW() WHERE id = ?");

        $stmtExpire->bind_param('si', $failedStatus, $order_id);

        $stmtExpire->execute();

        $stmtExpire->close();

        $conn->commit();

        http_response_code(400);

        echo json_encode([
            'success' => false,
            'message' => 'Le délai de 3 minutes est expiré. Commande marquée comme échouée.',
            'expired' => true
        ]);
        $conn->close();

        exit;
    }

    // Montant depuis la BDD (plus fiable que le client)
    $amount = floatval($order['amount']);

    // Mettre à jour la commande
    $newStatus = 'PAID';

    $stmt2 = $conn->prepare("UPDATE orders SET status = ?, updated_at = NOW() WHERE id = ?");

    $stmt2->bind_param('si', $newStatus, $order_id);

    if (!$stmt2->execute()) {

        throw new Exception('Erreur mise à jour commande : ' . $stmt2->error);
    }
    $stmt2->close();

    // Insérer le paiement
    $paymentStatus = 'SUCCESS';

    $stmt3 = $conn->prepare("INSERT INTO payments (transaction_id, order_id, amount, status, provider, created_at, paid_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())");

    $stmt3->bind_param('sidss', $transaction_id, $order_id, $amount, $paymentStatus, $provider);

    if (!$stmt3->execute()) {

        throw new Exception('Erreur insertion paiement : ' . $stmt3->error);
    }
    $stmt3->close();

    // Valider
    $conn->commit();

    echo json_encode([
        'success'        => true,
        'message'        => 'Paiement effectué avec succès.',
        'transaction_id' => $transaction_id,
        'order_id'       => $order_id,
        'amount'         => $amount,
        'status'         => 'PAID',
        'paid_at'        => date('Y-m-d H:i:s')
    ]);

} catch (Exception $e) {

    $conn->rollback();

    http_response_code(400);

    echo json_encode([

        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$conn->close();
?>