<?php

namespace App\Model;

/**
 * Contactus Model
 *
 * @author Alberto Vieira <albertovieiradelima@gmail.com>
 */
class ContactusModel extends BaseModel {

    /**
     * Get contactus by id
     */
    public function getById($id) {

        $sql = "SELECT * FROM contactus WHERE id = {$id}";

        return $this->query($sql);
    }

    /**
     * Get contactus
     */
    public function getAll($criteria = "*") {

        $sql = "SELECT {$criteria} FROM contactus";

        return $this->query($sql);
    }

    /**
     * Add a new contactus
     */
    public function insert($area, $subject, $name, $email, $business, $job, $cep, $address, $number, $complement, $neighborhood, $city, $state, $phone, $message, $date) {

        $sql = "INSERT INTO contactus (area, subject, name, email, business, job, cep, address, number, complement, neighborhood, city, state, phone, message, date, status) 
            VALUES ('{$area}', '{$subject}', '{$name}', '{$email}', '{$business}', '{$job}', '{$cep}', '{$address}', '{$number}', '{$complement}', '{$neighborhood}', '{$city}', '{$state}', '{$phone}', '{$message}', '{$date}', '2');";


        return $this->query($sql);
    }

    /**
     * Edit a contactus
     */
    public function update($criteria = null, $id) {

        $sql = "UPDATE contactus SET {$criteria} WHERE id = {$id};";

        return $this->query($sql);
    }

    /**
     * Delete a contactus
     */
    public function delete($id) {

        $sql = "DELETE FROM contactus WHERE id = {$id};";

        return $this->query($sql);
    }

    /**
     * Get count contactus
     */
    public function getCount() {

        $sql = "SELECT COUNT(id) AS qtde FROM contactus WHERE status = 'unread'";

        return $this->query($sql);
    }

}