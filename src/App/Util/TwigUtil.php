<?php

namespace App\Util;

/**
 * Classe utilizada para extender funcionalidades do twig
 *
 * @author Anderson Costa <arcostasi@gmail.com>
 */
class TwigUtil extends \Twig_Extension {

    /**
     * Get extension name
     */
    public function getName() {

        return "twig.util";
    }

    /**
     * Set new filter
     */
    public function getFilters() {

        return array(
            new \Twig_SimpleFilter('uflist', array($this, "uflist")),
            new \Twig_SimpleFilter('cargolist', array($this, "cargolist")),
            new \Twig_SimpleFilter('cargoParticipantsList', array($this, "cargoParticipantsList")),
            new \Twig_SimpleFilter('areaParticipantsList', array($this, "areaParticipantsList"))
        );
    }

    /**
     * Retorna lista de estados brasileiros
     * @return array (array) lista de estados
     *
     */
    public function uflist() {

        $uflist = array(
            '' => 'Selecione',
            'AC' => 'Acre',
            'AL' => 'Alagoas',
            'AP' => 'Amapá',
            'AM' => 'Amazonas',
            'BA' => 'Bahia',
            'CE' => 'Ceará',
            'DF' => 'Distrito Federal',
            'ES' => 'Espirito Santo',
            'GO' => 'Goiás',
            'MA' => 'Maranhão',
            'MT' => 'Mato Grosso',
            'MS' => 'Mato Grosso do Sul',
            'MG' => 'Minas Gerais',
            'PA' => 'Pará',
            'PB' => 'Paraiba',
            'PR' => 'Paraná',
            'PE' => 'Pernambuco',
            'PI' => 'Piauí',
            'RJ' => 'Rio de Janeiro',
            'RN' => 'Rio Grande do Norte',
            'RS' => 'Rio Grande do Sul',
            'RO' => 'Rondônia',
            'RR' => 'Roraima',
            'SC' => 'Santa Catarina',
            'SP' => 'São Paulo',
            'SE' => 'Sergipe',
            'TO' => 'Tocantis'
        );

        return $uflist;
    }

