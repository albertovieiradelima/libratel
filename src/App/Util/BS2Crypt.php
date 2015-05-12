<?php

/*
 * CRMALL SISTEMA DE INFORMACAO DE MARKETING LTDA - EPP
 * (c) Copyright 2013
 */

/**
 * Description of BS2Crypt
 *
 * @author Renato Peterman <renato.pet at gmail.com>
 */

namespace App\Util;

class BS2Crypt {
    
    /**
     * Criptografa uma string.
     *
     * @param  string $string
     * @param  string $key
     * @return string
     */
    public static function encrypt($string, $key = '/z8niKfqbXvTw9xe') { 
        $string = (string) $string;
	$result = ''; 
        
	for ($i=0; $i < strlen($string); $i++) { 
		$charstring = $char = substr($string, $i, 1); 
		$mod = (($i+1) % strlen($key));
		$keychar = $key[$mod]; 
		$char = chr(ord($char)+ord($keychar)); 
		$result .= $char; 
	} 
	return strtr(base64_encode($result), '+/=', '-_,');
    }
    
    /**
     * Descriptografa uma string.
     *
     * @param  string $string
     * @param  string $key
     * @return string
     */
    public static function decrypt($string, $key = '/z8niKfqbXvTw9xe') { 
        $string = (string) $string;
	$result = ''; 
	$string = base64_decode(strtr($string, '-_,', '+/='));

	for ($i=0; $i<strlen($string); $i++) { 
		$char = substr($string, $i, 1); 
		$mod = (($i+1) % strlen($key));
		$keychar = $key[$mod]; 
		$char = chr(ord($char)-ord($keychar)); 
		$result.=$char; 
	} 
	return $result; 
    } 
    
}

?>
