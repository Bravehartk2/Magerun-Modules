<?php
/**
 *
 * Magerun
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * It is  available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 *
 * @category   Magerun Command
 * @package    AskSheldonMisc
 * @copyright  Copyright (c) 2016 Marcel Lange (https://www.ask-sheldon.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author     Marcel Lange <info@ask-sheldon.com>
 *
 * Date:       20/04/16
 * Time:       14:36
 *
 * Order delete command for Magerun
 *
 * Class DeleteOrders
 *
 *  * @package AskSheldonMisc
 *  *
 * Created by IntelliJ IDEA.
 *
 */

namespace AskSheldonMisc;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;


class DeleteOrders extends AbstractBase
{
    protected function configure()
    {
        $this
            ->setName('order:delete:all')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Force delete')
            ->setDescription('Deletes all orders from magento');

        $help = <<<HELP
This will delete  all orders.
<comment>Example Usage:</comment>
n98-magerun order:delete:all                    <info># Will delete all orders</info>
n98-magerun order:delete:all --force || -f      <info># Will delete all orders without any confirmation</info>
HELP;
        $this->setHelp($help);
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return int|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->detectMagento($output);
            if ($this->initMagento()) {

                $this->input = $input;
                $this->output = $output;

                /** @var DialogHelper dialog */
                $this->dialog = $this->getHelperSet()->get('dialog');

                if ($this->_shouldRemove()) {
                    $this->_deleteAllOrders();
                    $this->output->writeln("<warning>All orders have been deleted!</warning>");

                    /* @var Zend_Db_Statement_Interface $oResult */
                    $oResult = $this->_resetIncrementIds(['order', 'invoice', 'creditmemo', 'shipment']);
                    $this->output->writeln("Order increment have been reset! (Result count: {$oResult->rowCount()})");
                } else {
                    $this->output->writeln("Nothing was removed!");
                }
            }
        } catch (\Exception $oEx) {
            $output->writeln("<error>Something got terrible wrong!!!:</error>");
            $output->writeln("<error>" . $oEx->getMessage() . "</error>");
        }
    }

    /**
     *
     * Deletes all orders by collection (triggers deletion of related entities)
     *
     * @throws \Exception
     */
    private function _deleteAllOrders()
    {
        $oOrderCollection = \Mage::getModel('sales/order')->getCollection();
        foreach ($oOrderCollection as $oQuote) {
            $oQuote->delete();
        }
    }

}