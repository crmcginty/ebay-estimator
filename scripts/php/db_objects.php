<?php
class DB {
	// Database connection - static scope for persistance
    protected static $connection;

    /**
     * Connect to the database
     * ------------------------
     * @return bool false on failure / PDO object instance on success
     */
    public function connect() {    
        // Try and connect to the database
        if(!isset(self::$connection)) {
            // Load configuration as an array
            $config = parse_ini_file('../../../../config/config_comics.ini');
            self::$connection = new PDO("mysql:host=".$config["host"].";dbname=".$config["dbname"].";charset=utf8mb4",$config['username'],$config['password']);
        }

        // If connection was not successful, handle the error
        if(self::$connection === false) {
            // Handle error - notify administrator, log to a file, show an error screen, etc.
            return false;
        }
        return self::$connection;
    }

    /**
     * Query the database
     *
     * @param $query The query string
     * @return mixed The result of the PDO fetchAll function
     */
    public function query($query) {
        // Connect to the database
        $connection = $this -> connect();

        // Query the database
        $prep = $connection -> prepare($query);
        $prep -> execute();
        $result = $prep -> fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    /**
     * Insert
     *
     * @param $query The query string
     */
    public function insert($query) {
        // Connect to the database
        $connection = $this -> connect();

        $prep = $connection -> prepare($query);
        $prep -> execute();
    }    

    /**
     * Fetch rows from the database (SELECT query)
     *
     * @param $query The query string
     * @return bool False on failure / array Database rows on success
     */
    public function select($query) {
        $rows = array();
        $result = $this -> query($query);
        if($result === false) {
            return false;
        }
        while ($row = $result -> fetch_assoc()) {
            $rows[] = $row;
        }
        return $rows;
    }      
}
?>