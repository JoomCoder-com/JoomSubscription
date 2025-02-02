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
<form method="post" name="adminForm" id="item-form" class="form-validate form-horizontal">
<div class="page-header">
    <div class="pull-right">
        <?php
        $layout = Mint::loadLayout('buttons', $basePath = JPATH_COMPONENT . '/layouts');
        echo $layout->render(NULL);
        ?>    
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

	<div class="row">
		<div class="col-6">
			<fieldset class="adminform">
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('id'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('id'); ?></div>
				</div>
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('plan_id'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('plan_id'); ?></div>
				</div>
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('user_id'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('user_id'); ?></div>
				</div>

				<?php if($this->item->id):?>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('ctime'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('ctime'); ?></div>
					</div>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('extime'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('extime'); ?></div>
					</div>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('access_limit'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('access_limit'); ?></div>
					</div>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('access_count'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('access_count'); ?></div>
					</div>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('price'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('price'); ?></div>
					</div>
				<?php endif;?>

				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('published'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('published'); ?></div>
				</div>
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('gateway'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('gateway'); ?></div>
				</div>
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('gateway_id'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('gateway_id'); ?></div>
				</div>
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('comment'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('comment'); ?></div>
				</div>
			</fieldset>
		</div>
	</div>

	<input type="hidden" name="id" value="<?php echo $app->input->getInt('id', 0);?>" />
	<input type="hidden" name="Itemid" value="<?php echo $app->input->getInt('Itemid');?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="return" value="<?php echo $this->state->get('group.return');?>" />
	<?php echo JHtml::_('form.token'); ?>
</form>