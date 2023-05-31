<?php
/**
 * JoomSubscription by JoomCoder
 * a component for Joomla! 3.0 CMS (http://www.joomla.org)
 * Author Website: https://www.joomcoder.com/
 * @copyright Copyright (C) 2012 JoomCoder (https://www.joomcoder.com/). All rights reserved.
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die('Restricted access');

JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');
JHtml::_('dropdown.init');
JHtml::_('formbehavior.chosen', 'select');

$app = JFactory::getApplication();
?>

<script type="text/javascript">
	Joomsubscription.submitbutton = function(task)
	{
		if (task == 'emsale.cancel' || document.formvalidator.isValid('#item-form')) {
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
		<?php if($this->item->id):?>
            <img src="<?php echo JUri::root(TRUE); ?>/components/com_joomsubscription/images/cpanel/sales.png" />
			<?php echo JText::sprintf('EEDITSUBSCRIPTION');?>
		<?php else:?>
            <img src="<?php echo JUri::root(TRUE); ?>/components/com_joomsubscription/images/cpanel/sales.png" />
			<?php echo JText::_('ENEWSUBSCRIPTION');?>
		<?php endif;?>
	</h1>
</div>
<hr />
	<div class="uk-grid">
		<div class="uk-width-1-1">
        <div class="uk-panel uk-panel-box">
			<fieldset class="adminform">
				<div class="uk-form-row">
					<label class="uk-form-label"><?php echo $this->form->getLabel('id'); ?></label>
					<div class="uk-form-controls"><?php echo $this->form->getInput('id'); ?></div>
				</div>
                <br />
				<div class="control-group">
					<label class="uk-form-label"><?php echo $this->form->getLabel('plan_id'); ?></label>
					<div class="uk-form-controls"><?php echo $this->form->getInput('plan_id'); ?></div>
				</div>
                <br />
				<div class="control-group">
					<label class="uk-form-label"><?php echo $this->form->getLabel('user_id'); ?></label>
					<div class="uk-form-controls"><?php echo $this->form->getInput('user_id'); ?></div>
				</div>
                <br />

				<?php if($this->item->id):?>
					<div class="control-group">
						<label class="uk-form-label"><?php echo $this->form->getLabel('ctime'); ?></label>
						<div class="uk-form-controls"><?php echo $this->form->getInput('ctime'); ?></div>
					</div>
                    <br />
					<div class="control-group">
						<label class="uk-form-label"><?php echo $this->form->getLabel('extime'); ?></label>
						<div class="uk-form-controls"><?php echo $this->form->getInput('extime'); ?></div>
					</div>
                    <br />
					<div class="control-group">
						<label class="uk-form-label"><?php echo $this->form->getLabel('access_limit'); ?></label>
						<div class="uk-form-controls"><?php echo $this->form->getInput('access_limit'); ?></div>
					</div>
                    <br />
					<div class="control-group">
						<label class="uk-form-label"><?php echo $this->form->getLabel('access_count'); ?></label>
						<div class="uk-form-controls"><?php echo $this->form->getInput('access_count'); ?></div>
					</div>
                    <br />
					<div class="control-group">
						<label class="uk-form-label"><?php echo $this->form->getLabel('price'); ?></label>
						<div class="uk-form-controls"><?php echo $this->form->getInput('price'); ?></div>
					</div>
                    <br />
				<?php endif;?>

				<div class="control-group">
					<label class="uk-form-label"><?php echo $this->form->getLabel('published'); ?></label>
					<div class="uk-form-controls"><?php echo $this->form->getInput('published'); ?></div>
				</div>
                <br />
				<div class="control-group">
					<label class="uk-form-label"><?php echo $this->form->getLabel('gateway'); ?></label>
					<div class="uk-form-controls"><?php echo $this->form->getInput('gateway'); ?></div>
				</div>
                <br />
				<div class="control-group">
					<label class="uk-form-label"><?php echo $this->form->getLabel('gateway_id'); ?></label>
					<div class="uk-form-controls"><?php echo $this->form->getInput('gateway_id'); ?></div>
				</div>
                <br />
				<div class="control-group">
					<label class="uk-form-label"><?php echo $this->form->getLabel('comment'); ?></label>
					<div class="uk-form-controls"><?php echo $this->form->getInput('comment'); ?></div>
				</div>
			</fieldset>
            </div>
		</div>
	</div>

	<input type="hidden" name="id" value="<?php echo $app->input->getInt('id', 0);?>" />
	<input type="hidden" name="Itemid" value="<?php echo $app->input->getInt('Itemid');?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="return" value="<?php echo $this->state->get('group.return');?>" />
	<?php echo JHtml::_('form.token'); ?>
</form>