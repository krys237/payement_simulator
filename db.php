<?php
// ============================================================
// Connexion à la base de données MySQL (mysqli orienté objet)
// ============================================================

$host   = 'localhost';

$user   = 'root';

$pass   = 'darel';

$dbname = 'payment_simulation';

$conn = new mysqli($host, $user, $pass, $dbname);

// Vérification de la connexion
if ($conn->connect_error) {

    die('Erreur de connexion à la base de données : ' . $conn->connect_error);
}

// Encodage UTF-8
$conn->set_charset('utf8mb4');
?>