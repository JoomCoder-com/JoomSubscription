<?php
/**
 * JoomSubscription by JoomCoder
 * a component for Joomla (http://www.joomla.org)
 * Author Website: https://www.joomcoder.com/
 *
 * @copyright Copyright (C) 2012 JoomCoder (https://www.joomcoder.com/). All rights reserved.
 * @license   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */
defined('_JEXEC') or die();

JHtml::_('formbehavior.chosen', 'select');

$total = $this->plan->total;
?>

<script type="text/javascript">
	(function($) {
		Joomsubscription.submitbutton = function(task) {
			Joomsubscription.submitform(task, document.getElementById('formsubscr'));
		};

		Joomsubscription.validate_form = function() {
			if($('#invoiceto_fields_billto').length && !$('#invoiceto_fields_billto').val()) {
				alert('<?php echo str_replace("'", "\\'", JText::sprintf('JLIB_FORM_VALIDATE_FIELD_REQUIRED', JText::_('E_INVOICE_BILLTO'))); ?>');
				return;
			}
			if($('#invoiceto_fields_address').length && !$('#invoiceto_fields_address').val()) {
				alert('<?php echo str_replace("'", "\\'", JText::sprintf('JLIB_FORM_VALIDATE_FIELD_REQUIRED', JText::_('E_INVOICE_ADDRESS'))); ?>');
				return;
			}
			if($('#invoiceto_fields_zip').length && !$('#invoiceto_fields_zip').val()) {
				alert('<?php echo str_replace("'", "\\'", JText::sprintf('JLIB_FORM_VALIDATE_FIELD_REQUIRED', JText::_('E_INVOICE_ZIP'))); ?>');
				return;
			}
			if($('#invoiceto_fields_country').length && !$('#invoiceto_fields_country').val()) {
				alert('<?php echo str_replace("'", "\\'", JText::sprintf('JLIB_FORM_VALIDATE_FIELD_REQUIRED', JText::_('E_INVOICE_COUNTRY'))); ?>');
				return;
			}
			if($('#invoiceto_fields_state').length && !$('#invoiceto_fields_state').val()) {
				alert('<?php echo str_replace("'", "\\'", JText::sprintf('JLIB_FORM_VALIDATE_FIELD_REQUIRED', JText::_('ESTATE'))); ?>');
				return;
			}

			if($('#em_email').length && !$('#em_email').val()) {
				alert('<?php echo str_replace("'", "\\'", JText::sprintf('JLIB_FORM_VALIDATE_FIELD_REQUIRED', JText::_('E_EMAIL'))); ?>');
				return;
			}

			<?php if($this->com_params->get('tax_id_rec', 1)): ?>
			if($('#invoiceto_fields_tax_id').length && !$('#invoiceto_fields_tax_id').val()) {
				alert('<?php echo str_replace("'", "\\'", JText::sprintf('JLIB_FORM_VALIDATE_FIELD_REQUIRED', JText::_('E_INVOICE_TAX_ID'))); ?>');
				return;
			}
			<?php endif; ?>

			<?php if($this->plan->params->get('properties.terms') && !empty($this->plan->terms->title)): ?>
			if(!$('input[name="terms"]:checked').length) {
				alert('<?php echo str_replace("'", "\\'", strip_tags(JText::sprintf('EMR_YOU_HAVE_TO_AGREE', $this->plan->terms->title))); ?>');
				return;
			}
			<?php endif; ?>

			<?php foreach($this->fields AS $field): ?>
			<?php if($field->required): ?>
			if(!$('#field<?php echo $field->id ?>').val()){
				alert('<?php echo str_replace("'", "\\'", JText::sprintf('JLIB_FORM_VALIDATE_FIELD_REQUIRED', $field->getLabel())); ?>');
				return;
			}
			<?php endif; ?>
			<?php endforeach; ?>

			return true;
		};

		Joomsubscription.checkout = function(processor) {
			if(!Joomsubscription.validate_form()) {
				return;
			}
			$('input[name="task"]').val('empayment.send');
			$('input[name="processor"]').val(processor);
			$('#formsubscr').submit();
		}
	}(jQuery));
