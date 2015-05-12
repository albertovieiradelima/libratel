<?php

namespace App\Model;

/**
 * Associate Model
 *
 * @author Alberto Vieira <albertovieiradelima@gmail.com>
 */
class AssociateModel extends BaseModel {

    /**
     * Get associate by id
     */
    public function getById($id) {

        $sql = "SELECT * FROM associate WHERE id = {$id}";

        return $this->query($sql);
    }

    /**
     * Get associates
     */
    public function getAll($criteria = "*") {

        $sql = "SELECT {$criteria} FROM associate";

        return $this->query($sql);
    }

    /**
     * Add a new associate
     */
    public function insert($email, $name, $phone, $business, $message, $date) {

        $sql = "INSERT INTO associate (email, name, phone, business, message, date, status) 
            VALUES ('{$email}', '{$name}', '{$phone}', '{$business}', '{$message}', '{$date}', '2');";

        return $this->query($sql);
    }

    /**
     * Edit a associate
     */
    public function update($criteria = null, $id) {

        $sql = "UPDATE associate SET {$criteria} WHERE id = {$id};";

        return $this->query($sql);
    }

    /**
     * Delete a associate
     */
    public function delete($id) {

        $sql = "DELETE FROM associate WHERE id = {$id};";

        return $this->query($sql);
    }

    /**
     * Get count associate
     */
    public function getCount() {

        $sql = "SELECT COUNT(id) AS qtde FROM associate WHERE status = 'unread'";

        return $this->query($sql);
    }

}