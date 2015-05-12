<?php

namespace App\Model;

/**
 * Inauguration Model
 *
 * @author Alberto Vieira <albertovieiradelima@gmail.com>
 * @author Victor Hugo Martins <victorhugo.pmartins@gmail.com>
 */
class InaugurationModel extends BaseModel {

    /**
     * Get inauguration by id
     */
    public function getById($id) {

        $sql = "SELECT * FROM inauguration WHERE id = {$id}";

        return $this->query($sql);
    }

    /**
     * Get inauguration
     */
    public function getAll() {

        $sql = "SELECT i.id, i.shopping, i.abl, c.name, i.inauguration_date, i.status
                FROM inauguration i JOIN inauguration_category c ON c.id = i.fk_inauguration_category;";

        return $this->query($sql);
    }


    /**
     * Get inauguration
     */
    public function getByCategory($id) {

        if ($id > 0) {
            $sql = "SELECT * FROM inauguration
                    WHERE fk_inauguration_category = {$id} ORDER BY inauguration_date";
        } else {
            $sql = "SELECT * FROM inauguration ORDER BY inauguration_date";
        }

        return $this->query($sql);
    }

    /**
     * Add a new inauguration
     */
    public function insert($fk_inauguration_category, $shopping, $inauguration_date, $city, $state, $abl, $link, $status) {

        $sql = "INSERT INTO inauguration (fk_inauguration_category, shopping, inauguration_date, city, state, abl, link, status)
            VALUES ('{$fk_inauguration_category}', '{$shopping}', '{$inauguration_date}', '{$city}', '{$state}', '{$abl}', '{$link}', {$status});";

        return $this->query($sql);
    }

    /**
     * Edit a inauguration
     */
    public function update($criteria = null, $id) {

        $sql = "UPDATE inauguration SET {$criteria} WHERE id = {$id};";

        return $this->query($sql);
    }

    /**
     * Delete a inauguration
     */
    public function delete($id) {

        $sql = "DELETE FROM inauguration WHERE id = {$id};";

        return $this->query($sql);
    }

}