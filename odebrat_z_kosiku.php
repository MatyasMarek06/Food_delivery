<?php
session_start();

$index = $_POST['index'] ?? null;

if ($index !== null && isset($_SESSION['kosik']['polozky'][$index])) {
    unset($_SESSION['kosik']['polozky'][$index]);
    $_SESSION['kosik']['polozky'] = array_values($_SESSION['kosik']['polozky']); // Přeuspořádání indexů

    // Pokud je košík prázdný, vymažeme i ID restaurace
    if (empty($_SESSION['kosik']['polozky'])) {
        $_SESSION['kosik']['restaurace_id'] = null;
    }
}

header("Location: kosik.php");
exit;
?>
