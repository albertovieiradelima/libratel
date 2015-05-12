<?php

namespace App\Model;

/**
 * Event Model
 *
 * @author Alberto Vieira <albertovieiradelima@gmail.com>
 */
class EventModel extends BaseModel {

    protected $tableName = 'event';

    /**
     * Get course or event by id
     */
    public function getById($id, $type = null) {

        if ($type === null) {
            $sql = "SELECT * FROM {$this->tableName} WHERE id = {$id}";
        } else {
            $sql = "SELECT * FROM {$this->tableName} WHERE id = {$id} AND type = {$type}";
        }

        return $this->query($sql);
    }

    /**
     * Get courses or events
     */
    public function getAll($criteria = "*", $type = null) {

        if ($type === null) {
            $sql = "SELECT {$criteria} FROM {$this->tableName}";
        } else {
            $sql = "SELECT {$criteria} FROM {$this->tableName} WHERE type = {$type}";
        }

        return $this->query($sql);
    }

    /**
     * Add a new course or event
     */
    public function insert($title, $description, $image, $type, $startdate, $enddate, $starthour, $endhour, $local, $inscription, $status, $site, $exclusive_associated, $free_event, $number_vacancies, $days_invoice, $cancellation_policy) {

        $sql = "INSERT INTO {$this->tableName} (title, description, image, type, start_date, end_date, start_hour, end_hour, local, inscription, status, site, exclusive_associated, free_event, number_vacancies, days_invoice, cancellation_policy)
            VALUES ('{$title}', '{$description}', '{$image}', {$type}, '{$startdate}', '{$enddate}', '{$starthour}', '{$endhour}', '{$local}', '{$inscription}', {$status}, '{$site}', '{$exclusive_associated}', '{$free_event}', {$number_vacancies}, {$days_invoice}, '{$cancellation_policy}');";

        return $this->query($sql);
    }

    /**
     * Edit a course or event
     */
    public function update($criteria = null, $id) {

        $sql = "UPDATE {$this->tableName} SET {$criteria} WHERE id = {$id};";

        return $this->query($sql);
    }

    /**
     * Delete a course or event
     */
    public function delete($id) {

        $sql = "DELETE FROM {$this->tableName} WHERE id = {$id};";

        return $this->query($sql);
    }

    /**
     * Search event
     */
    public function search($queryString, $offset = null, $limit = null) {

        $queryString = $this->escapeString($queryString);
        
        if($limit === null && $offset === null){
            $sql = "SELECT * FROM {$this->tableName} WHERE title LIKE '%{$queryString}%' OR description LIKE '%{$queryString}%' ORDER BY start_date desc";
        }else{
            $sql = "SELECT * FROM {$this->tableName} WHERE title LIKE '%{$queryString}%' OR description LIKE '%{$queryString}%' ORDER BY start_date desc LIMIT {$offset},{$limit}";
        }
        
        return $this->query($sql);
    }
    
    /**
     * Count
     */
    public function count($queryString) {

        $queryString = $this->escapeString($queryString);
        $sql = "SELECT count(*) as count FROM {$this->tableName} WHERE title LIKE '%{$queryString}%' OR description LIKE '%{$queryString}%'";
        
        return $this->query($sql);
    }

    /**
     * Get events by month
     */
    public function getAllByMonth($date, $type = null) {
        
        if ($type === null) {
            $sql = "SELECT * FROM {$this->tableName} WHERE MONTH(start_date)={$date['month']} AND YEAR(start_date)={$date['year']}";
        } else {
            $sql = "SELECT * FROM {$this->tableName} WHERE type = {$type} AND MONTH(start_date)={$date['month']} AND YEAR(start_date)={$date['year']}";
        }
        
        return $this->query($sql);
    }

    /**
     * Get events by year
     */
    public function getAllByYear($date, $type = null) {

        if ($type === null) {
            $sql = "SELECT * FROM {$this->tableName} WHERE YEAR(start_date)={$date['year']} ORDER BY MONTH(start_date), DAY(start_date)";
        } else {
            $sql = "SELECT * FROM {$this->tableName} WHERE type = {$type} AND YEAR(start_date)={$date['year']} ORDER BY MONTH(start_date), DAY(start_date)";
        }

        return $this->query($sql);
    }

    /**
     * Get events by year and active
     */
    public function getAllThemActiveByYear($date, $type = null) {
        /*
         * AND status = 'active'
         */
        if ($type === null) {
            $sql = "SELECT * FROM {$this->tableName} WHERE YEAR(start_date)={$date['year']} AND status = 'active' ORDER BY MONTH(start_date), DAY(start_date)";
        } else {
            $sql = "SELECT * FROM {$this->tableName} WHERE type = {$type} AND YEAR(start_date)={$date['year']} AND status = 'active' ORDER BY MONTH(start_date), DAY(start_date)";
        }

        return $this->query($sql);
    }

    /**
     * Get Home Events
     */
    public function getHomeEvents() {

        $today = date('Y-m-d');

        $sql = "SELECT * FROM {$this->tableName} WHERE start_date >= '{$today}' AND type = 'Evento' AND status = 'active' ORDER BY start_date, end_date LIMIT 0,4";

        return $this->query($sql);
    }

    public function getEventRegistered($register){

        $today = date('Y-m-d');

        $sql = "SELECT E.*, R.id as register, R.company_filiation,R.fk_coupon, P.associated_price, P.standard_price, C.minimum_number as coupon_min, C.maximum_number coupon_max, C.discount_participant as discount, C.used_number as coupon_used
                FROM event E
                INNER JOIN event_registration R ON E.id = R.fk_event
                INNER JOIN event_charge_period P ON E.id = P.fk_event
                LEFT JOIN event_discount_coupon C ON C.id = R.fk_coupon
                WHERE R.id = $register
                AND P.start_date <= '{$today}' && P.end_date >= '{$today}'
                ORDER BY P.start_date LIMIT 0,1";

        return $this->query($sql);
    }

    public function getReportTrackingByEvent($event)
    {
        $sql = "SELECT ER.id AS inscription, ERP.certificate_name, ERP.cpf, ERP.job, ERP.area, ERP.sex, ERP.badge_name, ERP.badge_company, ERP.email, ERP.phone,
                ER.company_name, ER.company_document, ER.company_city, ER.company_state,
                ER.billing_name, ER.billing_document, ER.billing_address, ER.billing_zip, ER.billing_phone,
                ER.invoice_value, ER.invoice_number, ER.status, ER.invoice_due_date, ER.invoice_payment_date,
                ER.responsible_name, ER.responsible_document, ER.responsible_email, ER.responsible_job, ER.responsible_phone,
                EDC.id AS coupon, EDC.discount_participant, ER.company_filiation
                FROM event_registration_participants ERP
                INNER JOIN event_registration ER ON ER.id = ERP.fk_event_registration
                LEFT JOIN event_discount_coupon EDC ON EDC.id = ER.fk_coupon
                WHERE ER.fk_event = {$event} ORDER BY ER.id";

        $this->query($sql);
        return $this->fetch_all(MYSQLI_NUM);
    }

}