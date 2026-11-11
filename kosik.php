<?php
session_start();
include "conn.php";
include "navbar.php";

// Načtení dat z košíku pokud není, vytvoří se prázdný
$kosik = $_SESSION['kosik'] ?? ['polozky' => [], 'restaurace_id' => null];
$polozky = $kosik['polozky'];
$produktIds = array_column($polozky, 'id');
$produkty = [];
$celkem = 0;
$doprava = 0;
$slevaProcent = 0;
$maPredplatne = false;
$nazevRestaurace = '';


//načtení názvu, dopravy a slevy restuarce z db
if (!empty($kosik['restaurace_id'])) {
    $stmt = $db->prepare("SELECT Nazev, Doprava, Sleva_predplatne FROM Restaurace WHERE id = ?");
    $stmt->bind_param("i", $kosik['restaurace_id']);
    $stmt->execute();
    $stmt->bind_result($nazevRestaurace, $doprava, $slevaProcent);
    $stmt->fetch();
    $stmt->close();
}

$mesto = $psc = $ulice = '';
$jmeno_karty = $cislo_karty = $expirace = $cvc = '';
$ma_ulozenou_kartu = false;


// načtení z db údaje přihlášeného uživatele pro doručení a případně platební kartu
if (isset($_SESSION['user_id'])) {
    $id = $_SESSION['user_id'];
    $stmt = $db->prepare("SELECT Mesto, PSC, Ulice, jmeno_karty, cislo_karty, expirace, cvc, Predplatne FROM Zakaznik WHERE Id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($user = $result->fetch_assoc()) {
        $mesto = htmlspecialchars($user['Mesto']);
        $psc = htmlspecialchars($user['PSC']);
        $ulice = htmlspecialchars($user['Ulice']);
        $maPredplatne = $user['Predplatne'] == 1;
        if (!empty($user['jmeno_karty']) && !empty($user['cislo_karty']) && !empty($user['expirace']) && !empty($user['cvc'])) {
            $jmeno_karty = htmlspecialchars($user['jmeno_karty']);
            $cislo_karty = htmlspecialchars($user['cislo_karty']);
            $expirace = htmlspecialchars($user['expirace']);
            $cvc = htmlspecialchars($user['cvc']);
            $ma_ulozenou_kartu = true;
        }
    }
}


