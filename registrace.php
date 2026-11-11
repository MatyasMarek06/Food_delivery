<?php
session_start();
?>

<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- kvulí @media (responzivata na mobily -->
    <title>Registrace</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: white;
            margin: 0;
            padding: 0;
        }

        .Uzivatel {
            max-width: 500px;
            margin: 60px auto;
            padding: 30px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .Uzivatel h2 {
            text-align: center;
            margin-bottom: 30px;
            color: gray;
        }

        .Blok {
            display: flex;
            flex-direction: column;
            margin-bottom: 20px;
        }

        .Blok label {
            margin-bottom: 5px;
            font-weight: bold;
            color: darkslategrey;
        }

        .Blok input {
            padding: 10px;
            border: 1px solid lightgrey;
            border-radius: 5px;
            font-size: 16px;
        }

        .tlacitko-ulozit {
            width: 100%;
            padding: 12px;
            background-color: gray;
            border: none;
            color: white;
            font-size: 18px;
            font-weight: bold;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 20px;
            transition: background-color 0.3s;
        }

        .tlacitko-ulozit:hover {
            background-color: gray;
        }

        .error {
            color: red;
            text-align: center;
            margin-bottom: 15px;
        }

        @media (max-width: 768px) {
            body {
                padding: 10px;
            }

            .container, form {
                width: 100% !important;
                max-width: 100%;
                padding: 10px;
                box-sizing: border-box;
            }

            input, select, button {
                width: 100% !important;
                font-size: 1.1em;
                margin-bottom: 12px;
            }
        }

    </style>
</head>
<body>

<?php include "navbar.php"; ?>

<div class="Uzivatel">
    <form action="add_user.php" method="post">
        <h2>Registrace</h2>

        <?php if (!empty($_SESSION["error"])): ?>
            <p class="error"><?= $_SESSION["error"];
                unset($_SESSION["error"]); ?></p>
        <?php endif; ?>

        <div class="Blok">
            <label for="Jmeno">Jméno:</label>
            <input type="text" id="Jmeno" name="Jmeno" pattern="[A-Za-zÁ-Žá-ž\s\-]{2,}"
                   title="Pouze písmena, minimálně 2 znaky" required>
        </div>

        <div class="Blok">
            <label for="Prijmeni">Příjmení:</label>
            <input type="text" id="Prijmeni" name="Prijmeni" pattern="[A-Za-zÁ-Žá-ž\s\-]{2,}"
                   title="Pouze písmena, minimálně 2 znaky" required>
        </div>

        <div class="Blok">
            <label for="Email">Email:</label>
            <input type="email" id="Email" name="Email" required>
        </div>

        <div class="Blok">
            <label for="password">Heslo:</label>
            <input type="password" id="password" name="password" minlength="6" title="Minimálně 6 znaků" required>
        </div>

        <div class="Blok">
            <label for="Telefon">Telefon:</label>
            <input type="tel" id="Telefon" name="Telefon" pattern="\d{9}" title="Zadejte 9 číslic bez mezer" required>
        </div>

        <div class="Blok">
            <label for="Mesto">Město:</label>
            <input type="text" id="Mesto" name="Mesto" pattern="[A-Za-zÁ-Žá-ž\s\-]{2,}"
                   title="Pouze písmena, minimálně 2 znaky" required>
        </div>

        <div class="Blok">
            <label for="PSC">PSČ:</label>
            <input type="text" id="PSC" name="PSC" pattern="\d{5}" title="Zadejte 5 číslic (např. 11000)" required>
        </div>

        <div class="Blok">
            <label for="Ulice">Ulice:</label>
            <input type="text" id="Ulice" name="Ulice" pattern=".{2,}" title="Zadejte název ulice" required>
        </div>

        <button class="tlacitko-ulozit">Registrovat</button>
    </form>
</div>

</body>
</html>