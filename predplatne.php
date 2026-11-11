<?php
session_start();
require_once "conn.php";


if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$uzivatel_id = $_SESSION['user_id'];

// pokud byl formulář odeslán, nastaví se předplatné a aktualizuje session
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $stmt = $db->prepare("UPDATE Zakaznik SET Predplatne = 1 WHERE id = ?");
    $stmt->bind_param("i", $uzivatel_id);
    $stmt->execute();
    $stmt->close();

    $stmt = $db->prepare("SELECT * FROM Zakaznik WHERE id = ?");
    $stmt->bind_param("i", $uzivatel_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $_SESSION['uzivatel'] = $result->fetch_assoc();
    $stmt->close();

    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <title>Předplatné Verze +</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        @media (max-width: 768px) {
            .nabidka {
                flex-direction: column;
                align-items: stretch;
                gap: 16px;
            }

            .plan {
                width: 100% !important;
                padding: 10px;
            }

            button {
                width: 100%;
            }
        }
    </style>
</head>
<body class="container mt-5">


<h2>Předplatné Verze +</h2>
<p>Za 100 Kč/měsíc získáte:</p>
<ul>
    <li>Neomezené doručování zdarma</li>
    <li>Slevy až 20 % ve vybraných restauracích</li>
</ul>

<form method="post">
    <button type="submit" class="btn btn-warning">
        <span class="material-symbols-outlined" style="vertical-align: middle; font-size: 20px;">paid</span>
        Zaplatit a aktivovat
    </button>
</form>
</body>
</html>
