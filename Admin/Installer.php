<?php
/**
 * Orange Management
 *
 * PHP Version 7.0
 *
 * @category   TBD
 * @package    TBD
 * @author     OMS Development Team <dev@oms.com>
 * @author     Dennis Eichhorn <d.eichhorn@oms.com>
 * @copyright  2013 Dennis Eichhorn
 * @license    OMS License 1.0
 * @version    1.0.0
 * @link       http://orange-management.com
 */
namespace Modules\Workflow\Admin;

use phpOMS\DataStorage\Database\DatabaseType;
use phpOMS\DataStorage\Database\DatabasePool;
use phpOMS\Module\InfoManager;
use phpOMS\Module\InstallerAbstract;

/**
 * Tasks install class.
 *
 * @category   Modules
 * @package    Modules\Tasks
 * @author     OMS Development Team <dev@oms.com>
 * @author     Dennis Eichhorn <d.eichhorn@oms.com>
 * @license    OMS License 1.0
 * @link       http://orange-management.com
 * @since      1.0.0
 */
class Installer extends InstallerAbstract
{

    /**
     * {@inheritdoc}
     */
    public static function install(string $path, DatabasePool $dbPool, InfoManager $info)
    {
        parent::install($path, $dbPool, $info);

        switch ($dbPool->get('core')->getType()) {
            case DatabaseType::MYSQL:
                $dbPool->get('core')->con->prepare(
                    'CREATE TABLE if NOT EXISTS `' . $dbPool->get('core')->prefix . 'workflow` (
                            `workflow_id` int(11) NOT NULL AUTO_INCREMENT,
                            `workflow_name` varchar(50) NOT NULL,
                            `workflow_status` int(11) NOT NULL,
                            `workflow_desc` varchar(100) DEFAULT NULL,
                            `workflow_created` datetime DEFAULT NULL,
                            `workflow_created_by` int(11) DEFAULT NULL,
                            PRIMARY KEY (`workflow_id`),
                            KEY `workflow_created_by` (`workflow_created_by`)
                        )ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;'
                )->execute();

                $dbPool->get('core')->con->prepare(
                    'ALTER TABLE `' . $dbPool->get('core')->prefix . 'workflow`
                            ADD CONSTRAINT `' . $dbPool->get('core')->prefix . 'workflow_ibfk_1` FOREIGN KEY (`workflow_created_by`) REFERENCES `' . $dbPool->get('core')->prefix . 'account` (`account_id`);'
                )->execute();

                $dbPool->get('core')->con->prepare(
                    'CREATE TABLE if NOT EXISTS `' . $dbPool->get('core')->prefix . 'workflow_media` (
                            `workflow_media_id` int(11) NOT NULL AUTO_INCREMENT,
                            `workflow_media_media` int(11) NOT NULL,
                            `workflow_media_workflow` int(11) NOT NULL,
                            `workflow_media_type` int(3) DEFAULT NULL,
                            PRIMARY KEY (`workflow_media_id`),
                            KEY `workflow_media_media` (`workflow_media_media`),
                            KEY `workflow_media_workflow` (`workflow_media_workflow`)
                        )ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;'
                )->execute();

                $dbPool->get('core')->con->prepare(
                    'ALTER TABLE `' . $dbPool->get('core')->prefix . 'workflow_media`
                            ADD CONSTRAINT `' . $dbPool->get('core')->prefix . 'workflow_media_ibfk_1` FOREIGN KEY (`workflow_media_media`) REFERENCES `' . $dbPool->get('core')->prefix . 'media` (`media_id`),
                            ADD CONSTRAINT `' . $dbPool->get('core')->prefix . 'workflow_media_ibfk_2` FOREIGN KEY (`workflow_media_workflow`) REFERENCES `' . $dbPool->get('core')->prefix . 'workflow` (`workflow_id`);'
                )->execute();

