<?php

namespace App\Model;

/**
 * Subscriber Model
 *
 * @author Alberto Vieira <albertovieiradelima@gmail.com>
 */
class SubscriberModel extends BaseModel {

    /**
     * Get subscriber by id
     */
    public function getById($id) {

        $sql = "SELECT s.*, e.title, e.type FROM subscriber s JOIN event e ON e.id = s.fk_event WHERE s.id = {$id}";

        return $this->query($sql);
    }

    /**
     * Get subscriber by event or course
     */
    public function getByEvent($fk_event) {

        $sql = "SELECT * FROM subscriber WHERE fk_event = {$fk_event}";

        return $this->query($sql);
    }

    /**
     * Get subscriber
     */
    public function getAll($criteria = 's.*, e.title, e.type') {

        $sql = "SELECT {$criteria} FROM subscriber s JOIN event e ON e.id = s.fk_event";

        return $this->query($sql);
    }

    /**
     * Add a new subscriber
     */
    public function insert($fk_event, $name, $email, $cpf, $phone, $job, $business, $date, $status) {

        $sql = "INSERT INTO subscriber (fk_event, name, email, cpf, phone, job, business, date, status) 
            VALUES ('{$fk_event}', '{$name}', '{$email}', '{$cpf}', '{$phone}', '{$job}', '{$business}', '{$date}', {$status});";

        return $this->query($sql);
    }

    /**
     * Edit a subscriber
     */
    public function update($criteria = null, $id) {

        $sql = "UPDATE subscriber SET {$criteria} WHERE id = {$id};";

        return $this->query($sql);
    }

    /**
     * Delete a subscriber
     */
    public function delete($id) {

        $sql = "DELETE FROM subscriber WHERE id = {$id};";

        return $this->query($sql);
    }

    /**
     * Get count subscriber
     */
    public function getCount() {

        $sql = "SELECT COUNT(id) AS qtde FROM subscriber WHERE status = 'unregistered'";

        return $this->query($sql);
    }

    /**
     * Add a new subscriber
     */
    public function insertSubscriber($data) {

        if (!$data || !is_array($data)) {
            throw new \Exception('Invalid param - param data must by an associative array');
        }
        
        # Fields
        $fieldsToValidade = array('Id', 'Nome', 'Email', 'CPF', 'Telefone', 'Cargo', 'Empresa');
        
        # Validate
        foreach ($data as $key => $val) {
            if (in_array($key, $fieldsToValidade)) {
                $data[$key] = $val ? $this->escapeString($val) : ""; # Validate and escape
            }
        }

        $sql = "INSERT INTO subscriber (fk_event, name, email, cpf, phone, job, business, date, status) 
            VALUES ('{$data['Id']}', '{$data['Nome']}', '{$data['Email']}', '{$data['CPF']}', '{$data['Telefone']}', 
                '{$data['Cargo']}', '{$data['Empresa']}', now(), 'unregistered');";

        return $this->query($sql);
    }

}