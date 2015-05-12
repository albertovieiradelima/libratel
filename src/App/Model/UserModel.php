<?php

namespace App\Model;
use App\Enum\SituationStatusEnum;

/**
 * User Model
 *
 * @author Anderson Costa <arcostasi@gmail.com>
 */
class UserModel extends BaseModel {

    /**
     * Get user by id
     */
    public function getById($id) {

        $sql = "SELECT * FROM user WHERE id = {$id}";

        return $this->query($sql);
    }

    /**
     * Get user by username
     */
    public function getByUsername($username, $status = SituationStatusEnum::ACTIVE) {

        $sql = "SELECT * FROM user WHERE username = '{$username}' AND status = {$status}";

        return $this->query($sql);
    }

    /**
     * Get user by criteria
     * @param $criteria
     * @return mixed
     * @throws \Exception
     */
    public function getByCriteria($criteria){
        $sql = "SELECT * FROM user WHERE {$criteria}";

        return $this->query($sql);
    }

    /**
     * Get users
     */
    public function getAllUsers($criteria = "*") {

        $sql = "SELECT {$criteria} FROM user";

        return $this->query($sql);
    }

    /**
     * Get users by group
     */
    public function getAllByGroup($criteria = "*", $fk_user_group) {

        $sql = "SELECT {$criteria} FROM user WHERE fk_user_group = {$fk_user_group}";

        $this->query($sql);
        return $this->fetch_all();
    }

    /**
     * Add a new user
     */
    public function insert($fullname, $email, $username, $password, $avatar, $status,$cpf,$phone,$job,$area) {

        $sql = "INSERT INTO user (username, password, fullname, email, roles, avatar, status,cpf,phone,job,area)
            VALUES ('{$username}', '{$password}', '{$fullname}', '{$email}', 'ROLE_USER', '{$avatar}', {$status},'{$cpf}','{$phone}','{$job}','{$area}');";

        return $this->query($sql);
    }

    /**
     * Edit a user
     */
    public function update($criteria = null, $id) {

        $sql = "UPDATE user SET {$criteria} WHERE id = {$id};";

        return $this->query($sql);
    }

    /**
     * Delete a user
     */
    public function delete($id) {

        $sql = "DELETE FROM user WHERE id = {$id};";

        return $this->query($sql);
    }

}