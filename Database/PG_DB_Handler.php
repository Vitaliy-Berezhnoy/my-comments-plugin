<?php

class PG_DB_Handler {
    private $connection;

    public function __construct() {
        $this->connect();
    }
    private function connect() {
        $connection_string = sprintf(
            "host=%s port=%s dbname=%s user=%s password=%s",
            PG_DB_HOST,
            PG_DB_PORT,
            PG_DB_NAME,
            PG_DB_USER,
            PG_DB_PASSWORD
        );
        $this->connection = pg_connect($connection_string);

        if (!$this->connection) {
            error_log('PostgreSQL connection failed: ' . pg_last_error());
            return false;
        }
        return true;
    }

    public function query($sql, $params=[]) {
        $result = pg_query_params($this->connection, $sql, $params);

        if (!$result) {
            error_log('PostgreSQL query failed: ' . pg_last_error($this->connection));
            return false;
        }
        
        return $result;
    }

    public function fetch_all($result) {
        return pg_fetch_all($result);
    }

    public function close() {
        pg_close($this->connection);
    }
}