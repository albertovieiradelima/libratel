<?php

namespace App\Util;

/**
 * Date Class Util
 *
 * @author Renato Peterman <renato.peterman@crmall.com>
 * @param  Create date: 30/01/2015 11:38 AM
 * 
 */
class DateUtil {

    public static function formatDateString($str, $fromFormat, $toFormat, $tz = null) {

        // If timezone is null define America/Sao_Paulo timezone
        if(null === $tz){
            $tz = new \DateTimeZone('America/Sao_Paulo');
        }
        return \DateTime::createFromFormat($fromFormat, $str, $tz)->format($toFormat);
    }

    public static function formatDatetimeStringToMysql($str) {

        return static::formatDateString($str, 'd/m/Y H:i', 'Y-m-d H:i:s');
    }

    public static function formatDateStringToMysql($str) {

        return static::formatDateString($str, 'd/m/Y', 'Y-m-d');
    }

    public static function formatDateMysqlToView($str) {

        return static::formatDateString($str, 'Y-m-d', 'd/m/Y');
    }

    /**
     * Get Month name
     *
     * @param (int)$id
     * @return String
     */
    public static function getMonthName($id) {

        $months = array(
            1 => 'Janeiro',
            2 => 'Fevereiro',
            3 => 'Março',
            4 => 'Abril',
            5 => 'Maio',
            6 => 'Junho',
            7 => 'Julho',
            8 => 'Agosto',
            9 => 'Setembro',
            10 => 'Outubro',
            11 => 'Novembro',
            12 => 'Dezembro'
        );

        return $months[intval($id)];
    }

}