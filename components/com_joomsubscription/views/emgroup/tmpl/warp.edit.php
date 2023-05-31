<?php
/**
 * JoomSubscription by JoomCoder
 * a component for Joomla! 3.0 CMS (http://www.joomla.org)
 * Author Website: https://www.joomcoder.com/
 * @copyright Copyright (C) 2012 JoomCoder (https://www.joomcoder.com/). All rights reserved.
 * @license   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die('Restricted access');

JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');
JHtml::_('dropdown.init');
JHtml::_('formbehavior.chosen', 'select');
?>

<script type="text/javascript">
	Joomsubscription.submitbutton = function(task) {
		if(task == 'emgroup.cancel' || document.formvalidator.isValid('#item-form')) {
			Joomsubscription.submitform(task, document.getElementById('item-form'));
		} else {
			alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
		}
	}
</script>
<form method="post" name="adminForm" id="item-form" class="uk-form uk-form-horizontal">
<div class="page-header">
    <div class="uk-float-right">
        <?php $layout = Mint::loadLayout('buttons', $basePath = JPATH_COMPONENT .'/layouts'); echo $layout->render(null); ?>
    </div>
	<h1>
		<?php if($this->item->id): ?>
            <img src="<?php echo JUri::root(TRUE); ?>/components/com_joomsubscription/images/cpanel/groups.png" />
			<?php echo JText::sprintf('EEDITGROUP', $this->item->name); ?>
		<?php else: ?>
            <img src="<?php echo JUri::root(TRUE); ?>/components/com_joomsubscription/images/cpanel/groups.png" />
			<?php echo JText::_('ENEWGROUP'); ?>
		<?php endif; ?>
	</h1>
</div>
<hr />
    <div class="uk-panel">
	<fieldset class="adminform">
		<div class="uk-form-row">
			<label class="uk-form-label"><?php echo $this->form->getLabel('id'); ?></label>
			<div class="uk-form-controls"><?php echo $this->form->getInput('id'); ?></div>
		</div>
		<div class="uk-form-row">
			<label class="uk-form-label"><?php echo $this->form->getLabel('name'); ?></label>
			<div class="uk-form-controls"><?php echo $this->form->getInput('name'); ?></div>
		</div>
		<div class="uk-form-row">
			<label class="uk-form-label"><?php echo $this->form->getLabel('ordering'); ?></label>
			<div class="uk-form-controls"><?php echo $this->form->getInput('ordering'); ?></div>
		</div>
		<div class="uk-form-row">
			<label class="uk-form-label"><?php echo $this->form->getLabel('published'); ?></label>
			<div class="uk-form-controls"><?php echo $this->form->getInput('published'); ?></div>
		</div>
		<div class="uk-form-row">
			<label class="uk-form-label"><?php echo str_replace('class="hasTooltip"', 'data-uk-tooltip', $this->form->getLabel('access')); ?></label>
			<div class="uk-form-controls"><?php echo $this->form->getInput('access'); ?></div>
		</div>
		<div class="uk-form-row">
			<label class="uk-form-label"><?php echo $this->form->getLabel('language'); ?></label>
			<div class="uk-form-controls"><?php echo $this->form->getInput('language'); ?></div>
		</div>
		<div class="uk-form-row">
			<label class="uk-form-label"><?php echo $this->form->getLabel('image'); ?></label>
			<div class="uk-form-controls"><?php echo str_replace(array('btn', 'hasTooltip', 'title='), array('uk-button', '', 'data-uk-tooltip title='), $this->form->getInput('image')); ?></div>
		</div>
	</fieldset>
    <br />
    </div>
	<?php echo MFormHelper::renderFieldset($this->params_form, 'main', $this->item->params, 'properties',  MFormHelper::FIELDSET_SEPARATOR_FIELDSET, MFormHelper::STYLE_CLASSIC); ?>
	<br>
	<fieldset>
	<legend><?php echo $this->form->getLabel('description'); ?></legend>
	<div><?php echo $this->form->getInput('description'); ?></div>
	</fieldset>

	<input type="hidden" name="task" value=""/>
	<input type="hidden" name="id" value="<?php echo $this->item->id; ?>"/>
	<input type="hidden" name="return" value="<?php echo $this->state->get('group.return'); ?>"/>
	<?php echo JHtml::_('form.token'); ?>
</form>