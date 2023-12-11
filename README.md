# User Upload Script

This PHP script processes a CSV file containing user data and inserts it into a MySQL database.

## Prerequisites

- PHP 8.1.x
- MySQL database server (version 5.7 or higher, including MariaDB 10.x)
- Composer (for installing PHPUnit)

## Setup

1. **Clone the Repository:**

    ```bash
    git clone [repository_url]
    ```

2. **Navigate to the Project Directory:**

    ```bash
    cd catalystchallenge
    ```

3. **Install Composer Dependencies:**

    ```bash
    composer install
    ```

## Usage

### Command Line Directives:

- `--file [csv file name]`: Name of the CSV file to be parsed.
- `--create_table`: Create or rebuild the MySQL users table (no further action will be taken).
- `--dry_run`: Run the script but do not insert data into the DB (use with `--file` directive).
- `-u`: MySQL username.
- `-p`: MySQL password.
- `-h`: MySQL host.
- `--help`: Output command line directive details.

### Examples:

1. **Create or Rebuild Users Table:**

    ```bash
    php user_upload.php --create_table -h [host] -u [username] -p [password]
	//or use default sql  credentials(127.0.0.1, root, )
	php user_upload.php --create_table
    ```

2. **Insert Data into Users Table:**

    ```bash
    php user_upload.php --file users.csv
    ```

3. **Dry Run (No Data Insertion):**

    ```bash
    php user_upload.php --file users.csv --dry_run
    ```

4. **Display Help:**

    ```bash
    php user_upload.php --help
    ```

## Running Tests

You can see from I code I was originally planning on writing phpunit tests for the upload script however I ran into problems when trying to do so.

PHPUnit executes the whole file and this includes getopt(), I struggled to find a way to handle getopt. Researching online, I only came across this:
https://stackoverflow.com/questions/11327167/phpunit-and-getopt

Implementing this solution would have overly complicated the environment setup, so I opted to omit it from the testing process.

## Notes

- The CSV file should be in the same directory as the script.
- Adjust MySQL username, password, and other options accordingly.

