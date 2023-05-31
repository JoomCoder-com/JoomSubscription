<?php
/**
 * JoomSubscription by JoomCoder
 * a component for Joomla (http://www.joomla.org)
 * Author Website: https://www.joomcoder.com/
 *
 * @copyright Copyright (C) 2012 JoomCoder (https://www.joomcoder.com/). All rights reserved.
 * @license   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */
defined('_JEXEC') or die('Restricted access');

jimport('joomla.mail.helper');
jimport('joomla.mail.mail');
jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');

require_once(JPATH_ROOT . '/components/com_joomsubscription/api.php');
$app = JFactory::getApplication();

if($app->input->getCmd('task') != 'paypalnotification')
{
	JError::raiseWarning(404, 'Old membership extension is depricated');
	$app->redirect(JoomsubscriptionApi::getLink('emlist'));
}

$controller = JControllerLegacy::getInstance('Joomsubscription');
$controller->execute($app->input->getCmd('task'));
$app->redirect();
$controller->redirect();