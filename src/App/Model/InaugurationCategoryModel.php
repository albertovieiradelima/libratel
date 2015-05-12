<?php

namespace App\Model;
use App\Enum\SituationStatusEnum;

/**
 * InaugurationCategory Model
 *
 * @author Alberto Vieira <albertovieiradelima@gmail.com>
 */
class InaugurationCategoryModel extends BaseModel {

    /**
     * Get inauguration_category by id
     */
    public function getById($id) {

        $sql = "SELECT * FROM inauguration_category WHERE id = {$id}";

        return $this->query($sql);
    }

    /**
     * Get inauguration_category
     */
    public function getAll($criteria = "*") {

        $sql = "SELECT {$criteria} FROM inauguration_category ORDER BY name";

        return $this->query($sql);
    }

    /**
     * Get inauguration_category actives
     */
    public function getAllThemActive($criteria = "*") {

        $sql = "SELECT {$criteria} FROM inauguration_category WHERE status = ".SituationStatusEnum::ACTIVE." ORDER BY name";

        return $this->query($sql);
    }

    /**
     * Add a new inauguration_category
     */
    public function insert($name, $status) {

        $sql = "INSERT INTO inauguration_category (name, status) 
            VALUES ('{$name}', {$status});";

        return $this->query($sql);
    }

    /**
     * Edit a inauguration_category
     */
    public function update($criteria = null, $id) {

        $sql = "UPDATE inauguration_category SET {$criteria} WHERE id = {$id};";

        return $this->query($sql);
    }

    /**
     * Delete a inauguration_category
     */
    public function delete($id) {

        $sql = "DELETE FROM inauguration_category WHERE id = {$id};";

        return $this->query($sql);
    }

}