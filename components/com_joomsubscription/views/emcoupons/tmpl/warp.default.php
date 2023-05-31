<?php
/**
 * JoomSubscription by JoomCoder
 * a component for Joomla! 3.0 CMS (http://www.joomla.org)
 * Author Website: https://www.joomcoder.com/
 * @copyright Copyright (C) 2012 JoomCoder (https://www.joomcoder.com/). All rights reserved.
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die('Restricted access');

JHtml::_('formbehavior.chosen', 'select');
$params = $this->state->get('params');

$user = JFactory::getUser();
$userId = $user->get('id');

$listOrder = $this->state->get('list.ordering');
$listDirn = $this->state->get('list.direction');
?>
<script type="text/javascript">
	Joomla.orderTable = function()
	{
		table = document.getElementById("sortTable");
		direction = document.getElementById("directionTable");
		order = table.options[table.selectedIndex].value;
		if (order != '<?php echo $listOrder; ?>')
		{
			dirn = 'asc';
		}
		else
		{
			dirn = direction.options[direction.selectedIndex].value;
		}
		Joomla.tableOrdering(order, dirn, '');
	}
</script>
<style type="text/css">
	.page-header .input-append {
		margin-top: 10px;
	}
	.arrow {
		margin-left: 8px;
	}
</style>
<form action="<?php echo JRoute::_('index.php?option=com_joomsubscription&view=emcoupons');?>" method="post" name="adminForm" id="adminForm" class="uk-form">
<?php echo $this->menu->render(null); ?>
	<div class="page-header">
		<div class="input-append uk-float-right">
			<input type="text" placeholder="<?php echo JText::_('CFILTER_SEARCH_COUPONDESC'); ?>" style="margin-left: 5px;" name="filter_search" id="filter_search" value="<?php echo $this->state->get('filter.search'); ?>" />
            <div class="uk-button-group">
			<button class="uk-button" type="submit" data-uk-tooltip title="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>"><i class="uk-icon-search"></i></button>
			<button class="uk-button" type="button" onclick="document.getElementById('filter_search').value='';this.form.submit();" data-uk-tooltip title="<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>"><i class="uk-icon-remove"></i></button>
            </div>
		</div>
		<h1>
			<img src="<?php echo JUri::root(TRUE); ?>/components/com_joomsubscription/images/cpanel/coupons.png" />
			<?php echo JText::_('COM_JOOMSUBSCRIPTION_COUPONS'); ?>
		</h1>
	</div>
	<hr />    
	<div id="j-main-container">
		<?php echo $this->buttons->render(null); ?>
		<div class="uk-clearfix"></div>
		<table class="uk-table uk-table-striped" id="groupsList">
			<thead>
				<tr>
					<th width="1%"><input type="checkbox" name="checkall-toggle" value="" onclick="Joomla.checkAll(this)" /></th>
					<th class="title" class="nowrap center">
						<?php echo str_replace('class="hasTooltip"', 'data-uk-tooltip', JHtml::_('grid.sort',  'X_VALUE', 'c.value', $listDirn, $listOrder)); ?>
					</th>
					<th width="1%" class="nowrap center">
						<?php echo str_replace('class="hasTooltip"', 'data-uk-tooltip', JHtml::_('grid.sort',  'EDISCOUNT', 'c.discount', $listDirn, $listOrder)); ?>
					</th>
					<th width="1%" class="nowrap center">
						<?php echo str_replace('class="hasTooltip"', 'data-uk-tooltip', JHtml::_('grid.sort',  'E_USE_LIMIT', 'c.use_num', $listDirn, $listOrder)); ?>
					</th>
					<th width="1%" class="nowrap center">
						<?php echo str_replace('class="hasTooltip"', 'data-uk-tooltip', JHtml::_('grid.sort',  'E_USED', 'c.used_num', $listDirn, $listOrder)); ?>
					</th>
					<th width="10%" class="nowrap center">
						<?php echo str_replace('class="hasTooltip"', 'data-uk-tooltip', JHtml::_('grid.sort',  'E_START', 'c.ctime', $listDirn, $listOrder)); ?>
					</th>
					<th width="10%" class="nowrap center">
						<?php echo str_replace('class="hasTooltip"', 'data-uk-tooltip', JHtml::_('grid.sort',  'E_FINISH', 'c.extime', $listDirn, $listOrder)); ?>
					</th>
					<th width="1%" class="nowrap">
						<?php echo str_replace('class="hasTooltip"', 'data-uk-tooltip', JHtml::_('grid.sort',  'JSTATUS', 'c.published', $listDirn, $listOrder)); ?>
					</th>
					<th width="1%" class="nowrap">
						<?php echo str_replace('class="hasTooltip"', 'data-uk-tooltip', JHtml::_('grid.sort',  'ID', 'c.id', $listDirn, $listOrder)); ?>
					</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td colspan="9">
						<div class="uk-float-right">
							<?php echo str_replace('<option value="0">'.JText::_('JALL').'</option>', '', $this->pagination->getLimitBox());?>
						</div>
						<div style="uk-float-left">
							<small>
								<?php if($this->pagination->getPagesCounter()):?>
									<?php echo $this->pagination->getPagesCounter(); ?> |
								<?php endif;?>
								<?php echo $this->pagination->getResultsCounter(); ?>
							</small>
						</div>
						<?php if($this->pagination->getPagesLinks()): ?>
							<div style="text-align: center;" class="pagination">
								<?php echo str_replace('<ul>', '<ul class="uk-pagination">', $this->pagination->getPagesLinks()); ?>
							</div>
							<div class="clearfix"></div>
						<?php endif; ?>
					</td>
				</tr>
			</tfoot>
			<tbody>
			<?php

			foreach($this->items as $i => $item):
				$canCheckin = $user->authorise('core.manage',     'com_checkin') || $item->checked_out == $userId || $item->checked_out == 0;
				$canChange  = $user->authorise('core.edit.state', 'com_joomsubscription.coupon.'.$item->id) && $canCheckin;

				$icon = $item->discount_type == 'PROCENT' ? 'procent' : 'sum';
				?>
				<tr class="row<?php echo $i % 2; ?>">
					<td class="center">
						<?php echo JHtml::_('grid.id', $i, $item->id); ?>
					</td>
					<td nowrap="nowrap">
						 <div class="uk-float-left">
							<?php if ($item->checked_out) : ?>
								<?php echo JHtml::_('jgrid.checkedout', $i, $item->checked_out, $item->checked_out_time, 'emcoupons.', $canCheckin); ?>
							<?php endif; ?>

							<a href="<?php echo JRoute::_('index.php?option=com_joomsubscription&task=emcoupon.edit&id='.(int) $item->id);?>">
								<?php echo $this->escape($item->value); ?>
							</a>
						</div>
					</td>
					<td class="nowrap center">
						<?php echo $item->discount ?> <img src="<?php echo JUri::root(true);?>/components/com_joomsubscription/images/<?php echo $icon; ?>.png" align="absmiddle" alt="">
					</td>
					<td class="nowrap">
						<?php echo $item->use_num == 0 ? JText::_('No Limit'): $item->use_num;?>
					</td>
					<td class="nowrap">
						<?php if ($item->use_num && $item->used_num >= $item->use_num) :?>
							<span class="icon-warning"></span>&nbsp;
							<span style="color:red;"><?php echo $item->used_num;?></span>
						<?php else:?>
							<?php echo $item->used_num;?>
						<?php endif;?>
					</td>
					<td class="nowrap center">
						<?php echo JoomsubscriptionHelper::getFormattedDate($item->ctime); ?>
					</td>
					<td class="nowrap center">
						<?php if($item->extime > 0):?>
							<?php $date = new JDate($item->extime);?>
							<?php if($item->expire == 1):?>
								<span style="color:red;">
									<?php echo JoomsubscriptionHelper::getFormattedDate($item->extime); ?>
								</span>
							<?php else:?>
									<?php echo JoomsubscriptionHelper::getFormattedDate($item->extime); ?>
							<?php endif;?>
						<?php else:?>
							<?php echo JText::_('E_NEVER');?>
						<?php endif;?>
					</td>
					<td class="center">
						<?php echo str_replace('hasTooltip"', '" data-uk-tooltip', JHtml::_('jgrid.published', $item->published, $i, 'emcoupons.',  $canChange ));?>
					</td>
					<td class="center">
						<?php echo (int) $item->id; ?>
					</td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>

		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