</script>
<style type="text/css">
	#box-terms {
		max-height: 200px;
		margin-right: 20px;
	}
</style>

<div class="page-header">
    <?php if(count(JoomsubscriptionHelper::getUserPlans())): ?>
        <div class="input-append uk-float-right">
       	    <a class="uk-button uk-button-large" href="<?php echo JoomsubscriptionApi::getLink('emhistory'); ?>">
                <img src="<?php echo JUri::root(TRUE); ?>/components/com_joomsubscription/images/cpanel/history-small.png" />
                <?php echo JText::_('EMP_CHECKHISTORY'); ?>
            </a>    
        </div>
    <?php endif; ?>
	<h1>
        <img src="<?php echo JUri::root(TRUE); ?>/components/com_joomsubscription/images/cpanel/purchase.png" />
        <?php echo $this->title; ?>
    </h1>
</div>
<hr />
	<a class="uk-button" href="<?php echo JoomsubscriptionApi::getLink('emlist'); ?>">
		<img src="<?php echo JUri::root(TRUE); ?>/components/com_joomsubscription/images/back.png" />
		<?php echo JText::_('EMR_BACKTOLIST'); ?>
	</a>
	
<form action="<?php echo JUri::getInstance()->toString(); ?>" method="post" name="formsubscr" id="formsubscr" class="uk-form">
<hr />
	<table class="uk-table ">
		<thead>
		<tr>
			<th width="1%">#</th>
			<th>
			<?php if($this->plan->is_donation == 1): ?>
				<?php echo JText::_('EMR_DONATE'); ?>
			<?php else: ?>
				<?php echo JText::_('EMR_PLANNAME'); ?>
			<?php endif; ?>
			</th>
			<th width="1%"><?php echo JText::_('EMR_PLANTOTAL'); ?></th>
		</tr>
		</thead>
		<tbody>
		<tr>
			<td>1</td>
			<td>
				<h4 style="margin-top: 0"><?php echo $this->plan->name; ?>
					<small><?php echo $this->plan->cname; ?></small>
				</h4>
				<?php echo $this->plan->description; ?>
			</td>
			<td nowrap="nowrap">
				<?php if($this->plan->is_donation == 1): ?>

					<?php
					$options = array();
					foreach ($this->plan->donation_prices as $value)
					{
						$options[] = JHtml::_('select.option', $value, JoomsubscriptionApi::getPrice($value, $this->plan->params));
					}
					$damount = JFactory::getApplication()->input->get('donation_amount', $value);
					$total = $damount;
					echo JHtml::_('select.genericlist', $options, 'donation_amount', $attribs = null, $optKey = 'value', $optText = 'text', $damount);
					?>

				<?php elseif($this->plan->is_donation == 2):?>
					<?php
					$total =  JFactory::getApplication()->input->get('donation_amount', $total);
					if($total <= $this->plan->total) $total = $this->plan->total;
					?>
					<div class="input-append">
						<input type="text" class="inputbox require" name="donation_amount" value="<?php echo $total ?>" />
						<button class="uk-button uk-button-primary" type="submit"><?php echo JText::_('EAPPLY'); ?></button>
					</div>
				<?php else: ?>
					<?php echo JoomsubscriptionApi::getPrice($this->plan->total, $this->plan->params); ?>
				<?php endif;?>
			</td>
		</tr>
		<?php foreach($this->addons AS $name => $addon): $i = 2; ?>
			<tr>
				<td><?php echo $i++; ?></td>
				<td>
					<h4 style="margin-top: 0">
						<?php echo $name; ?>
					</h4>
				</td>
				<td nowrap="nowrap"><?php echo JoomsubscriptionApi::getPrice($addon, $this->plan->params);
					$total += $addon; ?></td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>

	<table class="uk-float-right uk-table-condensed uk-table uk-table-bordered" style="width: 40%">
		<?php if(!empty($this->coupon->discount_total)): $total -= $this->coupon->discount_total; ?>
			<tr>
				<td class="uk-text-right">
					<?php echo JText::_('E_COUPON'); ?>
					<br />
					<small><?php echo JText::sprintf('EM_COUPON_TYPE_' . $this->coupon->discount_type,
							$this->coupon->discount, '<span class="label label-success">' . $this->coupon->value . '</span>',
							$this->plan->params->get('currency', 'USD')); ?></small>
				</td>
				<td class="uk-text-right">-<?php echo JoomsubscriptionApi::getPrice($this->coupon->discount_total, $this->plan->params); ?></td>
			</tr>
		<?php elseif($this->plan->discount): $total -= $this->plan->discount; ?>
			<tr>
				<td class="uk-text-right">
					<?php echo JText::_('EMR_DISCOUNT'); ?>
					<br />
					<small><?php echo JText::_('SUBSCRIPTION_DISCOUNT_' . $this->plan->discount_type); ?></small>
				</td>
				<td class="uk-text-right">-<?php echo JoomsubscriptionApi::getPrice($this->plan->discount, $this->plan->params); ?></td>
			</tr>
		<?php endif; ?>
		<tr>
			<td class="uk-text-right"><big><?php echo JText::_('EMR_INVOICETOTAL'); ?></big></td>
			<td class="uk-text-right"><strong><big><?php echo JoomsubscriptionApi::getPrice($total, $this->plan->params); ?></big></strong></td>
		</tr>
	</table>
	<div class="uk-clearfix"></div>

	<hr/>
	<div class="uk-form uk-form-horizontal">
		<?php if($this->plan->params->get('properties.muaccess') || $this->coupons && ($this->plan->price > 0 || JFactory::getApplication()->getUserState('last-joomsubscription-coupon'))): ?>
			<div class="uk-form-row">
				<label class="control-label" for="coupon"><?php echo JText::_('EMR_COUPONSERT') ?></label>

				<div class="uk-form-controls">
					<input type="text" id="coupon" name="coupon" value="<?php echo @$this->coupon->value; ?>" />
					<button class="uk-button uk-button-primary" type="button" id="apply-btn"><?php echo JText::_('EAPPLY'); ?></button>
				</div>
			</div>
		<?php endif; ?>

		<?php if($this->fields): ?>
			<?php foreach($this->fields AS $field): ?>
				<div class="uk-form-row">
					<label class="control-label" for="field<?php echo $field->id ?>">
						<?php echo $field->getLabel() ?>
						<?php if($field->required): ?> * <?php endif; ?>
					</label>

					<div class="uk-form-controls">
						<?php echo $field->getField(); ?>
						<hr/>
						<?php echo $field->getDescription(); ?>
					</div>
				</div>
			<?php endforeach; ?>
		<?php endif; ?>
		<?php if($this->plan->params->get('properties.rds') && !$this->user->get('id')): ?>
			<div class="uk-form-row">
				<label class="control-label" for="email"><?php echo JText::_('E_EMAIL'); ?><span class="star">&nbsp;*</span></label>

				<div class="uk-form-controls">
					<input type="text" aria-required="true" required="true" class="inputbox" value="" id="email" name="email" />
					<input type="hidden" value="-1" id="invoice" name="invoice" />
				</div>
			</div>
		<?php endif; ?>

		<?php if($this->params->get('use_invoice', 0) && $total > 0): ?>
			<div class="uk-form-row">
				<label class="control-label" for="invoice">
					<?php echo JText::_('E_INVOICE_BILLTO') ?>
					<?php if(JComponentHelper::getParams('com_joomsubscription')->get('use_invoice', 0) == 1): ?> * <?php endif; ?>
				</label>

				<div class="uk-form-controls">
					<?php if($this->user->get('id')): ?>
						<div class="row">
							<?php echo JHtml::_('select.genericlist', $this->inv_list, 'invoice', 'required class="col-12"', 'value', 'text', JFactory::getApplication()->getUserState('com_joomsubscription.invoiceto.selector')); ?>
						</div>

						<div id="invoice_data" class="hide"></div>

						<script>
							jQuery(document).ready(function() {
								jQuery('#invoice').bind('change keyup', function() {
									load(jQuery(this).val());
								});
								load(jQuery('#invoice').val());
							});
						</script>
					<?php endif; ?>
					<?php if(!$this->user->get('id') && $this->plan->params->get('properties.rds', 0)): ?>
						<div id="invoice_data" class=""></div>
						<script>
							jQuery(document).ready(function() {
								loadForm();
							});
						</script>
						<hr />
					<?php endif; ?>
				</div>
			</div>

		<?php endif; ?>
	</div>



	<?php if($this->plan->params->get('properties.terms') && !empty($this->plan->terms->title)): ?>
		<div>
			<h3><?php echo $this->plan->terms->title; ?></h3>

			<style>
				#box-terms {
					max-height: 200px;
					margin-right: 20px;
					overflow-y: auto;
				}
			</style>
			<div class="uk-block" id="box-terms">
				<?php echo $this->plan->terms->introtext; ?>
				<?php echo $this->plan->terms->fulltext; ?>
			</div>
		</div>
		<div class="uk-alert uk-alert-warning">

			<label class="checkbox">
				<input type="checkbox" name="terms" value="1"/>
				<?php echo JText::sprintf('EMR_AGREETERMS', $this->plan->terms->title); ?>
			</label>
		</div>
	<?php endif; ?>

	<?php if($total <= 0): ?>
		<p>
			<?php echo JText::_('EMR_FREEPLAN'); ?>
		</p>
		<button class="uk-button uk-button-large uk-button-block uk-button-danger" id="payfree" type="button"><?php echo JText::_('EMR_ACTIVATENOW'); ?></button>
	<?php else: ?>
		<h3><?php echo JText::_('EMR_PAYMENTMETHOD'); ?></h3>

		<?php if($this->plan->params->get('gateway.message')): ?>
			<p><?php echo JHtml::_('content.prepare', JText::_($this->plan->params->get('gateway.message'))); ?></p>
		<?php endif; ?>

		<?php foreach($this->plan->params->get('gateways', array()) AS $processor => $gateway): ?>
			<?php
			if($gateway->enable == 0)
			{
				continue;
			}
			include_once JPATH_ROOT . '/components/com_joomsubscription/library/gateways/' . $processor . '/' . $processor . '.php';
			$class = 'JoomsubscriptionGateway' . ucfirst($processor);
			$class = new $class($processor, $gateway);
			echo $class->getButton($this->plan, $total);
			?>
		<?php endforeach; ?>

	<?php endif; ?>

	<input type="hidden" name="task" value="" />
	<input type="hidden" name="postprocess" value="pay" />
	<input type="hidden" name="processor" value="" />
	<input type="hidden" name="sid" value="<?php echo $this->plan->id; ?>" />
