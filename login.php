<?php
session_start();
require_once "conn.php";


if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"] ?? '');
    $password = trim($_POST["password"] ?? '');


    if (empty($email) || empty($password)) {
        echo "Zadejte e-mail a heslo.";
    } else {
        // kontrola jestli existuje uživatel s tímto emailem
        $stmt = $db->prepare("SELECT * FROM Zakaznik WHERE Email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            // porovnání zadaného hesla s hashovaným heslem v databázi
            if (password_verify($password, $row["Heslo"])) {
                $_SESSION["user_id"] = $row["id"];
                $_SESSION["username"] = $row["Jmeno"];
                $_SESSION["uzivatel"] = $row;
                header("Location: index.php");
                exit();
            } else {
                echo "Nesprávné heslo.";
            }
        } else {
            echo "Uživatel s tímto e-mailem neexistuje.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- kvůli responzivitě -->
    <title>Přihlášení</title>
    <style>
        .login-box {
            max-width: 400px;
            margin: 80px auto;
            padding: 30px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            margin-bottom: 30px;
            color: gray;
        }

        form {
            display: flex;
            flex-direction: column;
        }

        input[type="email"], input[type="password"] {
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid lightgrey;
            border-radius: 5px;
            font-size: 16px;
        }

        button {
            padding: 10px;
            background-color: gray;
            color: white;
            font-weight: bold;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: darkslategrey;
        }

        .register-link {
            text-align: center;
            margin-top: 15px;
        }

        .register-link a {
            color: gray;
            text-decoration: none;
        }

        form, .form-wrapper, .container {
            width: 100% !important;
            max-width: 100%;
            margin: 0 auto;
            padding: 10px;
            box-sizing: border-box;
        }

        input, button {
            width: 100% !important;
            font-size: 1.1em;
            margin-bottom: 12px;
        }

        h1, h2 {
            text-align: center;
            font-size: 1.5em;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: white;
            margin: 0;
            padding: 0;
        }

        .login-box {
            max-width: 400px;
            margin: 80px auto;
            padding: 30px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 8px black;
        }

        h2 {
            text-align: center;
            margin-bottom: 30px;
            color: gray;
        }

        form {
            display: flex;
            flex-direction: column;
        }

        input[type="email"], input[type="password"] {
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid lightgrey;
            border-radius: 5px;
            font-size: 16px;
        }

        button {
            padding: 10px;
            background-color: gray;
            color: white;
            font-weight: bold;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: darkslategrey;
        }

        .register-link {
            text-align: center;
            margin-top: 15px;
        }

        .register-link a {
            color: gray;
            text-decoration: none;
        }

        @media (max-width: 768px) {
            body {
                padding: 10px;
            }

            form, .form-wrapper, .container {
                width: 100% !important;
                max-width: 100%;
                margin: 0 auto;
                padding: 10px;
                box-sizing: border-box;
            }

            input, button {
                width: 100% !important;
                font-size: 1.1em;
                margin-bottom: 12px;
            }

            h1, h2 {
                text-align: center;
                font-size: 1.5em;
            }
        }
    </style>
</head>
<body>
<?php include "navbar.php"; ?>


<div class="login-box">
    <h2>Přihlášení</h2>
    <form method="post" action="">
        <input type="email" name="email" placeholder="E-mail" required>
        <input type="password" name="password" placeholder="Heslo" required>
        <button type="submit">Přihlásit se</button>
    </form>
    <div class="register-link">
        Nemáte účet? <a href="registrace.php">Zaregistrujte se</a>
    </div>
</div>
</body>
</html>
