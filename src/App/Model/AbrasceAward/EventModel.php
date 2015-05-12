<?php

namespace App\Model\AbrasceAward;

/**
 * Event Model
 *
 * @author Alberto Vieira <albertovieiradelima@gmail.com>
 */
class EventModel extends \App\Model\BaseModel {

    /**
     * Get event by id
     */
    public function getById($id) {

        $sql = "SELECT * FROM aa_event WHERE id = {$id}";

        return $this->query($sql);
    }

    /**
     * Get event by id
     */
    public function getAllId($id) {

        $sql = "SELECT id FROM aa_event";

        $this->query($sql);
        return $this->fetch_all();
    }

    /**
     * Get events
     */
    public function getAll($criteria = "*") {

        $sql = "SELECT {$criteria} FROM aa_event";

        return $this->query($sql);
    }

    /**
     * Get the last event
     */
    public function getLast() {

        $sql = "SELECT * FROM aa_event ORDER BY id DESC LIMIT 1";

        return $this->query($sql);
    }

    /**
     * Get event by year
     */
    public function getByYear($year) {

        $sql = "SELECT * FROM aa_event WHERE `year`={$year} ORDER BY id DESC LIMIT 1";

        return $this->query($sql);
    }

    /**
     * Get events
     */
    public function getAllOrderByYear() {

        $sql = "SELECT * FROM aa_event ORDER BY `year` DESC";

        return $this->query($sql);
    }

    /**
     * Add a new event
     */
    public function insert($title, $description, $year, $start_date, $end_date) {

        $sql = "INSERT INTO aa_event (title, description, year, start_date, end_date) 
            VALUES ('{$title}', '{$description}', '{$year}', '{$start_date}', '{$end_date}');";

        return $this->query($sql);
    }

    /**
     * Edit a event
     */
    public function update($criteria = null, $id) {

        $sql = "UPDATE aa_event SET {$criteria} WHERE id = {$id};";

        return $this->query($sql);
    }

    /**
     * Delete a event
     */
    public function delete($id) {

        $sql = "DELETE FROM aa_event WHERE id = {$id};";

        return $this->query($sql);
    }

}