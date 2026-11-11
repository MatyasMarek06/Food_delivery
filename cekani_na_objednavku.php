<?php
session_start();

// pokud v session není poslední objednávka, ukončíme skript
if (!isset($_SESSION['posledni_objednavka'])) {
    echo "<p>Žádná aktivní objednávka.</p>";
    exit;
}

$obj = $_SESSION['posledni_objednavka'];
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Čekání na objednávku</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: linen;
            text-align: center;
            padding-top: 50px;
        }

        .box {
            background: white;
            max-width: 500px;
            margin: auto;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px lightgrey;
        }

        h2 {
            color: gray;
        }

        p {
            font-size: 18px;
            margin: 12px 0;
        }

        @media (max-width: 768px) {
            .box {
                width: 100% !important;
                padding: 10px;
                box-sizing: border-box;
            }

            h2 {
                font-size: 1.4em;
                margin-bottom: 10px;
            }

            p {
                font-size: 1.1em;
            }
        }
    </style>
</head>
<body>
<?php include "navbar.php"; ?>

<div class="box">
    <h2>Děkujeme za objednávku!</h2>

    <!-- vypís údajů o objednávce ze session -->
    <p><strong>Číslo objednávky:</strong> <?= $obj['cislo'] ?></p>
    <p><strong>Z toho doprava:</strong> <?= number_format($obj['doprava'], 2) ?> Kč</p>

    <?php if (!empty($obj['sleva'])): ?>
        <p><strong>Sleva uplatněna:</strong> <?= number_format($obj['sleva'], 2) ?> Kč</p>
    <?php endif; ?>

    <p><strong>Cena celkem:</strong> <?= number_format($obj['cena'], 2) ?> Kč</p>
    <p><strong>Způsob platby:</strong> <?= htmlspecialchars($obj['zpusob_platby']) ?></p>
    <p><strong>Platební metoda:</strong> <?= htmlspecialchars($obj['platebni_metoda']) ?></p>
    <p><strong>Doručovací
            adresa:</strong> <?= htmlspecialchars($obj['ulice'] . ', ' . $obj['psc'] . ' ' . $obj['mesto']) ?></p>

    <p style="margin-top: 30px; font-style: italic;">Vaše objednávka je zpracovávána...</p>
</div>
</body>
</html>
