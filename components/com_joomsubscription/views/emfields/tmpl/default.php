<?php
/**
 * JoomSubscription by JoomCoder
 * a component for Joomla! 3.0 CMS (http://www.joomla.org)
 * Author Website: https://www.joomcoder.com/
 * @copyright Copyright (C) 2012 JoomCoder (https://www.joomcoder.com/). All rights reserved.
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

use Joomla\CMS\Layout\LayoutHelper;

defined('_JEXEC') or die('Restricted access');

JHtml::_('formbehavior.chosen', 'select');

$user = JFactory::getUser();
$userId = $user->get('id');

$listOrder = $this->state->get('list.ordering');
$listDirn = $this->state->get('list.direction');
$canOrder = $user->authorise('core.edit.state', 'com_joomsubscription.fields');
$saveOrder = $listOrder == 'f.ordering';

if ($saveOrder)
{
	$saveOrderingUrl = 'index.php?option=com_joomsubscription&task=emfields.ordersave&tmpl=component';
	JHtml::_('sortablelist.sortable', 'fieldsList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
}
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

<?php echo $this->menu->render(null); ?>

<form
        action="<?php echo JRoute::_('index.php?option=com_joomsubscription&view=emfields');?>"
        method="post"
        name="adminForm"
        id="adminForm"
>

	<?php echo LayoutHelper::render('core.common.pageHeader', ['title' => 'COM_JOOMSUBSCRIPTION_FIELDS']); ?>

	<?php echo LayoutHelper::render('core.list.actionBar', ['filterName' => 'search', 'current' => $this]) ?>

    <div class="card my-3">
        <div class="card-body">
            <table class="table table-striped" id="fieldsList">
                <thead>
                <tr>
                    <th width="1%"><input type="checkbox" name="checkall-toggle" value="" onclick="Joomla.checkAll(this)" /></th>
                    <th width="1%" class="nowrap center hidden-phone">
				        <?php echo JHtml::_('grid.sort', '<i class="icon-menu-2"></i>', 'f.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING'); ?>
                    </th>
                    <th width="1%" class="nowrap">
				        <?php echo JHtml::_('grid.sort',  'EFIELDTYPE', 'f.type', $listDirn, $listOrder); ?>
                    </th>
                    <th class="title" class="nowrap center">
				        <?php echo JHtml::_('grid.sort',  'ENAME', 'f.name', $listDirn, $listOrder); ?>
                    </th>
                    <th width="1%" class="nowrap">
				        <?php echo JHtml::_('grid.sort',  'JSTATUS', 'f.published', $listDirn, $listOrder); ?>
                    </th>
                    <th width="1%" class="nowrap">
				        <?php echo JHtml::_('grid.sort',  'EACCESS', 'f.access', $listDirn, $listOrder); ?>
                    </th>
                    <th width="1%" class="nowrap">
				        <?php echo JHtml::_('grid.sort',  'ID', 'f.id', $listDirn, $listOrder); ?>
                    </th>
                </tr>
                </thead>
                <tfoot>
                <tr>
                    <td colspan="8">
                        <div class="pull-right">
					        <?php echo str_replace('<option value="0">'.JText::_('JALL').'</option>', '', $this->pagination->getLimitBox());?>
                        </div>
                        <div style="pull-left">
                            <small>
						        <?php if($this->pagination->getPagesCounter()):?>
							        <?php echo $this->pagination->getPagesCounter(); ?> |
						        <?php endif;?>
						        <?php echo $this->pagination->getResultsCounter(); ?>
                            </small>
                        </div>
				        <?php if($this->pagination->getPagesLinks()): ?>
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
		        foreach($this->items as $i => $item):
			        $canCheckin = $user->authorise('core.manage',     'com_checkin') || $item->checked_out == $userId || $item->checked_out == 0;
			        $canChange  = true;
			        ?>
                    <tr class="row<?php echo $i % 2; ?>">
                        <td class="center">
					        <?php echo JHtml::_('grid.id', $i, $item->id); ?>
                        </td>
                        <td class="center">
					        <?php echo $item->type; ?>
                        </td>
                        <td class="order nowrap center hidden-phone">
					        <?php if ($canChange) :
						        $disableClassName = '';
						        $disabledLabel	  = '';

						        if (!$saveOrder) :
							        $disabledLabel    = JText::_('JORDERINGDISABLED');
							        $disableClassName = 'inactive tip-top';
						        endif; ?>
                                <span class="sortable-handler <?php echo $disableClassName?>" title="<?php echo $disabledLabel?>" rel="tooltip">
								<i class="icon-menu"></i>
							</span>
                                <input type="text" style="display:none"  name="order[]" size="5" value="<?php echo $item->ordering;?>" class="width-20 text-area-order " />
					        <?php else : ?>
                                <span class="sortable-handler inactive" >
								<i class="icon-menu"></i>
							</span>
					        <?php endif; ?>
                        </td>
                        <td nowrap="nowrap">
                            <div class="pull-left">
						        <?php if ($item->checked_out) : ?>
							        <?php echo JHtml::_('jgrid.checkedout', $i, $item->checked_out, $item->checked_out_time, 'emfields.', $canCheckin); ?>
						        <?php endif; ?>

                                <a href="<?php echo JRoute::_('index.php?option=com_joomsubscription&task=emfield.edit&id='.(int) $item->id);?>">
							        <?php echo $this->escape(JText::_($item->name)); ?>
                                </a>
                            </div>
                        </td>
                        <td nowrap="nowrap" align="center">
					        <?php echo JHtml::_('jgrid.published', $item->published, $i, 'emfields.',  $canChange );?>
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

    <input type="hidden" name="task" value="" />
    <input type="hidden" name="boxchecked" value="0" />
    <input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
    <input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
	<?php echo JHtml::_('form.token'); ?>

</form>