</form>

<script type="text/javascript">
	(function($) {

		$('button[data-payment-gateway]').click(function() {
			Joomsubscription.checkout($(this).data('payment-gateway'));
		});

		$('#payfree').click(function() {
			$('input[name="task"]').val('empayment.send');
			$('#formsubscr').submit();
		});

		$('#apply-btn').click(function() {
			$('input[name="task"]').val('empayment.coupon');
			$('#formsubscr').submit();
		});

		$('#donation_amount').change(function() {
			$('#formsubscr').submit();
		});

	}(jQuery))

	function load(value) {
		value = parseInt(value);
		if(value > 0) {
			loadText(value);
		} else if(value == -1) {
			loadForm();
		}
	}
	var inv_dat = jQuery('#invoice_data');

	function loadText(value) {
		inv_dat.hide();
		jQuery.ajax({
			url: '<?php echo JRoute::_('index.php?option=com_joomsubscription&task=empayment.getinvoicetext', FALSE); ?>',
			type: 'GET',
			dataType: 'html',
			data: {id: value}
		}).done(function(html) {
			inv_dat.html(html).slideDown('fast');
		});
	}

	function loadForm() {
		inv_dat.hide();
		jQuery.ajax({
			url: '<?php echo JRoute::_('index.php?option=com_joomsubscription&task=empayment.getinvoiceform', FALSE); ?>',
			dataType: 'html'
		}).done(function(html) {
			inv_dat.html(html).slideDown('fast');
			jQuery('#invoiceto_fields_country')
				.chosen({
					disable_search_threshold: 10,
					allow_single_deselect: true
				});
		});
	}
</script>
