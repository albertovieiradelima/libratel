<?php

namespace App\Model;

/**
 * Base Model
 *
 * @author Anderson Costa <arcostasi@gmail.com>
 */
class BaseModel {

    protected $db;
    protected $result;
    protected $stmt;

    /**
     * Constructor
     * @param MySQLi Provider
     */
    function __construct($db) {

        $this->db = $db;
    }

    /**
     * SQL Query
     */
    public function query($sql) {

        if ($this->db->connect_errno) {
            throw new \Exception($this->db->connect_error);
        }

        $this->result = $this->db->query($sql);

        if (!$this->result) {
            throw new \Exception('Query failed: ' . $sql);
        }

        return $this->result;
    }

    /**
     * Fetch Array
     * @param $resultType = MYSQLI_NUM, MYSQLI_ASSOC, MYSQLI_BOTH
     */
    public function fetch($resultType = MYSQLI_ASSOC) {

        return $this->result->fetch_array($resultType);
    }

    /**
     * Fetch All
     * @param $resultType = MYSQLI_NUM, MYSQLI_ASSOC, MYSQLI_BOTH
     */
    public function fetch_all($resultType = MYSQLI_ASSOC) {

        return $this->result->fetch_all($resultType);
    }

    /**
     * Fetch Object
     */
    public function fetch_object() {

        return $this->result->fetch_object();
    }

    /**
     * SQL Rows
     */
    public function rows() {

        while ($row = $this->result->fetch_assoc());

        return $this->result->num_rows;
    }

    /**
     * SQL Result
     */
    public function getResult() {

        return $this->result;
    }

    /**
     * Result close
     */
    public function close() {

        return $this->result->close();
    }

    /**
     * Get DateTime
     */
    public function getDateTime() {
        $old = setlocale(LC_ALL, null);
        setlocale(LC_ALL, 'pt_BR', 'ptb');

        $datetime = date('Y-m-d\TH:i:s');

        setlocale(LC_ALL, $old);
        return $datetime;
    }

    /**
     * Convert DateTime
     */
    public function convertDateTime($time) {
        $old = setlocale(LC_ALL, null);

        setlocale(LC_ALL, 'pt_BR', 'ptb');
        $datetime = date('Y-m-d\TH:i:s', $time);

        setlocale(LC_ALL, $old);
        return $datetime;
    }
    
    public function errno(){
        return $this->db->errno;
    }
    
    public function error(){
        return $this->db->error;
    }
    
    public function escapeString($string){
        return $this->db->real_escape_string(stripslashes($string));
    }
    
    public function beginTransaction(){
        $this->db->autocommit(false);
    }
    
    public function commit(){
        $this->db->commit();
    }
    
    public function rollback(){
        $this->db->rollback();
    }
    
    /**
     * Escape all values
     * @param type $data
     * @param type $fields
     * @return type
     */
    public function escapeValues(&$data, $fields){
        
        foreach($data as $key => $val){
            $type = $fields[$key];
            if($type == 's'){
                $data[$key] = $val ? $this->escapeString($val) : ""; # Validate and escape
            }
        }

    }
    
    /**
     * Get last inserted id
     */
    public function insert_id(){
        return $this->db->insert_id;
    }
    
    /**
     * Prepared statement function
     * @param type $sql
     * @return type
     * @throws \Exception
     */
    public function prepare($sql){
        
        if ($this->db->connect_errno) {
            throw new \Exception($this->db->connect_error);
        }

        $this->stmt = $this->db->prepare($sql);

        if (!$this->stmt) {
            throw new \Exception('Prepared statement failed: ' . $this->db->error);
        }

        return $this->stmt;
        
    }
    
    /**
     * Execute prepared statement
     * @param type $sql
     * @return type
     * @throws \Exception
     */
    public function execute(){
        
        if (!$this->stmt->execute()) {
            throw new \Exception('Query failed: ' . $this->db->error);
        }
        
        $this->result = $this->stmt->get_result();
        return $this->result;
        
    }

}