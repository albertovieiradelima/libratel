<?php

namespace App\Model;
use App\Enum\SituationStatusEnum;

/**
 * Feed Model
 *
 * @author Alberto Vieira <albertovieiradelima@gmail.com>
 * @author Renato Peterman <renato.pet@gmail.com>
 */
class FeedModel extends BaseModel {

    /**
     * Get feed by id
     */
    public function getById($id) {

        $sql = "SELECT * FROM feed WHERE id = {$id}";
        return $this->query($sql);
    }

    /**
     * Get feed
     */
    public function getAll($criteria = "*", $type = null) {

        if($type === null){
            $sql = "SELECT {$criteria} FROM feed ORDER BY date DESC";
        }else{
            $sql = "SELECT {$criteria} FROM feed WHERE type = {$type} ORDER BY date DESC";
        }
        
        return $this->query($sql);
    }
    
    /**
     * Search feed
     */
    public function search($queryString, $offset = null, $limit = null, $type=null) {

        $queryString = $this->escapeString($queryString);
        
        if($limit === null && $offset === null){
            $sql = "SELECT * FROM feed WHERE type = {$type} AND title LIKE '%{$queryString}%' OR description LIKE '%{$queryString}%' ORDER BY date desc";
        }else{
            $sql = "SELECT * FROM feed WHERE type = {$type} AND title LIKE '%{$queryString}%' OR description LIKE '%{$queryString}%' ORDER BY date desc LIMIT {$offset},{$limit}";
        }
        
        return $this->query($sql);
    }
    
    /**
     * Count
     */
    public function count($queryString, $type=null) {

        $queryString = $this->escapeString($queryString);
        $sql = "SELECT count(*) as count FROM feed WHERE type = {$type} AND title LIKE '%{$queryString}%' OR description LIKE '%{$queryString}%'";
        
        return $this->query($sql);
    }
    
    /**
     * Get feed
     */
    public function getAllByMonth($date, $type=null) {
        
        if($type === null){
            $sql = "SELECT * FROM feed WHERE MONTH(date)={$date['month']} AND YEAR(date)={$date['year']} AND status = ".SituationStatusEnum::ACTIVE." ORDER BY date DESC";
        }else{
            $sql = "SELECT * FROM feed WHERE type = {$type} AND MONTH(date)={$date['month']} AND YEAR(date)={$date['year']} AND status = ".SituationStatusEnum::ACTIVE." ORDER BY date DESC";
        }
        
        return $this->query($sql);
    }

    /**
     * Add a new feed
     */
    public function insert($title, $description, $image, $thumb, $type, $date, $status) {

        $sql = "INSERT INTO feed (title, description, image, thumb, type, date, status) 
            VALUES ('{$title}', '{$description}', '{$image}', '{$thumb}', {$type}, '{$date}', {$status});";

        return $this->query($sql);
    }

    /**
     * Edit a feed
     */
    public function update($criteria = null, $id) {

        $sql = "UPDATE feed SET {$criteria} WHERE id = {$id};";

        return $this->query($sql);
    }

    /**
     * Delete a feed
     */
    public function delete($id) {

        $sql = "DELETE FROM feed WHERE id = {$id};";

        return $this->query($sql);
    }

    /**
     * Get Home Feeds
     */
    public function getHomeFeeds($type) {

        $sql = "SELECT * FROM feed
                WHERE type = {$type} AND thumb IS NOT NULL AND status = ".SituationStatusEnum::ACTIVE."
                ORDER BY RAND() LIMIT 0,1";

        return $this->query($sql);
    }

}