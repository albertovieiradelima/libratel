<?php

namespace App\Model;

/**
 * Shopping Model
 *
 * @author Anderson Costa <arcostasi@gmail.com>
 */
class ShoppingModel extends BaseModel {

    /**
     * Get Shopping All
     */
    public function getShoppingAll() {

        $sql = "SELECT id_shopping, fantasia, localidade, estado, telefone, filiacao, cnpj, marcacao
                FROM shopping
                ORDER BY fantasia";

        return $this->query($sql);
    }

    /**
     * Get Shopping by A-Z
     */
    public function getShoppingByAZ($letter) {

        $sql = "SELECT id_shopping, fantasia, localidade, estado, telefone, filiacao, marcacao
                FROM shopping
                WHERE fantasia LIKE '{$letter}%'
                ORDER BY fantasia";

        return $this->query($sql);
    }

    /**
     * Get Shopping by UF
     */
    public function getShoppingByUF($uf) {

        $sql = "SELECT id_shopping, fantasia, localidade, estado, telefone, filiacao, marcacao
                FROM shopping
                WHERE estado = '{$uf}'
                ORDER BY fantasia";

        return $this->query($sql);
    }

    /**
     * Get Shopping by Id
     */
    public function getShoppingById($id) {

        $sql = "SELECT id_shopping, fantasia, logradouro, numero, bairro, localidade, cep, estado, telefone, site,
                    area_terreno, area_construida, abl, perfil_a, perfil_b, perfil_c, 
                    pisos_lojas, lojas_ancoras, total_lojas, salas_cinemas, vagas_estacionamento, banner, logo, cnpj, marcacao
                FROM shopping
                WHERE id_shopping = {$id}";

        return $this->query($sql);
    }

    /**
     * Get criteria Shopping by Id
     */
    public function getShoppingCriteriaById($criteria = '*', $id) {

        $sql = "SELECT {$criteria}
                FROM shopping
                WHERE id_shopping = {$id}";

        $this->query($sql);
        return $this->fetch();
    }

    /**
     * Get Shopping Banner by Id
     */
    public function getShoppingBannerById($id) {

        $sql = "SELECT banner
                FROM shopping
                WHERE id_shopping = {$id}";

        return $this->query($sql);
    }

    /**
     * Get Shopping Logo by Id
     */
    public function getShoppingLogoById($id) {

        $sql = "SELECT logo
                FROM shopping
                WHERE id_shopping = {$id}";

        return $this->query($sql);
    }

    /**
     * Get Shopping Admin by Id
     */
    public function getShoppingAdminById($id) {

        $sql = "SELECT administradora 
                FROM shopping_administrator
                WHERE fk_shopping = {$id}
                ORDER BY administradora";

        return $this->query($sql);
    }

    /**
     * Get Shopping Entertainment by Id
     */
    public function getShoppingEntertainmentById($id) {

        $sql = "SELECT descricao 
                FROM shopping_entertainment
                WHERE fk_shopping = {$id}
                ORDER BY descricao";

        return $this->query($sql);
    }
    
    /**
     * Remove all shoppings records
     */
    public function removeAllShopping(){
        $sql = "DELETE FROM shopping";
        return $this->query($sql);
    }
    
    /**
     * Remove all shoppings administrators records
     */
    public function removeAllShoppingAdmin(){
        $sql = "DELETE FROM shopping_administrator";
        return $this->query($sql);
    }
    
    /**
     * Remove all shoppings entertainments
     */
    public function removeAllShoppingEntertainment(){
        $sql = "DELETE FROM shopping_entertainment";
        return $this->query($sql);
    }

    /**
     * Remove All Shopping opened non affiliate
     */
    public function removeAllOpenedNonAffiliate() {

        $sql = "DELETE FROM shopping
                WHERE filiacao IN (1, 3) AND marcacao = 2";
        return $this->query($sql);
    }

    /**
     * Get All Shopping opened non affiliate
     */
    public function getAllOpenedNonAffiliate() {

        $sql = "SELECT * FROM shopping
                WHERE filiacao IN (1, 3) AND marcacao = 2";
        return $this->query($sql);
    }

