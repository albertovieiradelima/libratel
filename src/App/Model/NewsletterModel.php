<?php

namespace App\Model;

/**
 * Newsletter Model
 *
 * @author Alberto Vieira <albertovieiradelima@gmail.com>
 */
class NewsletterModel extends BaseModel {

    /**
     * Get newsletter by id
     */
    public function getById($id) {

        $sql = "SELECT * FROM newsletter WHERE id = {$id}";

        return $this->query($sql);
    }
    
    /**
     * Get newsletter by e-mail
     */
    public function getByEmail($email) {

        $sql = "SELECT * FROM newsletter WHERE email = '{$email}'";

        return $this->query($sql);
    }

    /**
     * Get newsletter
     */
    public function getAll($criteria = "*") {

        $sql = "SELECT {$criteria} FROM newsletter";

        return $this->query($sql);
    }

    /**
     * Add a new newsletter
     */
    public function insert($email, $date) {

        $sql = "INSERT INTO newsletter (email, date, status) 
            VALUES ('{$email}', '{$date}', '1');";

        return $this->query($sql);
    }

    /**
     * Edit a newsletter
     */
    public function update($criteria = null, $id) {

        $sql = "UPDATE newsletter SET {$criteria} WHERE id = {$id};";

        return $this->query($sql);
    }

    /**
     * Delete a newsletter
     */
    public function delete($id) {

        $sql = "DELETE FROM newsletter WHERE id = {$id};";

        return $this->query($sql);
    }

    /**
     * Get count newsletter
     */
    public function getCount() {

        $sql = "SELECT COUNT(id) AS qtde FROM newsletter WHERE status = 'active'";

        return $this->query($sql);
    }

}