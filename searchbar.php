<div class="navbar_search">
    <form action="search.php" method="get">
        <input type="text" name="search" placeholder="Hledání restaurací">
        <button type="submit">
            <span class="material-symbols-outlined">search</span>
        </button>
    </form>
</div>


<style>

    .navbar_search {
        display: flex;
        justify-content: center;
        margin: 20px auto 10px;
        max-width: 100%;
        padding: 0 30px;
    }

    .navbar_search form {
        display: flex;
        align-items: center;
        background-color: white;
        border-radius: 10px;
        padding: 6px 10px;
        border: 1px solid black;
        width: 100%;
        max-width: 400px;
    }

    .navbar_search input[type="text"] {
        border: none;
        outline: none;
        padding: 10px;
        font-size: 16px;
        flex-grow: 1;
        background-color: transparent;
        color: #333;
    }

    .navbar_search button {
        background-color: forestgreen;
        border: none;
        padding: 8px 12px;
        border-radius: 6px;
        margin-left: 8px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background 0.3s ease;
    }

    .navbar_search button:hover {
        background-color: darkgreen;
    }

    .navbar_search .material-symbols-outlined {
        color: white;
        font-size: 22px;
    }

    @media (max-width: 768px) {
        .search-wrapper {
            width: 100% !important;
            padding: 10px;
            box-sizing: border-box;
        }

        input[type="text"] {
            width: 100%;
            font-size: 1.1em;
            margin-bottom: 10px;
        }

        .vysledky, .vysledek {
            width: 100% !important;
            flex-direction: column;
            gap: 12px;
        }

        .vysledek img {
            width: 100%;
            height: auto;
        }
    }


</style>