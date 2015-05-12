<?php

namespace App\Util;

/**
 * Classe utilizada para formatar textos e números
 *
 * @author Anderson Costa <arcostasi@gmail.com>
 */
class TwigMask extends \Twig_Extension {

    /**
     * Get extension name
     */
    public function getName() {

        return "twig.mask";
    }

    /**
     * Set new filter
     */
    public function getFilters() {

        return array(
            new \Twig_SimpleFilter('phone', array($this, "phone")),
            new \Twig_SimpleFilter('cep', array($this, "cep")),
        );
    }

    /**
     * Formata número de telefone no padrão BR
     *
     * @param (String) $phone - Número de telefone
     *
     */
    public function phone($phone) {

        $phone = preg_replace("/[^0-9]/", "", $phone);

        if(strlen($phone) == 8) {
            return preg_replace("/([0-9]{4})([0-9]{4})/", "$1-$2", $phone);
        } elseif(strlen($phone) == 10) {
            return preg_replace("/([0-9]{2})([0-9]{4})([0-9]{4})/", "($1) $2-$3", $phone);
        } elseif(strlen($phone) == 11) {
            return preg_replace("/([0-9]{2})([0-9]{5})([0-9]{4})/", "($1) $2-$3", $phone);
        } else {
            return $phone;
        }
    }

    /**
     * Formata o CEP no padrão BR
     *
     * @param (String) $cep
     *
     */
    public function cep($cep) {

        $cep = preg_replace("/[^0-9]/", "", $cep);

        if(strlen($cep) == 8) {
            return preg_replace("/([0-9]{5})([0-9]{3})/", "$1-$2", $cep);
        } else {
            return $cep;
        }
    }

}