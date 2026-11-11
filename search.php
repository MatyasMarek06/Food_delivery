<?php
include "conn.php";
include "navbar.php";
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// načtení a očištění vyhledávacího dotazu
$search_raw = strtolower(trim($_GET['search']));
$search_pattern = "%" . $search_raw . "%";

// získání města přihlášeného uživatele (pokud je přihlášen)
$uzivatel_mesto = null;
if (isset($_SESSION['user_id'])) {
    $stmt = $db->prepare("SELECT Mesto FROM Zakaznik WHERE Id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $stmt->bind_result($uzivatel_mesto);
    $stmt->fetch();
    $stmt->close();
}

// hledání restaurací podle názvu, kuchyně nebo města
$sql = "
SELECT Restaurace.*, Obrazek.Cesta
FROM Restaurace
JOIN Obrazek ON Restaurace.id = Obrazek.Restaurace_id
WHERE LOWER(Restaurace.Nazev) LIKE LOWER(?)
   OR LOWER(Restaurace.Kuchyne) LIKE LOWER(?)
   OR LOWER(Restaurace.Mesto) LIKE LOWER(?)
";
$stmt = $db->prepare($sql);
$stmt->bind_param("sss", $search_pattern, $search_pattern, $search_pattern);
$stmt->execute();
$result = $stmt->get_result();
$vsechny = $result->fetch_all(MYSQLI_ASSOC);

// rozdělíme restaurace podle toho, jestli odpovídají městu uživatele
$shoda = [];
$jine = [];

foreach ($vsechny as $row) {
    if ($uzivatel_mesto && $row['Mesto'] === $uzivatel_mesto) {
        $shoda[] = $row;
    } else {
        $jine[] = $row;
    }
}

$restaurace = array_merge($shoda, $jine);
?>



<?php include "searchbar.php"; ?>


<div class="hlavni-obsah">
    <div class="restaurace">
        <?php
        // pokud jsou nalezené restaurace, zobrazí se v seznamu
        if (!empty($restaurace)) {
            foreach ($restaurace as $row) {
                $id = (int)$row['id'];
                $nazevKuchyne = htmlspecialchars($row['Kuchyne']);
                $obrazek = htmlspecialchars($row['Cesta']);
                $doprava = (int)$row['Doprava'];
                $sleva = (float)($row['Sleva_predplatne'] ?? 0);

                echo '<a class="box" href="restaurace.php?id=' . $id . '">';

                if (!empty($obrazek)) {
                    echo '<div class="img-top"><img src="' . $obrazek . '" alt="' . $nazevKuchyne . '"></div>';
                } else {
                    echo '<div class="img-top"><img src="img/default.jpg" alt="Bez obrázku"></div>';
                }

                if (isset($_SESSION['uzivatel']) && $_SESSION['uzivatel']['Predplatne'] == 1 && $sleva > 0) {
                    echo '<div class="sleva-nahore">-' . $sleva . '% Verze +</div>';
                }

                echo '<h2>' . htmlspecialchars($row['Nazev']) . '</h2>';
                echo '<p><strong>Kuchyně:</strong> ' . $nazevKuchyne . '</p>';
                echo '<p><strong>Adresa:</strong><br>'
                    . htmlspecialchars($row['Ulice']) . ', '
                    . htmlspecialchars($row['PSC']) . ' '
                    . htmlspecialchars($row['Mesto']) . '</p>';

                if (isset($_SESSION['uzivatel']) && $_SESSION['uzivatel']['Predplatne'] == 1) {
                    echo '<p class="doprava-zdarma">Doprava: zdarma</p>';
                } else {
                    echo '<p><strong>Doprava:</strong> ' . $doprava . ' Kč</p>';
                }

                echo '</a>';
            }
        } else {

            echo '<p>Žádné restaurace nebyly nalezeny.</p>';
        }
        ?>
    </div>
</div>

<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <title>Hlavní stránka</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- kvulí @media (responzivata na mobily -->

    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            background-color: white;
        }

        .hlavni-obsah {
            display: flex;
            gap: 30px;
            align-items: flex-start;
            padding: 30px;
            max-width: 1400px;
            margin: 0 auto;
        }

        .restaurace {
            flex-grow: 1;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            align-items: start;
        }

        .box {
            border: 1px solid white;
            border-radius: 12px;
            padding: 0;
            background-color: white;
            box-shadow: 0 2px 10px black;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            text-decoration: none;
            color: inherit;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .box:hover {
            transform: translateY(-4px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .img-top {
            width: 100%;
            background-color: white;
            overflow: hidden;
            aspect-ratio: 3 / 2;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .img-top img {
            width: 100%;
            height: auto;
            object-fit: contain;
            display: block;
        }

        .box h2 {
            margin: 15px 15px 5px;
            font-size: 1.3em;
            color: black;
        }

        .box p {
            margin: 5px 15px 10px;
            font-size: 0.95em;
            color: black;
        }


        .sleva-nahore {
            position: absolute;
            top: 0;
            left: 0;
            background-color: purple;
            color: white;
            padding: 6px 12px;
            font-size: 0.9em;
            font-weight: bold;
            border-bottom-right-radius: 10px;
            z-index: 10;
        }

        .box {
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .doprava-zdarma {
            margin: 5px 15px 10px;
            font-size: 0.95em;
            font-weight: bold;
        }

        .doprava-normal {
            margin: 5px 15px 10px;
            font-size: 0.95em;
            color: black;
            font-weight: bold;
        }

        @media (max-width: 768px) {
            .hlavni-obsah {
                display: flex;
                flex-direction: column;
                gap: 20px;
            }

            .restaurace {
                width: 100% !important;
                box-sizing: border-box;
            }

            .restaurace img {
                width: 100%;
                height: auto;
            }

            .box {
                padding: 10px;
            }
        }
    </style>
</head>
<body>
</body>
</html>
