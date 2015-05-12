<?php

namespace App\Model;

/**
 * Supplier Model
 *
 * @author Anderson Costa <arcostasi@gmail.com>
 * @author Victor Hugo Martins <victorhugo.pmartins@gmail.com>
 */
class SupplierModel extends BaseModel {

    /**
     * Get Supplier All
     */
    public function getSupplierAll() {

        $sql = "SELECT id_fornecedor, fantasia, localidade, estado, telefone, site, logo
                FROM supplier
                ORDER BY fantasia";

        return $this->query($sql);
    }

    /**
     * Get Supplier by A-Z
     */
    public function getSupplierByAZ($letter) {

        $sql = "SELECT id_fornecedor, fantasia, localidade, estado, telefone, logo
                FROM supplier
                WHERE fantasia LIKE '{$letter}%'
                ORDER BY fantasia";

        return $this->query($sql);
    }

    /**
     * Get Supplier by category
     */
    public function getSupplierByCategory($id) {

        $sql = "SELECT id_fornecedor, fantasia, localidade, estado, telefone, logo
                FROM supplier
                WHERE fk_categoria_fornecedor LIKE '%#{$id}#%'
                ORDER BY fantasia";

        return $this->query($sql);
    }

    /**
     * Get Supplier by Id
     */
    public function getSupplierById($id) {

        $sql = "SELECT id_fornecedor, fantasia, localidade, estado, telefone, site, descricao_fornecedor, logo
                FROM supplier
                WHERE id_fornecedor = {$id}";

        return $this->query($sql);
    }

    /**
     * Get all categories
     */
    public function getSupplierCategoryAll() {

        $sql = "SELECT id_categoria_fornecedor, descricao
                FROM supplier_category";

        return $this->query($sql);
    }

    /**
     * Get Supplier Contacts by Id
     */
    public function getSupplierContactById($id) {

        $sql = "SELECT contato 
                FROM supplier_contact
                WHERE fk_cliente = {$id} LIMIT 0,1";

        return $this->query($sql);
    }

    /**
     * Get Supplier Logo by Id
     */
    public function getSupplierLogoById($id) {

        $sql = "SELECT logo
                FROM supplier
                WHERE id_fornecedor = {$id}";

        return $this->query($sql);
    }
    
    /**
     * Remove all shoppings records
     */
    public function removeAllSupplier(){
        $sql = "DELETE FROM supplier";
        return $this->query($sql);
    }
    
    /**
     * Remove all shoppings administrators records
     */
    public function removeAllSupplierCategory(){
        $sql = "DELETE FROM supplier_category";
        return $this->query($sql);
    }

    /**
     * Remove all shoppings administrators records
     */
    public function removeSupplierCategoryById($id_category){
        $sql = "DELETE FROM supplier_category WHERE id_categoria_fornecedor = {$id_category}";
        return $this->query($sql);
    }
    
    /**
     * Remove all shoppings entertainments
     */
    public function removeAllSupplierContact(){
        $sql = "DELETE FROM supplier_contact";
        return $this->query($sql);
    }
    
    /**
     * Insert new supplier
     * @param array $data
     */
    public function insertSupplier($data){
        
        if(!$data || !is_array($data)){
            throw new \Exception('Invalid param - param data must by an associative array');
        }
        
        # Fields
        $fieldsToValidade = array('FANTASIA','LOCALIDADE','ESTADO','TELEFONE','SITE', 'DESCRICAO_FORNECEDOR');
        
        # Validate
        foreach($data as $key => $val){
            if(in_array($key, $fieldsToValidade)){
                $data[$key] = $val ? $this->escapeString($val) : ""; # Validate and escape
            }
        }
        
        $data['FK_CATEGORIA_FORNECEDOR'] = $data['FK_CATEGORIA_FORNECEDOR'] ? $data['FK_CATEGORIA_FORNECEDOR'] : 'NULL';
        
        $sql = "INSERT INTO supplier(id_fornecedor, fantasia, localidade, estado, telefone, site, descricao_fornecedor, fk_categoria_fornecedor, logo)"
                . " VALUES({$data['ID_FORNECEDOR']}, '{$data['FANTASIA']}', '{$data['LOCALIDADE']}', '{$data['ESTADO']}', '{$data['TELEFONE']}', '{$data['SITE']}', '{$data['DESCRICAO_FORNECEDOR']}', '{$data['FK_CATEGORIA_FORNECEDOR']}', '{$data['LOGO']}')";
        
        return $this->query($sql);
    }
    
    /**
     * Insert supplier category
     * @param array $data
     */
    public function insertCategory($data){
        
        if(!$data || !is_array($data)){
            throw new \Exception('Invalid param - param data must by an associative array');
        }
        
        # Validate and insert
        $data['DESCRICAO'] = $data['DESCRICAO'] ? $this->escapeString($data['DESCRICAO']) : "";
        $sql = "INSERT INTO supplier_category(id_categoria_fornecedor, descricao) VALUES({$data['ID_CATEGORIA_FORNECEDOR']}, '{$data['DESCRICAO']}')";
        return $this->query($sql);
    }
    
    /**
     * Insert supplier category
     * @param array $data
     */
    public function insertSupplierContact($supplier, $data){
        
        if(!$data || !is_array($data)){
            throw new \Exception('Invalid param - param data must by an associative array');
        }
        
        # Validate and insert
        $data['CONTATO_PRINCIPAL'] = $data['CONTATO_PRINCIPAL'] ? $this->escapeString($data['CONTATO_PRINCIPAL']) : "";
        $sql = "INSERT INTO supplier_contact(id_contato, fk_cliente, contato) VALUES({$data['ID_CLIENTE']}, {$supplier},'{$data['CONTATO_PRINCIPAL']}')";
        return $this->query($sql);
    }

}