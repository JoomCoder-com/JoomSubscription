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

$listOrder = $this->state->get('list.ordering');
$listDirn = $this->state->get('list.direction');
?>
<?php echo $this->menu->render(null); ?>

<form
        action="<?php echo JRoute::_('index.php?option=com_joomsubscription&view=emstates');?>"
        method="post"
        name="adminForm"
        id="adminForm"
>

	<?php echo LayoutHelper::render('core.common.pageHeader', ['title' => 'ESTATES']); ?>

	<?php echo LayoutHelper::render('core.list.actionBar', ['filterName' => 'search', 'current' => $this]) ?>

	<div class="card my-3">
        <div class="card-body">
	        <span class="float-start">
		        <?php echo JHtml::_('select.genericlist', $this->countries, 'filter_country', 'onchange="this.form.submit();"', 'id', 'name', $this->state->get('filter.country'))?>
            </span>
        </div>
    </div>

	<div class="card my-3">
        <div class="card-body">
            <table class="table table-striped" id="groupsList">
                <thead>
                <tr>
                    <th width="1%"><input type="checkbox" name="checkall-toggle" value="" onclick="Joomla.checkAll(this)" /></th>
                    <th width="1%" class="title" class="nowrap">
				        <?php echo JText::_('ECOUNTRY'); ?>
                    </th>
                    <th width="1%" class="nowrap">
				        <?php echo JHtml::_('grid.sort',  'ESTATE', 'st.state', $listDirn, $listOrder); ?>
                    </th>
                    <th class="nowrap">
				        <?php echo JHtml::_('grid.sort',  'ELABEL', 'st.label', $listDirn, $listOrder); ?>
                    </th>
                    <th width="1%" class="nowrap">
				        <?php echo JHtml::_('grid.sort',  'ID', 'st.id', $listDirn, $listOrder); ?>
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
			        ?>
                    <tr class="row<?php echo $i % 2; ?>">
                        <td class="center">
					        <?php echo JHtml::_('grid.id', $i, $item->id); ?>
                        </td>
                        <td class="center">
                            <small class="border rounded px-2 shadow-sm bg-white"><?php echo $this->countries[$item->country]['name']; ?></small>
                        </td>
                        <td nowrap="nowrap">
					        <small class="border rounded px-2 shadow-sm bg-white"><?php echo $item->state; ?></small>
                        </td>
                        <td nowrap="nowrap">
                            <div>
                                <a href="<?php echo JRoute::_('index.php?option=com_joomsubscription&task=emstate.edit&id='.(int) $item->id);?>">
							        <?php echo $item->label; ?>
                                </a>
                            </div>
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
    </div>

</form>
