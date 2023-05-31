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
?>

<script type="text/javascript">
	Joomsubscription.submitbutton = function(task)
	{
		if (task == 'emcoupon.cancel' || document.formvalidator.isValid('#item-form')) {
			Joomsubscription.submitform(task, document.getElementById('item-form'));
		} else {
			alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
		}
	}
	//Joomsubscription.formatInt
</script>

<form method="post" name="adminForm" id="item-form" class="uk-form uk-form-horizontal">
<div class="page-header">
    <div class="uk-float-right">
        <?php $layout = Mint::loadLayout('buttons', $basePath = JPATH_COMPONENT .'/layouts'); echo $layout->render(null); ?>
    </div>
	<h1>
		<?php if($this->item->id):?>
            <img src="<?php echo JUri::root(TRUE); ?>/components/com_joomsubscription/images/cpanel/coupons.png" />
			<?php echo JText::sprintf('EEDITCOUPON', $this->item->value);?>
		<?php else:?>
            <img src="<?php echo JUri::root(TRUE); ?>/components/com_joomsubscription/images/cpanel/coupons.png" />
			<?php echo JText::_('ENEWCOUPON');?>
		<?php endif;?>
	</h1>
</div>
<hr />
	<div class="uk-grid">
		<div class="uk-width-1-1">
			<fieldset class="adminform">
				<div class="uk-form-row">
					<label class="uk-form-label"><?php echo $this->form->getLabel('id'); ?></label>
					<div class="uk-form-controls"><?php echo $this->form->getInput('id'); ?></div>
				</div>
				<div class="uk-form-row">
					<label class="uk-form-label"><?php echo $this->form->getLabel('value'); ?></label>
					<div class="uk-form-controls"><?php echo $this->form->getInput('value'); ?></div>
				</div>
				<div class="uk-form-row">
					<label class="uk-form-label"><?php echo $this->form->getLabel('published'); ?></label>
					<div class="uk-form-controls"><?php echo $this->form->getInput('published'); ?></div>
				</div>
				<div class="uk-form-row">
					<label class="uk-form-label"><?php echo $this->form->getLabel('discount'); ?></label>
					<div class="uk-form-controls"><?php echo $this->form->getInput('discount'); ?></div>
				</div>
				<div class="uk-form-row">
					<label class="uk-form-label"><?php echo $this->form->getLabel('discount_type'); ?></label>
					<div class="uk-form-controls"><?php echo $this->form->getInput('discount_type'); ?></div>
				</div>
				<div class="uk-form-row">
					<label class="uk-form-label"><?php echo $this->form->getLabel('user_ids'); ?></label>
					<div class="uk-form-controls"><?php echo $this->form->getInput('user_ids'); ?></div>
				</div>
				<div class="uk-form-row">
					<label class="uk-form-label"><?php echo $this->form->getLabel('plan_ids'); ?></label>
					<div class="uk-form-controls"><?php echo $this->form->getInput('plan_ids'); ?></div>
				</div>
				<div class="uk-form-row">
					<label class="uk-form-label"><?php echo $this->form->getLabel('use_num'); ?></label>
					<div class="uk-form-controls"><?php echo $this->form->getInput('use_num'); ?></div>
				</div>
				<div class="uk-form-row">
					<label class="uk-form-label"><?php echo $this->form->getLabel('use_user'); ?></label>
					<div class="uk-form-controls"><?php echo $this->form->getInput('use_user'); ?></div>
				</div>
				<div class="uk-form-row">
					<label class="uk-form-label"><?php echo $this->form->getLabel('ctime'); ?></label>
					<div class="uk-form-controls"><?php echo $this->form->getInput('ctime'); ?></div>
				</div>
				<div class="uk-form-row">
					<label class="uk-form-label"><?php echo $this->form->getLabel('extime'); ?></label>
					<div class="uk-form-controls"><?php echo $this->form->getInput('extime'); ?></div>
				</div>
				<?php if(!$this->item->id): ?>
				<p class="small"><?php echo JText::_('AMOUNT_COUPON_DESCR')?></p>
				<div class="uk-form-row">
					<label class="uk-form-label"><?php echo $this->form->getLabel('amount'); ?></label>
					<div class="uk-form-controls"><?php echo $this->form->getInput('amount'); ?></div>
				</div>
				<?php endif; ?>
			</fieldset>
		</div>
	</div>

	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>
<script type="text/javascript">
	(function($){
		$('#jform_discount').bind('keyup', function(){
			formatvalue($(this));
		});
		$('#jform_discount_type').change(function(){
			formatvalue($('#jform_discount'));
		});

		formatvalue($('#jform_discount'));

		function formatvalue(el) {
			var type = $('#jform_discount_type').val();
			if(type == 'procent') {
				Joomsubscription.formatInt(el[0], 100);
			} else {
				Joomsubscription.formatFloat(el[0], 2, 6);
			}
		}
	}(jQuery))
</script>