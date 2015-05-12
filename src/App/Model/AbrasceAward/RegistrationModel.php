<?php

namespace App\Model\AbrasceAward;

/**
 * Registration Model
 *
 * @author Anderson Costa <arcostasi@gmail.com>
 */
class RegistrationModel extends \App\Model\BaseModel {

    protected $tableName = 'aa_registration';
    protected $fields = array(
        'id' => 'i',
        'fk_registration' => 'i',
        'fk_event' => 'i',
        'fk_shopping' => 'i',
        'project_title' => 's',
        'registration_number' => 's',
        'invoice_number' => 's',
        'invoice_payment_date' => 's',
        'invoice_due_date' => 's',
        'invoice_value' => 'd',
        'status' => 's',
        'shopping_zip' => 's',
        'shopping_address' => 's',
        'shopping_city' => 's',
        'shopping_state' => 's',
        'shopping_phone' => 's',
        'shopping_fax' => 's',
        'responsible_document_number' => 's',
        'responsible_name' => 's',
        'responsible_position' => 's',
        'responsible_email' => 's',
        'responsible_phone' => 's',
        'administrator_name' => 's',
        'companies_shopping' => 's',
        'billing_document_number' => 's',
        'billing_name' => 's',
        'billing_zip' => 's',
        'billing_address' => 's',
        'billing_state' => 's',
        'billing_city' => 's',
        'billing_city' => 's',
        'created_at' => 's',
        'updated_at' => 's',
        'project_data' => 's'
    );

    /**
     * Get registration by id
     */
    public function getById($id) {
        $sql = "SELECT * FROM {$this->tableName} t WHERE t.id={$id}";
        $this->query($sql);
        return $this->fetch();
    }

    /**
     * Get registration by shopping
     */
    public function getByShopping($fk_shopping) {
        $sql = "SELECT * FROM {$this->tableName} t WHERE t.fk_shopping={$fk_shopping}";
        $this->query($sql);
        return $this->fetch();
    }

    /**
     * Get registration by fk_award, fk_event and fk_shopping
     */
    public function getByAwardEventShopping($fk_award, $fk_event, $fk_shopping) {
        $sql = "SELECT * FROM {$this->tableName} WHERE fk_award={$fk_award} AND fk_event={$fk_event} AND fk_shopping={$fk_shopping}";
        $this->query($sql);
        return $this->fetch_all();
    }

    public function getByAwardEvent($fk_award, $fk_event) {
        $sql = "SELECT * FROM {$this->tableName} WHERE fk_award={$fk_award} AND fk_event={$fk_event}";
        $this->query($sql);
        return $this->fetch_all();
    }

    public function getByRegistration($fk_award, $fk_event, $fk_shopping, $registration_number) {
        $registration_number = strtolower($registration_number);
        $sql = "SELECT * FROM {$this->tableName} WHERE fk_award={$fk_award} AND fk_event={$fk_event} AND fk_shopping={$fk_shopping} AND LOWER(registration_number)='{$registration_number}'";
        $this->query($sql);
        return $this->fetch();
    }

    /**
     * Get Billing by Id
     * $param (int)$id
     */
    public function getBillingById($id) {

        $sql = "SELECT id, fk_award, fk_event, registration_number, responsible_name, responsible_email, billing_name, billing_document_number, billing_zip,
                        billing_address, billing_state, billing_city
                FROM aa_registration
                WHERE id = {$id}";

        $this->query($sql);

        return $this->fetch();
    }

    /**
     * Get registration
     */
    public function getAll() {
        $sql = "SELECT * FROM {$this->tableName}";
        $this->query($sql);
        return $this->fetch_all();
    }

    /**
     * Get the last registration
     */
    public function getLast() {

        $sql = "SELECT * FROM {$this->tableName} ORDER BY id DESC LIMIT 1";

        return $this->query($sql);
    }

