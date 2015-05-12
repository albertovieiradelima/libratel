<?php

namespace App\Model;
use App\Enum\SituationStatusEnum;

/**
 * Store Model
 *
 * @author Alberto Vieira <albertovieiradelima@gmail.com>
 */
class StoreModel extends BaseModel {

    /**
     * Get store by id
     */
    public function getById($id) {

        $sql = "SELECT * FROM store WHERE id = {$id}";

        return $this->query($sql);
    }

    /**
     * Get store
     */
    public function getAll() {

        $sql = "SELECT s.id, s.title, s.image, c.name, s.year, s.price, s.status
                FROM store s JOIN store_category c ON c.id = s.fk_store_category;";

        return $this->query($sql);
    }

    /**
     * Get store active
     */
    public function getAllThemActive() {

        $sql = "SELECT s.id, s.title, s.image, c.name, s.year, s.price, s.status
                FROM store s JOIN store_category c ON c.id = s.fk_store_category
                WHERE s.status = ".SituationStatusEnum::ACTIVE;

        return $this->query($sql);
    }


    /**
     * Get store
     */
    public function getByCategory($id) {

        if ($id > 0) {
            $sql = "SELECT * FROM store
                    WHERE fk_store_category = {$id}";
        } else {
            $sql = "SELECT * FROM store";
        }

        return $this->query($sql);
    }

    /**
     * Get store by category and active
     */
    public function getThemActiveByCategory($id) {

        if ($id > 0) {
            $sql = "SELECT * FROM store
                    WHERE fk_store_category = {$id} AND status = ".SituationStatusEnum::ACTIVE." ORDER BY id DESC";
        } else {
            $sql = "SELECT * FROM store WHERE status = ".SituationStatusEnum::ACTIVE." ORDER BY id DESC";
        }

        return $this->query($sql);
    }

    /**
     * Add a new store
     */
    public function insert($fk_store_category, $title, $image, $sinopse, $year, $price, $status) {

        $sql = "INSERT INTO store (fk_store_category, title, image, sinopse, year, price, status)
            VALUES ('{$fk_store_category}', '{$title}', '{$image}', '{$sinopse}', '{$year}', '{$price}', {$status});";

        return $this->query($sql);
    }

    /**
     * Edit a store
     */
    public function update($criteria = null, $id) {

        $sql = "UPDATE store SET {$criteria} WHERE id = {$id};";

        return $this->query($sql);
    }

    /**
     * Delete a store
     */
    public function delete($id) {

        $sql = "DELETE FROM store WHERE id = {$id};";

        return $this->query($sql);
    }

}