// Pokud jsou v košíku produkty, načteme jejich detaily z databáze
if (!empty($produktIds)) {
    $placeholders = implode(',', array_fill(0, count($produktIds), '?'));
    $typy = str_repeat('i', count($produktIds));
    $stmt = $db->prepare("SELECT p.id, p.Nazev, p.Cena, o.Cesta AS obrazek FROM Produkt p LEFT JOIN Obrazek o ON p.id = o.Produkt_id WHERE p.id IN ($placeholders)");
    $stmt->bind_param($typy, ...$produktIds);
    $stmt->execute();
    $vysledek = $stmt->get_result();
    while ($row = $vysledek->fetch_assoc()) {
        $produkty[$row['id']] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Košík</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: whitesmoke;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 800px;
            margin: 30px auto;
            padding: 20px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        h2, h3, h4 {
            color: black;
        }

        ul {
            list-style: none;
            padding: 0;
        }

        li {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            margin-bottom: 10px;
            border-bottom: 1px solid lightgray;
            padding-bottom: 10px;
        }

        img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            margin-right: 10px;
            border-radius: 6px;
        }

        .odebrat-btn {
            margin-left: auto;
            background-color: red;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s;
        }

        .odebrat-btn:hover {
            background-color: red;
        }

        input[type="text"], input[type="number"], textarea {
            width: 100%;
            padding: 10px;
            margin: 6px 0 12px 0;
            border: 1px solid lightgray;
            border-radius: 4px;
            font-size: 15px;
            resize: none;
        }


        .payment-options {
            display: flex;
            gap: 20px;
            margin: 10px 0;
            align-items: center;
        }

        #payment_fields, #kuryr_metody, #poznamka_blok {
            margin-top: 15px;
            padding: 10px;
            background: white;
            border: 1px solid lightgray;
            border-radius: 6px;
        }

        button[type="submit"] {
            margin-top: 20px;
            background-color: forestgreen;
            color: white;
            padding: 10px 24px;
            font-size: 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }

        .ulozena-karta-blok {
            margin-bottom: 10px;
        }

        .ulozena-karta-btn {
            background-color: forestgreen;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .ulozena-karta-btn:hover {
            background-color: darkgreen;
        }
    </style>

    <script>
        //všechny scripty jsou z chatgpt

        // Přepíná viditelnost polí podle zvoleného způsobu platby
        function togglePaymentFields() {
            const online = document.getElementById("online_payment").checked;
            const kuryr = document.getElementById("kuryr_payment").checked;

            document.getElementById("payment_fields").style.display = online ? "block" : "none";
            document.getElementById("kuryr_metody").style.display = kuryr ? "block" : "none";
            document.getElementById("poznamka_blok").style.display = (online || kuryr) ? "block" : "none";

            kontrolujFormular();
        }

        // Kontroluje jestli jsou všechna potřebná pole vyplněna a povoluje tlačítko
        function kontrolujFormular() {
            const online = document.getElementById("online_payment").checked;
            const kuryr = document.getElementById("kuryr_payment").checked;

            const jmeno = document.getElementsByName("jmeno_karty")[0].value.trim();
            const cislo = document.getElementsByName("cislo_karty")[0].value.trim();
            const expirace = document.getElementsByName("expirace")[0].value.trim();
            const cvc = document.getElementsByName("cvc")[0].value.trim();

            const btn = document.getElementById("objednatBtn");

            if (online) {
                // Při online platbě: všechna pole musí být vyplněná
                btn.disabled = !(jmeno && cislo && expirace && cvc);
            } else if (kuryr) {
                // Při kurýrovi: musí být vybrána jedna z metod (hotově/kartou)
                const metodaVybrana = document.querySelector('input[name="platebni_metoda"]:checked') !== null;
                btn.disabled = !metodaVybrana;
            } else {
                btn.disabled = false;
            }
        }

        // Po kliknutí na tlačítko "Použít uloženou kartu" se předvyplní pole
        function vyplnKartu(button) {
            document.getElementsByName("jmeno_karty")[0].value = button.dataset.jmeno;
            document.getElementsByName("cislo_karty")[0].value = button.dataset.cislo;
            document.getElementsByName("expirace")[0].value = button.dataset.expirace;
            document.getElementsByName("cvc")[0].value = button.dataset.cvc;
            kontrolujFormular();
        }

        // Nastavení funkcí po načtení stránky
        document.addEventListener("DOMContentLoaded", function () {
            togglePaymentFields();

            document.querySelectorAll('input[name="zpusob_platby"]').forEach(el => {
                el.addEventListener("change", togglePaymentFields);
            });

            document.querySelectorAll('#payment_fields input').forEach(el => {
                el.addEventListener("input", kontrolujFormular);
            });

            document.querySelectorAll('input[name="platebni_metoda"]').forEach(el => {
                el.addEventListener("change", kontrolujFormular);
            });
        });


        // scrip pto automatické dělání mezer u čísla karty a / u expirace (chatgpt)

        document.addEventListener('DOMContentLoaded', function () {
            // Elementy pro vstup čísla karty a expirace
            const cardInput = document.getElementById('cislo_karty');
            const expInput = document.getElementById('expirace');

            // Přidání mezer každé 4 číslice pro číslo karty
            if (cardInput) {
                cardInput.addEventListener('input', function (e) {
                    let value = e.target.value.replace(/\D/g, '').substring(0, 16);
                    let formatted = value.match(/.{1,4}/g);
                    e.target.value = formatted ? formatted.join(' ') : '';
                });
            }

            // Přidání lomítka po dvou číslicích u expirace
            if (expInput) {
                expInput.addEventListener('input', function (e) {
                    let value = e.target.value.replace(/\D/g, '').substring(0, 4);
                    if (value.length >= 3) {
                        e.target.value = value.substring(0, 2) + '/' + value.substring(2);
                    } else {
                        e.target.value = value;
                    }
                });
            }
        });

    </script>
</head>
<body>


<div class="container">
    <h2>Váš košík</h2>


    <?php if (empty($polozky)): ?>
        <p>Košík je prázdný.</p>
    <?php else: ?>
        <p><strong>Restaurace:</strong> <?= htmlspecialchars($nazevRestaurace) ?></p>

        <!-- Výpis položek v košíku s obrázkem, názvem, množstvím a cenou -->
        <ul>
            <?php foreach ($polozky as $index => $p):
                $prod = $produkty[$p['id']] ?? null;
                if (!$prod) continue;
                $cenaPolozky = $prod['Cena'];
                if ($maPredplatne && $slevaProcent > 0) {
                    $cenaPolozky *= (1 - $slevaProcent / 100);
                }
                $mezisoucet = $cenaPolozky * $p['mnozstvi'];
                $celkem += $mezisoucet;
                ?>
                <li>
                    <img src="<?= $prod['obrazek'] ?? 'img/default.jpg' ?>" alt="">
                    <?= htmlspecialchars($prod['Nazev']) ?> x <?= $p['mnozstvi'] ?>
                    = <?= number_format($mezisoucet, 2) ?> Kč
                    <form method="post" action="odebrat_z_kosiku.php">
                        <input type="hidden" name="index" value="<?= $index ?>">
                        <button type="submit" class="odebrat-btn">Odebrat</button>
                    </form>
                </li>
            <?php endforeach; ?>
        </ul>
        <p><strong>Mezisoučet: <?= number_format($celkem, 2) ?> Kč</strong></p>
        <p><strong>Doprava:</strong> <?= $maPredplatne ? '0.00 Kč (verze+)' : number_format($doprava, 2) . ' Kč' ?></p>
        <p><strong>Celkem k úhradě:</strong> <?= number_format($celkem + ($maPredplatne ? 0 : $doprava), 2) ?> Kč</p>


        <!-- Formulář pro vytvoření objednávky -->
        <form method="post" action="vytvor_objednavku.php">
            <input type="hidden" name="restaurace_id" value="<?= htmlspecialchars($kosik['restaurace_id']) ?>">

            <h3>Doručovací adresa</h3>
            <label for="mesto">Město:</label>
            <input type="text" id="mesto" name="mesto" value="<?= $mesto ?>" required>
            <label for="psc">PSČ:</label>
            <input type="text" id="psc" name="psc" value="<?= $psc ?>" required>
            <label for="ulice">Ulice:</label>
            <input type="text" id="ulice" name="ulice" value="<?= $ulice ?>" required>

            <h4>Způsob platby</h4>


            <div class="payment-options">
                <label><input type="radio" id="online_payment" name="zpusob_platby" value="online" required>
                    Online</label>
                <label><input type="radio" id="kuryr_payment" name="zpusob_platby" value="kuryr"> Kurýr</label>
            </div>


            <div id="kuryr_metody" style="display: none;">
                <strong>Jak zaplatíte u kurýra?</strong><br>
                <label><input type="radio" name="platebni_metoda" value="hotově"> Hotově</label><br>
                <label><input type="radio" name="platebni_metoda" value="kartou"> Kartou</label>
            </div>


            <div id="poznamka_blok" style="display: none;">
                <label for="poznamka"><strong>Poznámka pro restauraci:</strong></label>
                <textarea name="poznamka" id="poznamka" rows="3"
                          placeholder="Např. bez cibule, zazvoňte 2x..."></textarea>
            </div>


            <div id="payment_fields" style="display:none;">

                <?php if ($ma_ulozenou_kartu): ?>
                    <div class="ulozena-karta-blok">
                        <button type="button"
                                class="ulozena-karta-btn"
                                onclick="vyplnKartu(this)"
                                data-jmeno="<?= $jmeno_karty ?>"
                                data-cislo="<?= $cislo_karty ?>"
                                data-expirace="<?= $expirace ?>"
                                data-cvc="<?= $cvc ?>">
                            Použít uloženou kartu
                        </button>
                    </div>
                <?php endif; ?>

                <label>Jméno na kartě:</label>
                <input type="text" name="jmeno_karty">
                <label>Číslo karty:</label>
                <input type="text" name="cislo_karty" id="cislo_karty">
                <label>Expirace:</label>
                <input type="text" name="expirace" id="expirace">
                <label>CVC:</label>
                <input type="number" name="cvc">
            </div>

            <button type="submit" id="objednatBtn" disabled>Objednat</button>
        </form>
    <?php endif; ?>
</div>
</body>
</html>
