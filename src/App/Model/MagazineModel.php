<?php

namespace App\Model;
use App\Enum\SituationStatusEnum;

/**
 * Magazine Model
 *
 * @author Alberto Vieira <albertovieiradelima@gmail.com>
 */
class MagazineModel extends BaseModel {

    /**
     * Get magazine by id
     */
    public function getById($id) {

        $sql = "SELECT * FROM magazine WHERE id = {$id};";

        return $this->query($sql);
    }

    /**
     * Get magazine
     */
    public function getAll($criteria = '*') {

        $sql = "SELECT {$criteria} FROM magazine;";

        return $this->query($sql);
    }

    /**
     * Get magazine
     */
    public function getAllOrder($criteria = '*', $order = 'ORDER BY date DESC') {

        $sql = "SELECT {$criteria} FROM magazine WHERE status = ".SituationStatusEnum::ACTIVE." {$order};";

        return $this->query($sql);
    }

    /**
     * Add a new magazine
     */
    public function insert($publication, $title, $image, $sinopse, $description, $date, $status) {

        $sql = "INSERT INTO magazine (publication, title, image, sinopse, description, date, status) 
            VALUES ('{$publication}', '{$title}', '{$image}', '{$sinopse}', '{$description}', '{$date}', {$status});";

        return $this->query($sql);
    }

    /**
     * Edit a magazine
     */
    public function update($criteria = null, $id) {

        $sql = "UPDATE magazine SET {$criteria} WHERE id = {$id};";

        return $this->query($sql);
    }

    /**
     * Delete a magazine
     */
    public function delete($id) {

        $sql = "DELETE FROM magazine WHERE id = {$id};";

        return $this->query($sql);
    }

    /**
     * Get Home Events
     */
    public function getHomeMagazines() {

        $sql = "SELECT * FROM magazine 
                WHERE status = ".SituationStatusEnum::ACTIVE."
                ORDER BY date DESC
                LIMIT 0,1";

        return $this->query($sql);
    }

}