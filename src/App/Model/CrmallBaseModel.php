<?php

namespace App\Model;

/**
 * CRMALL Base Model
 *
 * @author Anderson Costa <arcostasi@gmail.com>
 */
class CrmallBaseModel {

    protected $db;
    protected $result;
    protected $rows;

    /**
     * Constructor
     * @param OCI8 Provider
     */
    function __construct($db) {

        $this->db = $db;
    }

    /**
     * SQL Query
     */
    public function query($sql) {

        $this->result = oci_parse($this->db, $sql);

        // Executes a statement
        oci_execute($this->result);

        return $this->result;
    }

    /**
     * Fetch Array
     * @param $resultType = OCI_NUM, OCI_ASSOC, OCI_BOTH
     */
    public function fetch($resultType = OCI_ASSOC) {

        return oci_fetch($this->result);
    }

    /**
     * Fetch All
     * @param $resultType = OCI_NUM, OCI_ASSOC, OCI_BOTH
     */
    public function fetch_all($resultType = OCI_ASSOC) {

        $result = array();

        $this->rows = oci_fetch_all($this->result, $result, null, null, OCI_FETCHSTATEMENT_BY_ROW);

        return $result;
    }

    /**
     * Fetch Object
     */
    public function fetch_object() {

        return oci_fetch_object($this->result);
    }

    /**
     * SQL Rows
     */
    public function rows() {

        return $this->rows;
    }

    /**
     * Result close
     */
    public function close() {

        oci_free_statement($this->result);

        return oci_close($this->db);
    }
    
    /**
     * Save blob to file
     * @param type $blob
     * @param type $file
     */
    public function saveBlobToFile($blob, $file){
        
        if(!$blob){
            throw new \Exception('Invalid blob paramter');
        }
        
        if(!$file){
            throw new \Exception('Invalid file paramter');
        }
        
        if(!method_exists($blob, 'export')){
            throw new \Exception('Not a OCI-Lob object (export method not found)');
        }
        
        return $blob->export($file);
        
    }
    
}