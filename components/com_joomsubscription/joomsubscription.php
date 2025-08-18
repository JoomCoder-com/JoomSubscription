<?php
/**
 * JoomSubscription by JoomCoder
 * a component for Joomla (http://www.joomla.org)
 * Author Website: https://www.joomcoder.com/
 *
 * @copyright Copyright (C) 2012 JoomCoder (https://www.joomcoder.com/). All rights reserved.
 * @license   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

use Joomla\CMS\HTML\HTMLHelper;

defined('_JEXEC') or die('Restricted access');

$app = JFactory::getApplication();
$doc = JFactory::getDocument();

// Include scripts
HTMLHelper::_('behavior.core');
HTMLHelper::_('jquery.framework');

// require new joomsusbcription library
require_once __DIR__.'/libraries/vendor/autoload.php';

include_once __DIR__.'/api.php';

$controller = MControllerBase::getInstance('Joomsubscription');
$controller->execute($app->input->getCmd('task'));
$controller->redirect();
