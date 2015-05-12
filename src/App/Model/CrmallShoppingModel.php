<?php

namespace App\Model;

/**
 * CRMALL Shopping Model
 *
 * @author Anderson Costa <arcostasi@gmail.com>
 */
class CrmallShoppingModel extends CrmallBaseModel {
    
    protected $schemaCliente;
    protected $schemaCrmall;
    
    public function __construct($db) {
        parent::__construct($db);
        
        $this->schemaCliente    = 'CLIENTE_ABRASCE';
        $this->schemaCrmall     = 'CRMALL_ABRASCE';
        
    }

    /**
     * Get Shopping All
     */
    public function getShoppingAll() {
        
        $sql = "SELECT CLI.ID_CLIENTE, UPPER(CLI.FANTASIA) AS FANTASIA, CLI.LOGRADOURO, CLI.NUMERO, CLI.BAIRRO, UPPER(CLI.LOCALIDADE) AS LOCALIDADE, CLI.LOCALIDADE, CLI.CEP, CLI.ESTADO, CLI.TELEFONE1 AS TELEFONE,
                CLI.SITE, INF.AREA_TERRENO, INF.AREA_CONSTRUIDA, INF.ABL, INF.PERFIL_A, INF.PERFIL_B, INF.PERFIL_C, INF.PISOS_LOJAS, INF.LOJAS_ANCORAS, INF.TOTAL_LOJAS, ENT.SALAS AS SALAS_CINEMAS, INF.VAGAS_ESTACIONAMENTO,
                {$this->schemaCliente}.GET_ID_STATUS_FILIACAO_ATUAL(CLI.ID_CLIENTE) AS FILIACAO, CLI.CNPJ,
                CASE WHEN INSTR(REGEXP_REPLACE('-'||INF.CARACTERISTICA_SHOPPING||'-', '[a-zA-Z]', ''),'-2-') > 0 THEN 2
                    WHEN INSTR(REGEXP_REPLACE('-'||INF.CARACTERISTICA_SHOPPING||'-', '[a-zA-Z]', ''),'-3-') > 0 THEN 3
                END MARCACAO
                FROM {$this->schemaCliente}.CLIENTE CLI
                INNER JOIN {$this->schemaCliente}.CLIENTE_TIPO_CLIENTE CTC ON (CTC.FK_CLIENTE = CLI.ID_CLIENTE)
                INNER JOIN {$this->schemaCliente}.CLIENTE_TIPO CT ON (CTC.FK_CLIENTE_TIPO = CT.ID_CLIENTE_TIPO)
                INNER JOIN {$this->schemaCliente}.CLIENTE_INFO_SHOPPING INF ON (INF.FK_CLIENTE = CLI.ID_CLIENTE)
                LEFT JOIN (SELECT * FROM {$this->schemaCliente}.CLIENTE_ENTRETENIMENTO where FK_TIPO_ENTRETENIMENTO = 29) ENT ON (ENT.FK_CLIENTE = CLI.ID_CLIENTE)
                WHERE CTC.FK_CLIENTE_TIPO = 8
                AND (CASE WHEN INSTR(REGEXP_REPLACE('-'||INF.CARACTERISTICA_SHOPPING||'-', '[a-zA-Z]', ''),'-2-') > 0 THEN 2
                     WHEN INSTR(REGEXP_REPLACE('-'||INF.CARACTERISTICA_SHOPPING||'-', '[a-zA-Z]', ''),'-3-') > 0 THEN 3
                     ELSE 0
                END) > 0
                ORDER BY CLI.FANTASIA";

        return $this->query($sql);
    }

    /**
     * Get Shopping Banner by Id
     */
    public function getShoppingBannerById($id) {

        $sql = "SELECT IA.ARQUIVO, LOWER(EXT_ARQUIVO) AS ARQUIVO_EXT
            FROM {$this->schemaCliente}.INFO_ARQUIVOS_WEB IA
            WHERE IA.FK_CLIENTE = {$id}
            AND IA.ARQUIVO_PUBLICO = 'S'
            AND (LOWER(IA.EXT_ARQUIVO) = '.png' OR LOWER(IA.EXT_ARQUIVO) = '.jpg')
            AND IA.TIPO_ARQUIVO = 0 ORDER BY ID_INFO_ARQUIVOS_WEB DESC";

        return $this->query($sql);
    }

    /**
     * Get Shopping Logo by Id
     */
    public function getShoppingLogoById($id) {

        $sql = "SELECT IA.ARQUIVO, LOWER(EXT_ARQUIVO) AS ARQUIVO_EXT
            FROM {$this->schemaCliente}.INFO_ARQUIVOS_WEB IA
            WHERE IA.FK_CLIENTE = {$id}
            AND IA.ARQUIVO_PUBLICO = 'S'
            AND (LOWER(IA.EXT_ARQUIVO) = '.png' OR LOWER(IA.EXT_ARQUIVO) = '.jpg')
            AND IA.TIPO_ARQUIVO = 1 ORDER BY ID_INFO_ARQUIVOS_WEB DESC";

        return $this->query($sql);
    }

    /**
     * Get Shopping Admin by Id
     */
    public function getShoppingAdminById($id) {

        $sql = "SELECT DISTINCT CP.FK_CLIENTE ID_ADMINISTRADORA,
                    NVL(C.NOME, NVL(C.FANTASIA,C.RAZAO_SOCIAL)) ADMINISTRADORA
                    FROM {$this->schemaCliente}.CLIENTE_PARENTESCO CP
                    JOIN {$this->schemaCliente}.CLIENTE C ON C.ID_CLIENTE = CP.FK_CLIENTE
                    JOIN {$this->schemaCliente}.PARENTESCO P ON P.ID_PARENTESCO = CP.FK_PARENTESCO
                    AND P.ID_PARENTESCO = 31
                WHERE FK_CLIENTE_PARENTE = {$id}";

        return $this->query($sql);
    }

    /**
     * Get Shopping Entertainment by Id
     */
    public function getShoppingEntertainmentById($id) {

        $sql = "SELECT CT.ID_TIPO_ENTRETENIMENTO, CT.DESCRICAO FROM {$this->schemaCliente}.CLIENTE_ENTRETENIMENTO CE
                INNER JOIN {$this->schemaCliente}.TIPO_ENTRETENIMENTO CT ON (CT.ID_TIPO_ENTRETENIMENTO = CE.FK_TIPO_ENTRETENIMENTO)
                WHERE CE.FK_CLIENTE = {$id}
                ORDER BY CT.DESCRICAO";

        return $this->query($sql);
    }
    
    /**
     * Save shopping images for id
     * @param string $type (banner or logo)
     * @param int $id
     */
    public function saveShoppingImageForId($id, $type){
        
        # Check type
        switch ($type){
            case 'logo':
                $stmt = $this->getShoppingLogoById($id);
                break;
            case 'banner':
                $stmt = $this->getShoppingBannerById($id);
                break;
            default:
                return false;
        }
        
        if(!$stmt){
            return false;
        }
        
        $res = oci_fetch_array($stmt);
        if(!$res){
            return false;
        }
        
        $filename = $type . '_' . md5($id) . $res['ARQUIVO_EXT'];
        $file = PATH_UPLOAD_SHOPPING . '/' . $filename;
        
        if($this->saveBlobToFile($res['ARQUIVO'], $file)){
            return $filename;
        }
        
        return false;
    }
    
}
