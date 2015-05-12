<?php

namespace App\Model\AbrasceAward;

/**
 * Award Event Model
 *
 * @author Renato Peterman <renato.peterman@crmall.com>
 */
class AwardEventModel extends \App\Model\BaseModel {

    protected $tableName = 'aa_award_event';
    protected $fields = array(
        'fk_award'                  => 'i',
        'fk_event'                  => 'i',
        'title'                     => 's',
        'description'               => 's',
        'registration_date_begin'   => 's',
        'registration_date_end'     => 's',
        'billing_days_to_due'       => 'i',
        'registration_price'        => 'd',
        'banner'                    => 's',
        'logo'                      => 's'
    );

    /**
     * Get by id
     */
    public function getById($fk_award = null, $fk_event = null) {

        if(!$fk_award && !$fk_event){
            throw new \Exception("Primary keys not defined");
        }

        $sql = "SELECT * FROM {$this->tableName} t WHERE fk_award={$fk_award} AND fk_event={$fk_event}";
        $this->query($sql);

        return $this->fetch();
    }

    /**
     * Get list
     */
    public function getAll($fk_event = null) {
        if($fk_event === null){
            $sql = "SELECT * FROM {$this->tableName}";
        }else{
            $sql = "SELECT * FROM {$this->tableName} WHERE fk_event={$fk_event}";
        }
        $this->query($sql);
        return $this->fetch_all();
    }

    /**
     * Get list
     */
    public function getAllActive($fk_event = null) {
        if($fk_event === null){
            $sql = "SELECT * FROM {$this->tableName} ae JOIN aa_award a ON a.id = ae.fk_award WHERE a.inactive = 0";
        }else{
            $sql = "SELECT * FROM {$this->tableName} ae JOIN aa_award a ON a.id = ae.fk_award WHERE a.inactive = 0 AND fk_event={$fk_event}";
        }
        $this->query($sql);
        return $this->fetch_all();
    }

    /**
     * Get data order by id
     */
    public function getOrderById($fk_award = null, $fk_event = null) {

        if(!$fk_award && !$fk_event){
            throw new \Exception("Primary keys not defined");
        }

        $sql = "SELECT title, billing_days_to_due, registration_price, registration_date_end FROM {$this->tableName} t WHERE fk_award={$fk_award} AND fk_event={$fk_event}";
        $this->query($sql);

        return $this->fetch();
    }

    /**
     * Get data for datatables
     *
     * @param null $exclude fields to exclude from return
     * @param null $fk_event fk
     * @return array
     * @throws \Exception
     */
    public function getAllForDT($exclude = null, $fk_event = null) {

        $sql = "SELECT fk_award, fk_event, title, registration_date_begin, registration_date_end, billing_days_to_due, registration_price FROM {$this->tableName} t";

        if($fk_event !== null){
            $sql .= " WHERE t.fk_event={$fk_event}";
        }
        
        $this->query($sql);
        $list = $this->fetch_all();
        
        $retorno = array();
        foreach($list as $obj){
            $el = array();
            foreach($obj as $key => $val){

                if($key == 'fk_award' || $key == 'fk_event'){
                    continue;
                }

                if($exclude !== null && is_array($exclude) && in_array($key, $exclude)){
                    continue;
                }

                if($key == 'registration_date_begin' || $key == 'registration_date_end'){
                    $val = date('d/m/Y H:i', strtotime($val));
                }

                if($key == 'registration_price'){
                    $val = number_format($val,2,',','.');
                }
                
                $el[] = $val;
            }

            // Add buttons
            $el[5] = "<a href='javascript:void(0)' onclick='showModalAwardEventForm({$obj['fk_award']},{$obj['fk_event']})' class='btn btn-info btn-xs'>Editar</a>";
            $el[5] .= "&nbsp;<a href='javascript:void(0)' onclick='removeAwardEvent({$obj['fk_award']},{$obj['fk_event']})' class='btn btn-danger btn-xs'>Remover</a>";

            $retorno[] = $el;
        }
        return $retorno;
    }

    /**
     * Add a new record
     */
    public function insert($data) {

        if(!$data || !is_array($data)){
            throw new \Exception('Invalid param - param data must by an associative array');
        }
        
        # Escape values
        $this->escapeValues($data, $this->fields);

        # stmt
        $sql  = "INSERT INTO {$this->tableName} (fk_award, fk_event, title, description, registration_date_begin, registration_date_end, billing_days_to_due, registration_price, banner, logo)";
        $sql .= " VALUES({$data['fk_award']}, {$data['fk_event']}, '{$data['title']}', '{$data['description']}', '{$data['registration_date_begin']}', '{$data['registration_date_end']}', {$data['billing_days_to_due']}, '{$data['registration_price']}', '{$data['banner']}', '{$data['logo']}')";

        $this->query($sql);
        
        return $this->insert_id();
    }

    /**
     * Edit a record
     */
    public function update($data, $fk_award, $fk_event) {

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
                if($type == 's' || $type == 'dt' || $type == 'd'){
                    $st .= "`{$key}` = '{$val}'";
                }else{
                    $st .= "`{$key}` = {$val}";
                }
                
            }
            
        }
        
        $sql = "UPDATE {$this->tableName} SET {$st} WHERE fk_award={$fk_award} AND fk_event={$fk_event}";

        return $this->query($sql);
    }

    /**
     * Delete a record
     */
    public function delete($fk_award, $fk_event) {

        $sql = "DELETE FROM {$this->tableName} WHERE fk_award={$fk_award} AND fk_event={$fk_event}";

        return $this->query($sql);
    }

}