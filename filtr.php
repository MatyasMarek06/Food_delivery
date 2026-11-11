<?php
if (!isset($db)) {
    include 'conn.php';
}

$query = "SELECT DISTINCT Kuchyne FROM Restaurace WHERE Kuchyne IS NOT NULL AND Kuchyne != ''";
$result = $db->query($query);
$moznosti = [];
while ($row = $result->fetch_assoc()) {
    $moznosti[] = $row['Kuchyne'];
}
$vybrana = $_GET['kuchyne'] ?? '';
?>


<div class="filtr-box">
    <h3>Druh kuchyně</h3>
    <div class="filtr-kuchyne">
        <a href="index.php" class="filtr-link <?= $vybrana === '' ? 'active' : '' ?>">Všechny</a>
        <?php foreach ($moznosti as $kuchyne): ?>
            <a href="index.php?kuchyne=<?= urlencode($kuchyne) ?>"
               class="filtr-link <?= $vybrana === $kuchyne ? 'active' : '' ?>">
                <?= htmlspecialchars(ucfirst($kuchyne)) ?>
            </a>
        <?php endforeach; ?>
    </div>
</div>

<style>
    .filtr-box {
        background-color: white;
        padding: 15px 20px;
        border: 1px solid white;
        border-radius: 10px;
        font-family: Arial, sans-serif;
        font-size: 15px;
        box-shadow: 0 1px 6px black;
        max-width: 300px;
        margin: 0 auto;
    }

    .filtr-box h3 {
        margin: 0 0 15px;
        font-size: 16px;
        color: black;
        border-bottom: 1px solid white;
        padding-bottom: 5px;
    }

    .filtr-kuchyne {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .filtr-link {
        display: block;
        padding: 6px 10px;
        background-color: white;
        border: 1px solid white;
        border-radius: 6px;
        text-decoration: none;
        color: black;
        transition: background-color 0.2s;
        font-size: 14px;
    }

    .filtr-link:hover {
        background-color: #e0e0e0;
    }

    .filtr-link.active {
        background-color: black;
        color: white;
        border-color: black;
    }

    @media (max-width: 768px) {
        .filtr-box {
            width: 100% !important;
            padding: 10px;
            box-sizing: border-box;
        }

        select, input[type="submit"] {
            width: 100%;
            margin-bottom: 10px;
        }
    }

</style>