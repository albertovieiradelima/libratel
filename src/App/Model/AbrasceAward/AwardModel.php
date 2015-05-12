<?php

namespace App\Model\AbrasceAward;

/**
 * Award Model
 *
 * @author Renato Peterman <renato.peterman@crmall.com>
 */
class AwardModel extends \App\Model\BaseModel {

    protected $tableName = 'aa_award';
    protected $fields = array('name' => 's','description' => 's','code' => 's','inactive' => 'b');

    /**
     * Get award by id
     */
    public function getById($id) {
        $sql = "SELECT * FROM {$this->tableName} t WHERE t.id={$id}";
        $this->query($sql);
        return $this->fetch();
    }

    /**
     * Get award
     */
    public function getAll() {
        $sql = "SELECT * FROM {$this->tableName}";
        $this->query($sql);
        return $this->fetch_all();
    }

    public function getAllNameActive() {
        $sql = "SELECT name FROM {$this->tableName} WHERE inactive = 0";
        $this->query($sql);
        return $this->fetch_all();
    }
    
    public function getAllForDT($exclude = null) {
        $sql = "SELECT id, name, description, code, inactive FROM {$this->tableName}";
        $this->query($sql);
        $list = $this->fetch_all();
        
        $retorno = array();
        foreach($list as $obj){
            $el = array();
            foreach($obj as $key => $val){
                if($exclude !== null && is_array($exclude) && in_array($key, $exclude)){
                    continue;
                }
                
                if($key == 'inactive'){
                    if($val == true){
                        $val = 'Inativo';
                    }else{
                        $val = 'Ativo';
                    }
                }
                
                $el[] = $val;
            }
            $retorno[] = $el;
        }
        return $retorno;
    }

    /**
     * Add a new award
     */
    public function insert($data) {

        if(!$data || !is_array($data)){
            throw new \Exception('Invalid param - param data must by an associative array');
        }
        
        # Escape values
        $this->escapeValues($data, $this->fields);
        
        if(!$data['inactive']){
            $data['inactive'] = 0;
        }
        
        # stmt
        $sql = "INSERT INTO {$this->tableName} (name, description, code, inactive) VALUES ('{$data['name']}', '{$data['description']}', '{$data['code']}', {$data['inactive']})";
        $this->query($sql);
        
        return $this->insert_id();
    }

    /**
     * Edit a award
     */
    public function update($data, $id) {

        if(!$data || !is_array($data)){
            throw new \Exception('Invalid param - param data must by an associative array');
        }
        
        # Escape values
        $this->escapeValues($data, $this->fields);
        
        # Check for inactive parameter
        if(!$data['inactive']){
            $data['inactive'] = 0;
        }
        
        $st = '';
        $first = true;
        foreach($data as $key => $val){
            
            if($key != 'id'){
                
                if($first){
                   $first = false; 
                }else{
                   $st .= ","; 
                }

                $type = $this->fields[$key];
                if($type == 's'){
                    $st .= "{$key} = '{$val}'";
                }else{
                    $st .= "{$key} = {$val}";
                }
                
            }
            
        }
        
        $sql = "UPDATE {$this->tableName} SET {$st} WHERE id = {$id}";
        
        return $this->query($sql);
    }

    /**
     * Delete a award
     */
    public function delete($id) {

        $sql = "DELETE FROM {$this->tableName} WHERE id = {$id}";

        return $this->query($sql);
    }

}