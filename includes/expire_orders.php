<?php
// ============================================================
// Marquer comme FAILED toutes les commandes PENDING
// créées il y a plus de 3 minutes.
// À inclure UNIQUEMENT dans les pages d'affichage (dashboard, stats)
// ============================================================

$conn->query("
    UPDATE orders
    SET status = 'FAILED', updated_at = NOW()
    WHERE status = 'PENDING'
      AND created_at < (NOW() - INTERVAL 3 MINUTE)
");

// Également marquer comme FAILED les éventuels paiements PENDING associés
$conn->query("
    UPDATE payments p
    INNER JOIN orders o ON p.order_id = o.id
    SET p.status = 'FAILED'
    WHERE p.status = 'PENDING' AND o.status = 'FAILED'
");
?>