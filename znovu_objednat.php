<?php
session_start();
require_once "conn.php";

if (!isset($_SESSION['user_id']) || !isset($_GET['objednavka_id'])) {
    header("Location: uzivatel.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$objednavka_id = intval($_GET['objednavka_id']);

// Ověření, že objednávka patří uživateli
$stmt = $db->prepare("SELECT Restaurace_id FROM Objednavka WHERE Id = ? AND zakaznik_id = ?");
$stmt->bind_param("ii", $objednavka_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    header("Location: uzivatel.php");
    exit();
}
$row = $result->fetch_assoc();
$restaurace_id = $row['Restaurace_id'];

// Načtení produktů a množství
$stmt2 = $db->prepare("SELECT Produkt_id, Mnozstvi FROM Polozky_Objednavky WHERE Objednavka_id = ?");
$stmt2->bind_param("i", $objednavka_id);
$stmt2->execute();
$res2 = $stmt2->get_result();

$polozky = [];
while ($r = $res2->fetch_assoc()) {
    $polozky[] = ['id' => $r['Produkt_id'], 'mnozstvi' => $r['Mnozstvi']];
}

// Uložení do session košíku
$_SESSION['kosik'] = [
    'restaurace_id' => $restaurace_id,
    'polozky' => $polozky
];

header("Location: kosik.php");
exit();
?>
