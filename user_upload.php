<?php

class UserUploader
{
    private $pdo;

    public function __construct(PDO $pdo)
    {
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

        if (!file_exists($filename) || !is_readable($filename)) {
            die("Error: CSV file '$filename' does not exist or is not readable." . PHP_EOL);
        }

        if (($handle = fopen($filename, "r")) !== false) {
            fgetcsv($handle, 1000, ",");
            while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                $csvData[] = $data;
            }
            fclose($handle);
        } else {
            die("Error opening CSV file: $filename" . PHP_EOL);
        }

        return $csvData;
    }

    public function insertData(array $data)
    {
        $count = 0;
        try {
            $stmt = $this->pdo->prepare("INSERT INTO users(name, surname, email) VALUES (:name, :surname, :email)");

            foreach ($data as $row) {
                $name = $this->sanitizeName($row[0]);
                $surname = $this->sanitizeName($row[1]);
                $email = strtolower(trim($row[2]));

                if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    if ($this->isEmailUnique($email)) {
                        $stmt->bindParam(':name', $name);
                        $stmt->bindParam(':surname', $surname);
                        $stmt->bindParam(':email', $email);
                        $stmt->execute();
                        echo "Data inserted successfully" . PHP_EOL;
                        $count++;
                    } else {
                        echo "Email already exists: $email. Skipping" . PHP_EOL;
                    }
                } else {
                    echo "Invalid email: $email. Skipping" . PHP_EOL;
                }
            }
            echo $count . " rows inserted" . PHP_EOL;
        } catch (PDOException $e) {
            die("Data Insertion failed:" . $e->getMessage());
        }
    }

    public function isEmailUnique(string $email) : bool
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM users WHERE email = :email");

        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $count = $stmt->fetchColumn();
        return $count === 0;
    }

    //used for separating surnames with apostrophes and removing special chars
    public function sanitizeName(string $name ) :string
    {
        $nameParts = explode("'", $name);
        $sanitizedParts = array_map(
            function ($part) {
                return ucfirst(preg_replace('/[^A-Za-z]/', '', strtolower(trim($part))));
            },
            $nameParts
        );

        return implode("'", $sanitizedParts);
    }

    public function run(array $options)
    {
        if (isset($options['create_table'])) {
            $this->createTable();
            exit;
        }

        $filename = $options['file'] ?? '';

        if (empty($filename)) {
            die("Error: Please provide a CSV file name" . PHP_EOL);
        }
        $this->pdo->exec("USE users_db");

        $data = $this->parseCSV($filename);

        if (empty($data)) {
            die("Error: Failed to parse CSV file. Please try again" . PHP_EOL);
        }

        if (isset($options['dry_run'])) {
            echo "Dry run mode, No data will be inserted into DB" . PHP_EOL;
        } else {
            $this->insertData($data);
        }
    }
}

$options = getopt("u:p:h:", ["file:", "create_table", "dry_run", "help"]);

if (isset($options['help'])) {
    echo "Usage: php user_upload.php --file [csv file name] --create_table --dry_run -u [MySQL username] -p [MySQL password] -h [MySQL host] --help" . PHP_EOL;
    exit;
}

// Database Connection
$host = $options['h'] ?? '127.0.0.1';
$username = $options['u'] ?? 'root';
$password = $options['p'] ?? '';

try {
    $pdo = new PDO("mysql:host=$host", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Script Execution
    $userUploader = new UserUploader($pdo);
    $userUploader->run($options);
} catch (PDOException $e) {
    die("Error connecting to MySQL: " . $e->getMessage() . PHP_EOL);
}
