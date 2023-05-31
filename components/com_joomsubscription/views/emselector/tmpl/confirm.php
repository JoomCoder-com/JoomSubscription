<?php
/**
 * JoomSubscription by JoomCoder
 * a component for Joomla (http://www.joomla.org)
 * Author Website: https://www.joomcoder.com/
 * @copyright Copyright (C) 2012 JoomCoder (https://www.joomcoder.com/). All rights reserved.
 * @license   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */
defined('_JEXEC') or die();
$total = $this->plan->total;
?>
<button type="button" id="selector-back-btn" class="btn btn-primary">
	<?php echo JText::_('EMBACK'); ?>
</button>

<table class="table">
	<thead>
	<tr>
		<th width="1%">#</th>
		<th><?php echo JText::_('EMR_PLANNAME'); ?></th>
		<th width="1%"><?php echo JText::_('EMR_PLANTOTAL'); ?></th>
	</tr>
	</thead>
	<tbody>
	<tr>
		<td>1</td>
		<td>
			<h4 style="margin-top: 0"><?php echo $this->plan->name; ?></h4>
			<?php echo $this->plan->description; ?>
		</td>
		<td nowrap="nowrap"><?php echo JoomsubscriptionApi::getPrice($this->plan->total, $this->plan->params); ?></td>
	</tr>
	</tbody>
</table>

<table class="pull-right table-condensed table table-bordered" style="width: 40%">
	<?php if(!empty($this->coupon->discount_total)): $total -= $this->coupon->discount_total; ?>
		<tr>
			<td>
				<?php echo JText::_('E_COUPON'); ?>
				<br>
				<small><?php echo JText::sprintf('EM_COUPON_TYPE_' . $this->coupon->discount_type,
						$this->coupon->discount, '<span class="label label-success">' . $this->coupon->value . '</span>', $this->plan->params->get('currency', 'USD')); ?></small>
			</td>
			<td>-<?php echo JoomsubscriptionApi::getPrice($this->coupon->discount_total, $this->plan->params); ?></td>
		</tr>
	<?php elseif($this->plan->discount): $total -= $this->plan->discount; ?>
		<tr>
			<td>
				<?php echo JText::_('EMR_DISCOUNT'); ?>
			</td>
			<td>-<?php echo JoomsubscriptionApi::getPrice($this->plan->discount, $this->plan->params); ?></td>
		</tr>
	<?php endif; ?>
	<tr>
		<td><big><?php echo JText::_('EMR_INVOICETOTAL'); ?></big></td>
		<td><strong><big><?php echo JoomsubscriptionApi::getPrice($total, $this->plan->params); ?></big></strong></td>
	</tr>
</table>
<div class="clearfix"></div>


<?php if($this->coupons): ?>
	<hr>
	<div class="form-inline">
		<label for="coupon"><?php echo JText::_('EMR_COUPONSERT') ?></label>
		<input type="text" id="selector-coupon" name="coupon"
			   value="<?php echo JFactory::getApplication()->input->get('coupon'); ?>">
		<button class="btn btn-primary" type="button"
				id="selector-coupon-btn"><?php echo JText::_('EAPPLY'); ?></button>
	</div>
<?php endif; ?>

<hr>

<?php if($total <= 0): ?>
	<p class="alert alert-success">
		<?php echo JText::_('EMR_FREEPLANNOTE'); ?>
	</p>
<?php else: ?>
	<h3><?php echo JText::_('EMR_PAYMENTMETHOD'); ?></h3>
	<?php foreach($this->plan->params->get('gateways', array()) AS $processor => $gateway): ?>
		<?php if($gateway->enable == 0)
		{
			continue;
		} ?>
		<button type="button" class="btn" data-gateway-provider="<?php echo $processor; ?>">
			<?php echo JText::_($gateway->label); ?>
		</button>

	<?php endforeach; ?>
<?php endif; ?>

<script>
	(function($) {
		$('#selector-hidden-price').val('<?php echo $total; ?>');

		$('#selector-back-btn').click(function() {
			$('#selector-hidden-plan').val(0);
			$('#selector-hidden-coupon').val('');
			$('#selector-hidden-gateway').val('');
			$('#selector-hidden-price').val('');
			window.selectorLoadLayout('list');
		});
		$('#selector-coupon-btn').click(function() {
			var cpn = $('#selector-coupon').val();
			if(!cpn) {
				return;
			}
			$('#selector-hidden-coupon').val(cpn);
			window.selectorLoadLayout('confirm', {id: <?php echo $this->plan->id; ?>, coupon: cpn});
		});

		gtws = $('button[data-gateway-provider]');

		gtws.each(function() {
			$(this).click(function() {
				gtws.removeClass('btn-warning');
				$(this).addClass('btn-warning');
				$('#selector-hidden-gateway').val($(this).data('gateway-provider'));
			});
		});

		if($('#selector-hidden-gateway').val()) {
			$('button[data-gateway-provider="' + $('#selector-hidden-gateway').val() + '"]').trigger('click');
		}

	}(jQuery))
</script>