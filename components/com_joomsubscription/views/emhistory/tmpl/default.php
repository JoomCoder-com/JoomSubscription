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
<div class="page-header">
<div class="input-append float-end">
	<a class="btn" href="<?php echo JoomsubscriptionApi::getLink('emlist'); ?>">
		<img src="<?php echo \Joomla\CMS\Uri\Uri::root(TRUE); ?>/components/com_joomsubscription/images/cpanel/purchase-small.png" />
        <?php echo \Joomla\CMS\Language\Text::_('EPURCHASENEW'); ?>
	</a>
</div>
	<h1>
        <img src="<?php echo \Joomla\CMS\Uri\Uri::root(TRUE); ?>/components/com_joomsubscription/images/cpanel/history.png" />
        <?php echo $this->mparams->get('data.page_title'); ?>
    </h1>
</div>

<?php if(!$this->user->id): ?>
	<div class="alert">
		<?php echo \Joomla\CMS\Language\Text::_('EM_LOGINFORHISTORY'); ?>
	</div>
<?php endif; ?>

<form action="<?php echo \Joomla\CMS\Uri\Uri::getInstance()->toString(); ?>" method="post" name="historyForm" id="historyForm">
	<?php foreach($this->items AS $catname => $items): ?>
		<?php if(count($this->items) > 1): ?>
			<h3><?php echo \Joomla\CMS\Language\Text::_($catname); ?></h3>
		<?php endif; ?>
		<table class="table table-striped">
			<thead>
			<tr>
				<th><?php echo \Joomla\CMS\Language\Text::_('E_SUBSCRIPTION') ?></th>
				<th nowrap width="1%"><?php echo \Joomla\CMS\Language\Text::_('EDAYSLEFT') ?></th>
				<th nowrap width="1%"><?php echo \Joomla\CMS\Language\Text::_('ELIMIT') ?></th>
				<th nowrap width="1%"><?php echo \Joomla\CMS\Language\Text::_('EUSED') ?></th>
				<th width="1%"><?php echo \Joomla\CMS\Language\Text::_('X_PRICE') ?></th>
			</tr>
			</thead>
			<tbody>
			<?php foreach($items AS $item): ?>
				<?php
				$b = array();
				if(!empty($item->paypal_email))
				{
					$b[] = array(\Joomla\CMS\Language\Text::_("ECANCELSUBSCRIPTION"), 'https://www.paypal.com/cgi-bin/webscr?cmd=_subscr-find&alias=' . urlencode($item->paypal_email));
				}
				if($item->muaccess && !$item->parent)
				{
					$b[] = array(\Joomla\CMS\Language\Text::_("E_MUA_SHARE"), \Joomla\CMS\Router\Route::_('index.php?option=com_joomsubscription&view=emmua&subscr_id=' . $item->sid));
				}
				if($item->gateway == 'offline' && $item->plan_params->get('gateways.offline.billing', FALSE) && !$item->activated)
				{
					$b[] = array(\Joomla\CMS\Language\Text::_("E_BILL"), \Joomla\CMS\Router\Route::_('index.php?option=com_joomsubscription&view=embill&tmpl=component&id=' . $item->sid), 'target="_blank"');
				}
				if($this->params->get('use_invoice', 0) && $item->activated && $item->invoice_num && (float)$item->price)
				{
					$b[] = array(\Joomla\CMS\Language\Text::_("E_GET_INVOICE"), \Joomla\CMS\Router\Route::_('index.php?option=com_joomsubscription&view=eminvoice&tmpl=component&id=' . $item->sid), 'target="_blank"');
				}
				?>
				<tr>
					<td>
						<?php if(!empty($b)): ?>
							<div class="float-end">
								<div class="btn-group">
									<a href="#" data-toggle="dropdown" class="dropdown-toggle btn btn-mini">
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

						<div class="float-end">
							<?php
							Joomla\CMS\HTML\HTMLHelper::_('dropdown.edit', $item->sid, 'type.');
							echo Joomla\CMS\HTML\HTMLHelper::_('dropdown.render');
							?>
						</div>
						<div>
							<img src="<?php echo \Joomla\CMS\Uri\Uri::root(TRUE) ?>/components/com_joomsubscription/images/<?php echo $item->img; ?>" title="<?php echo \Joomla\CMS\Language\Text::_($item->state); ?>">
							<?php echo \Joomla\CMS\Language\Text::_($item->name); ?>
							<small>[<?php echo \Joomla\CMS\Language\Text::_($item->group); ?>]</small>
						</div>
						<?php if($item->note): ?>
							<p>
								<?php echo $item->note; ?>
							</p>
						<?php endif; ?>
						<?php if($item->activated == 0): ?>
							<div class="alert alert-warning mt-2">
								<?php echo \Joomla\CMS\Language\Text::_('EMR_SUBSCR_INACTIVE'); ?>
							</div>
							<a href="<?php echo \Joomla\CMS\Router\Route::_('index.php?option=com_joomsubscription&view=empayment&sid=' . $item->id) ?>" class="btn btn-outline-primary">
								<?php echo \Joomla\CMS\Language\Text::_('E_PPAYAGAIN'); ?>
							</a>
								<button type="button" class="btn btn-cancel-order btn-outline-danger" data-subscr-id="<?php echo $item->sid ?>">
									<?php echo \Joomla\CMS\Language\Text::_('ECANCEL'); ?>
								</button>
						<?php else: ?>
							<small>
								<?php echo \Joomla\CMS\Language\Text::_('ID') ?>: <b><?php echo $item->sid; ?></b> |
								<?php echo \Joomla\CMS\Language\Text::_('EORDERID') ?>: <b><?php echo $item->gateway_id; ?></b> |
								<?php echo \Joomla\CMS\Language\Text::_('ESTARTON') ?>: <b><?php echo Joomla\CMS\HTML\HTMLHelper::_('date', $item->ctime, \Joomla\CMS\Language\Text::_('DATE_FORMAT_LC3')); ?></b> |
								<?php echo \Joomla\CMS\Language\Text::_('EENDON') ?>: <b class="<?php echo @$class; ?>"><?php Joomla\CMS\HTML\HTMLHelper::_('date', $item->extime, $this->params->get('date_format')); ?>
									<?php echo ($item->days_enable >= 36500 || $item->extime == '0000-00-00 00:00:00') ? \Joomla\CMS\Language\Text::_('E_NEVER') : Joomla\CMS\HTML\HTMLHelper::_('date', $item->extime, $this->params->get('date_format')); ?></b>

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
							<?php echo Joomla\CMS\HTML\HTMLHelper::_('content.prepare', Mint::_($item->plan_params->get('descriptions.description_history'))); ?>
						<?php endif; ?>
					</td>
					<td>
						<?php if($item->days_enable >= 36500 || $item->extime == '0000-00-00 00:00:00'): ?>
							<div class="label label-success">
								<?php echo \Joomla\CMS\Language\Text::_("ELIFETIME"); ?>
							</div>
						<?php else: ?>
							<?php echo ($item->days > 0) ? '<span class="badge badge-success">' . $item->days . '</span>' : '<span class="badge badge-important">0</span>'; ?>
						<?php endif; ?>
					</td>
					<td nowrap>
						<?php echo ($item->access_limit > 0) ? '<span class="badge badge-warning">' . $item->access_limit . '</span>' : '<span class="label label-success">' . \Joomla\CMS\Language\Text::_('ENOLIMITS') . '</span>'; ?>
					</td>
					<td>
						<span class="badge <?php echo (($item->access_limit > 0) && ($item->access_count >= $item->access_limit)) ? 'bg-dark' : 'bg-success'; ?>">
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
		<div class="float-end">
			<?php echo str_replace('<option value="0">' . \Joomla\CMS\Language\Text::_('JALL') . '</option>', '', $this->pagination->getLimitBox()); ?>
		</div>
		<div style="pull-left">
			<small>
				<?php if($this->pagination->getPagesCounter()): ?>
					<?php echo $this->pagination->getPagesCounter(); ?> |
				<?php endif; ?>
				<?php echo $this->pagination->getResultsCounter(); ?>
			</small>
		</div>
		<?php if($this->pagination->getPagesLinks()): ?>
			<div style="text-align: center;" class="pagination">
				<?php echo str_replace('<ul>', '<ul class="pagination-list">', $this->pagination->getPagesLinks()); ?>
			</div>
			<div class="clearfix"></div>
		<?php endif; ?>
	</div>
	<div class="clearfix"></div>
	<input type="hidden" name="task" value=""/>
	<input type="hidden" name="return" value="<?php echo base64_encode(\Joomla\CMS\Uri\Uri::getInstance()->toString()); ?>"/>
	<input type="hidden" name="id" value="" id="sub_id"/>
	<?php echo Joomla\CMS\HTML\HTMLHelper::_('form.token'); ?>
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