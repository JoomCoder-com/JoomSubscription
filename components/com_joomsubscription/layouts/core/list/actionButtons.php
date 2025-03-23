<?php
/**
 * Cobalt by JoomCoder
 * a component for Joomla! 1.7 - 2.5 CMS (http://www.joomla.org)
 * Author Website: https://www.joomcoder.com/
 * @copyright Copyright (C) 2012 JoomCoder (https://www.joomcoder.com/). All rights reserved.
 * @license   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;

extract($displayData);

$app        = JFactory::getApplication();
$controller = $app->input->getCmd('view', 'emplan');
if($controller == 'emtaxes')
{
	$single = 'emtax';
} else {
	$single = rtrim($controller, 's');
}
?>

<div class="d-flex">
	<div>
		<img src="<?php echo JUri::root(TRUE); ?>/components/com_joomsubscription/images/arrow-turn-270-left.png" alt="Select and"
		     class="arrow"/>
		<button type="button" class="btn-submit btn btn-outline-primary" onclick="if (document.adminForm.boxchecked.value==0){alert('<?php echo JText::_('SELECTFIRST', TRUE) ?>');}else{Joomla.submitbutton('<?php echo $single; ?>.edit');}">
			<?php echo JText::_('EEDIT'); ?>
		</button>

		<div class="btn-group m-0 p-0">
			<button type="button" class="btn btn-outline-success" onclick="if (document.adminForm.boxchecked.value==0){alert('<?php echo JText::_('SELECTFIRST', TRUE) ?>');}else{Joomla.submitbutton('<?php echo $controller; ?>.publish');}">
				<?php echo JText::_('EPUBLISH'); ?>
			</button>

			<button type="button" class="btn-outline-secondary btn" onclick="if (document.adminForm.boxchecked.value==0){alert('<?php echo JText::_('SELECTFIRST', TRUE) ?>');}else{Joomla.submitbutton('<?php echo $controller; ?>.unpublish');}">
				<?php echo JText::_('EUNPUBLISH'); ?>
			</button>
		</div>

		<button type="button" class=" btn btn-outline-danger"
		        onclick="if (document.adminForm.boxchecked.value==0){alert('<?php echo JText::_('SELECTFIRST', TRUE) ?>');}else{if(confirm('<?php echo JText::_('E_SURE_DELETE'); ?>'))Joomla.submitbutton('<?php echo $controller; ?>.delete');}">
			<?php echo JText::_('EDELETE'); ?>
		</button>
	</div>
	<div class="ms-2">
		<button type="button" class="btn-submit btn btn-success" onclick="Joomla.submitbutton('<?php echo $single; ?>.add');">
			<?php echo JText::_('EADD'); ?>
		</button>
	</div>
</div>
