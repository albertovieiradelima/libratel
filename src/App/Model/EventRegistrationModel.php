<?php

namespace App\Model;

/**
 * Created by PhpStorm.
 * User: albertovieira
 * Date: 4/01/15
 * Time: 2:38 PM
 * 
 * EventRegistration Model
 *
 * @author Alberto Vieira <albertovieiradelima@gmail.com>
 */
class EventRegistrationModel extends BaseModel {

    protected $tableName = 'event_registration';
    protected $fields = array(
        'id' => 'i',
        'fk_event' => 'i',
        'fk_coupon' => 's',
        'company_name' => 's',
        'company_document' => 's',
        'company_zip' => 's',
        'company_state' => 's',
        'company_city' => 's',
        'company_address' => 's',
        'company_neighborhood' => 's',
        'company_number' => 's',
        'company_phone' => 's',
        'company_filiation'=> 'i',
        'billing_document' => 's',
        'billing_name' => 's',
        'billing_zip' => 's',
        'billing_state' => 's',
        'billing_city' => 's',
        'billing_address' => 's',
        'billing_neighborhood' => 's',
        'billing_number' => 's',
        'billing_phone' => 's',
        'responsible_name' => 's',
        'responsible_email' => 's',
        'responsible_phone' => 's',
        'responsible_job' => 's',
        'responsible_document' => 's',
        'invoice_number' => 's',
        'invoice_payment_date' => 's',
        'invoice_due_date' => 's',
        'invoice_value' => 'd',
        'created_date' => 's',
        'status' => 's',
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

        $sql = "SELECT id, created_date, company_name, responsible_name, invoice_number, invoice_due_date, invoice_value, status
                FROM {$this->tableName}
                WHERE fk_event = {$fk_event} AND status <> 'incompleta'";

        $this->query($sql);
        return $this->fetch_all(MYSQLI_NUM);
    }

    /**
     * Get Billing by Id
     * $param (int)$id
     */
    public function getBillingById($id) {

        $sql = "SELECT id, fk_event, invoice_number, invoice_value, invoice_due_date, responsible_name,
                        responsible_email, billing_name, billing_document, billing_zip, billing_address, billing_state,
                        billing_city
                FROM {$this->tableName}
                WHERE id = {$id}";

        $this->query($sql);

        return $this->fetch();
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
        $sql = "INSERT INTO {$this->tableName} (`fk_event`, `fk_coupon`, `company_name`, `company_document`, `company_zip`, `company_state`, `company_city`, `company_address`, `company_neighborhood`, `company_number`, `company_phone`,`company_filiation`, `billing_name`, `billing_document`,`billing_zip`, `billing_state`, `billing_city`, `billing_address`, `billing_neighborhood`, `billing_number`, `billing_phone`, `responsible_name`, `responsible_email`, `responsible_phone`, `responsible_job`, `responsible_document`, `status`) VALUES ({$data['fk_event']}, '{$data['fk_coupon']}', '{$data['company_name']}', '{$data['company_document']}', '{$data['company_zip']}', '{$data['company_state']}', '{$data['company_city']}', '{$data['company_address']}', '{$data['company_neighborhood']}', '{$data['company_number']}', '{$data['company_phone']}', '{$data['company_filiation']}','{$data['billing_name']}', '{$data['billing_document']}','{$data['billing_zip']}', '{$data['billing_state']}', '{$data['billing_city']}', '{$data['billing_address']}', '{$data['billing_neighborhood']}', '{$data['billing_number']}', '{$data['billing_phone']}', '{$data['responsible_name']}', '{$data['responsible_email']}', '{$data['responsible_phone']}', '{$data['responsible_job']}', '{$data['responsible_document']}','{$data['status']}')";
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
}