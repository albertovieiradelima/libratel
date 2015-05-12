<?php

namespace App\Model;

/**
 * CRMALL Shopping Model
 *
 * @author Anderson Costa <arcostasi@gmail.com>
 */
class CrmallSupplierModel extends CrmallBaseModel {
    
    protected $schemaCliente;
    protected $schemaCrmall;
    
    public function __construct($db) {
        parent::__construct($db);
        
        $this->schemaCliente    = 'CLIENTE_ABRASCE';
        $this->schemaCrmall     = 'CRMALL_ABRASCE';
        
    }
    
    /**
     * Get Supplier All
     */
    public function getSupplierAll() {

        $sql = "SELECT CLI.ID_CLIENTE AS ID_FORNECEDOR,
                UPPER(CLI.FANTASIA) AS FANTASIA,
                UPPER(CLI.LOCALIDADE) AS LOCALIDADE,
                CLI.ESTADO,
                CLI.TELEFONE1 AS TELEFONE,
                CLI.SITE,
                CCO.DESCRICAO_FORNECEDOR,
                (SELECT REPLACE({$this->schemaCrmall}.STRING_AGG('#' || CF.FK_CATEGORIA_FORNECEDOR || '#'),',','')
                    FROM {$this->schemaCliente}.CLIENTE_CATEGORIA_FORNEC CF
                    WHERE CF.FK_CLIENTE = CLI.ID_CLIENTE
                    GROUP BY CF.FK_CLIENTE) FK_CATEGORIA_FORNECEDOR
                FROM {$this->schemaCliente}.CLIENTE CLI
                INNER JOIN {$this->schemaCliente}.CLIENTE_TIPO_CLIENTE CTC ON (CTC.FK_CLIENTE = CLI.ID_CLIENTE)
                INNER JOIN {$this->schemaCliente}.CLIENTE_TIPO CT ON (CTC.FK_CLIENTE_TIPO = CT.ID_CLIENTE_TIPO)
                INNER JOIN {$this->schemaCliente}.CLIENTE_COLABORADOR CCO ON (CCO.FK_CLIENTE = CLI.ID_CLIENTE)
                WHERE CT.COLABORADOR = 'S' AND {$this->schemaCliente}.GET_ID_STATUS_FILIACAO_ATUAL(CLI.ID_CLIENTE) = 2
                ORDER BY CLI.FANTASIA";

        return $this->query($sql);
    }

    /**
     * Get all categories
     */
    public function getSupplierCategoryAll() {

        $sql = "SELECT CFO.ID_CATEGORIA_FORNECEDOR, CFO.DESCRICAO
                FROM {$this->schemaCliente}.CATEGORIA_FORNECEDOR CFO
                WHERE CFO.ATIVO = 'A'";

        return $this->query($sql);
    }

    /**
     * Get all contacts by supplier id
     */
    public function getSupplierContactById($id) {

        $sql = "SELECT CLI.ID_CLIENTE, NVL(C2.NOME,RAZAO_SOCIAL) AS CONTATO_PRINCIPAL
                FROM {$this->schemaCliente}.CLIENTE CLI
                INNER JOIN {$this->schemaCliente}.CLIENTE_TIPO_CLIENTE CTC ON (CTC.FK_CLIENTE = CLI.ID_CLIENTE)
                INNER JOIN {$this->schemaCliente}.CLIENTE_TIPO CT ON (CTC.FK_CLIENTE_TIPO = CT.ID_CLIENTE_TIPO)
                INNER JOIN {$this->schemaCliente}.CLIENTE_COLABORADOR CCO ON (CCO.FK_CLIENTE = CLI.ID_CLIENTE)
                INNER JOIN {$this->schemaCliente}.CLIENTE_PARENTESCO CP ON CP.FK_CLIENTE = CLI.ID_CLIENTE
                INNER JOIN {$this->schemaCliente}.CLIENTE C2 ON CP.FK_CLIENTE_PARENTE = C2.ID_CLIENTE
                WHERE CT.COLABORADOR = 'S' 
                AND CP.FK_PARENTESCO = 16
                AND CLI.ID_CLIENTE = {$id}
                ORDER BY CLI.FANTASIA";

        return $this->query($sql);
    }

    /**
     * Get Supplier Logo by Id
     */
    public function getSupplierLogoById($id) {

        $sql = "SELECT IA.ARQUIVO, LOWER(EXT_ARQUIVO) AS ARQUIVO_EXT
            FROM {$this->schemaCliente}.INFO_ARQUIVOS_WEB IA
            WHERE IA.FK_CLIENTE = {$id}
            AND IA.ARQUIVO_PUBLICO = 'S'
            AND (LOWER(IA.EXT_ARQUIVO) = '.png' OR LOWER(IA.EXT_ARQUIVO) = '.jpg')
            AND IA.TIPO_ARQUIVO = 1 ORDER BY ID_INFO_ARQUIVOS_WEB DESC";

        return $this->query($sql);
    }
    
    /**
     * Save shopping images for id
     * @param string $type (banner or logo)
     * @param int $id
     */
    public function saveSupplierImageForId($id, $type){
        
        # Check type
        switch ($type){
            case 'logo':
                $stmt = $this->getSupplierLogoById($id);
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
        $file = PATH_UPLOAD_SUPPLIER . '/' . $filename;
        
        if($this->saveBlobToFile($res['ARQUIVO'], $file)){
            return $filename;
        }
        
        return false;
    }

}