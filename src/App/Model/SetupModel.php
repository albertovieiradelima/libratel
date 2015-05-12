<?php

namespace App\Model;

/**
 * Setup Model
 *
 * @author Anderson Costa <arcostasi@gmail.com>
 */
class SetupModel extends BaseModel {

    /**
     * Get SMTP by Id
     * @param (int)$id
     */
    public function getSMTPById($id = 1) {

        $sql = "SELECT id, smtp_host, smtp_port, smtp_user, smtp_pass, smtp_email, smtp_name
                FROM setup
                WHERE id = {$id}";

        $this->query($sql);

        return $this->fetch();
    }

    /**
     * Get Data Boleto by Id
     * @param (int)$id
     */
    public function getDataBoletoById($id = 1) {

        $sql = "SELECT id, cedente_name, cedente_cnpj, cedente_address, cedente_zip, cedente_city, cedente_state, 
                    cedente_agencia, cedente_agencia_dv, cedente_conta, cedente_conta_dv, cedente_carteira, 
                    cedente_label1, cedente_label2, cedente_label3, cedente_label4, cedente_label5, cedente_label6
                FROM setup
                WHERE id = {$id}";

        $this->query($sql);

        return $this->fetch();
    }

    /**
     * Update Data Boleto
     * @param (array)$data 
     */
    public function updateDataBoleto($data = null, $id) {

        $sql = "UPDATE setup SET
                    cedente_name        = '{$data['cedente_name']}',
                    cedente_cnpj        = '{$data['cedente_cnpj']}',
                    cedente_address     = '{$data['cedente_address']}',
                    cedente_zip         = '{$data['cedente_zip']}',
                    cedente_city        = '{$data['cedente_city']}',
                    cedente_state       = '{$data['cedente_state']}',
                    cedente_agencia     = '{$data['cedente_agencia']}',
                    cedente_agencia_dv  = '{$data['cedente_agencia_dv']}',
                    cedente_conta       = '{$data['cedente_conta']}',
                    cedente_conta_dv    = '{$data['cedente_conta_dv']}',
                    cedente_label1      = '{$data['cedente_label1']}',
                    cedente_label2      = '{$data['cedente_label2']}',
                    cedente_label3      = '{$data['cedente_label3']}',
                    cedente_label4      = '{$data['cedente_label4']}',
                    cedente_label5      = '{$data['cedente_label5']}',
                    cedente_label6      = '{$data['cedente_label6']}'
                WHERE id = {$id}";

        return $this->query($sql);
    }

    /**
     * Update SMTP
     * @param (array)$data 
     */
    public function updateSMTP($data = null, $id) {

        $sql = "UPDATE setup SET
                    smtp_host  = '{$data['smtp_host']}',
                    smtp_port  = '{$data['smtp_port']}',
                    smtp_user  = '{$data['smtp_user']}',
                    smtp_pass  = '{$data['smtp_pass']}',
                    smtp_email = '{$data['smtp_email']}',
                    smtp_name  = '{$data['smtp_name']}'
                WHERE id = {$id}";

        return $this->query($sql);
    }

}