<?php
// Zadrží výstup, aby bylo možné použít header() bez chyby
ob_start(); ?>

<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once "conn.php";

include 'navbar.php';


$jmeno = $prijmeni = $email = $telefon = $heslo = $mesto = $psc = $ulice = '';

if (isset($_SESSION["user_id"])) {
    $id = $_SESSION["user_id"];

    // zpracování formuláře při odeslání aktualizace údajů
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $pole = [
            'jmeno' => 'Jmeno',
            'prijmeni' => 'Prijmeni',
            'email' => 'Email',
            'telefon' => 'Telefon',
            'mesto' => 'Mesto',
            'psc' => 'PSC',
            'ulice' => 'Ulice'
        ];
        // aktualizace hodnoty udaju v db
        foreach ($pole as $klic => $sloupec) {
            if (isset($_POST[$klic])) {
                $nove = trim($_POST[$klic]);
                if (!empty($nove)) {
                    $stmt = $db->prepare("UPDATE Zakaznik SET $sloupec = ? WHERE Id = ?");
                    $stmt->bind_param("si", $nove, $id);
                    $stmt->execute();
                }
            }
        }

        // uložení karty do db + udaju
        if (isset($_POST['ulozit_kartu'])) {
            $jmeno_karty = trim($_POST['jmeno_karty']);
            $cislo_karty = trim($_POST['cislo_karty']);
            $expirace = trim($_POST['expirace']);
            $cvc = trim($_POST['cvc']);

            if (!empty($jmeno_karty) && !empty($cislo_karty) && !empty($expirace) && !empty($cvc)) {
                $stmt = $db->prepare("UPDATE Zakaznik SET jmeno_karty = ?, cislo_karty = ?, expirace = ?, cvc = ? WHERE Id = ?");
                $stmt->bind_param("ssssi", $jmeno_karty, $cislo_karty, $expirace, $cvc, $id);
                $stmt->execute();
            }
        }

        header("Location: uzivatel.php");
    }


