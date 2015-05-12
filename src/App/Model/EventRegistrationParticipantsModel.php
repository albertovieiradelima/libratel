<?php

namespace App\Model;

/**
 * Created by PhpStorm.
 * User: albertovieira
 * Date: 4/01/15
 * Time: 2:38 PM
 *
 * EventRegistrationParticipants Model
 *
 * @author Alberto Vieira <albertovieiradelima@gmail.com>
 */
class EventRegistrationParticipantsModel extends BaseModel {

    protected $tableName = 'event_registration_participants';
    protected $fields = array(
        'id' => 'i',
        'fk_event_registration' => 'i',
        'certificate_name' => 's',
        'cpf' => 's',
        'sex' => 's',
        'email' => 's',
        'badge_name' => 's',
        'badge_company' => 's',
        'job' => 's',
        'area' => 's',
        'phone' => 's'
    );

    /**
     * Get registration by id
     */
    public function getById($id) {

        $sql = "SELECT * FROM {$this->tableName} WHERE id = {$id}";

        $this->query($sql);
        return $this->fetch();
    }

    /**
     * Get registration
     */
    public function getAll($criteria = "*") {

        $sql = "SELECT {$criteria} FROM {$this->tableName}";

        $this->query($sql);
        return $this->fetch_all();
    }

    /**
     * Get registration
     */
    public function getAllByEvent($fk_event) {

        $sql = "SELECT p.id, p.badge_name, p.badge_company, p.job, p.area FROM {$this->tableName} p JOIN event_registration r ON r.id = p.fk_event_registration JOIN event e ON e.id = r.fk_event WHERE r.fk_event = {$fk_event}";

        $this->query($sql);
        return $this->fetch_all(MYSQLI_NUM);
    }

    /**
     * Add a new registration
     */
    public function insert($data) {
        if(!$data || !is_array($data)){
            throw new \Exception('Invalid param - param data must by an associative array');
        }

        # Escape values
        $this->escapeValues($data, $this->fields);

        # stmt
        $sql = "INSERT INTO {$this->tableName} (`fk_event_registration`, `certificate_name`, `cpf`, `sex`, `email`, `badge_name`, `badge_company`, `job`, `area`, `phone`) VALUES ({$data['fk_event_registration']}, '{$data['certificate_name']}', '{$data['cpf']}', '{$data['sex']}', '{$data['email']}', '{$data['badge_name']}', '{$data['badge_company']}', '{$data['job']}', '{$data['area']}', '{$data['phone']}')";
        error_log($sql);
        $this->query($sql);

        return $this->insert_id();
    }

    /**
     * Edit a registration
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
     * Delete a registration
     */
    public function delete($id) {
        $sql = "DELETE FROM {$this->tableName} WHERE id = {$id}";
        return $this->query($sql);
    }

    /**
     * Get all participants by register
     * @param $register
     */
    public function getAllByRegister($register){
        $sql = "SELECT * FROM {$this->tableName} WHERE fk_event_registration = {$register}";
        error_log($sql);
        $this->query($sql);

        return $this->fetch_all();
    }
}