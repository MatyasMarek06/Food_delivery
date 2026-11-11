<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <title>Hlavní stránka</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- kvulí @media (responzivata na mobily -->
</head>
<body>

<?php session_start();
?>


<?php include "navbar.php"; ?>

<?php include "restaurace_list.php"; ?>

<?php include "footer.php"; ?>

</body>
</html>
