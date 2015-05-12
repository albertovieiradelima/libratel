<?php

namespace App\Model;

/**
 * CRMALL Costumer Model
 *
 * @author Victor Hugo Martins <victorhugo.pmartins@gmail.com>
 */
class CrmallCostumerModel extends CrmallBaseModel {

    protected $schemaCliente;
    protected $schemaCrmall;

    public function __construct($db) {
        parent::__construct($db);

        $this->schemaCliente    = 'CLIENTE_ABRASCE';
        $this->schemaCrmall     = 'CRMALL_ABRASCE';
    }

    /**
     * Get Costumer by CPF
     */
    public function getCostumerByCPF($cpf) {
        $sql = "SELECT C.CPF, C.NOME, C.CEP, C.LOGRADOURO ENDERECO, C.NUMERO, C.BAIRRO, C.LOCALIDADE CIDADE, C.ESTADO, NVL(NVL(TELEFONE1, TELEFONE2), TELEFONE3) TELEFONE, C.EMAIL, G.DESCRICAO CARGO FROM {$this->schemaCliente}.CLIENTE C LEFT JOIN {$this->schemaCliente}.CLIENTE_CARGO CG ON C.ID_CLIENTE = CG.FK_CLIENTE AND CG.PF_CARGO_PRINCIPAL = 'S' LEFT JOIN {$this->schemaCliente}.CARGO G ON CG.FK_CARGO = G.ID_CARGO WHERE REGEXP_REPLACE(C.CPF,'[^0-9]','') = REGEXP_REPLACE('$cpf','[^0-9]','')";

        return $this->query($sql);
    }

    /**
     * Get Costumer by CNPJ
     */
    public function getCostumerByCNPJ($cnpj) {
        $sql = "SELECT C.CNPJ as DOCUMENT, NVL(C.FANTASIA, C.RAZAO_SOCIAL) NOME, C.CEP, C.LOGRADOURO ENDERECO, C.NUMERO, C.BAIRRO, C.LOCALIDADE CIDADE, C.ESTADO, NVL(NVL(TELEFONE1, TELEFONE2), TELEFONE3) TELEFONE, C.EMAIL, {$this->schemaCliente}.GET_ID_STATUS_FILIACAO_ATUAL(C.ID_CLIENTE) AS FILIACAO FROM {$this->schemaCliente}.CLIENTE C WHERE REGEXP_REPLACE(C.CNPJ,'[^0-9]','') = REGEXP_REPLACE('$cnpj','[^0-9]','')";

        return $this->query($sql);
    }

}