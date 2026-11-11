<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <title>Rozvoz jídla</title>
    <style>
        footer {
            background-color: lightgray;
            padding: 30px 40px;
            margin-top: 40px;
            border-top: 1px solid gray;
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            font-family: Arial, sans-serif;
        }

        .footer-left {
            max-width: 300px;
        }

        .footer-left p {
            margin: 0 0 10px 0;
            color: black;
            font-size: 14px;
        }

        .footer-right ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .footer-right li {
            margin: 6px 0;
        }

        .footer-right a {
            color: black;
            text-decoration: none;
            font-weight: bold;
            transition: color 0.3s ease;
        }

        .footer-right a:hover {
            color: dimgray;
            text-decoration: underline;
        }

        .footer-bottom {
            width: 100%;
            text-align: center;
            margin-top: 20px;
            font-size: 13px;
            color: dimgray;
        }

        @media (max-width: 768px) {
            footer {
                font-size: 0.9em;
                text-align: center;
                padding: 10px;
            }
        }
    </style>
</head>
<body>

<footer>
    <!-- Levá část patičky s popisem služby -->
    <div class="footer-left">
        <p><strong>Rozvoz jídla</strong></p>
        <p>Rychlý a spolehlivý rozvoz z vašich oblíbených restaurací až ke dveřím.</p>
    </div>

    <!-- Pravá část s odkazy -->
    <div class="footer-right">
        <ul>
            <li><a href="#">Kontakty</a></li>
            <li><a href="#">O nás</a></li>
            <li><a href="#">Obchod</a></li>
            <li><a href="#">Partneři</a></li>
        </ul>
    </div>

    <!-- Spodní řádek patičky -->
    <div class="footer-bottom">
        &copy; 2025 Rozvoz jídla. Všechna práva vyhrazena.
    </div>
</footer>

</body>
</html>
