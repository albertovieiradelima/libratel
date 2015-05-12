<?php

namespace App\Model;

/**
 * Client Model
 *
 * @author Alberto Vieira <albertovieiradelima@gmail.com>
 */
class ClientModel extends BaseModel {

    /**
     * Get client by id
     */
    public function getById($id) {

        $sql = "SELECT * FROM client WHERE id = {$id}";

        return $this->query($sql);
    }

    /**
     * Get client
     */
    public function getAll($criteria = "*") {

        $sql = "SELECT {$criteria} FROM client";

        return $this->query($sql);
    }

    /**
     * Add a new client
     */
    public function insert($name, $nickname, $cpf_cnpj, $rg_ie, $email, $phone, $cep, $address, $number, $complement, $neighborhood, $city, $state, $type, $date, $status) {

        $sql = "INSERT INTO client (name, nickname, cpf_cnpj, rg_ie, email, phone, cep, address, number, complement, neighborhood, city, state, type, date, status) 
            VALUES ('{$name}', '{$nickname}', '{$cpf_cnpj}', '{$rg_ie}', '{$email}', '{$phone}', '{$cep}', '{$address}', '{$number}', '{$complement}', '{$neighborhood}', '{$city}', '{$state}', '{$type}', '{$date}', {$status});";

        return $this->query($sql);
    }

    /**
     * Edit a client
     */
    public function update($criteria = null, $id) {

        $sql = "UPDATE client SET {$criteria} WHERE id = {$id};";

        return $this->query($sql);
    }

    /**
     * Delete a client
     */
    public function delete($id) {

        $sql = "DELETE FROM client WHERE id = {$id};";

        return $this->query($sql);
    }

}