    /**
     * Set shopping
     * @param array $data
     */
    public function insertShopping($data){
        
        if(!$data || !is_array($data)){
            throw new \Exception('Invalid param - param data must by an associative array');
        }
        
        # Fields
        $stringFields = array('FANTASIA','LOGRADOURO','NUMERO','BAIRRO','LOCALIDADE','CEP','ESTADO','TELEFONE','SITE','CNPJ');
        $numberFields = array('AREA_TERRENO','AREA_CONSTRUIDA','ABL','PERFIL_A','PERFIL_B','PERFIL_C','PISOS_LOJAS','LOJAS_ANCORAS','TOTAL_LOJAS','SALAS_CINEMAS','VAGAS_ESTACIONAMENTO','FILIACAO', 'MARCACAO');
        
        # Validate
        foreach($data as $key => $val){

            if(in_array($key,$stringFields)){

                $data[$key] = $val ? $this->escapeString($val) : ""; # Validate and escape

            }else if(in_array($key,$numberFields)){

                $data[$key] = $val ? $val : "0";

            }

        }
        
        $sql = "INSERT INTO shopping (id_shopping, fantasia, logradouro, numero, bairro, localidade, cep, estado, telefone, site, area_terreno, area_construida, abl, perfil_a, perfil_b, perfil_c, pisos_lojas, lojas_ancoras, total_lojas, salas_cinemas, vagas_estacionamento, filiacao, banner, logo, cnpj, marcacao) "
            . "VALUES ({$data['ID_CLIENTE']}, '{$data['FANTASIA']}', '{$data['LOGRADOURO']}', '{$data['NUMERO']}', '{$data['BAIRRO']}', '{$data['LOCALIDADE']}', '{$data['CEP']}',"
            . "'{$data['ESTADO']}', '{$data['TELEFONE']}', '{$data['SITE']}', {$data['AREA_TERRENO']}, {$data['AREA_CONSTRUIDA']}, {$data['ABL']}, {$data['PERFIL_A']}, {$data['PERFIL_B']}, {$data['PERFIL_C']}, {$data['PISOS_LOJAS']},"
            . "{$data['LOJAS_ANCORAS']}, {$data['TOTAL_LOJAS']}, {$data['SALAS_CINEMAS']}, {$data['VAGAS_ESTACIONAMENTO']}, {$data['FILIACAO']}, '{$data['BANNER']}', '{$data['LOGO']}', '{$data['CNPJ']}', '{$data['MARCACAO']}')";
            
        return $this->query($sql);
        
    }
    
    /**
     * Insert shopping administrator
     * @param int $shopping
     * @param array $data
     * @throws \Exception
     */
    public function insertShoppingAdmin($shopping, $data){
        
        if(!$data || !is_array($data)){
            throw new \Exception('Invalid param - param data must by an associative array');
        }
        
        if(!$shopping){
            throw new \Exception('Invalid shopping id');
        }
        
        $data['ADMINISTRADORA'] = $data['ADMINISTRADORA'] ? $data['ADMINISTRADORA'] : "";
        $sql = "INSERT INTO shopping_administrator(fk_shopping, id_administrator, administradora) VALUES({$shopping},{$data['ID_ADMINISTRADORA']}, '{$data['ADMINISTRADORA']}')";
        return $this->query($sql);
    }
    
    /**
     * Insert shopping entertainments
     * @param int $shopping
     * @param array $data
     * @throws \Exception
     */
    public function insertShoppingEntertainment($shopping, $data){
        
        if(!$data || !is_array($data)){
            throw new \Exception('Invalid param - param data must by an associative array');
        }
        
        if(!$shopping){
            throw new \Exception('Invalid shopping id');
        }
        
        $data['DESCRICAO'] = $data['DESCRICAO'] ? $data['DESCRICAO'] : "";
        $sql = "INSERT INTO shopping_entertainment(fk_shopping, id_entertainment, descricao) VALUES({$shopping},{$data['ID_TIPO_ENTRETENIMENTO']}, '{$data['DESCRICAO']}')";
        return $this->query($sql);
    }

}