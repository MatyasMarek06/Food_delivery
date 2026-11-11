<?php

class connector
{
    private $conn;

    public function __construct($host, $user, $password, $dbname)
    {
        try {
            $this->conn = new mysqli($host, $user, $password, $dbname);
            if ($this->conn->connect_error) {
                throw new Exception("Chyba vytvoření spojení s databází: " . $this->conn->connect_error);
            }
        } catch (Exception $e) {
            die("Chyba: " . $e->getMessage());
        }
    }

    public function query($stmt)
    {
        try {
            $result = $this->conn->query($stmt);
            if (!$result) {
                throw new Exception("Chyba dotazu na databázi: " . $this->conn->error);
            }
            return $result;
        } catch (Exception $e) {
            echo "Chyba: " . $e->getMessage();
            return false;
        }
    }

    public function prepare($stmt)
    {
        try {
            $preparedStmt = $this->conn->prepare($stmt);
            if (!$preparedStmt) {
                throw new Exception("Chyba přípravy dotazu: " . $this->conn->error);
            }
            return $preparedStmt;
        } catch (Exception $e) {
            echo "Chyba: " . $e->getMessage();
            return false;
        }
    }

    public function getConnection()
    {
        return $this->conn;
    }

    public function __destruct()
    {
        if ($this->conn) {
            $this->conn->close();
        }
    }
}

$db = new connector("", "", "", "");
?>