// Pokud je uživatel přihlášen, načteme údaje o kartě z databáze
    if (isset($_SESSION["user_id"])) {
        $id = $_SESSION["user_id"];
        $dotaz = $db->prepare("SELECT jmeno_karty, cislo_karty, expirace, cvc FROM Zakaznik WHERE Id = ?");
        $dotaz->bind_param("i", $id);
        $dotaz->execute();
        $dotaz->bind_result($jmeno_karty, $cislo_karty, $expirace, $cvc);
        $dotaz->fetch();
        $dotaz->close();
    }


    $stmt = $db->prepare("SELECT Jmeno, Prijmeni, Email, Telefon, Mesto, PSC, Ulice FROM Zakaznik WHERE Id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        $jmeno = htmlspecialchars($user["Jmeno"]);
        $prijmeni = htmlspecialchars($user["Prijmeni"]);
        $email = htmlspecialchars($user["Email"]);
        $telefon = htmlspecialchars($user["Telefon"]);
        $mesto = htmlspecialchars($user["Mesto"]);
        $psc = htmlspecialchars($user["PSC"]);
        $ulice = htmlspecialchars($user["Ulice"]);
    }
}
?>
    <!DOCTYPE html>
    <html lang="cs">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <!-- kvulí @media (responzivata na mobily -->
        <title>Účet uživatele</title>
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined"/>
        <style>
            body {
                font-family: Arial, sans-serif;
                margin: 0;
                padding: 0;
            }

            .Udaje {
                max-width: 1200px;
                margin: 30px auto;
                display: flex;
                flex-wrap: wrap;
                justify-content: flex-start;
                gap: 50px;
                padding: 20px;
            }

            .formular-blok {
                width: 100%;
                max-width: 350px;
                text-align: left;
            }

            .policko {
                margin-bottom: 20px;
            }

            .policko label {
                display: block;
                margin-bottom: 5px;
                font-weight: bold;
            }

            .policko form {
                display: flex;
                align-items: center;
                gap: 10px;
            }

            .policko input {
                flex-grow: 1;
                padding: 8px;
            }

            .material-symbols-outlined {
                font-size: 24px;
                color: black;
            }

            .tlacitko {
                padding: 8px 16px;
                background-color: gray;
                border: none;
                color: white;
                cursor: pointer;
                border-radius: 4px;
            }

            .historie {
                width: 100%;
                max-width: 350px;
                margin: 30px auto;
                padding: 10px;
                box-sizing: border-box;
            }

            .historie h2 {
                font-size: 22px;
                margin-bottom: 15px;
                border-bottom: 2px solid gray;
                padding-bottom: 5px;
            }

            .historie details {
                background-color: white;
                border: 1px solid lightgrey;
                border-radius: 6px;
                padding: 10px 15px;
                margin-bottom: 10px;
                transition: background-color 0.3s;
            }

            .historie details:hover {
                background-color: white;
            }

            .historie summary {
                font-weight: bold;
                cursor: pointer;
                list-style: none;
            }

            .historie ul {
                margin-top: 10px;
                margin-left: 20px;
                padding-left: 0;
                list-style: disc;
            }

            @media (max-width: 768px) {
                .historie-objednavek {
                    width: 100%;
                    box-sizing: border-box;
                    padding: 10px;
                    overflow-x: auto;
                }

                .historie-objednavek .objednavka-box {
                    width: 100%;
                    margin-bottom: 10px;
                    background-color: white;
                    padding: 10px;
                    border-radius: 8px;
                    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
                    font-size: 1em;
                    word-wrap: break-word;
                }

                .historie-objednavek .objednavka-box strong {
                    display: block;
                }
            }
        </style>
        <!-- scrip pto automatické dělání mezer u čísla karty a / u expirace (chatgpt)  -->
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                // Elementy pro vstup čísla karty a expirace
                const cardInput = document.getElementById('cislo_karty');
                const expInput = document.getElementById('expirace');
                const form = document.querySelector('form');

                // Automatické formátování čísla karty: každé 4 číslice přidá mezeru
                if (cardInput) {
                    cardInput.addEventListener('input', function (e) {
                        let value = e.target.value.replace(/\D/g, '').substring(0, 16);
                        let formatted = value.match(/.{1,4}/g);
                        e.target.value = formatted ? formatted.join(' ') : '';
                    });
                }

                // Automatické přidání lomítka (/) po měsíci u expirace
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

                // Validace čísla karty před odesláním: musí mít 16 číslic
                if (form && cardInput) {
                    form.addEventListener('submit', function (e) {
                        const cleanCard = cardInput.value.replace(/\s+/g, '');
                        if (cleanCard.length !== 0 && !/^\d{16}$/.test(cleanCard)) {
                            alert("Číslo karty musí obsahovat přesně 16 číslic.");
                            e.preventDefault();
                        } else {
                            cardInput.value = cleanCard;
                        }
                    });
                }
            });
        </script>

    </head>
    <body>

    <div class="Udaje">
        <div class="formular-blok">
            <h2>Osobní údaje</h2>
            <?php echo vytvorPolicko('Jméno', 'jmeno', $jmeno); ?>
            <?php echo vytvorPolicko('Příjmení', 'prijmeni', $prijmeni); ?>
            <?php echo vytvorPolicko('Email', 'email', $email); ?>
            <?php echo vytvorPolicko('Telefon', 'telefon', $telefon); ?>

            <h2>Uložená karta</h2>
            <form method="post" action="">
                <input type="hidden" name="ulozit_kartu" value="1">
                <div class="policko">
                    <label for="jmeno_karty">Jméno na kartě</label>
                    <input type="text" id="jmeno_karty" name="jmeno_karty"
                           value="<?php echo htmlspecialchars($jmeno_karty ?? ''); ?>" required>
                </div>
                <div class="policko">
                    <label for="cislo_karty">Číslo karty</label>
                    <input type="text" id="cislo_karty" name="cislo_karty"
                           maxlength="19"
                           placeholder="1234 5678 9012 3456"
                           value="<?php echo htmlspecialchars($cislo_karty ?? ''); ?>"
                           required>
                </div>
                <div class="policko">
                    <label for="expirace">Expirace (MM/RR)</label>
                    <input type="text" id="expirace" name="expirace"
                           maxlength="5"
                           placeholder="MM/RR"
                           value="<?php echo htmlspecialchars($expirace ?? ''); ?>"
                           required>
                </div>
                <div class="policko">
                    <label for="cvc">CVC</label>
                    <input type="text" id="cvc" name="cvc" maxlength="4"
                           value="<?php echo htmlspecialchars($cvc ?? ''); ?>" required>
                </div>
                <button type="submit" class="tlacitko">Uložit kartu</button>
            </form>
        </div>

        <div class="formular-blok">
            <h2>Adresa</h2>
            <?php echo vytvorPolicko('Město', 'mesto', $mesto); ?>
            <?php echo vytvorPolicko('PSČ', 'psc', $psc); ?>
            <?php echo vytvorPolicko('Ulice', 'ulice', $ulice); ?>
        </div>
    </div>


    <div class="historie">
        <h2>Historie objednávek</h2>
        <?php
        // kontrolola dostupnosti id přihlášeného uživatele
        if (isset($id)) {
            // všechny objednávky uživatele z db
            $stmt = $db->prepare("
            SELECT o.Id AS objednavka_id, r.Nazev AS restaurace, o.Cena
            FROM Objednavka o
            JOIN Restaurace r ON o.Restaurace_id = r.Id
            WHERE o.zakaznik_id = ?
            ORDER BY o.Id DESC
        ");
            if ($stmt) {
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $objednavky = $stmt->get_result();

                // cyklus pro projetí každé objednávky
                while ($obj = $objednavky->fetch_assoc()) {
                    echo '<details>';
                    echo '<summary><strong>' . htmlspecialchars($obj['restaurace']) . '</strong> – ' . number_format($obj['Cena'], 2) . ' Kč</summary>';

                    // načtení produktů v dané objednávce
                    $stmt2 = $db->prepare("
                    SELECT p.Nazev, po.Mnozstvi
                    FROM Polozky_Objednavky po
                    JOIN Produkt p ON po.Produkt_id = p.Id
                    WHERE po.Objednavka_id = ?
                ");
                    if ($stmt2) {
                        $stmt2->bind_param("i", $obj['objednavka_id']);
                        $stmt2->execute();
                        $produkty = $stmt2->get_result();

                        // vypís
                        echo '<ul>';
                        while ($produkt = $produkty->fetch_assoc()) {
                            echo '<li>' . htmlspecialchars($produkt['Nazev']) . ' × ' . $produkt['Mnozstvi'] . '</li>';
                        }
                        echo '</ul>';

                        // btn zopakování objednávky
                        echo '<form method="get" action="znovu_objednat.php" style="margin-top: 10px;">';
                        echo '<input type="hidden" name="objednavka_id" value="' . $obj['objednavka_id'] . '">';
                        echo '<button type="submit" class="tlacitko">Znovu objednat</button>';
                        echo '</form>';
                    } else {
                        echo "<p>Chyba: " . $db->error . "</p>";
                    }
                    echo '</details>';
                }
            } else {
                echo "<p>Chyba: " . $db->error . "</p>";
            }
        }
        ?>
    </div>


    <?php

    // funkce pro generování jednoho editačního políčka (chat gpt)
    function vytvorPolicko($label, $name, $value)
    {
        return '
    <div class="policko">
        <label for="' . $name . '">' . $label . '</label>
        <form method="post" action="">
            <input type="text" id="' . $name . '" name="' . $name . '" value="' . $value . '">
            <button type="submit" class="tlacitko"><span class="material-symbols-outlined">edit</span></button>
        </form>
    </div>';
    }

    include 'footer.php';
    ?>
    </body>
    </html>

<?php
// Uvolní výstup a odešle ho prohlížeči
ob_end_flush(); ?>