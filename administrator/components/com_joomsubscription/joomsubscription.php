<?php
/**
 * JoomSubscription by JoomCoder
 * a component for Joomla (http://www.joomla.org)
 * Author Website: https://www.joomcoder.com/
 * @copyright Copyright (C) 2012 JoomCoder (https://www.joomcoder.com/). All rights reserved.
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.filter.filteroutput');
jimport('joomla.filesystem.file');
jimport('joomla.utilities.date');

JHtml::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR. DIRECTORY_SEPARATOR .'helpers'. DIRECTORY_SEPARATOR .'html');

$controller	= JControllerLegacy::getInstance('Joomsubscription');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();
?>
<div style="clear:both;"></div>
<p>
<center class="small">&copy; 2012 <a href="https://www.joomcoder.com">JoomCoder</a></center>
</p>