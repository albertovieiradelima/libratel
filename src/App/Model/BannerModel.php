<?php

namespace App\Model;
use App\Enum\BannerTypeEnum;
use App\Enum\SituationStatusEnum;

/**
 * Banner Model
 *
 * @author Alberto Vieira <albertovieiradelima@gmail.com>
 */
class BannerModel extends BaseModel {

    /**
     * Get banner by id
     */
    public function getById($id) {

        $sql = "SELECT * FROM banner WHERE id = {$id}";

        return $this->query($sql);
    }

    /**
     * Get banners
     */
    public function getAll($criteria = "*", $type = BannerTypeEnum::TOPIMAGE) {

        $sql = "SELECT {$criteria} FROM banner WHERE type = {$type}";

        return $this->query($sql);
    }

    /**
     * Get banners order by order
     */
    public function get($sql = null) {

        if(!is_null($sql)) {
            return $this->query($sql);
        } else {
            return false;
        }

    }

    /**
     * Get Count Active banners
     */
    public function getCountActive($type = BannerTypeEnum::TOPIMAGE) {

        $sql = "SELECT COUNT(id) AS qtde FROM banner WHERE status = ". SituationStatusEnum::ACTIVE." AND type = {$type} AND banner.order IS NOT NULL";

        return $this->query($sql);
    }

    /**
     * Add a new banner
     */
    public function insert($image, $link, $order, $type, $date, $status, $title, $description) {

        $sql = "INSERT INTO banner (image, link, banner.order, type, date, status, title, description) VALUES ('{$image}', '{$link}', '{$order}', '{$type}', '{$date}', '{$status}', '{$title}', '{$description}');";

        return $this->query($sql);
    }

    /**
     * Edit a banner
     */
    public function update($criteria = null, $id) {

        $sql = "UPDATE banner SET {$criteria} WHERE id = {$id};";

        return $this->query($sql);
    }

    /**
     * Delete a banner
     */
    public function delete($id) {

        $sql = "DELETE FROM banner WHERE id = {$id};";

        return $this->query($sql);
    }

    /**
     * Get Home Banners
     */
    public function getTopImagesByActive() {

        $sql = "SELECT * FROM banner 
                WHERE status = 'active' AND type = ".BannerTypeEnum::TOPIMAGE." AND banner.order IS NOT NULL
                ORDER BY banner.order";

        return $this->query($sql);
    }

    /**
     * Get Home Banners
     */
    public function getBannersByActive() {

        $sql = "SELECT * FROM banner 
                WHERE status = 'active' AND type = ".BannerTypeEnum::BANNER."
                LIMIT 0,1";

        return $this->query($sql);
    }

}