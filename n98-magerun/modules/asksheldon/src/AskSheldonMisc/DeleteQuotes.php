<?php
/**
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * It is  available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 *
 * @category   AskSheldon
 * @package    AskSheldonMisc
 * @copyright  Copyright (c) 2016 Marcel Lange (https://www.ask-sheldon.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author     Marcel Lange <info@ask-sheldon.com>
 *
 * Date:       20/04/16
 * Time:       14:36
 *
 * Quote delete command for magerun
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


class DeleteQuotes extends AbstractBase
{
    protected function configure()
    {
        $this
            ->setName('quote:delete:all')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Force delete')
            ->setDescription('Deletes all quotes from magento');

        $help = <<<HELP
This will delete  all quotes.
<comment>Example Usage:</comment>
n98-magerun order:delete:all                    <info># Will delete all quotes</info>
n98-magerun order:delete:all --force || -f      <info># Will delete all quotes without any confirmation</info>
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
                    $this->_deleteAllQuotess();
                    $this->output->writeln("<warning>All Quotes have been deleted!</warning>");
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
     * Deletes all quotes by collection (triggers deletion of related entities)
     *
     * @throws \Exception
     */
    private function _deleteAllQuotess()
    {
        $oQuoteCollection = \Mage::getModel('sales/quote')->getCollection();
        foreach ($oQuoteCollection as $oQuote) {
            $oQuote->delete();
        }
    }

}