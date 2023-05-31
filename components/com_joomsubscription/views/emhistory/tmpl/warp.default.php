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
echo $this->menu->render(NULL);
?>
<style>
	.page-header .input-append {
		margin-top: 10px;
	}
</style>
<div class="page-header">
<div class="input-append uk-float-right">
	<a class="uk-button uk-button-large" href="<?php echo JoomsubscriptionApi::getLink('emlist'); ?>">
		<img src="<?php echo JUri::root(TRUE); ?>/components/com_joomsubscription/images/cpanel/purchase-small.png" />
        <?php echo JText::_('EPURCHASENEW'); ?>
	</a>
</div>
	<h1>
        <img src="<?php echo JUri::root(TRUE); ?>/components/com_joomsubscription/images/cpanel/history.png" />
        <?php echo $this->mparams->get('data.page_title'); ?>
    </h1>
</div>
<hr />

<?php if(!$this->user->id): ?>
	<div class="uk-alert">
		<?php echo JText::_('EM_LOGINFORHISTORY'); ?>
	</div>
<?php endif; ?>

<form action="<?php echo JUri::getInstance()->toString(); ?>" method="post" name="historyForm" id="historyForm" class="uk-form">
	<?php foreach($this->items AS $catname => $items): ?>
		<?php if(count($this->items) > 1): ?>
			<h3><?php echo JText::_($catname); ?></h3>
		<?php endif; ?>
		<table class="uk-table uk-table-striped">
			<thead>
			<tr>
				<th><?php echo JText::_('E_SUBSCRIPTION') ?></th>
				<th nowrap width="1%"><?php echo JText::_('EDAYSLEFT') ?></th>
				<th nowrap width="1%"><?php echo JText::_('ELIMIT') ?></th>
				<th nowrap width="1%"><?php echo JText::_('EUSED') ?></th>
				<th width="1%"><?php echo JText::_('X_PRICE') ?></th>
			</tr>
			</thead>
			<tbody>
			<?php foreach($items AS $item): ?>
				<?php
				$b = array();
				if(!empty($item->paypal_email))
				{
					$b[] = array(JText::_("ECANCELSUBSCRIPTION"), 'https://www.paypal.com/cgi-bin/webscr?cmd=_subscr-find&alias=' . urlencode($item->paypal_email));
				}
				if($item->muaccess && !$item->parent)
				{
					$b[] = array(JText::_("E_MUA_SHARE"), JRoute::_('index.php?option=com_joomsubscription&view=emmua&subscr_id=' . $item->sid));
				}
				if($item->gateway == 'offline' && $item->plan_params->get('gateways.offline.billing', FALSE) && !$item->activated)
				{
					$b[] = array(JText::_("E_BILL"), JRoute::_('index.php?option=com_joomsubscription&view=embill&tmpl=component&id=' . $item->sid), 'target="_blank"');
				}
				if($this->params->get('use_invoice', 0) && $item->activated && $item->invoice_num && (float)$item->price)
				{
					$b[] = array(JText::_("E_GET_INVOICE"), JRoute::_('index.php?option=com_joomsubscription&view=eminvoice&tmpl=component&id=' . $item->sid), 'target="_blank"');
				}
				?>
				<tr>
					<td>
						<?php if(!empty($b)): ?>
							<div class="uk-float-right">
								<div class="uk-button-group">
									<a href="#" data-toggle="dropdown" class="dropdown-toggle uk-button uk-button-mini">
										<span class="caret"></span>
									</a>
									<ul class="dropdown-menu">
										<?php foreach($b as $link): ?>
											<li class="">
												<a href="<?php echo $link[1] ?>" <?php echo @$link[2] ?>><?php echo $link[0] ?></a>
											</li>
										<?php endforeach ?>
									</ul>
								</div>
							</div>
						<?php endif; ?>

						<div class="uk-float-right">
							<?php
							JHtml::_('dropdown.edit', $item->sid, 'type.');
							echo JHtml::_('dropdown.render');
							?>
						</div>
						<div>
							<img src="<?php echo JURI::root(TRUE) ?>/components/com_joomsubscription/images/<?php echo $item->img; ?>" title="<?php echo JText::_($item->state); ?>" />
							<?php echo JText::_($item->name); ?>
							<small>[<?php echo JText::_($item->group); ?>]</small>
						</div>
						<?php if($item->note): ?>
							<p>
								<?php echo $item->note; ?>
							</p>
						<?php endif; ?>
						<?php if($item->activated == 0): ?>
							<div class="uk-alert uk-alert-warning">
								<?php echo JText::_('EMR_SUBSCR_INACTIVE'); ?>
							</div>
							<a href="<?php echo JRoute::_('index.php?option=com_joomsubscription&view=empayment&sid=' . $item->id) ?>" class="uk-button-small uk-button-primary ">
								<?php echo JText::_('E_PPAYAGAIN'); ?>
							</a>
							<button type="button" class="btn-small btn-cancel-order btn-primary" data-subscr-id="<?php echo $item->sid ?>">
								<?php echo JText::_('ECANCEL'); ?>
							</button>
						<?php else: ?>
							<small>
								<?php echo JText::_('ID') ?>: <b><?php echo $item->sid; ?></b> |
								<?php echo JText::_('EORDERID') ?>: <b><?php echo $item->gateway_id; ?></b> |
								<?php echo JText::_('ESTARTON') ?>: <b><?php echo JHtml::_('date', $item->ctime, JText::_('DATE_FORMAT_LC3')); ?></b> |
								<?php echo JText::_('EENDON') ?>: <b class="<?php echo @$class; ?>"><?php JHtml::_('date', $item->extime, $this->params->get('date_format')); ?>
									<?php echo ($item->days_enable >= 36500 || $item->extime == '0000-00-00 00:00:00') ? JText::_('E_NEVER') : JHtml::_('date', $item->extime, $this->params->get('date_format')); ?></b>

								<!--<div>
								<?php /*if($coupon = $item->coupon_info): */ ?>
									<?php /*echo $coupon->value; */ ?>
									<br>
									<small><s><?php /*echo JoomsubscriptionApi::getPrice($coupon->discount, $item->plan_params, $item->params); */ ?></s></small>
								<?php /*endif; */ ?>
							</div>-->

							</small>
						<?php endif; ?>
						<div style="clear: both;"></div>
						<?php if($item->additions): ?>
							<table class="table-bordered">
								<?php foreach($item->additions AS $key => $val): ?>
									<tr>
										<td><?php echo $key ?></td>
										<td><?php echo $val ?></td>
									</tr>
								<?php endforeach; ?>
							</table>
						<?php endif; ?>
						<?php if($item->is_active && $item->plan_params->get('descriptions.description_history')): ?>
							<?php echo JHtml::_('content.prepare', Mint::_($item->plan_params->get('descriptions.description_history'))); ?>
						<?php endif; ?>
					</td>
					<td>
						<?php if($item->days_enable >= 36500 || $item->extime == '0000-00-00 00:00:00'): ?>
							<div class="label label-success">
								<?php echo JText::_("ELIFETIME"); ?>
							</div>
						<?php else: ?>
							<?php echo ($item->days > 0) ? '<span class="uk-badge uk-badge-success">' . $item->days . '</span>' : '<span class="uk-badge uk-badge-important">0</span>'; ?>
						<?php endif; ?>
					</td>
					<td nowrap>
						<?php echo ($item->access_limit > 0) ? '<span class="uk-badge uk-badge-warning">' . $item->access_limit . '</span>' : '<span class="label label-success">' . JText::_('ENOLIMITS') . '</span>'; ?>
					</td>
					<td>
						<span class="uk-badge <?php echo (($item->access_limit > 0) && ($item->access_count >= $item->access_limit)) ? 'uk-badge-important' : 'uk-badge-success'; ?>">
							<?php echo $item->access_count; ?></span>
					</td>
					<td align="right" nowrap>
						<?php echo JoomsubscriptionApi::getPrice($item->price, $item->plan_params, $item->params); ?>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	<?php endforeach; ?>
	<div>
		<div class="uk-float-right">
			<?php echo str_replace('<option value="0">' . JText::_('JALL') . '</option>', '', $this->pagination->getLimitBox()); ?>
		</div>
		<div style="uk-float-left">
			<small>
				<?php if($this->pagination->getPagesCounter()): ?>
					<?php echo $this->pagination->getPagesCounter(); ?> |
				<?php endif; ?>
				<?php echo $this->pagination->getResultsCounter(); ?>
			</small>
		</div>
		<?php if($this->pagination->getPagesLinks()): ?>
			<div style="text-align: center;" class="pagination">
				<?php echo str_replace('<ul>', '<ul class="uk-pagination">', $this->pagination->getPagesLinks()); ?>
			</div>
			<div class="uk-clearfix"></div>
		<?php endif; ?>
	</div>
	<div class="uk-clearfix"></div>
</form>
<script>
	(function($){
		$('.btn-cancel-order').each(function(k, v){
			var btn = $(v);
			var sid = btn.data('subscr-id');
			btn.click(function(){
				$('#sub_id').val(sid);
				Joomsubscription.submitform('emhistory.cancels', document.getElementById('historyForm'))
			});
			console.log(btn, sid);
		});
	}(jQuery))
</script>