    /**
     * Retorna lista de Cargos
     * @return array (array) lista de cargos
     *
     */
    public function cargolist() {
        $cargolist = array(
            '' => 'Selecione',
            'Analista de Sistemas' => 'Analista de Sistemas',
            'Artesao' => 'Artesao',
            'Artista Plastico' => 'Artista Plastico',
            'Assessor De Imprensa' => 'Assessor De Imprensa',
            'Assistente' => 'Assistente',
            'Assistente Administrativo' => 'Assistente Administrativo',
            'Assistente Financeiro' => 'Assistente Financeiro',
            'Assistente Social' => 'Assistente Social',
            'Atendente' => 'Atendente',
            'Atendente De Televendas' => 'Atendente De Televendas',
            'Ator' => 'Ator',
            'Auditor' => 'Auditor',
            'Autonomo' => 'Autonomo',
            'Auxiliar' => 'Auxiliar',
            'Auxiliar Administrativo' => 'Auxiliar Administrativo',
            'Auxiliar Contabil' => 'Auxiliar Contabil',
            'Auxiliar De Enfermagem' => 'Auxiliar De Enfermagem',
            'Auxiliar De Escritorio' => 'Auxiliar De Escritorio',
            'Auxiliar De Producao' => 'Auxiliar De Producao',
            'Auxiliar De Servicos Gerais' => 'Auxiliar De Servicos Gerais',
            'Auxiliar Financeiro' => 'Auxiliar Financeiro',
            'Baba' => 'Baba',
            'Balconista' => 'Balconista',
            'Bancario' => 'Bancario',
            'Bibliotecaria' => 'Bibliotecaria',
            'Biologo' => 'Biologo',
            'Bioquimico' => 'Bioquimico',
            'Cabeleireiro' => 'Cabeleireiro',
            'Caixa' => 'Caixa',
            'Chefe De Cozinha' => 'Chefe De Cozinha',
            'Cirurgiao Dentista' => 'Cirurgiao Dentista',
            'Cobrador' => 'Cobrador',
            'Comerciante' => 'Comerciante',
            'Comerciante Atacadista' => 'Comerciante Atacadista',
            'Comerciante Varejista' => 'Comerciante Varejista',
            'Comissario De Voo' => 'Comissario De Voo',
            'Comprador' => 'Comprador',
            'Conferente' => 'Conferente',
            'Consultor' => 'Consultor',
            'Contabilista' => 'Contabilista',
            'Contador' => 'Contador',
            'Coordenador' => 'Coordenador',
            'Coordenador De Estoque' => 'Coordenador De Estoque',
            'Copeiro' => 'Copeiro',
            'Corretor' => 'Corretor',
            'Corretor De Imoveis' => 'Corretor De Imoveis',
            'Costureiro' => 'Costureiro',
            'Cozinheiro' => 'Cozinheiro',
            'Decorador' => 'Decorador',
            'Dentista' => 'Dentista',
            'Desempregado' => 'Desempregado',
            'Desenhista' => 'Desenhista',
            'Designer' => 'Designer',
            'Designer De Interiores' => 'Designer De Interiores',
            'Diretor' => 'Diretor',
            'Diretor Administrativo' => 'Diretor Administrativo',
            'Diretor Comercial' => 'Diretor Comercial',
            'Do Lar' => 'Do Lar',
            'Domestica' => 'Domestica',
            'Economista' => 'Economista',
            'Editor' => 'Editor',
            'Educador' => 'Educador',
            'Eletricista' => 'Eletricista',
            'Eletrotecnico' => 'Eletrotecnico',
            'Empreendedor' => 'Empreendedor',
            'Empresario' => 'Empresario',
            'Encarregado' => 'Encarregado',
            'Encarregado De Producao' => 'Encarregado De Producao',
            'Enfermeiro' => 'Enfermeiro',
            'Engenharia Eletricista' => 'Engenharia Eletricista',
            'Engenheiro' => 'Engenheiro',
            'Engenheiro Agronomo' => 'Engenheiro Agronomo',
            'Engenheiro Civil' => 'Engenheiro Civil',
            'Escrevente' => 'Escrevente',
            'Escrituario' => 'Escrituario',
            'Estagiario' => 'Estagiario',
            'Esteticista' => 'Esteticista',
            'Estilista' => 'Estilista',
            'Estudante' => 'Estudante',
            'Executivo' => 'Executivo',
            'Farmaceutico' => 'Farmaceutico',
            'Ferramenteiro' => 'Ferramenteiro',
            'Financeiro' => 'Financeiro',
            'Fisioterapeuta' => 'Fisioterapeuta',
            'Fonoaudiologo' => 'Fonoaudiologo',
            'Fotografo' => 'Fotografo',
            'Frentista' => 'Frentista',
            'Funcionario Publico' => 'Funcionario Publico',
            'Garcom' => 'Garcom',
            'Gerente' => 'Gerente',
            'Grafico' => 'Grafico',
            'Hotelaria' => 'Hotelaria',
            'Industrial' => 'Industrial',
            'Jornalista' => 'Jornalista',
            'Juiz' => 'Juiz',
            'Lider' => 'Lider',
            'Magistrado' => 'Magistrado',
            'Manicure' => 'Manicure',
            'Maquiador' => 'Maquiador',
            'Marceneiro' => 'Marceneiro',
            'Marketing' => 'Marketing',
            'Matematico' => 'Matematico',
            'Mecanico' => 'Mecanico',
            'Medico' => 'Medico',
            'Metalurgico' => 'Metalurgico',
            'Microempresario' => 'Microempresario',
            'Militar' => 'Militar',
            'Montador' => 'Montador',
            'Motorista' => 'Motorista',
            'Musico' => 'Musico',
            'Nao Existe' => 'Nao Existe',
            'Nutricionista' => 'Nutricionista',
            'Operador' => 'Operador',
            'Operador De Caixa' => 'Operador De Caixa',
            'Operador De Maquinas' => 'Operador De Maquinas',
            'Operador De Telemarketing' => 'Operador De Telemarketing',
            'Orientador Educacional' => 'Orientador Educacional',
            'Outro' => 'Outro',
            'Pastor' => 'Pastor',
            'Pecuarista' => 'Pecuarista',
            'Pedagogo' => 'Pedagogo',
            'Pedreiro' => 'Pedreiro',
            'Pintor' => 'Pintor',
            'Policial' => 'Policial',
            'Politico' => 'Politico',
            'Porteiro' => 'Porteiro',
            'Procurador' => 'Procurador',
            'Professor' => 'Professor',
            'Profissional Liberal' => 'Profissional Liberal',
            'Programador' => 'Programador',
            'Projetista' => 'Projetista',
            'Promotor' => 'Promotor',
            'Promotor De Vendas' => 'Promotor De Vendas',
            'Psicanalista' => 'Psicanalista',
            'Psicologo' => 'Psicologo',
            'Publicitario' => 'Publicitario',
            'Quimico' => 'Quimico',
            'Recepcionista' => 'Recepcionista',
            'Relacoes Publicas' => 'Relacoes Publicas',
            'Repositor' => 'Repositor',
            'Representante Comercial' => 'Representante Comercial',
            'Secretaria' => 'Secretaria',
            'Securitario' => 'Securitario',
            'Seguranca' => 'Seguranca',
            'Serventuraria Da Justica' => 'Serventuraria Da Justica',
            'Servicos Gerais' => 'Servicos Gerais',
            'Servidor PUBLICO' => 'Publico Publico',
            'Sociologo' => 'Sociologo',
            'Soldador' => 'Soldador',
            'Supervisor' => 'Supervisor',
            'Taxista' => 'Taxista',
            'Tecnico' => 'Tecnico',
            'Tecnico Administrativo' => 'Tecnico Administrativo',
            'Tecnico De Enfermagem' => 'Tecnico De Enfermagem',
            'Tecnico De Informatica' => 'Tecnico De Informatica',
            'Tecnico De Seguranca Do Trabalho' => 'Tecnico De Seguranca Do Trabalho',
            'Tecnico Em Eletronica' => 'Tecnico Em Eletronica',
            'Telefonista' => 'Telefonista',
            'Telemarketing' => 'Telemarketing',
            'Terapeuta' => 'Terapeuta',
            'Tradutor' => 'Tradutor',
            'Turismologo' => 'Turismologo',
            'Universitario' => 'Universitario',
            'Vendas' => 'Vendas',
            'Vendedor' => 'Vendedor',
            'Vendedor Autonomo' => 'Vendedor Autonomo',
            'Veterinario' => 'Veterinario',
            'Vigia' => 'Vigia',
            'Vigilante' => 'Vigilante',
            'Zelador' => 'Zelador',
            'Zootecnista' => 'Zootecnista',
            'Outra' => 'Outra'
        );

        return $cargolist;
    }

