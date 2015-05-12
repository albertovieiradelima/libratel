<?php

namespace App\Model\AbrasceAward;

/**
 * Sponsor Model
 *
 * @author Alberto Vieira <albertovieiradelima@gmail.com>
 */
class SponsorModel extends \App\Model\BaseModel {

    /**
     * Get sponsor by id
     */
    public function getById($id) {

        $sql = "SELECT * FROM aa_sponsor WHERE id = {$id}";

        return $this->query($sql);
    }

    /**
     * Get sponsors
     */
    public function getAll($criteria = '*') {

        $sql = "SELECT {$criteria} FROM aa_sponsor";

        return $this->query($sql);
    }

    /**
     * Get sponsors by query
     */
    public function get($sql = null) {

        if(!is_null($sql)) {
            return $this->query($sql);
        } else {
            return false;
        }

    }

    /**
     * Add a new sponsor
     */
    public function insert($name, $logo, $description, $link, $status) {

        $sql = "INSERT INTO aa_sponsor (name, logo, description, link, status)
            VALUES ('{$name}', '{$logo}', '{$description}', '{$link}', {$status});";

        return $this->query($sql);
    }

    /**
     * Edit a sponsor
     */
    public function update($criteria = null, $id) {

        $sql = "UPDATE aa_sponsor SET {$criteria} WHERE id = {$id};";

        return $this->query($sql);
    }

    /**
     * Delete a sponsor
     */
    public function delete($id) {

        $sql = "DELETE FROM aa_sponsor WHERE id = {$id};";

        return $this->query($sql);
    }

}