                $dbPool->get('core')->con->prepare(
                    'CREATE TABLE if NOT EXISTS `' . $dbPool->get('core')->prefix . 'workflow_element` (
                            `workflow_element_id` int(11) NOT NULL AUTO_INCREMENT,
                            `workflow_element_name` varchar(50) NOT NULL,
                            `workflow_element_status` int(11) NOT NULL,
                            `workflow_element_data` text NOT NULL,
                            `workflow_element_desc` varchar(100) DEFAULT NULL,
                            `workflow_element_created` datetime DEFAULT NULL,
                            `workflow_element_created_by` int(11) DEFAULT NULL,
                            `workflow_element_workflow` int(11) DEFAULT NULL,
                            PRIMARY KEY (`workflow_element_id`),
                            KEY `workflow_element_created_by` (`workflow_element_created_by`),
                            KEY `workflow_element_workflow` (`workflow_element_workflow`)
                        )ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;'
                )->execute();

                $dbPool->get('core')->con->prepare(
                    'ALTER TABLE `' . $dbPool->get('core')->prefix . 'workflow_element`
                            ADD CONSTRAINT `' . $dbPool->get('core')->prefix . 'workflow_element_ibfk_1` FOREIGN KEY (`workflow_element_created_by`) REFERENCES `' . $dbPool->get('core')->prefix . 'account` (`account_id`),
                            ADD CONSTRAINT `' . $dbPool->get('core')->prefix . 'workflow_element_ibfk_2` FOREIGN KEY (`workflow_element_workflow`) REFERENCES `' . $dbPool->get('core')->prefix . 'workflow` (`workflow_id`);'
                )->execute();

                $dbPool->get('core')->con->prepare(
                    'CREATE TABLE if NOT EXISTS `' . $dbPool->get('core')->prefix . 'workflow_element_media` (
                            `workflow_element_media_id` int(11) NOT NULL AUTO_INCREMENT,
                            `workflow_element_media_media` int(11) NOT NULL,
                            `workflow_element_media_workflow_element` int(11) NOT NULL,
                            PRIMARY KEY (`workflow_element_media_id`),
                            KEY `workflow_element_media_media` (`workflow_element_media_media`),
                            KEY `workflow_element_media_workflow_element` (`workflow_element_media_workflow_element`)
                        )ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;'
                )->execute();

                $dbPool->get('core')->con->prepare(
                    'ALTER TABLE `' . $dbPool->get('core')->prefix . 'workflow_element_media`
                            ADD CONSTRAINT `' . $dbPool->get('core')->prefix . 'workflow_element_media_ibfk_1` FOREIGN KEY (`workflow_element_media_media`) REFERENCES `' . $dbPool->get('core')->prefix . 'media` (`media_id`),
                            ADD CONSTRAINT `' . $dbPool->get('core')->prefix . 'workflow_element_media_ibfk_2` FOREIGN KEY (`workflow_element_media_workflow_element`) REFERENCES `' . $dbPool->get('core')->prefix . 'workflow_element` (`workflow_element_id`);'
                )->execute();

                    $dbPool->get('core')->con->prepare(
                        'CREATE TABLE if NOT EXISTS `' . $dbPool->get('core')->prefix . 'workflow_element_task` (
                            `workflow_element_task_id` int(11) NOT NULL AUTO_INCREMENT,
                            `workflow_element_task_task` int(11) NOT NULL,
                            `workflow_element_task_workflow_element` int(11) NOT NULL,
                            PRIMARY KEY (`workflow_element_task_id`),
                            KEY `workflow_element_task_task` (`workflow_element_task_task`),
                            KEY `workflow_element_task_workflow_element` (`workflow_element_task_workflow_element`)
                        )ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;'
                    )->execute();

                $dbPool->get('core')->con->prepare(
                    'ALTER TABLE `' . $dbPool->get('core')->prefix . 'workflow_element_task`
                            ADD CONSTRAINT `' . $dbPool->get('core')->prefix . 'workflow_element_task_ibfk_1` FOREIGN KEY (`workflow_element_task_task`) REFERENCES `' . $dbPool->get('core')->prefix . 'task` (`media_id`),
                            ADD CONSTRAINT `' . $dbPool->get('core')->prefix . 'workflow_element_task_ibfk_2` FOREIGN KEY (`workflow_element_task_workflow_element`) REFERENCES `' . $dbPool->get('core')->prefix . 'workflow_element` (`workflow_element_id`);'
                )->execute();
                break;
        }
    }
}
