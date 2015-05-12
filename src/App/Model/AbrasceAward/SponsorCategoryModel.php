<?php
/**
 * Created by PhpStorm.
 * User: albertovieiradelima
 * Date: 27/01/15
 * Time: 09:36
 */
namespace App\Model\AbrasceAward;

/**
 * SponsorCategory Model
 *
 * @author Alberto Vieira <albertovieiradelima@gmail.com>
 */
class SponsorCategoryModel extends \App\Model\BaseModel {

    /**
     * Get sponsor category by id
     */
    public function getById($id) {

        $sql = "SELECT * FROM aa_sponsor_category WHERE id = {$id}";

        return $this->query($sql);
    }

    /**
     * Get sponsor categories
     */
    public function getAll($criteria = '*') {

        $sql = "SELECT {$criteria} FROM aa_sponsor_category";

        return $this->query($sql);
    }

    /**
     * Add a new sponsor category
     */
    public function insert($name, $status) {

        $sql = "INSERT INTO aa_sponsor_category (name, status)
            VALUES ('{$name}', {$status});";

        return $this->query($sql);
    }

    /**
     * Edit a sponsor category
     */
    public function update($criteria = null, $id) {

        $sql = "UPDATE aa_sponsor_category SET {$criteria} WHERE id = {$id};";

        return $this->query($sql);
    }

    /**
     * Delete a sponsor category
     */
    public function delete($id) {

        $sql = "DELETE FROM aa_sponsor_category WHERE id = {$id};";

        return $this->query($sql);
    }

}