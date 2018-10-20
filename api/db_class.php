<?php

/* * ********************************************************************
 *
 *  Database functions
 *  ------------------
 *  MySQL queries are delegated to these functions for parameterization.
 *  These functions also add an extra layer of abstraction in case we
 *  need to optimize our database query execution approach.
 *
 * ******************************************************************** */

require_once('vah_class.php');
require_once('db_connection.php');

class db_class {

    private $conn = FALSE; // This is replaced with database connection only when it is needed.

    private function db_connect() {
        if ($this->conn === FALSE) {
            $this->conn = mysqli_connect(DB_SERVER, DB_SERVER_USERNAME, DB_SERVER_PASSWORD, DB_DATABASE) or die('Error connecting to database.');
            $this->conn->set_charset("utf8");
        }
    }

// **** SQL functions ****
    public function select($sql = "") {
        $this->db_connect();
        if (empty($sql)) {
            return false;
        }
        $data = array();
        $results = @mysqli_query($this->conn, $sql);
        if ((!$results) or ( empty($results))) {
            return false;
        }
        if (mysqli_num_rows($results) > 0) {
            $count = 0;
            while ($row = mysqli_fetch_assoc($results)) {
                $data[$count] = $row;
                $count++;
            }
        }
        mysqli_free_result($results);
        //db_close();
        return $data;
    }

    public function selectArray($sql, $field) {
        if (empty($sql)) {
            return false;
        }
        $data = array();
        $results = @mysqli_query($this->conn, $sql);
        if ((!$results) or ( empty($results))) {
            return false;
        }
        if (mysqli_num_rows($results) > 0) {
            while ($row = mysqli_fetch_array($results)) {
                $data[] = $row[$field];
            }
        }
        mysqli_free_result($results);
        return $data;
    }

    public function selectassocArray($sql, $key, $value) {
        if (empty($sql)) {
            return false;
        }
        $data = array();
        $results = @mysqli_query($this->conn, $sql);
        if ((!$results) or ( empty($results))) {
            return false;
        }
        if (mysqli_num_rows($results) > 0) {
            while ($row = mysqli_fetch_array($results)) {
                $pid = $row[$key];
                $data[$pid] = $row[$value];
            }
        }
        mysqli_free_result($results);
        return $data;
    }

    public function selectassoc_multiArray($sql, $key, $value) {
        if (empty($sql)) {
            return false;
        }
        $data = array();
        $results = @mysqli_query($this->conn, $sql);
        if ((!$results) or ( empty($results))) {
            return false;
        }
        if (mysqli_num_rows($results) > 0) {
            while ($row = mysqli_fetch_array($results)) {
                $pid = $row[$key];
                $uid = $row[$value];
                $data[$pid][] = $uid;
            }
        }
        mysqli_free_result($results);
        return $data;
    }

    public function selectBatch($sql = "", $field = "") {
        if (empty($sql)) {
            return false;
        }
        $results = @mysqli_query($this->conn, $sql);
        if ((!$results) or ( empty($results))) {
            return false;
        }
        $formattedArr = array();
        if (mysqli_num_rows($results) > 0) {
            while ($row = mysqli_fetch_array($results)) {
                $keysArr = (array_keys($row));
                if (count($keysArr) > 0) {
                    foreach ($keysArr as $mykey) {
                        $formattedArr[$row[$field]][$mykey] = $row[$mykey];
                    }
                }
            }
        }
        mysqli_free_result($results);
        return $formattedArr;
    }

    public function selectBatcharray($sql = "", $field = "") {
        if (empty($sql)) {
            return false;
        }
        $results = @mysqli_query($this->conn, $sql);
        if ((!$results) or ( empty($results))) {
            return false;
        }
        $formattedArr = array();
        if (mysqli_num_rows($results) > 0) {
            while ($row = mysqli_fetch_array($results)) {
                $formattedArr[$row[$field]][] = $row;
            }
        }
        mysqli_free_result($results);
        return $formattedArr;
    }

    public function insert($sql = "") {
        $this->db_connect();
        if (empty($sql)) {
            return false;
        }
        mysqli_query($this->conn, $sql);
        $id = mysqli_insert_id($this->conn);
        return $id;
    }

    public function update($sql = "") {
        $this->db_connect();
        if (empty($sql)) {
            return false;
        }
        mysqli_query($this->conn, $sql);
        return mysqli_affected_rows($this->conn);
    }

    public function delete($sql = "") {
        $this->db_connect();
        if (empty($sql)) {
            return false;
        }
        mysqli_query($this->conn, $sql);
        return mysqli_affected_rows($this->conn);
    }

    // Explode string with multiple delimiters
    public function multiexplode($delimiters, $string) {
        $ready = str_replace($delimiters, $delimiters[0], $string);
        $launch = explode($delimiters[0], $ready);
        return $launch;
    }

}

$DB = new db_class();
?>