    /**
     * Retorna lista de Cargos para Participantes de Cursos ou Eventos
     * @return array (array) lista de cargos
     *
     */
    public function cargoParticipantsList() {
        $cargoParticipantsList = array(
            '' => 'Selecione',
            'Advogado' => 'Advogado',
            'Analista' => 'Analista',
            'Arquiteto' => 'Arquiteto',
            'Assessor' => 'Assessor',
            'Assistente' => 'Assistente',
            'Auxiliar' => 'Auxiliar',
            'Chefe' => 'Chefe',
            'Consultor' => 'Consultor',
            'Controller' => 'Controller',
            'Coordenador' => 'Coordenador',
            'Diretor' => 'Diretor',
            'Encarregado' => 'Encarregado',
            'Engenheiro' => 'Engenheiro',
            'Estagiário' => 'Estagiário',
            'Executivo' => 'Executivo',
            'Gerente' => 'Gerente',
            'Gestor' => 'Gestor',
            'Inspetor' => 'Inspetor',
            'Jornalista' => 'Jornalista',
            'Outros' => 'Outros',
            'Presidente' => 'Presidente',
            'Responsável' => 'Responsável',
            'Sócio - Diretor' => 'Sócio - Diretor',
            'Sócio - Empreendedor' => 'Sócio - Empreendedor',
            'Sócio - Gerente' => 'Sócio - Gerente',
            'Superintendente' => 'Superintendente',
            'Superintendente Adjunto' => 'Superintendente Adjunto',
            'Supervisor' => 'Supervisor',
            'Técnico' => 'Técnico',
            'Trainee' => 'Trainee',
            'Vice - Presidente' => 'Vice - Presidente'
        );

        return $cargoParticipantsList;
    }

    /**
     * Retorna lista de Areas para Participantes de Cursos ou Eventos
     * @return array (array) lista de Areas
     *
     */
    public function areaParticipantsList() {
        $areaParticipantsList = array(
            '' => 'Selecione',
            'Adm - Financeiro' => 'Adm - Financeiro',
            'Administrativo' => 'Administrativo',
            'Arquitetura' => 'Arquitetura',
            'Atendimento - SAC' => 'Atendimento - SAC',
            'Auditoria' => 'Auditoria',
            'Comercial' => 'Comercial',
            'Conselho' => 'Conselho',
            'Desenvolvimento de Negócios' => 'Desenvolvimento de Negócios',
            'Diretoria' => 'Diretoria',
            'Empreendedor' => 'Empreendedor',
            'Financeiro' => 'Financeiro',
            'Imprensa - Comunicação' => 'Imprensa - Comunicação',
            'Jurídico' => 'Jurídico',
            'Mall & Merchandising' => 'Mall & Merchandising',
            'Marketing' => 'Marketing',
            'Não se aplica' => 'Não se aplica',
            'Operações' => 'Operações',
            'Pesquisas' => 'Pesquisas',
            'Planejamento Estratégico' => 'Planejamento Estratégico',
            'Presidência' => 'Presidência',
            'Relacionamento' => 'Relacionamento',
            'RH' => 'RH',
            'RI' => 'RI',
            'Segurança' => 'Segurança',
            'Superintendencia' => 'Superintendencia',
            'Sustentabilidade' => 'Sustentabilidade',
            'TI' => 'TI'
        );

        return $areaParticipantsList;
    }

}