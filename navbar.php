<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>


<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined"/>

<style>
    html, body {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    .navbar-gf {
        position: sticky;
        top: 0;
        z-index: 999;
        display: flex;
        justify-content: space-between;
        align-items: center;
        background-color: gray;
        padding: 10px 20px;
        font-family: Arial, sans-serif;
        border-radius: 0;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
    }

    .navbar-gf * {
        box-sizing: border-box;
    }

    .navbar-gf .logo a {
        display: flex;
        align-items: center;
        text-decoration: none;
    }

    .navbar-gf .logo img {
        height: 50px;
        width: auto;
        object-fit: contain;
        display: block;
        border-radius: 0;
    }

    .navbar-gf .nav-actions {
        display: flex;
        gap: 15px;
        align-items: center;
    }

    .navbar-gf .nav-btn {
        padding: 9px 18px;
        border-radius: 6px;
        text-decoration: none;
        font-weight: bold;
        transition: background 0.3s, transform 0.2s;
        box-shadow: 0 2px 6px black;
    }

    .navbar-gf .btn-prihlasit {
        background-color: green;
        color: white;
    }

    .navbar-gf .btn-prihlasit:hover {
        background-color: green;
        transform: translateY(-1px);
    }

    .navbar-gf .btn-odhlasit {
        background-color: red;
        color: white;
    }

    .navbar-gf .btn-odhlasit:hover {
        background-color: red;
        transform: translateY(-1px);
    }

    .navbar-gf .btn-predplatne {
        background-color: gold;
        color: black;
        font-weight: bold;
    }

    .navbar-gf .btn-predplatne:hover {
        background-color: goldenrod;
        transform: translateY(-1px);
    }

    .navbar-gf .icon-btn {
        background-color: white;
        border-radius: 50%;
        padding: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        transition: background 0.3s ease;
    }

    .navbar-gf .icon-btn:hover {
        background-color: white;
    }

    .navbar-gf .material-symbols-outlined {
        color: black;
        font-size: 24px;
    }

    .navbar-gf .navbar_search {
        display: flex;
        justify-content: center;
        align-items: center;
        flex-grow: 1;
        margin: 0 20px;
    }

    .navbar-gf .navbar_search form {
        display: flex;
        align-items: center;
        background-color: white;
        border-radius: 6px;
        padding: 5px 8px;
        box-shadow: 0 2px 6px black;
    }

    .navbar-gf .navbar_search input[type="text"] {
        border: none;
        outline: none;
        padding: 8px;
        font-size: 16px;
        width: 220px;
        background-color: transparent;
    }

    .navbar-gf .navbar_search button {
        background-color: #28a745;
        border: none;
        padding: 6px 10px;
        border-radius: 4px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background 0.3s;
    }

    .navbar-gf .navbar_search button:hover {
        background-color: #218838;
    }

    .navbar-gf .navbar_search .material-symbols-outlined {
        color: white;
        font-size: 22px;
    }

    .hamburger {
        display: none;
        font-size: 30px;
        cursor: pointer;
        color: white;
    }

    .nav-menu-mobile {
        display: none;
        flex-direction: column;
        gap: 10px;
        background-color: gray;
        padding: 10px 20px;
    }

    .nav-menu-mobile a {
        color: white;
        text-decoration: none;
        padding: 8px 0;
        border-bottom: 1px solid rgba(255, 255, 255, 0.2);
    }

    @media (max-width: 768px) {
        .navbar-gf .nav-actions {
            display: none;
        }

        .hamburger {
            display: block;
        }

        .nav-menu-mobile.active {
            display: flex;
        }
    }
</style>

<div class="navbar-gf">
    <div class="logo">
        <a href="index.php">
            <img src="gorila.jpg" alt="Logo">
        </a>
    </div>
    <div class="hamburger" onclick="toggleMenu()">☰</div>

    <div class="nav-actions">
        <?php if (isset($_SESSION["user_id"])): ?>
            <a class="nav-btn btn-odhlasit" href="logout.php">Odhlásit se</a>

            <?php if (isset($_SESSION['uzivatel']) && $_SESSION['uzivatel']['Predplatne'] == 1): ?>
                <a class="nav-btn btn-predplatne" href="#">✓ Verze +</a>
            <?php else: ?>
                <a class="nav-btn btn-predplatne" href="predplatne.php">Získat Verzi +</a>
            <?php endif; ?>

        <?php else: ?>
            <a class="nav-btn btn-prihlasit" href="login.php">Přihlásit se</a>
        <?php endif; ?>

        <a class="icon-btn" href="kosik.php">
            <span class="material-symbols-outlined">shopping_cart</span>
        </a>
        <a class="icon-btn" href="uzivatel.php">
            <span class="material-symbols-outlined">person</span>
        </a>
    </div>
</div>

<div class="nav-menu-mobile" id="navMobile">
    <?php if (isset($_SESSION["user_id"])): ?>
        <a href="logout.php">Odhlásit se</a>
        <?php if (isset($_SESSION['uzivatel']) && $_SESSION['uzivatel']['Predplatne'] == 1): ?>
            <a href="#">✓ Verze +</a>
        <?php else: ?>
            <a href="predplatne.php">Získat Verzi +</a>
        <?php endif; ?>
    <?php else: ?>
        <a href="login.php">Přihlásit se</a>
    <?php endif; ?>
    <a href="kosik.php">Košík</a>
    <a href="uzivatel.php">Účet</a>
</div>


<script>
    // přepínání zobrazení mobilního menu
    function toggleMenu() {
        document.getElementById("navMobile").classList.toggle("active");
    }
</script>
