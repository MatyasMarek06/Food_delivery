<?php
session_start();
include "conn.php";

// získání údajů z formuláře a session
$zakaznik_id = $_SESSION['user_id'] ?? null;
$restaurace_id = $_POST['restaurace_id'] ?? null;
$zpusob_platby = $_POST['zpusob_platby'] ?? '';
$platebni_metoda = '';
$mesto = $_POST['mesto'] ?? '';
$psc = $_POST['psc'] ?? '';
$ulice = $_POST['ulice'] ?? '';
$poznamka = isset($_POST['poznamka']) && trim($_POST['poznamka']) !== '' ? $_POST['poznamka'] : null;

$kosik = $_SESSION['kosik'] ?? ['polozky' => []];
$polozky = $kosik['polozky'];

$jmeno_karty = $_POST['jmeno_karty'] ?? null;
$cislo_karty = $_POST['cislo_karty'] ?? null;
$expirace = $_POST['expirace'] ?? null;
$cvc = $_POST['cvc'] ?? null;

// košík = prázdný, konec skript
if (empty($polozky)) {
    die("Košík je prázdný.");
}

// určení platební metody podle typu platby
if ($zpusob_platby === 'online') {
    $platebni_metoda = 'Karta';
} elseif ($zpusob_platby === 'kuryr') {
    $platebni_metoda = $_POST['platebni_metoda'] ?? '';
    $jmeno_karty = $cislo_karty = $expirace = $cvc = null;
    if (!in_array($platebni_metoda, ['kartou', 'hotově'])) {
        die("Neplatná platební metoda u kurýra.");
    }
} else {
    die("Neplatný způsob platby.");
}

// výpočet celkové ceny objednávky včetně případné slevy a dopravy
$produktIds = array_column($polozky, 'id');
$placeholders = implode(',', array_fill(0, count($produktIds), '?'));
$typy = str_repeat('i', count($produktIds));

// získání cen produktů z db
$stmt = $db->prepare("SELECT id, Cena FROM Produkt WHERE id IN ($placeholders)");
$stmt->bind_param($typy, ...$produktIds);
$stmt->execute();
$result = $stmt->get_result();
$ceny = [];
while ($row = $result->fetch_assoc()) {
    $ceny[$row['id']] = $row['Cena'];
}

$puvodniCena = 0;
// uložení každé položky objednávky
foreach ($polozky as $polozka) {
    $id = $polozka['id'];
    $mnozstvi = $polozka['mnozstvi'];
    $puvodniCena += $ceny[$id] * $mnozstvi;
}

$doprava = 0;
$sleva_procent = 0;
if (!empty($restaurace_id)) {
    $stmt = $db->prepare("SELECT Doprava, Sleva_predplatne FROM Restaurace WHERE id = ?");
    $stmt->bind_param("i", $restaurace_id);
    $stmt->execute();
    $stmt->bind_result($doprava, $sleva_procent);
    $stmt->fetch();
    $stmt->close();
}

$sleva_kc = 0;

if (isset($_SESSION['uzivatel']) && $_SESSION['uzivatel']['Predplatne'] == 1) {
    if ($sleva_procent > 0) {
        $sleva_kc = round($puvodniCena * ($sleva_procent / 100));
    }
    $doprava = 0;
}

$celkovaCena = $puvodniCena - $sleva_kc + $doprava;

// Vytvoření čísla objednávky
$cislo_objednavky = rand(1000, 9999);

// Pokud je poznámka prázdná = NULL
$poznamka_clean = isset($_POST['poznamka']) && trim($_POST['poznamka']) !== '' ? $_POST['poznamka'] : null;

// uložení nové objednávky do db
$stmt = $db->prepare("INSERT INTO Objednavka 
    (Cislo_objednavky, Cena, Platebni_metoda, Zpusob_platby, Sleva, Popis, Restaurace_id, zakaznik_id, jmeno_karty, cislo_karty, expirace, cvc) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

$stmt->bind_param("idssdsisssss", $cislo_objednavky, $celkovaCena, $platebni_metoda, $zpusob_platby,
    $sleva_kc, $poznamka_clean, $restaurace_id, $zakaznik_id, $jmeno_karty, $cislo_karty, $expirace, $cvc);

$stmt->execute();
$nova_objednavka_id = $stmt->insert_id;

// Uložení položek objednávky
foreach ($polozky as $polozka) {
    $produkt_id = $polozka['id'];
    $mnozstvi = $polozka['mnozstvi'];
    $stmt = $db->prepare("INSERT INTO Polozky_Objednavky 
        (Mnozstvi, Cena, Produkt_id, Objednavka_id, zakaznik_id) 
        VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("idiii", $mnozstvi, $celkovaCena, $produkt_id, $nova_objednavka_id, $zakaznik_id);
    $stmt->execute();
}

// Vymazání košíku

unset($_SESSION['kosik']);


// uložení dat do session pro stránku s potvrzením
$_SESSION['posledni_objednavka'] = [
    'cislo' => $cislo_objednavky,
    'cena' => $celkovaCena,
    'zpusob_platby' => $zpusob_platby,
    'platebni_metoda' => $platebni_metoda,
    'mesto' => $mesto,
    'psc' => $psc,
    'ulice' => $ulice,
    'doprava' => $doprava,
    'sleva' => $sleva_kc
];

header("Location: cekani_na_objednavku.php");
exit;
?>
