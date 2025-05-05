<?php
/**
 * @license   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 *
 */
defined('_JEXEC') or die('Restricted access');
?>

<style>
	.company-name {
		font-size: 12pt;
	}

	.mainwrap {
		padding: 20px;
	}

	.gray {
		background-color: #DDDDDD;
	}

	.align-right {
		text-align: right !important;
	}

	.bold {
		font-weight: bold;
	}

	.order-item {
		font-size: 16px;
		padding-bottom: 10px;
		display: block;
	}

	.table-items td h4 {
		margin-top: 0px;
		padding-top: 0px;
	}

	@media print {
		.btn-print {
			display: none;
		}
	}

	.invtable {
		width: 100%
	}

	.invtable td {
		vertical-align: top;
		width: 50%;
	}
</style>
<div class="container">
	<button class="btn btn-block btn-print" onclick="window.print();"><?php echo JText::_('EM_PRINT'); ?></button>
	<div class="mainwrap">
		<table class="invtable">
			<tr>
				<td>
				<span class="company-name">
					<?php echo $this->params->get('name') ?>
				</span>
					<br/>
					<?php echo $this->params->get('address') ?><br/><br/>
					<?php echo JText::_('E_INVOICE_TAX_ID'); ?>:

					<?php if($this->params->get('vies')): ?>
						<?php echo $this->params->get('country'); ?>
					<?php endif; ?>

					<?php if($this->params->get('tax_id')): ?>
						<?php echo $this->params->get('tax_id') ?><br/>
					<?php endif; ?>
					<?php echo JText::_('E_INVOICE_PHONE'); ?>: <?php echo $this->params->get('phone') ?><br/>
				</td>
				<td>
					<?php if($this->params->get('logo')): ?>
						<img src="<?php echo JUri::root(TRUE) . '/' . $this->params->get('logo') ?>" alt="<?php echo $this->params->get('name') ?>"/>
					<?php endif; ?>
				</td>
			</tr>
			<tr>
				<td>
					<table class="table table-bordered table-condensed">
						<thead>
						<tr class="gray">
							<th width="1%" nowrap="nowrap"><?php echo JText::_('E_INVOICE_BILLTO'); ?></th>
							<td>&nbsp;</td>
						</tr>
						</thead>
						<tbody>
						<tr>
							<td><?php echo JText::_('E_INVOICE_BILLTO') ?></td>
							<td><?php echo $this->invoice->fields->get('billto') ?></td>
						</tr>
						<tr>
							<td><?php echo JText::_('E_INVOICE_ADDRESS') ?></td>
							<td><?php echo $this->invoice->fields->get('address') ?></td>
						</tr>

						<?php if($this->invoice->fields->get('tax_id')): ?>
							<tr>
								<td><?php echo JText::_('E_INVOICE_TAX_ID') ?></td>
								<td>
									<?php if($this->invoice->fields->get('vies')): ?>
										<?php echo $this->invoice->fields->get('country_id'); ?>
									<?php endif; ?>
									<?php echo $this->invoice->fields->get('tax_id') ?>
								</td>
							</tr>
						<?php endif; ?>

						<tr>
							<td><?php echo JText::_('ECOUNTRY') ?></td>
							<td><?php echo $this->invoice->fields->get('country') ?></td>
						</tr>
						<?php if($this->invoice->fields->get('state', FALSE)): ?>
							<tr>
								<td><?php echo JText::_('ESTATE') ?></td>
								<td><?php echo $this->invoice->fields->get('state') ?></td>
							</tr>
						<?php endif; ?>
						<tr>
							<td><?php echo JText::_('E_INVOICE_ZIP') ?></td>
							<td><?php echo $this->invoice->fields->get('zip') ?></td>
						</tr>
						<tr>
							<td><?php echo JText::_('E_INVOICE_PHONE') ?></td>
							<td><?php echo $this->invoice->fields->get('phone') ?></td>
						</tr>
						</tbody>
					</table>
				</td>
				<td>
					<table class="table table-bordered table-condensed">
						<thead>
						<tr class="gray">
							<th width="1%" nowrap="nowrap"><?php echo JText::_('E_INVOICE_INFO') ?></th>
							<td>&nbsp;</td>
						</tr>
						</thead>
						<tbody>
						<tr>
							<td><?php echo JText::_('E_INVOICE_NUM') ?></td>
							<td><?php echo $this->subscr->invoice_num ?></td>
						</tr>
						<tr>
							<td><?php echo JText::_('E_INVOICE_DATE') ?></td>
							<td><?php echo JoomsubscriptionHelper::getFormattedDate($this->subscr->ctime) ?></td>
						</tr>
						<tr>
							<td><?php echo JText::_('E_PAYMENT_METHOD') ?></td>
							<td><?php echo JText::_($this->subscr->gateway) ?></td>
						</tr>
						<tr>
							<td><?php echo JText::_('E_PAYMENT_ID') ?></td>
							<td><?php echo $this->subscr->gateway_id ?></td>
						</tr>
						</tbody>
					</table>
				</td>
			</tr>
		</table>

		<div class="row">
			<span class="order-item"><?php echo JText::_('E_ORDER_ITEM') ?></span>
			<table class="table table-bordered table-items table-condensed">
				<thead>
				<tr class="gray">
					<th>#</th>
					<th><?php echo JText::_('EDESCR') ?></th>
					<th><?php echo JText::_('X_PRICE') ?></th>
				</tr>
				</thead>
				<tbody>
				<tr>
					<td>1</td>
					<td><h4><?php echo $this->plan->name; ?></h4></td>
					<td class="align-right">
						<big>
							<?php
							$price = $this->subscr->price;

							if(!empty($this->tax['percent']))
							{
								$price = $this->subscr->price / (100 + $this->tax['percent']) * 100;
							}

							echo JoomsubscriptionApi::getPrice(($this->discount ? $this->discount + $price : $price) - $this->items_total, $this->plan->params, $this->subscr->params);
							?>
						</big>
					</td>
				</tr>
				<?php foreach($this->items AS $i => $item): ?>
					<tr>
						<td><?php echo $i + 2; ?></td>
						<td><h4><?php echo $item['name']; ?></h4></td>
						<td class="align-right">
							<big>
								<?php echo JoomsubscriptionApi::getPrice($item['price'], $this->plan->params); ?>
							</big>
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>

			<table class=" table table-bordered float-end" style="width: 35%;">
				<tbody>
				<tr>
					<td class="align-right bold">
						<?php echo JText::_('EDISCOUNT') ?>:<br/>
						<small>
							<?php echo $this->discount_type ? JText::_('EM_DT_' . strtoupper($this->discount_type)) : NULL ?>
							<?php if(!empty($this->coupon->value)): ?>
								<span class="label label-success"><?php echo $this->coupon->value ?></span>
							<?php endif; ?>
						</small>
					</td>
					<td class="align-right">- <?php echo JoomsubscriptionApi::getPrice($this->discount, $this->plan->params, $this->subscr->params) ?></td>
				</tr>
				<tr class="success">
					<td class="align-right bold"><?php echo JText::_('E_SUBTOTAL') ?>:</td>
					<td class="align-right bold"><?php echo JoomsubscriptionApi::getPrice($price, $this->plan->params, $this->subscr->params) ?></td>
				</tr>
				<?php $tax = 0;
				if(!empty($this->tax['percent'])): ?>
					<tr>
						<td class="align-right bold"><?php echo JText::_($this->tax['name']) ?> <?php echo $this->tax['percent'] ?>%:</td>
						<td class="align-right">
							<?php $tax = $price * ($this->tax['percent'] / 100); ?>
							<?php echo JoomsubscriptionApi::getPrice($tax, $this->plan->params, $this->subscr->params) ?>
						</td>
					</tr>
				<?php endif; ?>
				<?php $price += $tax; ?>
				<tr>
					<td class="align-right bold"><big><?php echo JText::_('EMR_INVOICETOTAL')?>:</big></td>
					<td class="align-right bold"><big><?php echo JoomsubscriptionApi::getPrice($price, $this->plan->params, $this->subscr->params)?></big></td>
				</tr>
				</tbody>
			</table>

		</div>
	</div>
</div>
