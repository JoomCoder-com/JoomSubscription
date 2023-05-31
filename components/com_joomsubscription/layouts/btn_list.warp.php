<?php
/**
 * Cobalt by JoomCoder
 * a component for Joomla! 1.7 - 2.5 CMS (http://www.joomla.org)
 * Author Website: https://www.joomcoder.com/
 *
 * @copyright Copyright (C) 2012 JoomCoder (https://www.joomcoder.com/). All rights reserved.
 * @license   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */
defined('_JEXEC') or die();
$app = JFactory::getApplication();
$controller = $app->input->getCmd('view', 'emplan');
if ($controller == 'emtaxes') {
    $single = 'emtax';
} else {
    $single = rtrim($controller, 's');
}
?>
<div class="uk-float-left">
	<img src="<?php echo JUri::root(true); ?>/components/com_joomsubscription/images/arrow-turn-270-left.png" alt="Select and" class="arrow" />
	<button type="button" class="uk-button-submit uk-button uk-button-primary " onclick="if (document.adminForm.boxchecked.value==0){alert('<?php echo
JText::_('SELECTFIRST', true) ?>');}else{Joomsubscription.submitbutton('<?php echo
$single; ?>.edit');}">
		<i class="uk-icon-edit"></i>&nbsp;&nbsp;<?php echo JText::_('EEDIT'); ?>
	</button>
	<div class="uk-button-group">
		<button type="button" class="uk-button-submit uk-button" onclick="if (document.adminForm.boxchecked.value==0){alert('<?php echo
JText::_('SELECTFIRST', true) ?>');}else{Joomsubscription.submitbutton('<?php echo
$controller; ?>.publish');}">
			<i class="uk-icon-thumbs-o-up"></i>&nbsp;&nbsp;<?php echo JText::_('EPUBLISH'); ?>
		</button>
		<button type="button" class="uk-button-submit uk-button" onclick="if (document.adminForm.boxchecked.value==0){alert('<?php echo
JText::_('SELECTFIRST', true) ?>');}else{Joomsubscription.submitbutton('<?php echo
$controller; ?>.unpublish');}">
			<i class="uk-icon-thumbs-o-down"></i>&nbsp;&nbsp;<?php echo JText::_('EUNPUBLISH'); ?>
		</button>
	</div>
	<button type="button" class="uk-button-submit uk-button uk-button-danger"
			onclick="if (document.adminForm.boxchecked.value==0){alert('<?php echo JText::
_('SELECTFIRST', true) ?>');}else{if(confirm('<?php echo
JText::_('E_SURE_DELETE'); ?>'))Joomsubscription.submitbutton('<?php echo
$controller; ?>.delete');}">
		<i class="uk-icon-remove"></i>&nbsp;&nbsp;<?php echo JText::_('EDELETE'); ?>
	</button>
</div>
<div class="uk-float-right">
	<button type="button" class="uk-button-submit uk-button uk-button-success" onclick="Joomsubscription.submitbutton('<?php echo
$single; ?>.add');">
		<i class="uk-icon-plus"></i>&nbsp;&nbsp;<?php echo JText::_('EADD'); ?>
	</button>
</div>