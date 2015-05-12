<?php
/**
 * Created by PhpStorm.
 * User: albertovieira
 * Date: 3/30/15
 * Time: 5:38 PM
 */

namespace App\Model;

/**
 * EventDiscountCoupon Model
 *
 * @author Alberto Vieira <albertovieiradelima@gmail.com>
 */
class EventDiscountCouponModel extends BaseModel {

    protected $tableName = 'event_discount_coupon';
    protected $fields = array(
        'id' => 's',
        'fk_event' => 'i',
        'company' => 's',
        'email' => 's',
        'contact' => 's',
        'minimum_number' => 'i',
        'maximum_number' => 'i',
        'discount_participant' => 'd',
        'created_date' => 's',
        'expiration_date' => 's',
        'observations' => 's',
        'used_number' => 'i',
    );

    /**
     * Get discount coupon by id
     */
    public function getById($id) {

        $sql = "SELECT * FROM {$this->tableName} WHERE id = '{$id}'";

        $this->query($sql);
        return $this->fetch();
    }

    /**
     * Get discount coupon
     */
    public function getAll($criteria = "c.*, e.title") {

        $sql = "SELECT {$criteria} FROM {$this->tableName} c JOIN event e ON e.id = c.fk_event";

        $this->query($sql);
        return $this->fetch_all(MYSQLI_NUM);
    }

    /**
     * Get discount coupon
     */
    public function getAllByEvent($fk_event) {

        $sql = "SELECT id, company, email, contact, minimum_number, maximum_number, expiration_date, created_date, used_number FROM {$this->tableName} WHERE fk_event = {$fk_event}";

        $this->query($sql);
        return $this->fetch_all(MYSQLI_NUM);
    }

    /**
     * Add a new discount coupon
     */
    public function insert($data) {

        if(!$data || !is_array($data)){
            throw new \Exception('Invalid param - param data must by an associative array');
        }

        # Escape values
        $this->escapeValues($data, $this->fields);

        # stmt
        $sql = "INSERT INTO {$this->tableName} (`id`, `fk_event`, `company`, `email`, `contact`, `minimum_number`, `maximum_number`, `discount_participant`, `created_date`, `expiration_date`, `observations`, `used_number`) VALUES ('{$data['id']}', {$data['fk_event']}, '{$data['company']}', '{$data['email']}', '{$data['contact']}', '{$data['minimum_number']}', '{$data['maximum_number']}', '{$data['discount_participant']}', '{$data['expiration_date']}', '{$data['created_date']}', '{$data['observations']}', '{$data['used_number']}')";
        error_log($sql);

        return $this->query($sql);
    }

    /**
     * Edit a discount coupon
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
     * Delete a discount coupon
     */
    public function delete($id) {
        $sql = "DELETE FROM {$this->tableName} WHERE id = '{$id}'";

        return $this->query($sql);
    }

}