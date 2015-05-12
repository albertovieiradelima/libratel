<?php

namespace App\Model\AbrasceAward;

/**
 * Award Event Field Registration Model
 *
 * @author Renato Peterman <renato.peterman@crmall.com>
 */
class AwardEventFieldRegistrationModel extends \App\Model\BaseModel {

    protected $tableName = 'aa_award_event_field_registration';
    protected $fields = array(
        'fk_award_event_field' => 'i',
        'fk_registration' => 'i',
        'value' => 's'
    );

    /**
     * Get by id
     */
    public function getById($fk_award_event_field = null, $fk_registration = null) {
        $sql = "SELECT * FROM {$this->tableName} t WHERE t.fk_award_event_field={$fk_award_event_field} AND t.fk_registration={$fk_registration}";
        $this->query($sql);
        return $this->fetch();
    }

    /**
     * Get by fk_award_event_field
     */
    public function getByAwardEventField($fk_award_event_field = null) {
        $sql = "SELECT * FROM {$this->tableName} t WHERE t.fk_award_event_field={$fk_award_event_field}";
        $this->query($sql);
        return $this->fetch_all();
    }

    /**
     * Get all
     */
    public function getAll() {
        $sql = "SELECT * FROM {$this->tableName}";
        $this->query($sql);
        return $this->fetch_all();
    }

    /**
     * Get all by
     */
    public function getAllBy($data) {
        $sql = "SELECT * FROM {$this->tableName}";
        $this->query($sql);
        return $this->fetch_all();
    }
    
    public function getAllForDT($exclude = null) {
        $sql = "SELECT * FROM {$this->tableName}";
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
     * Set value
     * @param $fk_award_event_field
     * @param $fk_registration
     * @param $value
     */
    public function setValue($fk_award_event_field, $fk_registration, $value){

        if(!$fk_award_event_field || !$fk_registration){
            throw new \Exception('Invalid parameters');
        }

        $this->query("SELECT * FROM {$this->tableName} WHERE fk_award_event_field={$fk_award_event_field} AND fk_registration={$fk_registration}");
        if($this->rows() > 0){
            return $this->update(array('value' => $value), $fk_award_event_field, $fk_registration);
        }else{
            return $this->insert(array(
                'fk_award_event_field'  => $fk_award_event_field,
                'fk_registration'       => $fk_registration,
                'value'                 => $value
            ));
        }
    }

    public function getValues($fk_registration){

        $sql = "SELECT * FROM {$this->tableName} WHERE fk_registration={$fk_registration}";
        $this->query($sql);
        $list = $this->fetch_all();

        if($list && count($list) > 0){
            $returnArray = array();
            foreach($list as $obj){
                $returnArray[ $obj['fk_award_event_field'] ] = $obj['value'];
            }
            return $returnArray;
        }

        return null;
    }

    /**
     * Insert new record
     */
    public function insert($data) {

        if(!$data || !is_array($data)){
            throw new \Exception('Invalid param - param data must by an associative array');
        }
        
        # Escape values
        $this->escapeValues($data, $this->fields);
        
        # stmt
        $sql = "INSERT INTO {$this->tableName} (fk_award_event_field, fk_registration, value) VALUES ({$data['fk_award_event_field']}, {$data['fk_registration']}, '{$data['value']}')";
        $this->query($sql);
        
        return $this->insert_id();
    }

    /**
     * Update record
     */
    public function update($data, $fk_award_event_field, $fk_registration) {

        if(!$data || !is_array($data)){
            throw new \Exception('Invalid param - param data must by an associative array');
        }
        
        # Escape values
        $this->escapeValues($data, $this->fields);

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
        
        $sql = "UPDATE {$this->tableName} SET {$st} WHERE fk_award_event_field={$fk_award_event_field} AND fk_registration={$fk_registration}";
        
        return $this->query($sql);
    }

    /**
     * Delete record
     */
    public function delete($fk_award_event_field, $fk_registration) {

        $sql = "DELETE FROM {$this->tableName} WHERE fk_award_event_field={$fk_award_event_field} AND fk_registration={$fk_registration}";

        return $this->query($sql);
    }

    /**
     * Delete record
     */
    public function deleteByAwardEventField($fk_award_event_field) {

        $sql = "DELETE FROM {$this->tableName} WHERE fk_award_event_field={$fk_award_event_field}";

        return $this->query($sql);
    }

}