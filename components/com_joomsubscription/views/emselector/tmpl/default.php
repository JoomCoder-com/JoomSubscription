<?php
/**
 * JoomSubscription by JoomCoder
 * a component for Joomla (http://www.joomla.org)
 * Author Website: https://www.joomcoder.com/
 * @copyright Copyright (C) 2012 JoomCoder (https://www.joomcoder.com/). All rights reserved.
 * @license   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */
defined('_JEXEC') or die();
?>

<style>
	.alert p:last-child {
		margin-bottom: 0;
	}
</style>

<div class="progress progress-striped active" id="selector-progress">
	<div class="bar" style="width: 100%;"><?php echo JText::_('EM_LOADING'); ?></div>
</div>

<div id="plans-selector" class="">

</div>

<!-- CHANGE TO HIDDEN LATER -->
<input type="hidden" id="selector-hidden-plan" name="<?php echo $this->name; ?>[plan_id]"
	   value="<?php echo @$this->default['plan_id']; ?>"/>
<input type="hidden" id="selector-hidden-price" name="<?php echo $this->name; ?>[price]"
	   value="<?php echo @$this->default['price']; ?>"/>
<input type="hidden" id="selector-hidden-coupon" name="<?php echo $this->name; ?>[coupon]"
	   value="<?php echo @$this->default['coupon']; ?>"/>
<input type="hidden" id="selector-hidden-gateway" name="<?php echo $this->name; ?>[gateway]"
	   value="<?php echo @$this->default['gateway']; ?>"/>

<script type="text/javascript">
	(function($) {

		var container = $('#plans-selector');
		var progress = $('#selector-progress');

		window.selectorLoadLayout = function(what, options) {

			var data = $.extend({}, options, {
				layout: what,
				plans:  '<?php echo $this->plans; ?>',
				groups: '<?php echo $this->groups; ?>'
			});

			container.slideUp('fast', function() {
				progress.show();

				$.ajax({
					url:      '<?php echo JRoute::_('index.php?option=com_joomsubscription&task=emajax.selectorShow&tmpl=component', FALSE); ?>',
					dataType: 'json',
					type:     'POST',
					data:     data
				}).done(function(json) {

						progress.hide(100, function() {
							if(json.error) {
								alert(json.error);
								return;
							}

							container.html(json.html).slideDown('fast');
						});
					});
			});
		}

		<?php if(!empty($this->default['plan_id'])): ?>
			window.selectorLoadLayout('confirm', {
				id: <?php echo $this->default['plan_id']; ?>,
				coupon: '<?php echo @$this->default['coupon']; ?>'
			});
		<?php else: ?>
			window.selectorLoadLayout('list');
		<?php endif; ?>

		$('#selector-hidden-plan').closest('form').submit(function(event) {
			if($('#selector-hidden-plan').val() && parseFloat($('#selector-hidden-price').val()) > 0 && !$('#selector-hidden-gateway').val()) {
				alert('<?php echo JText::_('EM_SELECTGATEWAY'); ?>');
				event.preventDefault();
			}
			<?php if($this->required): ?>
			if(!$('#selector-hidden-plan').val()) {
				alert('<?php echo JText::_('EM_SELECTPLANSEL'); ?>');
				event.preventDefault();
			}
			<?php endif; ?>
		});
	}(jQuery))
</script>