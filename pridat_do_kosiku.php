<?php
session_start();

// z formuláře přijdou id produktu a restaurace
$produkt_id = $_POST['produkt_id'] ?? null;
$restaurace_id = $_POST['restaurace_id'] ?? null;

// kontrola jestli obě ID existují
if (!$produkt_id || !$restaurace_id) {
    echo json_encode(['stav' => 'chyba', 'zprava' => 'Chybí ID produktu nebo restaurace.']);
    exit;
}

// pokud už něco máme v košíku, načteme, jinak vytvoříme prázdný
$kosik = $_SESSION['kosik'] ?? ['polozky' => [], 'restaurace_id' => null];

// kontrola jestli je košík prázdný nebo je ze stejné restaurace
if ($kosik['restaurace_id'] === null || $kosik['restaurace_id'] == $restaurace_id) {
    $existuje = false;

    // pokud už produkt v košíku je, jen zvýšíme množství
    foreach ($kosik['polozky'] as &$polozka) {
        if ($polozka['id'] == $produkt_id) {
            $polozka['mnozstvi'] += 1;
            $existuje = true;
            break;
        }
    }
    unset($polozka);

    // pokud produkt v košíku ještě nebyl, přidáme ho
    if (!$existuje) {
        $kosik['polozky'][] = ['id' => $produkt_id, 'mnozstvi' => 1];
    }

    // nastavíme restauraci a uložíme zpět do session
    $kosik['restaurace_id'] = $restaurace_id;
    $_SESSION['kosik'] = $kosik;

    echo json_encode(['stav' => 'ok']);
} else {
    // pokud je košík z jiné restaurace, nedovolíme přidání
    echo json_encode(['stav' => 'chyba', 'zprava' => 'Košík již obsahuje produkty z jiné restaurace.']);
}
?>
