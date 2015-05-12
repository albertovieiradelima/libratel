<?php

namespace App\Model\RestrictedArea;
use App\Enum\SituationStatusEnum;

/**
 * SponsorCategory Model
 *
 * @author Victor Hugo Martins <victorhugo.pmartins@gmail.com>
 */
class FileCategoryModel extends \App\Model\BaseModel
{

    /**
     * Get file category by id
     */
    public function getById($id)
    {

        $sql = "SELECT * FROM file_category WHERE id = {$id}";

        return $this->query($sql);
    }

    /**
     * Get all file categories
     */
    public function getAll($criteria = '*')
    {

        $sql = "SELECT {$criteria} FROM file_category";

        $this->query($sql);
        return $this->fetch_all(MYSQL_NUM);
    }

    /**
     * Get all active file categories
     */
    public function getAllActive($criteria = '*')
    {

        $sql = "SELECT {$criteria} FROM file_category WHERE status = ". SituationStatusEnum::ACTIVE;

        $this->query($sql);
        return $this->fetch_all();
    }

    /**
     * Add a new file category
     */
    public function insert($name, $status)
    {

        $sql = "INSERT INTO file_category (name, status)
            VALUES ('{$name}', {$status});";

        return $this->query($sql);
    }

    /**
     * Edit a file category
     */
    public function update($criteria = null, $id)
    {

        $sql = "UPDATE file_category SET {$criteria} WHERE id = {$id};";

        return $this->query($sql);
    }

    /**
     * Delete a file category
     */
    public function delete($id)
    {

        $sql = "DELETE FROM file_category WHERE id = {$id};";

        return $this->query($sql);
    }

}