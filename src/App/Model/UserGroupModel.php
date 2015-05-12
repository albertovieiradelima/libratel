<?php
/**
 * Created by PhpStorm.
 * User: albertovieira
 * Date: 5/5/15
 * Time: 9:21 AM
 */

namespace App\Model;

/**
 * UserGroup Model
 *
 * @author Alberto Vieira <albertovieiradelima@gmail.com>
 */
class UserGroupModel extends BaseModel {

    protected $tableName = 'user_group';
    protected $fields = array(
        'id' => 'i',
        'name' => 's',
        'status' => 'i',
    );

    /**
     * Get user group by id
     */
    public function getById($id) {

        $sql = "SELECT * FROM {$this->tableName} WHERE id = '{$id}'";

        $this->query($sql);
        return $this->fetch();
    }

    /**
     * Get user group
     */
    public function getAll($criteria = "*") {

        $sql = "SELECT {$criteria} FROM {$this->tableName}";

        $this->query($sql);
        return $this->fetch_all(MYSQLI_NUM);
    }

    /**
     * Add a new user group
     */
    public function insert($data) {

        if(!$data || !is_array($data)){
            throw new \Exception('Invalid param - param data must by an associative array');
        }

        # Escape values
        $this->escapeValues($data, $this->fields);

        # stmt
        $sql = "INSERT INTO {$this->tableName} (`name`, `status`) VALUES ('{$data['name']}', {$data['status']})";
        error_log($sql);

        $this->query($sql);
        return $this->insert_id();
    }

    /**
     * Edit a user group
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

        $sql = "UPDATE {$this->tableName} SET {$st} WHERE id = '{$id}'";

        return $this->query($sql);
    }

    /**
     * Delete a user group
     */
    public function delete($id) {
        $sql = "DELETE FROM {$this->tableName} WHERE id = '{$id}'";

        return $this->query($sql);
    }

}