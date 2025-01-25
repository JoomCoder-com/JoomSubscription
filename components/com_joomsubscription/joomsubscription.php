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

$app = JFactory::getApplication();
$doc = JFactory::getDocument();

if(
	substr($app->input->get('task',''), 0, 7) !== 'emajax.' &&
	substr($app->input->get('task',''), 0, 9) !== 'emcharts.' &&
	substr($app->input->get('task',''), 0, 7) !== 'emcron.' &&
	substr($app->input->get('task',''), 0, 6) !== 'plans.')
{
	echo '<link rel="stylesheet" href="'.JUri::root(TRUE).'/components/com_joomsubscription/assets/font/css/joomsubscription.css" type="text/css">';
	echo '<link rel="stylesheet" href="'.JUri::root(TRUE).'/components/com_joomsubscription/assets/joomsubscription.css" type="text/css">';
}

include_once __DIR__.'/api.php';

$controller = MControllerBase::getInstance('Joomsubscription');
$controller->execute($app->input->getCmd('task'));
$controller->redirect();

JoomsubscriptionHelper::copyright();