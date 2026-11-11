<?php
include "conn.php";


// získání filtru kuchyně z URL
$filtr_kuchyne = $_GET['kuchyne'] ?? '';

// zjištění města přihlášeného uživatele
$uzivatel_mesto = null;
if (isset($_SESSION['user_id'])) {
    $stmt = $db->prepare("SELECT Mesto FROM Zakaznik WHERE Id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $stmt->bind_result($uzivatel_mesto);
    $stmt->fetch();
    $stmt->close();
}

// výběr restaurací podle zvolené kuchyně nebo všech
if (!empty($filtr_kuchyne)) {
    $sql = "SELECT r.id, r.Nazev, r.Kuchyne, r.Mesto, r.PSC, r.Ulice, r.Doprava, r.Sleva_predplatne, o.Cesta AS obrazek 
            FROM Restaurace r 
            LEFT JOIN Obrazek o ON r.id = o.Restaurace_id 
            WHERE r.Kuchyne = ?";
    $stmt = $db->prepare($sql);
    $stmt->bind_param("s", $filtr_kuchyne);
    $stmt->execute();
    $result = $stmt->get_result();
    $vsechny_restaurace = $result->fetch_all(MYSQLI_ASSOC);
} else {
    $sql = "SELECT r.id, r.Nazev, r.Kuchyne, r.Mesto, r.PSC, r.Ulice, r.Doprava, r.Sleva_predplatne, o.Cesta AS obrazek 
            FROM Restaurace r 
            LEFT JOIN Obrazek o ON r.id = o.Restaurace_id";
    $result = $db->query($sql);
    $vsechny_restaurace = $result->fetch_all(MYSQLI_ASSOC);
}

// rozdělení restaurace podle toho, jestli se shodují s městem uživatele
$shoda = [];
$jine = [];

foreach ($vsechny_restaurace as $restaurace) {
    if ($uzivatel_mesto && $restaurace['Mesto'] === $uzivatel_mesto) {
        $shoda[] = $restaurace;
    } else {
        $jine[] = $restaurace;
    }
}

$restaurace_seznam = array_merge($shoda, $jine);
?>



<?php include "searchbar.php"; ?>


<div class="hlavni-obsah">
    <?php include "filtr.php"; ?>

    <div class="restaurace">
        <?php
        if (!empty($restaurace_seznam)) {
            foreach ($restaurace_seznam as $row) {
                $id = (int)$row['id'];
                $nazevKuchyne = htmlspecialchars($row['Kuchyne']);
                $obrazek = htmlspecialchars($row['obrazek']);
                $doprava = (int)$row['Doprava'];
                $sleva = (float)($row['Sleva_predplatne'] ?? 0);

                echo '<a class="box" href="restaurace.php?id=' . $id . '">';

                if (!empty($obrazek)) {
                    echo '<div class="img-top"><img src="' . $obrazek . '" alt="' . $nazevKuchyne . '"></div>';
                } else {
                    echo '<div class="img-top"><img src="img/default.jpg" alt="Bez obrázku"></div>';
                }

                echo '<h2>' . htmlspecialchars($row['Nazev']) . '</h2>';
                echo '<p><strong>Kuchyně:</strong> ' . $nazevKuchyne . '</p>';
                echo '<p><strong>Adresa:</strong><br>'
                    . htmlspecialchars($row['Ulice']) . ', '
                    . htmlspecialchars($row['PSC']) . ' '
                    . htmlspecialchars($row['Mesto']) . '</p>';

                if (isset($_SESSION['uzivatel']) && $_SESSION['uzivatel']['Predplatne'] == 1) {
                    if ($sleva > 0) {
                        echo '<div class="sleva-nahore">-' . $sleva . '% Verze +</div>';
                    }
                    echo '<p1 class="doprava-zdarma">Doprava: zdarma</p1>';
                } else {
                    echo '<p class="doprava-normal"><strong>Doprava:</strong> ' . $doprava . ' Kč</p>';
                }

                echo '</a>';
            }
        } else {
            echo '<p>Žádné restaurace nebyly nalezeny.</p>';
        }
        ?>
    </div>
</div>

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
