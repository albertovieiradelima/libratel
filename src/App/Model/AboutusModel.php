<?php

namespace App\Model;

/**
 * Aboutus Model
 *
 * @author Alberto Vieira <albertovieiradelima@gmail.com>
 */
class AboutusModel extends BaseModel {

    /**
     * Get aboutus by id
     */
    public function getById($id) {

        $sql = "SELECT id, title, image, description FROM aboutus WHERE id = {$id}";

        return $this->query($sql);
    }

    /**
     * Get aboutus
     */
    public function getAll($criteria = 'id, title, image') {

        $sql = "SELECT {$criteria} FROM aboutus;";

        return $this->query($sql);
    }

    /**
     * Add a new aboutus
     */
    public function insert($title, $image, $description) {

        $sql = "INSERT INTO aboutus (title, image, description)
            VALUES ('{$title}', '{$image}', '{$description}');";

        return $this->query($sql);
    }

    /**
     * Edit a aboutus
     */
    public function update($criteria = null, $id) {

        $sql = "UPDATE aboutus SET {$criteria} WHERE id = {$id};";

        return $this->query($sql);
    }

    /**
     * Delete a aboutus
     */
    public function delete($id) {

        $sql = "DELETE FROM aboutus WHERE id = {$id};";

//        if($this->query($sql)){
//            $sql = "UPDATE aboutus_type SET status = 'unused' WHERE id = {$fk_aboutus_type};";
//        }

        return $this->query($sql);
    }

    /**
     * Get aboutus by fk
     */
    public function getByTitle($title) {

        $sql = "SELECT id, title, image, description FROM aboutus WHERE title like '%{$title}%';";

        return $this->query($sql);
    }

}