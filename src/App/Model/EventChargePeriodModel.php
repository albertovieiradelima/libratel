<?php

namespace App\Model;

/**
 * Created by PhpStorm.
 * User: albertovieira
 * Date: 3/30/15
 * Time: 5:38 PM
 *
 * EventChargePeriod Model
 *
 * @author Alberto Vieira <albertovieiradelima@gmail.com>
 */
class EventChargePeriodModel extends BaseModel {
    protected $tableName = 'event_charge_period';
    protected $fields = array(
        'id' => 'i',
        'fk_event' => 'i',
        'start_date' => 's',
        'end_date' => 's',
        'associated_price' => 'd',
        'standard_price' => 'd'
    );

    /**
     * Get charge period by id
     */
    public function getById($id) {
        $sql = "SELECT * FROM {$this->tableName} WHERE id = {$id}";

        $this->query($sql);
        return $this->fetch();
    }

    /**
     * Get charge period
     */
    public function getAll($criteria = "*") {

        $sql = "SELECT {$criteria} FROM {$this->tableName}";

        $this->query($sql);
        return $this->fetch_all();
    }

    /**
     * Get charge period
     */
    public function getAllByEvent($fk_event) {
        $sql = "SELECT id, start_date, end_date, associated_price, standard_price FROM {$this->tableName} WHERE fk_event = {$fk_event}";

        $this->query($sql);
        return $this->fetch_all(MYSQLI_NUM);
    }

    /**
     * Add a new charge period
     */
    public function insert($data) {

        if(!$data || !is_array($data)){
            throw new \Exception('Invalid param - param data must by an associative array');
        }

        # Escape values
        $this->escapeValues($data, $this->fields);

        # stmt
        $sql = "INSERT INTO {$this->tableName} (`fk_event`, `start_date`, `end_date`, `associated_price`, `standard_price`) VALUES ({$data['fk_event']}, '{$data['start_date']}', '{$data['end_date']}', '{$data['associated_price']}', '{$data['standard_price']}')";
        error_log($sql);
        $this->query($sql);

        return $this->insert_id();
    }

    /**
     * Edit a charge period
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
     * Delete a charge period
     */
    public function delete($id) {

        $sql = "DELETE FROM {$this->tableName} WHERE id = {$id}";

        return $this->query($sql);
    }

    /**
     * Get charge period by date from one event
     */
    public function getByDate($date,$event) {
        $sql = "SELECT * FROM {$this->tableName} WHERE fk_event = {$event} AND start_date <= '{$date}' AND end_date >= '{$date}' ORDER BY start_date LIMIT 0,1";

        $this->query($sql);
        return $this->fetch();
    }

}