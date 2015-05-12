<?php

namespace App\Model\AbrasceAward;

/**
 * Award Event Fields Model
 *
 * @author Renato Peterman <renato.peterman@crmall.com>
 */
class AwardEventFieldModel extends \App\Model\BaseModel {

    protected $tableName = 'aa_award_event_field';
    protected $fields = array(
        'id'                => 'i',
        'type'              => 's',
        'title'             => 's',
        'description'       => 's',
        'weight'            => 'd',
        'order'             => 'i',
        'accept_filetypes'  => 's',
        'maxlength'         => 'i',
        'fk_award'          => 'i',
        'fk_event'          => 'i'
    );

    /**
     * Get award by id
     */
    public function getById($id) {
      $sql = "SELECT * FROM {$this->tableName} t WHERE t.id={$id}";
        $this->query($sql);
        return $this->fetch();
    }

    /**
     * Get award by id
     */
    public function getByEvent($event) {
        $sql = "SELECT * FROM {$this->tableName} t WHERE t.fk_event={$event}";
        $this->query($sql);
        return $this->fetch();
    }

    /**
     * Get award by event
     */
    public function getAll($fk_award = null, $fk_event = null, $orderBy = null, $order = null) {
        if($fk_award === null){
            $sql = "SELECT * FROM {$this->tableName}";
        }else{
            $sql = "SELECT * FROM {$this->tableName} WHERE fk_award={$fk_award} AND fk_event={$fk_event}";
        }

        if($orderBy){
            $order = $order ? $order : 'ASC';
            $sql .= " ORDER BY `{$orderBy}` {$order}";
        }

        $this->query($sql);
        return $this->fetch_all();
    }
    
    public function getAllForDT($exclude = null, $fk_award = null, $fk_event = null) {
        
        if($fk_award === null){
            $sql = "SELECT `id`, `type`, `title`, `weight`, `order` FROM {$this->tableName}";
        }else{
            $sql = "SELECT `id`, `type`, `title`, `weight`, `order` FROM {$this->tableName} WHERE fk_award={$fk_award} AND fk_event={$fk_event}";
        }
        
        $this->query($sql);
        $list = $this->fetch_all();
        
        $retorno = array();
        foreach($list as $obj){
            $el = array();
            foreach($obj as $key => $val){
                
                if($exclude !== null && is_array($exclude) && in_array($key, $exclude)){
                    continue;
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
        
        # stmt
        if(!$data['maxlength']){
            $data['maxlength'] = 200;
        }
        $sql = "INSERT INTO {$this->tableName} (`id`, `type`, `title`, `description`, `weight`, `order`, `accept_filetypes`, `maxlength`, `fk_award`, `fk_event`) VALUES ('{$data['id']}', '{$data['type']}', '{$data['title']}', '{$data['description']}', {$data['weight']}, {$data['order']}, '{$data['accept_filetypes']}', {$data['maxlength']}, {$data['fk_award']}, {$data['fk_event']})";
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
                    $st .= "`{$key}` = '{$val}'";
                }else{
                    $st .= "`{$key}` = {$val}";
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

    /**
     * Delete from award event
     */
    public function deleteAwardEventFields($fk_award, $fk_event) {

        $sql = "DELETE FROM {$this->tableName} WHERE fk_award={$fk_award} AND fk_event={$fk_event}";

        return $this->query($sql);
    }

}