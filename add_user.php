<?php
include "conn.php";
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // načtení hodnot z formuláře
    $Email = trim($_POST['Email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $Telefon = trim($_POST['Telefon'] ?? '');
    $Mesto = trim($_POST['Mesto'] ?? '');
    $PSC = trim($_POST['PSC'] ?? '');
    $Ulice = trim($_POST['Ulice'] ?? '');
    $Jmeno = trim($_POST['Jmeno'] ?? '');
    $Prijmeni = trim($_POST['Prijmeni'] ?? '');

    // kontrola jestli jsou všechna pole vyplněná
    if (empty($Email) || empty($password) || empty($Telefon) || empty($Mesto) || empty($Ulice) || empty($Jmeno) || empty($Prijmeni)) {
        echo "Všechna pole jsou povinná.";
    } else {
        // hashování hesla
        $pass_hash = password_hash($password, PASSWORD_DEFAULT);

        // vložení nového uživatele do databáze
        $stmt = $db->getConnection()->prepare("INSERT INTO Zakaznik (Jmeno, Prijmeni, Email, Heslo, Telefon, Mesto, PSC, Ulice) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssss", $Jmeno, $Prijmeni, $Email, $pass_hash, $Telefon, $Mesto, $PSC, $Ulice);

        if ($stmt->execute()) {
            // po úspěšném vložení načteme nového uživatele podle ID
            $novy_id = $db->getConnection()->insert_id;
            $stmt2 = $db->getConnection()->prepare("SELECT * FROM Zakaznik WHERE id = ?");
            $stmt2->bind_param("i", $novy_id);
            $stmt2->execute();
            $result = $stmt2->get_result();
            $novy_uzivatel = $result->fetch_assoc();
            $stmt2->close();

            // uložíme data uživatele do session
            $_SESSION["user_id"] = $novy_uzivatel["id"];
            $_SESSION["username"] = $novy_uzivatel["Jmeno"];
            $_SESSION["uzivatel"] = $novy_uzivatel;

            // přesměrování na hlavní stránku
            header("Location: index.php");
            exit();
        } else {
            echo "Chyba při registraci: " . $stmt->error;
        }

        $stmt->close();
    }
}
?>
