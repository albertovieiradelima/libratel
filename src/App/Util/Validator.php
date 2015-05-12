<?php

namespace App\Util;

/**
 * Classe utilizada para validar parâmetros de requisição
 *
 * @author Anderson Costa <arcostasi@gmail.com>
 */
class Validator {

    /**
     * Validação de campos nulos e especiais com retorno de mensagem de erro
     * 
     * @param (array) $data : dados da requisição no formato array ou json, exemplo: {'nome' => 'Anderson Costa', 'email' => 'arcostasi@gmail.com'}
     * @param (array) $fields : campos de apresentação no formato array, exemplo {'nome' => 'Nome Completo', 'email' => 'Email do Cliente'}
     * @param (string) $mask_fields : mascara de formação dos campos de apresentação, exemplo "- %f<br>", onde $f = campo, $k = chave
     * @param (array) $special_fields : parâmetros especiais, exemplo {'EmailCliente' => 'email', 'Telefone' => 'phone'}
     *
     * @return (string) 
     *
     */
    public function validateData($data, $fields, $mask_fields, $special_fields = array()) {

        $error = '';

        foreach ($fields as $key => $field) {

            if (isset($data[$key]) && empty($data[$key])) {
                $error .= $this->transMask($mask_fields, $key, $field);
            } else {
                // special validators
                if (isset($special_fields[$key]) && $special_fields[$key] == 'email') {
                    // e-mail validator
                    if (!$this->validateEmail($data[$key])) {
                        $error .= $this->transMask($mask_fields, $key, $field . " informado é inválido.");
                    }
                }
            }
        }

        return empty($error) ? true : $error;
    }

    /**
     * Parse de formatação da mascara
     *
     * @return (string)
     *
     */
    public function transMask($mask, $key, $field) {

        $result = '';
        $str = str_split($mask);
        $open_tag = false;

        foreach ($str as $char) {

            if ($open_tag) {
                // result key value
                if ($char == 'k') {
                    $result .= $key;
                } else if ($char == 'f') {
                    $result .= $field;
                } else {
                    $result .= $char;
                }
            } else {
                if ($char == '%') {
                    $open_tag = true;
                } else {
                    $open_tag = false;
                    $result .= $char;
                }
            }
        }

        return $result;
    }

    /**
     * Verifica se o E-mail é válido
     *
     * @author Alberto Vieira <albertovieiradelima@gmail.com>
     */
    public function validateEmail($email) {

        $pattern = "/^(([0-9a-zA-Z]+[-._+&])*[0-9a-zA-Z]+@([-0-9a-zA-Z]+[.])+[a-zA-Z]{2,6}){0,1}$/";

        if (preg_match($pattern, $email)) {
            return true;
        } else {
            return false;
        }
    }

}

