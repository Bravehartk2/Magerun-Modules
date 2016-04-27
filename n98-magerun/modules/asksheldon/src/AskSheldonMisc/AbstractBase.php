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
 * Time:       16:40
 *
 * Base class for all Magerun command classes
 *
 * Class AbstractBase
 *
 *  * @package AskSheldonMisc
 *  *
 * Created by IntelliJ IDEA.
 *
 */

namespace AskSheldonMisc;

use N98\Magento\Command\AbstractMagentoCommand;

abstract class AbstractBase extends AbstractMagentoCommand
{
    /**
     * Recommended in https://github.com/netz98/n98-magerun/wiki/Modules#module-best-practices
     *
     * @return bool
     */
    public function isEnabled()
    {
        return version_compare($this->getApplication()->getVersion(), '1.74.1', '>=');
    }

    /**
     * Enables all child commands to reset increment ids in eav_entity_store
     *
     * @param array|string $mEntityTypeCode array of entity_type_code's or single entity_type_code
     *                                      (@see table eav_entity_type)
     *
     * @return Zend_Db_Statement_Interface
     */
    protected function _resetIncrementIds($mEntityTypeCode)
    {
        if (is_array($mEntityTypeCode)) {
            $mEntityTypeCode = implode(',', $mEntityTypeCode);
        }
        /* @var Varien_Db_Adapter_Mysqli $oWriter */
        $oWriter = \Mage::getSingleton('core/resource')->getConnection('core_write');

        $sQuery = "
            UPDATE eav_entity_store AS ees
            INNER JOIN eav_entity_type AS eet ON eet.entity_type_id = ees.entity_type_id
            SET ees.increment_last_id = CONCAT(LEFT(CONCAT(ees.increment_prefix, '00000000'), 8), '1')
            WHERE FIND_IN_SET(eet.entity_type_code, ?)
        ";
        $sQuery = $oWriter->quoteInto($sQuery, $mEntityTypeCode);

        return $oWriter->query($sQuery);
    }

    /**
     * @return bool
     */
    protected function _shouldRemove()
    {
        $shouldRemove = $this->input->getOption('force');
        if (!$shouldRemove) {
            $shouldRemove = $this->dialog->askConfirmation(
                $this->output,
                $this->_getQuestion('Are you sure?', 'n'),
                false
            );
        }

        return $shouldRemove;
    }

    /**
     * @param string $message
     * @param string $default [optional]
     *
     * @return string
     */
    protected function _getQuestion($message, $default = null)
    {
        $params = [$message];
        $pattern = '%s: ';
        if (null !== $default) {
            $params[] = $default;
            $pattern .= '[%s] ';
        }

        return vsprintf($pattern, $params);
    }
}