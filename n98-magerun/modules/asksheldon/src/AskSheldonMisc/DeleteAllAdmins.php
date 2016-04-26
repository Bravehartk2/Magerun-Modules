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
 * Admin delete command for magerun
 *
 * Class DeleteAllAdmins
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


class DeleteAllAdmins extends AbstractBase
{
    protected function configure()
    {
        $this
            ->setName('admin:user:delete:all')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Force delete')
            ->setDescription('Deletes all admin users from magento');

        $help = <<<HELP
This will delete all admin user. ATTENTION!!!: You have to create at least one new one afterwards to login to Magento Backend again
<comment>Example Usage:</comment>
n98-magerun admin:user:delete:all                    <info># Will delete all admin users</info>
n98-magerun admin:user:delete:all --force || -f      <info># Will delete all admin users without any confirmation</info>
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
                    $this->_deleteAdminUsers();
                    $output->writeln("<warning>All admin users have been deleted!</warning>");
                    $output->writeln("<warning>ATTENTION!!!: You have to create at least one new one afterwards to login to Magento Backend again (f.e. with n98-magerun admin:user:create)!!!</warning>");
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
     * Deletes all admin accounts and created a dummy admin
     *
     * @throws \Exception
     */
    private function _deleteAdminUsers()
    {
        $oAdminCollection = \Mage::getModel('admin/user')->getCollection();
        foreach ($oAdminCollection as $oAdmin) {
            $oAdmin->delete();
        }
    }

}