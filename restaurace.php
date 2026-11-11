<?php
include "conn.php";
session_start();

// získání id restaurace z url
$id = $_GET['id'] ?? null;
// pokud není id zadané, ukončíme skript
if (!$id) {
    echo "Nebyla vybrána žádná restaurace.";
    exit;
}

// načtení informací o restauraci z db
$stmt = $db->prepare("SELECT * FROM Restaurace WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$restaurace = $stmt->get_result()->fetch_assoc();


if (!$restaurace) {
    echo "Restaurace nebyla nalezena.";
    exit;
}


// načtení produktů z databáze pro danou restauraci
$sql = "SELECT p.id, p.Nazev, p.Cena, p.Popis, o.Cesta AS obrazek 
        FROM Produkt p 
        INNER JOIN Restaurace_Produkt rp ON p.id = rp.Produkt_id 
        LEFT JOIN Obrazek o ON p.id = o.Produkt_id  
        WHERE rp.Restaurace_id = ?";
$stmt = $db->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$produkty = $stmt->get_result();
?>


<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- kvulí @media (responzivata na mobily -->
    <title><?= htmlspecialchars($restaurace['Nazev']) ?></title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined"/>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            background-color: white;
        }

        .container {
            max-width: 1000px;
            margin: 30px auto;
            padding: 0 20px;
        }

        h1 {
            margin-bottom: 5px;
        }

        .info {
            margin-bottom: 30px;
        }

        .produkt-box {
            display: flex;
            align-items: flex-start;
            gap: 15px;
            border: 1px solid lightgrey;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
            background-color: white;
            box-shadow: 0 2px 10px;
            position: relative;
        }

        .produkt-img img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid lightgrey;
        }

        .produkt-info {
            flex-grow: 1;
        }

        .produkt-info h3 {
            margin: 0 0 8px;
        }

        .produkt-info p {
            margin: 4px 0;
            color: dimgray;
        }

        .plus-icon {
            position: absolute;
            top: 12px;
            right: 15px;
            font-size: 22px;
            color: green;
            cursor: pointer;
            transition: transform 0.2s ease;
        }

        .plus-icon:hover {
            transform: scale(1.2);
        }

        #kosik-zprava {
            display: none;
            position: fixed;
            top: 20px;
            right: 20px;
            background-color: green;
            color: white;
            padding: 12px 18px;
            border-radius: 8px;
            box-shadow: 0 4px 8px black;
            font-size: 15px;
            z-index: 1000;
            transition: opacity 0.5s ease;
        }

        @media (max-width: 768px) {
            .info, .produkty {
                width: 100%;
                padding: 10px;
                box-sizing: border-box;
            }

            .produkt {
                flex-direction: column !important;
                gap: 10px;
            }

            .produkt img {
                width: 100%;
                height: auto;
            }
        }

    </style>
</head>
<body>

<?php include "navbar.php"; ?>


<div class="container">
    <div class="info">
        <h1><?= htmlspecialchars($restaurace['Nazev']) ?></h1>
        <p><strong>Kuchyně:</strong> <?= htmlspecialchars($restaurace['Kuchyne']) ?></p>
        <p><strong>Adresa:</strong> <?= htmlspecialchars($restaurace['Ulice']) ?>,
            <?= htmlspecialchars($restaurace['PSC']) ?> <?= htmlspecialchars($restaurace['Mesto']) ?>
        </p>
    </div>

    <h2>Menu</h2>

    <?php if ($produkty && $produkty->num_rows > 0): ?>
        <?php while ($row = $produkty->fetch_assoc()): ?>
            <div class="produkt-box">
                <div class="plus-icon"
                     onclick="pridatDoKosiku(this)"
                     data-produkt-id="<?= $row['id'] ?>"
                     data-produkt-nazev="<?= htmlspecialchars($row['Nazev']) ?>"
                     data-produkt-cena="<?= $row['Cena'] ?>"
                     data-restaurace-id="<?= $id ?>">
                    +
                </div>

                <div class="produkt-img">
                    <img src="<?= !empty($row['obrazek']) ? htmlspecialchars($row['obrazek']) : 'img/default.jpg' ?>"
                         alt="<?= htmlspecialchars($row['Nazev']) ?>">
                </div>
                <div class="produkt-info">
                    <h3><?= htmlspecialchars($row['Nazev']) ?></h3>
                    <p><?= htmlspecialchars($row['Popis']) ?></p>
                    <p><strong>Cena:</strong> <?= number_format($row['Cena'], 2) ?> Kč</p>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>Tato restaurace zatím nenabízí žádné produkty.</p>
    <?php endif; ?>
</div>

<!--
Tento skript slouží k tomu, aby se produkt přidal do košíku bez toho, aby se stránka znovu načetla.
Funkce `pridatDoKosiku(elem)` získá z HTML elementu data o produktu a odešle je do PHP skriptu pridat_do_kosiku.php pomocí fetch (POST) chatGPT
-->


<div id="kosik-zprava">Přidáno do košíku</div>

<script>

    // zobrazí krátkou hlášku při přidání do košíku
    function zobrazZpravu(text) {
        const zprava = document.getElementById("kosik-zprava");
        zprava.textContent = text;
        zprava.style.display = "block";
        setTimeout(() => {
            zprava.style.display = "none";
        }, 2000);
    }


    // odešle data o produktu do skriptu pridat_do_kosiku.php pomocí fetch
    function pridatDoKosiku(elem) {
        const data = {
            produkt_id: elem.dataset.produktId,
            restaurace_id: elem.dataset.restauraceId,
            nazev: elem.dataset.produktNazev,
            cena: elem.dataset.produktCena
        };

        fetch('pridat_do_kosiku.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: new URLSearchParams(data)
        })
            .then(response => response.json())
            .then(res => {
                if (res.stav === 'ok') {
                    zobrazZpravu("Přidáno do košíku");
                } else {
                    zobrazZpravu("Nelze přidat – jiná restaurace v košíku");
                }
            });
    }
</script>

</body>
</html>
