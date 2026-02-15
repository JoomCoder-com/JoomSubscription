<?php
/**
 * JoomSubscription by JoomCoder
 * a component for Joomla! 3.0 CMS (http://www.joomla.org)
 * Author Website: https://www.joomcoder.com/
 * @copyright Copyright (C) 2012 JoomCoder (https://www.joomcoder.com/). All rights reserved.
 * @license   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

use Joomla\CMS\Layout\LayoutHelper;

defined('_JEXEC') or die('Restricted access');

Joomla\CMS\HTML\HTMLHelper::_('formbehavior.chosen', 'select');

$user      = \Joomla\CMS\Factory::getUser();
$userId    = $user->get('id');
$listOrder = $this->state->get('list.ordering');
$listDirn  = $this->state->get('list.direction');
$canOrder  = $user->authorise('core.edit.state', 'com_joomsubscription.plans');
$saveOrder = $listOrder == 'p.ordering';

if ($saveOrder)
{
	$saveOrderingUrl = 'index.php?option=com_joomsubscription&task=emplans.ordersave&tmpl=component';
	Joomla\CMS\HTML\HTMLHelper::_('sortablelist.sortable', 'plansList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
}
?>
<script type="text/javascript">
    Joomla.orderTable = function () {
        table = document.getElementById("sortTable");
        direction = document.getElementById("directionTable");
        order = table.options[table.selectedIndex].value;
        if (order != '<?php echo $listOrder; ?>') {
            dirn = 'asc';
        } else {
            dirn = direction.options[direction.selectedIndex].value;
        }
        Joomla.tableOrdering(order, dirn, '');
    }
</script>

<?php echo $this->menu->render(null); ?>

<form
        action="<?php echo \Joomla\CMS\Router\Route::_('index.php?option=com_joomsubscription&view=emplans'); ?>"
        method="post"
        name="adminForm"
        id="adminForm"
>

	<?php echo LayoutHelper::render('core.common.pageHeader', ['title' => 'COM_JOOMSUBSCRIPTION_PLANS']); ?>

	<?php echo LayoutHelper::render('core.list.actionBar', ['filterName' => 'search', 'current' => $this]) ?>

    <div class="card my-3">

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped" id="plansList">
                    <thead>
                    <tr>
                        <th width="1%"><input type="checkbox" name="checkall-toggle" value=""
                                              onclick="Joomla.checkAll(this)"/></th>
                        <th width="1%" class="nowrap center hidden-phone">
							<?php echo Joomla\CMS\HTML\HTMLHelper::_('grid.sort', '<i class="icon-menu-2"></i>', 'p.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING'); ?>
                        </th>
                        <th class="title" class="nowrap center">
							<?php echo Joomla\CMS\HTML\HTMLHelper::_('grid.sort', 'ENAME', 'p.name', $listDirn, $listOrder); ?>
                        </th>
                        <th width="1%" class="nowrap center">
							<?php echo \Joomla\CMS\Language\Text::_('X_SUBSCR'); ?>
                        </th>
                        <th width="10%" class="nowrap center">
							<?php echo \Joomla\CMS\Language\Text::_('X_PRICE'); ?>
                        </th>
                        <th width="10%" class="nowrap center">
							<?php echo Joomla\CMS\HTML\HTMLHelper::_('grid.sort', 'EGROUP', 'group_name', $listDirn, $listOrder); ?>
                        </th>
                        <th width="1%" class="nowrap">
							<?php echo Joomla\CMS\HTML\HTMLHelper::_('grid.sort', 'JSTATUS', 'p.published', $listDirn, $listOrder); ?>
                        </th>
                        <th width="1%" class="nowrap">
							<?php echo Joomla\CMS\HTML\HTMLHelper::_('grid.sort', 'EACCESS', 'p.access', $listDirn, $listOrder); ?>
                        </th>
                        <th width="1%" class="nowrap">
							<?php echo Joomla\CMS\HTML\HTMLHelper::_('grid.sort', 'ID', 'p.id', $listDirn, $listOrder); ?>
                        </th>
                    </tr>
                    </thead>
                    <tfoot>
                    <tr>
                        <td colspan="9">
                            <div class="float-end">
								<?php echo str_replace('<option value="0">' . \Joomla\CMS\Language\Text::_('JALL') . '</option>', '', $this->pagination->getLimitBox()); ?>
                            </div>
                            <div style="pull-left">
                                <small>
									<?php if ($this->pagination->getPagesCounter()): ?>
										<?php echo $this->pagination->getPagesCounter(); ?> |
									<?php endif; ?>
									<?php echo $this->pagination->getResultsCounter(); ?>
                                </small>
                            </div>
							<?php if ($this->pagination->getPagesLinks()): ?>
                                <div style="text-align: center;" class="pagination">
									<?php echo str_replace('<ul>', '<ul class="pagination-list">', $this->pagination->getPagesLinks()); ?>
                                </div>
                                <div class="clearfix"></div>
							<?php endif; ?>
                        </td>
                    </tr>
                    </tfoot>
                    <tbody>
					<?php

					foreach ($this->items as $i => $item):
						$canCheckin = $user->authorise('core.manage', 'com_checkin') || $item->checked_out == $userId || $item->checked_out == 0;
						$canChange = $user->authorise('core.edit.state', 'com_joomsubscription.plan.' . $item->id) && $canCheckin;
						$item->params = new \Joomla\Registry\Registry($item->params);
						?>
                        <tr class="row<?php echo $i % 2; ?>" sortable-group-id="<?php echo $item->group_id; ?>">
                            <td class="center">
								<?php echo Joomla\CMS\HTML\HTMLHelper::_('grid.id', $i, $item->id); ?>
                            </td>
                            <td class="order nowrap center hidden-phone">
								<?php if ($canChange) :
									$disableClassName = '';
									$disabledLabel = '';

									if (!$saveOrder) :
										$disabledLabel    = \Joomla\CMS\Language\Text::_('JORDERINGDISABLED');
										$disableClassName = 'inactive tip-top';
									endif; ?>
                                    <span class="sortable-handler <?php echo $disableClassName ?>"
                                          title="<?php echo $disabledLabel ?>" rel="tooltip">
								<i class="icon-menu"></i>
							</span>
                                    <input type="text" style="display:none" name="order[]" size="5"
                                           value="<?php echo $item->ordering; ?>" class="width-20 text-area-order "/>
								<?php else : ?>
                                    <span class="sortable-handler inactive">
								<i class="icon-menu"></i>
							</span>
								<?php endif; ?>
                            </td>
                            <td nowrap="nowrap">
                                <div class="pull-left">
									<?php if ($item->checked_out) : ?>
										<?php echo Joomla\CMS\HTML\HTMLHelper::_('jgrid.checkedout', $i, $item->checked_out, $item->checked_out_time, 'emplans.', $canCheckin); ?>
									<?php endif; ?>

                                    <a href="<?php echo \Joomla\CMS\Router\Route::_('index.php?option=com_joomsubscription&task=emplan.edit&id=' . (int) $item->id); ?>">
										<?php echo $this->escape(\Joomla\CMS\Language\Text::_($item->name)); ?>
                                    </a>
                                    <small class="ms-2 border rounded shadow-sm px-2 bg-white">
										<?php
										if ($item->params->get('properties.date_fixed'))
										{
											echo \Joomla\CMS\Language\Text::_('XML_OPT_PERIOD' . $item->params->get('properties.date_fixed'));

										}
                                        elseif ($item->params->get('properties.date_from') && $item->params->get('properties.date_to'))
										{
											echo $item->params->get('properties.date_from') . ' - ' . $item->params->get('properties.date_to');
										}
										else
										{
											if ($item->params->get('properties.days') >= 100 && $item->params->get('properties.days_type') == 'years')
											{
												echo \Joomla\CMS\Language\Text::_('XML_OPT_PERIOD1');
											}
											else
											{
												echo $item->params->get('properties.days') . ' ' . \Joomla\CMS\Language\Text::plural($item->params->get('properties.days_type'), $item->params->get('properties.days'));
											}
										} ?>
                                    </small>
                                </div>
                            </td>
                            <td class="nowrap center">
                                <div class="badge<?php echo($item->subscr ? ' bg-success' : ' bg-warning'); ?>">
									<?php echo $item->subscr; ?>
                                </div>
                            </td>
                            <td class="nowrap center">
								<?php echo JoomsubscriptionApi::getPrice($item->params->get('properties.price'), $item->params); ?>
                            </td>
                            <td class="nowrap center">
								<?php echo \Joomla\CMS\Language\Text::_($item->group_name); ?>
                            </td>
                            <td nowrap="nowrap" align="center">
								<?php echo Joomla\CMS\HTML\HTMLHelper::_('jgrid.published', $item->published, $i, 'emplans.', $canChange); ?>
                            </td>
                            <td class="center">
                                <small><?php echo $this->escape($item->access_level); ?></small>
                            </td>
                            <td class="center">
								<?php echo (int) $item->id; ?>
                            </td>
                        </tr>
					<?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <input type="hidden" name="task" value=""/>
    <input type="hidden" name="boxchecked" value="0"/>
    <input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>"/>
    <input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>"/>
	<?php echo Joomla\CMS\HTML\HTMLHelper::_('form.token'); ?>

</form>