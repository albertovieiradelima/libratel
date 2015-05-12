<?php

namespace App\Model\AbrasceAward;

/**
 * Created by PhpStorm.
 * User: albertovieiradelima
 * Date: 29/01/15
 * Time: 09:48
 */
class EventSponsorModel extends \App\Model\BaseModel {

    protected $tableName = 'aa_event_sponsor';
    protected $fields = array(
        'id' => 'i',
        'fk_event' => 'i',
        'fk_sponsor' => 'i',
        'fk_sponsor_category' => 'i',
        'order' => 'i'
    );

    /**
     * Get award by id
     */
    public function getById($id) {
        $sql = "SELECT * FROM {$this->tableName} WHERE id={$id}";
        $this->query($sql);
        return $this->fetch();
    }

    /**
     * Get award
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

    public function getAllForDT($exclude = null, $fk_event = null) {

        if($fk_event === null){
            $sql = "SELECT es.id, es.order, s.name AS sponsor, sc.name AS category FROM {$this->tableName} es
                    JOIN aa_event e ON e.id = es.fk_event
                    JOIN aa_sponsor s ON s.id = es.fk_sponsor
                    JOIN aa_sponsor_category sc ON sc.id = es.fk_sponsor_category";
        }else{
            $sql = "SELECT es.id, es.order, s.name AS sponsor, sc.name AS category FROM {$this->tableName} es
                    JOIN aa_event e ON e.id = es.fk_event
                    JOIN aa_sponsor s ON s.id = es.fk_sponsor
                    JOIN aa_sponsor_category sc ON sc.id = es.fk_sponsor_category
                    WHERE es.fk_event={$fk_event}";
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
        $sql = "INSERT INTO {$this->tableName} (`fk_event`, `fk_sponsor`, `fk_sponsor_category`, `order`) VALUES ({$data['fk_event']}, {$data['fk_sponsor']}, {$data['fk_sponsor_category']}, {$data['order']})";
        error_log($sql);
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
     * Get Count Active event sponsor
     */
    public function getCountActive($fk_event) {

        $sql = "SELECT COUNT(id) AS qtde FROM {$this->tableName} WHERE fk_event = '{$fk_event}' AND `order` IS NOT NULL";

        return $this->query($sql);
    }

    /**
     * Get event sponsor order by order
     */
    public function get($sql = null) {

        if(!is_null($sql)) {
            return $this->query($sql);
        } else {
            return false;
        }

    }

}