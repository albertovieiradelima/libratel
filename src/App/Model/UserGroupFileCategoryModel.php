<?php
/**
 * Created by PhpStorm.
 * User: albertovieira
 * Date: 5/6/15
 * Time: 5:34 PM
 */

namespace App\Model;

/**
 * UserGroupFileCategory Model
 *
 * @author Alberto Vieira <albertovieiradelima@gmail.com>
 */
class UserGroupFileCategoryModel extends BaseModel {

    protected $tableName = 'user_group_file_category';
    protected $fields = array(
        'id' => 'i',
        'fk_user_group' => 'i',
        'fk_file_category' => 'i',
    );

    /**
     * Get all by user group
     */
    public function getAllByUserGroup($criteria = '*', $fk_user_group) {

        $sql = "SELECT {$criteria} FROM {$this->tableName} WHERE fk_user_group = {$fk_user_group}";

        $this->query($sql);
        return $this->fetch_all();
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
        $sql = "INSERT INTO {$this->tableName} (`fk_user_group`, `fk_file_category`) VALUES ({$data['fk_user_group']}, {$data['fk_file_category']})";
        error_log($sql);

        return $this->query($sql);
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

        $sql = "UPDATE {$this->tableName} SET {$st} WHERE id = {$id}";

        return $this->query($sql);
    }

    /**
     * Delete by user group
     */
    public function deleteByUserGroup($id) {
        $sql = "DELETE FROM {$this->tableName} WHERE fk_user_group = {$id}";

        return $this->query($sql);
    }

}