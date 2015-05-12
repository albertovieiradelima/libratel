<?php

namespace App\Console;

class SyncDbCommand extends \Knp\Command\Command
{

    /**
     * Configure Console command
     */
    protected function configure()
    {
        $this->setName('sync-db')
            ->setDescription('Import ABRASCE CRMALL Oracle data.')
            ->addArgument(
                'userId', \Symfony\Component\Console\Input\InputArgument::OPTIONAL, 'Import for a specific user' // Example
            )
            ->addOption(
                'debug', null, \Symfony\Component\Console\Input\InputOption::VALUE_NONE, 'If set, the task will run in debug mode' // Example
            );
    }

    /**
     * Execute console import command
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    protected function execute(\Symfony\Component\Console\Input\InputInterface $input, \Symfony\Component\Console\Output\OutputInterface $output)
    {

        $app = $this->getSilexApplication();

        # Time
        $startTime = time();

        # Import
        $this->importShoppings($app, $output);
        $this->importSuppliers($app, $output);

        # Print total time
        $totalTime = time() - $startTime;
        $output->writeln("<fg=green>Tempo total: {$totalTime} segundos</fg=green>");

    }

    # Private

    /**
     * Import all shoppings from Oracle DB to MySQL
     * @param \Knp\Console\Application $app
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @throws \Exception
     */
    private function importShoppings($app, $output)
    {

        $startTimeShopping = time();

        try {

            $app['crmall_shopping']->getShoppingAll();

            $listShoppings = $app['crmall_shopping']->fetch_all();
            var_dump(oci_error());
            if ($app['crmall_shopping']->rows() <= 0) {
                throw new \Exception('Não existem shoppings para importação na base de dados Oracle ou ocorreu algum erro na conexão com o banco de dados.');
            }

            # Count
            $recordsShoppings = $app['crmall_shopping']->rows();

            $app['shopping']->beginTransaction();

            # Remove all records from MySQL database
            $app['shopping']->removeAllShopping();
            $app['shopping']->removeAllShoppingAdmin();
            $app['shopping']->removeAllShoppingEntertainment();

            for ($i = 0; $i < $recordsShoppings; $i++) {

                $obj = $listShoppings[$i];

                # Save images
                try {

                    # Save Logo
                    $filename = $app['crmall_shopping']->saveShoppingImageForId($obj['ID_CLIENTE'], 'logo');
                    if ($filename) {
                        $obj['LOGO'] = $filename;
                    } else {
                        $obj['LOGO'] = "";
                    }

                    # Save Banner
                    $filename = $app['crmall_shopping']->saveShoppingImageForId($obj['ID_CLIENTE'], 'banner');
                    if ($filename) {
                        $obj['BANNER'] = $filename;
                    } else {
                        $obj['BANNER'] = "";
                    }

                } catch (\Exception $ex) {

                    $output->writeln("<error>Erro ao salvar imagem do shopping {$obj['ID_CLIENTE']}. Ex.: {$ex->getMessage()}</error>");
                }

                # Save Shopping
                $q = $app['shopping']->insertShopping($obj);

                if (!$q) {
                    throw new \Exception("<error>[ERRO]\t{$obj['ID_CLIENTE']}\t\t{$obj['FANTASIA']}\t\t\tErro ao inserir registro. Error: {$app['shopping']->error()}</error>");
                }

                # Save Administrators
                $app['crmall_shopping']->getShoppingAdminById($obj['ID_CLIENTE']);
                $admins = $app['crmall_shopping']->fetch_all();
                if ($app['crmall_shopping']->rows() > 0 && $admins !== false) {
                    foreach ($admins as $adm) {
                        $app['shopping']->insertShoppingAdmin($obj['ID_CLIENTE'], $adm);
                        $output->writeln("<fg=blue>[OK]\t{$adm['ID_ADMINISTRADORA']}\t\t{$adm['ADMINISTRADORA']}</fg=blue>");
                    }
                }

                # Save Entertainments
                $app['crmall_shopping']->getShoppingEntertainmentById($obj['ID_CLIENTE']);
                $ents = $app['crmall_shopping']->fetch_all();
                if ($app['crmall_shopping']->rows() > 0 && $ents !== false) {
                    foreach ($ents as $ent) {
                        $app['shopping']->insertShoppingEntertainment($obj['ID_CLIENTE'], $ent);
                    }
                }

                $output->writeln("<fg=green>[OK]\t{$obj['ID_CLIENTE']}\t\t{$obj['FANTASIA']}</fg=green>");

            }


            $app['shopping']->commit();

            $app['shopping']->getAllOpenedNonAffiliate();
            $shoppingsOpenedNonAffiliate = $app['shopping']->rows();
            $recordsShoppings = (int)$recordsShoppings - (int)$shoppingsOpenedNonAffiliate;

            if ($app['shopping']->removeAllOpenedNonAffiliate()) {
                $timeExclusao = time() - $startTimeShopping;
                # Print message shopping
                $output->writeln("<fg=blue>Filtrando shoppings a inaugurar[OK]</fg=blue>");
                $output->writeln("<fg=green>Não filiados a inaugurar\t[OK]\t{$shoppingsOpenedNonAffiliate} registros excluídos com sucesso! Tempo: {$timeExclusao} segundos</fg=green>");
            } else {
                # Print message shopping
                $output->writeln("<fg=red>Filtrando shoppings a inaugurar[ERRO]</fg=red>");
            }


        } catch
        (\Exception $ex) {

            $app['shopping']->rollback();
            $output->writeln("<error>Erro ao importar shoppings. Error: {$ex->getMessage()}</error>");
            return;

        }

        $totalTimeShopping = time() - $startTimeShopping;

        # Print message shopping
        $output->writeln("<fg=green>Shoppings\t[OK]\t{$recordsShoppings} registros importados com sucesso! Tempo: {$totalTimeShopping} segundos</fg=green>");

    }

