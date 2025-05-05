<?php
/**
 * JoomSubscription by JoomCoder
 * a component for Joomla (http://www.joomla.org)
 * Author Website: https://www.joomcoder.com/
 * @copyright Copyright (C) 2012 JoomCoder (https://www.joomcoder.com/). All rights reserved.
 * @license   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

use Joomla\CMS\Layout\LayoutHelper;

defined('_JEXEC') or die();

JHtml::_('formbehavior.chosen', 'select');

$listOrder = $this->state->get('list.ordering');
$listDirn  = $this->state->get('list.direction');
$user      = JFactory::getUser();
$userId    = $user->get('id');
?>


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
        action="<?php echo JRoute::_('index.php?option=com_joomsubscription&view=emsales'); ?>"
        method="post"
        name="adminForm"
        id="adminForm"
>

	<?php echo LayoutHelper::render('core.common.pageHeader', ['title' => $this->mparams->get('data.page_title')]); ?>

	<?php echo LayoutHelper::render('core.list.actionBar', ['filterName' => 'search', 'current' => $this]) ?>

    <div class="card my-3">
        <div class="card-body">
            <div class="float-start me-2">
		        <?php echo JHtml::_('select.genericlist', $this->model->getGroups(true), 'filter_group', 'onchange="this.form.submit()"', 'value', 'text', $this->escape((string) $this->state->get('group_id')), false, true); ?>
            </div>

            <div class="float-start me-2">
		        <?php echo JHtml::_('select.genericlist', $this->model->getPLans(true), 'filter_plan', 'onchange="this.form.submit()"', 'value', 'text', $this->escape((string) $this->state->get('plan_id')), false, true); ?>
            </div>

            <div class="float-start">
		        <?php echo JHtml::_('select.genericlist', $this->model->getSt(), 'filter_state', 'onchange="this.form.submit()"', 'value', 'text', $this->escape((string) $this->state->get('state')), false, true); ?>
            </div>
        </div>
    </div>

    <div class="card my-3">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                        <tr>
                            <th width="1%" class="center">
                                <input type="checkbox" name="checkall-toggle" value="" onclick="Joomla.checkAll(this)"/>
                            </th>
                            <th width="1%" class="nowrap center">
				                <?php echo JHtml::_('grid.sort', 'ID', 's.id', $listDirn, $listOrder); ?>
                            </th>
                            <th class="nowrap center">
				                <?php echo JHtml::_('grid.sort', 'E_SUBSCRIPTION', 'p.name', $listDirn, $listOrder); ?>
                            </th>
                            <th width="1%" nowrap>
				                <?php echo JText::_('EUSER'); ?>
                            </th>
                            <th width="1%" class="nowrap">
				                <?php echo JHtml::_('grid.sort', 'EPUBLISHSTATE', 's.published', $listDirn, $listOrder); ?>
                            </th>
                            <th width="1%" class="nowrap">
				                <?php echo JHtml::_('grid.sort', 'X_PRICE', 's.price', $listDirn, $listOrder); ?>
                            </th>
                            <th width="1%" class="nowrap">
				                <?php echo JHtml::_('grid.sort', 'E_CREATED', 's.created', $listDirn, $listOrder); ?>
                            </th>
                        </tr>
                        </thead>
                        <tfoot>
                        <tr>
                            <td colspan="7">
                                <div class="float-end">
					                <?php echo str_replace('<option value="0">' . JText::_('JALL') . '</option>', '', $this->pagination->getLimitBox()); ?>
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
		                <?php foreach ($this->items as $i => $item): ?>
			                <?php
			                $canCheckin = $user->authorise('core.manage', 'com_checkin') || $item->checked_out == $userId || $item->checked_out == 0;
			                $canChange  = $user->authorise('core.edit.state', 'com_joomsubscription.subscription.' . $item->id) && $canCheckin;
			                ?>
                            <tr>
                                <td class="center">
					                <?php echo JHtml::_('grid.id', $i, $item->id); ?>
                                </td>
                                <td><?php echo $item->sid; ?></td>
                                <td>
                                    <div>
                                        <img class="btn btn-mini btn-link"
                                             src="<?php echo JURI::root(true) ?>/components/com_joomsubscription/images/control.png"
                                             alt="" data-toggle="collapse" data-target="#panel-<?php echo $item->id; ?>"/>
                                        <img src="<?php echo JURI::root(true) ?>/components/com_joomsubscription/images/<?php echo $item->img; ?>"
                                             rel="tooltip" data-original-title="<?php echo JText::_($item->state); ?>">

						                <?php if ($item->checked_out) : ?>
							                <?php echo JHtml::_('jgrid.checkedout', $i, $item->checked_out, $item->checked_out_time, 'emsales.', $canCheckin); ?>
						                <?php endif; ?>

                                        <a href="<?php echo JRoute::_('index.php?option=com_joomsubscription&task=emsale.edit&id=' . (int) $item->id); ?>">
							                <?php echo $this->escape(JText::_($item->name)); ?>
                                        </a>
                                        <small>
                                            [<?php echo JText::_($item->group_name); ?>]
                                        </small>
                                    </div>
                                    <div id="panel-<?php echo $item->id; ?>" class="collapse fade info-panel">
                                        <br/>
                                        <table class="table table-bordereds table-condensed table-stripped">
                                            <tr>
                                                <td><?php echo JText::_('EUSER'); ?></td>
                                                <td>
									                <?php echo $item->uname; ?> <small>(<?php echo $item->username; ?>)</small>
                                                    <br>
									                <?php echo $item->email; ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><?php echo JText::_('E_USER_ID'); ?></td>
                                                <td><?php echo $item->uid; ?></td>
                                            </tr>
                                            <tr>
                                                <td><?php echo ucwords(str_replace(array('-', '_'), ' ', $item->gateway)); ?></td>
                                                <td><?php echo $item->gateway_id; ?></td>
                                            </tr>
                                            <tr>
                                                <td><?php echo JText::_('EGROUP'); ?></td>
                                                <td><?php echo JText::_($item->group_name); ?></td>
                                            </tr>
                                            <tr>
                                                <td><?php echo JText::_('ELIMIT'); ?></td>
                                                <td><?php echo ($item->access_limit > 0) ? '<span class="badge badge-warning">' . $item->access_limit . '</span>' : '<span class="label label-success">' . JText::_('ENOLIMITS') . '</span>'; ?></td>
                                            </tr>
							                <?php if ($coupon = $item->coupon_info): ?>
                                                <tr>
                                                    <td><?php echo JText::_('E_COUPON'); ?></td>
                                                    <td><?php echo JoomsubscriptionApi::getPrice($coupon->discount, $item->plan_params, $item->params); ?>
                                                        <span class="label label-info"><?php echo $coupon->value; ?></span>
                                                        <a href="javascript:void(0)"
                                                           onclick="Joomsubscription.setAndSubmit('filter_search', 'cpn:<?php echo $coupon->id ?>')"><img
                                                                    src="<?php echo JUri::root(true) ?>/components/com_joomsubscription/images/funnel.png"/></a>
                                                    </td>
                                                </tr>
							                <?php endif; ?>
                                            <tr>
                                                <td><?php echo JText::_('EUSED'); ?></td>
                                                <td>
									<span class="badge <?php echo (($item->access_limit > 0) && ($item->access_count >= $item->access_limit)) ? 'badge-important' : 'badge-success'; ?>">
										<?php echo $item->access_count; ?></span>
                                                </td>
                                            </tr>
							                <?php if ($item->activated): ?>
                                                <tr>
                                                    <td><?php echo JText::_('X_PERIOD'); ?></td>
                                                    <td><?php echo JHtml::_('date', $item->ctime, $this->params->get('date_format')); ?>
                                                        -
                                                        <span class="<?php echo $item->expired ? '' : ''; ?>">
											<?php echo ($item->days_enable >= 36500 || $item->extime == '0000-00-00 00:00:00') ? JText::_('E_NEVER') : JHtml::_('date', $item->extime, $this->params->get('date_format')); ?>
										</span>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td><?php echo JText::_('EDAYSLEFT'); ?></td>
                                                    <td>
										                <?php if ($item->days_enable >= 36500 || $item->extime == '0000-00-00 00:00:00'): ?>
											                <?php echo JText::_("ELIFETIME"); ?>
										                <?php else: ?>
                                                            <span class="badge <?php echo($item->days > 0 ? 'badge-success' : 'badge-warning'); ?>">
												<?php echo $item->days; ?>
											</span>
										                <?php endif; ?>
                                                    </td>
                                                </tr>
							                <?php endif; ?>
							                <?php if ($item->comment): ?>
                                                <tr>
                                                    <td><?php echo JText::_('X_ADMINCOMMENT'); ?></td>
                                                    <td><?php echo $item->comment; ?></td>
                                                </tr>
							                <?php endif; ?>

							                <?php foreach ($item->fields_list as $field): ?>
								                <?php $val = trim($field->getValue());
								                if (!empty($val)): ?>
                                                    <tr>
                                                        <td><?php echo $field->getLabel() ?></td>
                                                        <td><?php echo $field->getValue(); ?></td>
                                                    </tr>
								                <?php endif; ?>
							                <?php endforeach; ?>
                                        </table>


						                <?php if ($item->gateway == 'offline' && $item->params->get('gateways.offline.billing', false) && !$item->activated): ?>
                                            <a href="javascript:void(0);" class="btn-small btn-primary"
                                               onclick="window.open('<?php echo JRoute::_('index.php?option=com_joomsubscription&view=embill&tmpl=component&id=' . $item->sid) ?>', '<?php echo JText::_('E_BILL') ?>', 'scrollbars=1,width=1024,height=600');">
								                <?php echo JText::_('E_BILL'); ?>
                                            </a>
						                <?php endif; ?>

						                <?php if ($this->params->get('use_invoice', 0) && $item->activated && $item->invoice_num && (float) $item->price): ?>
                                            <a href="javascript:void(0);" class="btn-small btn-primary"
                                               onclick="window.open('<?php echo JRoute::_('index.php?option=com_joomsubscription&view=eminvoice&tmpl=component&id=' . $item->sid) ?>', '<?php echo JText::_('E_INVOICE') ?>', 'scrollbars=1,width=1024,height=600');">
								                <?php echo JText::_('E_GET_INVOICE'); ?>
                                            </a>
						                <?php endif; ?>
                                    </div>
                                </td>
                                <td nowrap><?php echo $item->uname; ?></td>
                                <td nowrap="nowrap" align="center">
					                <?php echo JHtml::_('jgrid.published', $item->published, $i, 'emsales.', $canChange); ?>
                                </td>
                                <td align="right" nowrap>
					                <?php echo JoomsubscriptionApi::getPrice($item->price, $item->plan_params, $item->params); ?>
                                </td>
                                <td nowrap>
					                <?php echo JoomsubscriptionHelper::getFormattedDate($item->created); ?>
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
	<?php echo JHtml::_('form.token'); ?>

</form>


<script type="text/javascript">
    (function ($) {
        $('.info-panel')
            .on('shown', function () {
                $('img[data-target="#' + $(this).attr('id') + '"]').attr('src', '<?php echo JURI::root(true) ?>/components/com_joomsubscription/images/control-270.png');
            })
            .on('hidden', function () {
                $('img[data-target="#' + $(this).attr('id') + '"]').attr('src', '<?php echo JURI::root(true) ?>/components/com_joomsubscription/images/control.png');
            });
    }(jQuery))
</script>