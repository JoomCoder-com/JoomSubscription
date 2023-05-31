<?php
/**
 * @license   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 *
 */
defined('_JEXEC') or die('Restricted access');
?>

<style>
.company-name{
	font-size: 12pt;
}
.mainwrap{
	padding: 20px;
}
.gray{
	background-color: #DDDDDD;
}
.align-right{
	text-align: right !important;
}
.bold{
	font-weight: bold;
}
.order-item{
	font-size: 16px;
	padding-bottom: 10px;
	display: block;
}
.table-items td h4{
	margin-top: 0px;
	padding-top: 0px;
}
@media print {
	.btn-print {
		display: none;
	}
}
.invtable{
	width: 100%
}
.invtable td{
	vertical-align: top;
	width: 50%;
}
</style>
<button class="btn btn-block btn-print" onclick="window.print();">Print</button>
<div class="mainwrap">
	<table class="invtable">
		<tr>
			<td>
				<span class="company-name">
					<?php echo $this->params->get('name')?>
				</span>
				<br />
				<?php echo $this->params->get('address')?><br /><br />
				<?php if($this->params->get('tax_id')): ?>
					<?php echo JText::_('E_INVOICE_TAX_ID');?>: <?php echo $this->params->get('tax_id')?><br />
				<?php endif; ?>
				<?php echo JText::_('E_INVOICE_PHONE');?>: <?php echo $this->params->get('phone')?><br />
			</td>
			<td>
					<?php if ($this->params->get('logo')):?>
						<img src="<?php echo JUri::root(true).'/'.$this->params->get('logo')?>" alt="<?php echo $this->params->get('name')?>" />
					<?php endif;?>
			</td>
		</tr>
		<tr>
			<td>
				<table class="table table-bordered table-condensed">
					<thead>
						<tr class="gray">
							<th width="1%" nowrap="nowrap">Customer Information</th>
							<td>&nbsp;</td>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td><?php echo JText::_('E_INVOICE_BILLTO')?></td>
							<td><?php echo $this->invoice->fields->get('billto')?></td>
						</tr>
						<tr>
							<td><?php echo JText::_('E_INVOICE_ADDRESS')?></td>
							<td><?php echo $this->invoice->fields->get('address')?></td>
						</tr>

						<?php if($this->invoice->fields->get('tax_id')): ?>
						<tr>
							<td><?php echo JText::_('E_INVOICE_TAX_ID')?></td>
							<td><?php echo $this->invoice->fields->get('tax_id')?></td>
						</tr>
						<?php endif; ?>

						<tr>
							<td><?php echo JText::_('ECOUNTRY')?></td>
							<td><?php echo $this->invoice->fields->get('country')?></td>
						</tr>
						<tr>
							<td><?php echo JText::_('ESTATE')?></td>
							<td><?php echo $this->invoice->fields->get('state')?></td>
						</tr>
						<tr>
							<td><?php echo JText::_('E_INVOICE_PHONE')?></td>
							<td><?php echo $this->invoice->fields->get('phone')?></td>
						</tr>
					</tbody>
				</table>
			</td>
			<td>
				<table class="table table-bordered table-condensed">
					<thead>
						<tr class="gray">
							<th width="1%" nowrap="nowrap"><?php echo JText::_('E_INVOICE_INFO')?></th>
							<td>&nbsp;</td>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td><?php echo JText::_('E_PAYMENT_METHOD')?></td>
							<td><?php echo $this->subscr->gateway?></td>
						</tr>
						<tr>
							<td><?php echo JText::_('E_PAYMENT_ID')?></td>
							<td><?php echo $this->subscr->gateway_id?></td>
						</tr>
						<tr>
							<td><?php echo JText::_('OFF_BILL_INST')?></td>
							<td><?php echo $this->plan->params->get('gateways.offline.bill_inst');?></td>
						</tr>
					</tbody>
				</table>
			</td>
		</tr>
	</table>

	<div class="row-fluid">
					<span class="order-item"><?php echo JText::_('E_ORDER_ITEM')?></span>
		<table class="table table-bordered table-items table-condensed">
			<thead>
				<tr class="gray">
					<th>#</th>
					<th><?php echo JText::_('EDESCR')?></th>
					<th><?php echo JText::_('X_PRICE')?></th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td>1</td>
					<td><h4><?php echo $this->plan->name;?></h4></td>
					<td class="align-right">
						<big>
						<?php
						$price = $this->subscr->price;

						if ($this->tax)
						{
							$price = $this->subscr->price / (100 + $this->tax->tax) * 100;
						}

						echo JoomsubscriptionApi::getPrice($this->coupon ? $this->coupon->discount + $price : $price , $this->plan->params);
						?>
						</big>
					</td>
				</tr>
			</tbody>
		</table>

		<table class=" table table-bordered pull-right" style="width: 25%;">
			<tbody>
				<tr>
					<td class="align-right bold"><?php echo JText::_('EDISCOUNT')?>:</td>
					<td class="align-right">- <?php echo JoomsubscriptionApi::getPrice($this->coupon ? $this->coupon->discount : 0, $this->plan->params)?></td>
				</tr>
				<tr class="success">
					<td class="align-right bold"><?php echo JText::_('E_SUBTOTAL')?>:</td>
					<td class="align-right bold"><?php echo JoomsubscriptionApi::getPrice($price, $this->plan->params)?></td>
				</tr>
				<?php $tax = 0; if($this->tax):?>
				<tr>
					<td class="align-right bold"><?php echo JText::_($this->tax->tax_name)?> <?php echo $this->tax->tax?>%:</td>
					<td class="align-right">
						<?php $tax = $price * ($this->tax->tax/100);?>
						<?php echo JoomsubscriptionApi::getPrice($tax, $this->plan->params)?>
					</td>
				</tr>
				<?php endif;?>
				<?php $price += $tax; ?>
				<tr>
					<td class="align-right bold"><big><?php echo JText::_('EMR_INVOICETOTAL')?>:</big></td>
					<td class="align-right bold"><big><?php echo JoomsubscriptionApi::getPrice($price, $this->plan->params)?></big></td>
				</tr>
			</tbody>
		</table>

	</div>


</div>