    /**
     * Import all suppliers from Oracle DB to MySQL
     * @param \Knp\Console\Application $app
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @throws \Exception
     */
    private function importSuppliers($app, $output)
    {

        $startTime = time();

        try {

            $app['supplier']->beginTransaction();

            # Remove all records from MySQL database
            $app['supplier']->removeAllSupplier();
            $app['supplier']->removeAllSupplierCategory();
            $app['supplier']->removeAllSupplierContact();

            # Add Categories
            $app['crmall_supplier']->getSupplierCategoryAll();
            $listCategories = $app['crmall_supplier']->fetch_all();
            if ($app['crmall_supplier']->rows() <= 0) {
                $output->writeln('<comment>Não existem categorias para importação na base de dados Oracle ou ocorreu algum erro na conexão com o banco de dados.</comment>');
            } else {
                # Insert categories
                foreach ($listCategories as $cat) {
                    $app['supplier']->insertCategory($cat);
                }

            }

            # Import suppliers
            $app['crmall_supplier']->getSupplierAll();

            $list = $app['crmall_supplier']->fetch_all();
            if ($app['crmall_supplier']->rows() <= 0) {
                throw new \Exception('Não existem fornecedores para importação na base de dados Oracle ou ocorreu algum erro na conexão com o banco de dados.');
            }

            # Count
            $records = $app['crmall_supplier']->rows();

            for ($i = 0; $i < $records; $i++) {

                $obj = $list[$i];

                # Save logo
                try {

                    $filename = $app['crmall_supplier']->saveSupplierImageForId($obj['ID_FORNECEDOR'], 'logo');
                    if ($filename) {
                        $obj['LOGO'] = $filename;
                    } else {
                        $obj['LOGO'] = "";
                    }

                } catch (\Exception $ex) {

                    $output->writeln("<error>Erro ao salvar imagem do shopping {$obj['ID_FORNECEDOR']}. Ex.: {$ex->getMessage()}</error>");
                }

                # Save
                $q = $app['supplier']->insertSupplier($obj);

                if (!$q) {
                    throw new \Exception("<error>[ERRO]\t{$obj['ID_FORNECEDOR']}\t\t{$obj['FANTASIA']}\t\t\tErro ao inserir registro. Error: {$app['shopping']->error()}</error>");
                }

                # Save Contacts
                $app['crmall_supplier']->getSupplierContactById($obj['ID_FORNECEDOR']);
                $contacts = $app['crmall_supplier']->fetch_all();
                if ($app['crmall_supplier']->rows() > 0 && $contacts !== false) {
                    foreach ($contacts as $con) {
                        $app['supplier']->insertSupplierContact($obj['ID_FORNECEDOR'], $con);
                    }
                }

                $output->writeln("<fg=green>[OK]\t{$obj['ID_CLIENTE']}\t\t{$obj['FANTASIA']}</fg=green>");

            }

            $app['supplier']->commit();

            $app['supplier']->getSupplierCategoryAll();
            $listCat = $app['supplier']->fetch_all();

            # Count
            $recordsCategories = $app['supplier']->rows();

            # Print message
            $output->writeln("<fg=green>Filtrando categorias\t[OK]\t{$recordsCategories}</fg=green>");
            $app['supplier']->beginTransaction();
            foreach ($listCat as $category) {
                $app['supplier']->getSupplierByCategory($category['id_categoria_fornecedor']);
                $app['supplier']->fetch_all();

                if ($app['supplier']->rows() <= 0) {

                    if($app['supplier']->removeSupplierCategoryById($category['id_categoria_fornecedor'])){
                        $app['supplier']->commit();
                        $output->writeln("<fg=blue>[OK]\t{$category['id_categoria_fornecedor']}\t\t{$category['descricao']}\t removida</fg=blue>");
                    }
                }
            }

        } catch (\Exception $ex) {

            $app['supplier']->rollback();
            $output->writeln("<error>Erro ao importar fornecedores. Error: {$ex->getMessage()}</error>");
            return;

        }

        $totalTime = time() - $startTime;

        # Print message 
        $output->writeln("<fg=green>Fornecedores\t[OK]\t{$records} registros importados com sucesso! Tempo: {$totalTime} segundos</fg=green>");

    }

}
