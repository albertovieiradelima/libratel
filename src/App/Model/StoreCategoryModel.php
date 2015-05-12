<?php

namespace App\Model;
use App\Enum\SituationStatusEnum;

/**
 * StoreCategory Model
 *
 * @author Alberto Vieira <albertovieiradelima@gmail.com>
 */
class StoreCategoryModel extends BaseModel {

    /**
     * Get store_category by id
     */
    public function getById($id) {

        $sql = "SELECT * FROM store_category WHERE id = {$id}";

        return $this->query($sql);
    }

    /**
     * Get store_category
     */
    public function getAll($criteria = "*") {

        $sql = "SELECT {$criteria} FROM store_category";

        return $this->query($sql);
    }

    /**
     * Get store_category active
     */
    public function getAllThemActive($criteria = "*") {

        $sql = "SELECT {$criteria} FROM store_category WHERE status = ".SituationStatusEnum::ACTIVE;

        return $this->query($sql);
    }

    /**
     * Add a new store_category
     */
    public function insert($name, $status) {

        $sql = "INSERT INTO store_category (name, status) 
            VALUES ('{$name}', {$status});";

        return $this->query($sql);
    }

    /**
     * Edit a store_category
     */
    public function update($criteria = null, $id) {

        $sql = "UPDATE store_category SET {$criteria} WHERE id = {$id};";

        return $this->query($sql);
    }

    /**
     * Delete a store_category
     */
    public function delete($id) {

        $sql = "DELETE FROM store_category WHERE id = {$id};";

        return $this->query($sql);
    }

}