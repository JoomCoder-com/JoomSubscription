<?php
/**
 * JoomSubscription by JoomCoder
 * a component for Joomla! 3.0 CMS (http://www.joomla.org)
 * Author Website: https://www.joomcoder.com/
 * @copyright Copyright (C) 2012 JoomCoder (https://www.joomcoder.com/). All rights reserved.
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die('Restricted access');

/** @var \Joomla\Component\Content\Site\View\Form\HtmlView $this */
/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = \Joomla\CMS\Factory::getDocument()->getWebAssetManager();
$wa->useScript('keepalive')
	->useScript('form.validate');
$wa->usePreset('choicesjs');

Joomla\CMS\HTML\HTMLHelper::_('dropdown.init');
?>

<script type="text/javascript">
	Joomsubscription.submitbutton = function(task)
	{
		if (task == 'emtax.cancel' || document.formvalidator.isValid('#item-form')) {
			Joomsubscription.submitform(task, document.getElementById('item-form'));
		} else {
			alert('<?php echo $this->escape(\Joomla\CMS\Language\Text::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
		}
	}
</script>
<form method="post" name="adminForm" id="item-form" class="form-validate form-horizontal">
<div class="page-header">
    <div class="float-end">
        <?php
        $layout = Mint::loadLayout('buttons', $basePath = JPATH_COMPONENT . '/layouts');
        echo $layout->render(NULL);
        ?>    
    </div>
	<h1>
		<?php if($this->item->id):?>
            <img src="<?php echo \Joomla\CMS\Uri\Uri::root(TRUE); ?>/components/com_joomsubscription/images/cpanel/taxes.png" />
			<?php echo \Joomla\CMS\Language\Text::sprintf('EEDITTAX', $this->item->tax_name);?>
		<?php else:?>
            <img src="<?php echo \Joomla\CMS\Uri\Uri::root(TRUE); ?>/components/com_joomsubscription/images/cpanel/taxes.png" />
			<?php echo \Joomla\CMS\Language\Text::_('ENEWTAX');?>
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
					<div class="control-label"><?php echo $this->form->getLabel('country_id'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('country_id'); ?></div>
				</div>
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('state_id'); ?></div>
					<div class="controls" id="statediv"><?php echo \Joomla\CMS\Language\Text::_('ESELECT_COUNTRY'); ?></div>
				</div>
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('tax_name'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('tax_name'); ?></div>
				</div>
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('vies'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('vies'); ?></div>
				</div>
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('tax'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('tax'); ?></div>
				</div>
			</fieldset>
		</div>
	</div>

	<input type="hidden" name="task" value="" />
	<input type="hidden" name="id" value="<?php echo $this->item->id;?>" />
	<input type="hidden" name="return" value="<?php echo $this->state->get('state.return');?>" />
	<?php echo Joomla\CMS\HTML\HTMLHelper::_('form.token'); ?>
</form>

<script type="text/javascript">
	(function($) {
		var state = $(document.createElement('div'))
			.css({
				'display':     'inline',
				"margin-left": '10px'
			});

		$('#jform_country_id')
			.after(state)
			.bind('change keyup', function() {
				changeCountry($(this));
			});

		changeCountry($('#jform_country_id'));

		function changeCountry(el) {
			if(!el.val()) {
				return;
			}
			if(el.val() == '*')
			{
				$('#statediv').html('<?php echo \Joomla\CMS\Language\Text::_('ESELECT_COUNTRY', true)?>');
				return;
			}

			$('#statediv').html('<img src="<?php echo \Joomla\CMS\Uri\Uri::root(TRUE); ?>/components/com_cobalt/images/load.gif" >');

			$.ajax({
				url: '<?php echo \Joomla\CMS\Router\Route::_('index.php?option=com_joomsubscription&task=emajax.getstates', FALSE); ?>',
				type: 'GET',
				dataType: 'html',
				data: {id: el.val(), name:'jform[state_id]', 'default': '<?php echo @$this->default['state_id']; ?>'}
			})
				.done(function(html) {
					if(!html)
					{
						$('#statediv').html('<?php echo \Joomla\CMS\Language\Text::sprintf('E_STATE_NOT_FOUND', \Joomla\CMS\Router\Route::_('index.php?option=com_joomsubscription&view=emstates'), array('jsSafe'=>true))?>');
						return;

					}
					$('#statediv').html(html);
					if (document.getElementById('jformstate_id')) {
						new Choices(document.getElementById('jformstate_id'), {
							searchEnabled: true,
							allowHTML: true,
							shouldSort: false,
							placeholderValue: '',
							removeItemButton: true
						});
					}
				})
		}

		Joomsubscription.redrawBS();

	}(jQuery));
</script>