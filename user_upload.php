<?php

class UserUploader
{
    public function __construct(PDO $pdo){
        $this->pdo = $pdo;
    }

    public function createTable(string $dbName = 'users_db')
    {
        try {
            $this->pdo->exec("CREATE DATABASE IF NOT EXISTS $dbName");
            $this->pdo->exec("USE $dbName");
            $this->pdo->exec("CREATE TABLE IF NOT EXISTS users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(50) NOT NULL,
                surname VARCHAR(50) NOT NULL,
                email VARCHAR(100) NOT NULL UNIQUE
            )");
            echo "Table created successfully." . PHP_EOL;
        } catch (PDOException $e) {
            die("Table creation failed: " . $e->getMessage());
        }
    }

    public function parseCSV(string $filename): array
    {
        $csvData = [];
        if (($handle = fopen($filename, "r")) !== false) {
            while(($data = fgetcsv($handle,1000,",")) !== false);{
                $csvData[] = $data;
            }
            fclose($handle);
        }
        return $csvData;
    }
}