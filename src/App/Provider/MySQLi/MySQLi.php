<?php

namespace App\Provider\MySQLi;

/**
 * Methods for MySQLi
 *
 * @author Anderson Costa <arcostasi@gmail.com>
 */
class MySQLi extends \mysqli {

    /**
     * Query
     *
     * @param string $statement SQL statement
     * @param int    $type      Result Mode MYSQLI_USE_RESULT or MYSQLI_STORE_RESULT
     *
     * @return \mysqli_result
     */
    public function query($statement, $type = MYSQLI_USE_RESULT) {

        $result = parent::query($statement, $type);

        return $result;
    }

}