    /**
     * Get registration by id
     */
    public function getInfoById($id) {

        $sql = "SELECT rg.*, sh.fantasia as shopping
                FROM aa_registration rg
                INNER JOIN shopping sh ON (sh.id_shopping = rg.fk_shopping)
                WHERE rg.id = {$id}";

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

        $sql = "INSERT INTO {$this->tableName} (`fk_award`, `fk_event`, `fk_shopping`,`project_title`, `registration_number`, `invoice_number`, `invoice_payment_date`, `invoice_due_date`,`invoice_value`, `status`, `shopping_zip`, `shopping_address`, `shopping_city`, `shopping_state`,`shopping_phone`, `shopping_fax`, `responsible_document_number`, `responsible_name`,`responsible_position`, `responsible_email`, `responsible_phone`, `administrator_name`,`companies_shopping`, `billing_document_number`, `billing_name`, `billing_zip`, `billing_address`,`billing_state`, `billing_city`, `project_data`, `created_at`, `updated_at`) VALUES ({$data['fk_award']}, {$data['fk_event']}, {$data['fk_shopping']},'{$data['project_title']}', '{$data['registration_number']}', '{$data['invoice_number']}','{$data['invoice_payment_date']}', '{$data['invoice_due_date']}','{$data['invoice_value']}', '{$data['status']}', '{$data['shopping_zip']}', '{$data['shopping_address']}','{$data['shopping_city']}', '{$data['shopping_state']}', '{$data['shopping_phone']}', '{$data['shopping_fax']}','{$data['responsible_document_number']}', '{$data['responsible_name']}', '{$data['responsible_position']}','{$data['responsible_email']}', '{$data['responsible_phone']}', '{$data['administrator_name']}','{$data['companies_shopping']}', '{$data['billing_document_number']}', '{$data['billing_name']}','{$data['billing_zip']}', '{$data['billing_address']}', '{$data['billing_state']}','{$data['billing_city']}', '{$data['project_data']}', '{$data['created_at']}', '{$data['updated_at']}')";

        $this->query($sql);

        return $this->insert_id();
    }

    /**
     * Edit a registration
     */
    public function update($data = null, $id) {

        if(!$data || !is_array($data)){
            throw new \Exception('Invalid param - param data must by an associative array');
        }
        
        # Escape values
        $this->escapeValues($data, $this->fields);
        
        $st = '';
        $first = true;

        foreach ($data as $key => $val) {
            
            if ($key != 'id') {
                
                if ($first) {
                   $first = false; 
                } else {
                   $st .= ","; 
                }

                $type = $this->fields[$key];

                if ($type == 's') {
                    $st .= "{$key} = '{$val}'";
                } else {
                    $st .= "{$key} = {$val}";
                }
                
            }
            
        }
        
        $sql = "UPDATE {$this->tableName} SET {$st} WHERE id = {$id}";
        
        return $this->query($sql);
    }

    /**
     * Update data billing
     */
    public function updateBilling($data = null, $id) {

        $sql = "UPDATE aa_registration SET 
                invoice_payment_date = '{$data['invoice_payment_date']}',
                billing_document_number = '{$data['billing_document_number']}',
                billing_name = '{$data['billing_name']}',
                billing_zip = '{$data['billing_zip']}',
                billing_address = '{$data['billing_address']}',
                billing_state = '{$data['billing_state']}',
                billing_city = '{$data['billing_city']}',
                status = '{$data['status']}'
                WHERE id = {$id}";

        return $this->query($sql);
    }

    /**
     * Delete a registration
     */
    public function delete($id) {

        $sql = "DELETE FROM {$this->tableName} WHERE id = {$id}";

        return $this->query($sql);
    }

    public function getAllForDT($exclude = null, $fk_event = null) {

        $sql = "SELECT rg.id, rg.project_title, sh.fantasia, aa_a.name as event, rg.registration_number, rg.invoice_number, rg.invoice_due_date, rg.status
                FROM aa_registration rg
                INNER JOIN shopping sh ON (sh.id_shopping = rg.fk_shopping)
                INNER JOIN aa_award aa_a ON (aa_a.id = rg.fk_award)";

        if ($fk_event) {
            $sql .= " WHERE fk_event = {$fk_event}";
        }

        $this->query($sql);
        $list = $this->fetch_all();

        $retorno = array();
        foreach($list as $obj) {
            $el = array();
            foreach($obj as $key => $val) {

                if ($key == 'status') {
                    if ($val == 'cancelled') {
                        $val = 'Cancelado';
                    } else if ($val == 'confirmed') {
                        $val = 'Confirmado';
                    } else if ($val == 'pending') {
                        $val = 'Pendente';
                    }
                }

                if ($exclude !== null && is_array($exclude) && in_array($key, $exclude)) {
                    continue;
                }

                $el[] = $val;
            }
            $retorno[] = $el;
        }
        return $retorno;
    }

}