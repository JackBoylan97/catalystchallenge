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

    public function insertData(array $data){
        try{
            $stmt = $this->pdo->prepare("INSERT INTO users(name, surname, email) VALUES (:name, :surname, :email)");

            foreach ($data as $row) {
                $name = ucfirst(strtolower(trim($row[0])));
                $surname = ucfirst(strtolower(trim($row[1])));
                $email = strtolower(trim($row[2]));

                if(filter_var($email, FILTER_VALIDATE_EMAIL)){
                    $stmt->bindParam(':name', $name);
                    $stmt->bindParam(':surname', $surname);
                    $stmt->bindParam(':email', $email);
                    $stmt->execute();
                }else{
                    echo "Invalid email: $email. Skipping". PHP_EOL;
                }
            }
            echo "Data inserted successfully" . PHP_EOL;
        }catch (PDOException $e){
            die("Data Insertion failed:" . $e->getMessage());
        }
    }

    public function run(array $options)
    {
        if(isset($options['create_table'])){
            $this->createTable();
            exit;
        }

        $filename = $options['file'] ?? ' ';

        if(empty($filename)){
            echo "Please provide a CSV file name" . PHP_EOL;
            exit;
        }

        $data = $this->parseCSV($filename);

        if(empty($data)){
            echo "Failed to parse CSV file. Please try again" . PHP_EOL;
            exit;
        }

        if(isset($options['dry_run'])){
            echo "Dry run mode, No data will be inserted into DB" . PHP_EOL;
        } else{
            $this->insertData($data);
        }

    }
}