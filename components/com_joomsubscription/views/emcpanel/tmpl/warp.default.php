<?php
/**
 * Cobalt by JoomCoder
 * a component for Joomla! 1.7 - 2.5 CMS (http://www.joomla.org)
 * Author Website: https://www.joomcoder.com/
 *
 * @copyright Copyright (C) 2012 JoomCoder (https://www.joomcoder.com/). All rights reserved.
 * @license   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */
defined('_JEXEC') or die();
?>
<style type="text/css">
.cpbut a:hover {
    background-color:#f5f5f5;
    text-decoration:none;
}
.cpbut a {
    display:block;
    border:1px solid #e9e9e9;
    background-color:#ffffff;
    margin-bottom:10px!important;
    background-repeat:no-repeat;
    background-position:center 15px;
    text-align:center;
    padding-top:70px;
    height:30px;
    font-weight:bold;
}
.subscr-date {
    color:purple;
}
.table-subscr small {
    color:darkgrey;
}
ul.unstyled {
    list-style:none;
}
</style>
<div class="page-header">
    <h1>
        <img src="<?php echo JUri::root(true); ?>/components/com_joomsubscription/images/cpanel/dashboard.png" />
        <strong><?php echo JText::_('ECPANEL'); ?></strong>
    </h1>
</div>
<hr />
<div class="uk-grid">
    <div class="uk-width-1-1">
        <!--<h3 class="uk-panel-title"><?php echo JText::_('EMCPQUIKICONS'); ?></h3>-->
        <div class="uk-grid uk-grid-small">
            <div class="cpbut uk-width-small-1-4 uk-width-large-1-6">
                <a style="background-image: url(<?php echo JUri::root(true); ?>/components/com_joomsubscription/images/cpanel/sales.png)" href="<?php echo
JRoute::_('index.php?option=com_joomsubscription&view=emsales') ?>">
                    <?php echo JText::_('ESUBSCRIPTIONS') ?>
                </a>
            </div>
            <div class="cpbut uk-width-small-1-4 uk-width-large-1-6">
                <a style="background-image: url(<?php echo JUri::root(true); ?>/components/com_joomsubscription/images/cpanel/plans.png)" href="<?php echo
JRoute::_('index.php?option=com_joomsubscription&view=emplans') ?>">
                    <?php echo JText::_('EPLANS') ?>
                </a>
            </div>
            <div class="cpbut uk-width-small-1-4 uk-width-large-1-6">
                <a style="background-image: url(<?php echo JUri::root(true); ?>/components/com_joomsubscription/images/cpanel/groups.png)" href="<?php echo
JRoute::_('index.php?option=com_joomsubscription&view=emgroups') ?>">
                    <?php echo JText::_('EGROUPS') ?>
                </a>
            </div>
            <div class="cpbut uk-width-small-1-4 uk-width-large-1-6">
                <a style="background-image: url(<?php echo JUri::root(true); ?>/components/com_joomsubscription/images/cpanel/fields.png)" href="<?php echo
JRoute::_('index.php?option=com_joomsubscription&view=emfields') ?>">
                    <?php echo JText::_('EFIELDS') ?>
                </a>
            </div>
            <div class="cpbut uk-width-small-1-4 uk-width-large-1-6">
                <a style="background-image: url(<?php echo JUri::root(true); ?>/components/com_joomsubscription/images/cpanel/coupons.png)" href="<?php echo
JRoute::_('index.php?option=com_joomsubscription&view=emcoupons') ?>">
                    <?php echo JText::_('ECOUPONS') ?>
                </a>
            </div>
            <div class="cpbut uk-width-small-1-4 uk-width-large-1-6">
                <a style="background-image: url(<?php echo JUri::root(true); ?>/components/com_joomsubscription/images/cpanel/taxes.png)" href="<?php echo
JRoute::_('index.php?option=com_joomsubscription&view=emtaxes') ?>">
                    <?php echo JText::_('ETAXES') ?>
                </a>
            </div>
            <!--<h3 class="uk-panel-title"><?php echo JText::_('EMCPQUIKICONSUSER'); ?></h3>-->
            <div class="cpbut uk-width-small-1-4 uk-width-large-1-6">
                <a style="background-image: url(<?php echo JUri::root(true); ?>/components/com_joomsubscription/images/cpanel/purchase.png)" href="<?php echo
JRoute::_('index.php?option=com_joomsubscription&view=emlist') ?>">
                    <?php echo JText::_('EPLUNLIST') ?>
                </a>
            </div>
            <div class="cpbut uk-width-small-1-4 uk-width-large-1-6">
                <a style="background-image: url(<?php echo JUri::root(true); ?>/components/com_joomsubscription/images/cpanel/history.png)" href="<?php echo
JRoute::_('index.php?option=com_joomsubscription&view=emhistory') ?>">
                    <?php echo JText::_('EPLUNHISTORY') ?>
                </a>
            </div>
            <!--<h3 class="uk-panel-title"><?php echo JText::_('EMCPQUIKICONSTOOLS'); ?></h3>-->
            <div class="cpbut uk-width-small-1-4 uk-width-large-1-6">
                <a style="background-image: url(<?php echo JUri::root(true); ?>/components/com_joomsubscription/images/cpanel/states.png)" href="<?php echo
JRoute::_('index.php?option=com_joomsubscription&view=emstates') ?>">
                    <?php echo JText::_('ESTATES') ?>
                </a>
            </div>
            <div class="cpbut uk-width-small-1-4 uk-width-large-1-6">
                <a style="background-image: url(<?php echo JUri::root(true); ?>/components/com_joomsubscription/images/cpanel/analytics.png)" href="<?php echo
JRoute::_('index.php?option=com_joomsubscription&view=emanalytics') ?>">
                    <?php echo JText::_('EANALYTICS') ?>
                </a>
            </div>
            <div class="cpbut uk-width-small-1-4 uk-width-large-1-6">
                <a style="background-image: url(<?php echo JUri::root(true); ?>/components/com_joomsubscription/images/cpanel/import.png)" href="<?php echo
JRoute::_('index.php?option=com_joomsubscription&view=emimports') ?>">
                    <?php echo JText::_('EIMPORT') ?>
                </a>
            </div>
            <div class="cpbut uk-width-small-1-4 uk-width-large-1-6">
                <a target="_blank" style="background-image: url(<?php echo JUri::
root(true); ?>/components/com_joomsubscription/images/cpanel/support.png)" href="https://www.joomcoder.com/support/community-forum/category-items/6-community-forum/52-joomsubscription-9.html">
                    <?php echo JText::_('ESUPPORT') ?>
                </a>
            </div>
        </div>
    </div>
    <div class="uk-width-1-1">
    <hr />
    <?php if ($this->data): ?>
	<div id="chart" style="width:100%; height:300px;"></div>
	<script type="text/javascript">
		(function($) {
			$('#chart').highcharts({
				chart: {
					type: 'areaspline'
				},
				title: {
					text: '<?php echo htmlspecialchars(JText::_('ESALESPROGRESS'), ENT_QUOTES,
'UTF-8'); ?>'
				},
				tooltip: {
					crosshairs: [true]
				},
				xAxis: {
					type: 'datetime',
					title: {
						text: null
					},
					offset: 10
				},
				yAxis: [{
					title: {
						text: '<?php echo JText::_('EA_SALESTOT'); ?>',
						style: {
							color: '#2e95b9'
						}
					},
					labels: {
						style: {
							color: '#2e95b9'
						},
						formatter: function() {
							return this.value.toFixed(2) + '$';
						}
					},
					gridLineColor: '#EEEEEE'
				}],
				legend: {
					enabled: true
				},
				plotOptions: {
					areaspline: {
						fillOpacity: 0.2
					}
				},
				series: [{
					yAxis: 0,
					pointInterval: 24 * 3600 * 1000,
					//pointStart: <?php echo mktime(0, 0, 0, date('m'), date('d') - 29, date('Y')); ?>000,
					pointStart: new Date().getTime() - (24 * 3600 * 29 * 1000),
					name: '<?php echo JText::_('EA_SALESTOT'); ?>',
					color: '#2e95b9',
					data: [<?php echo $this->data['amount']; ?>
					]
				}]
			});
		}(jQuery))
	</script>
<a href="<?php echo JRoute::_('index.php?option=com_joomsubscription&view=emanalytics') ?>" class="uk-button uk-button-success uk-button-mini"><?php echo
JText::_('EMOREDETAILS') ?></a>
<?php endif; ?>
<hr />
<div class="uk-panel">
    <h3 class="uk-panel-title"><?php echo JText::_('ELASTSUBSCR'); ?></h3>
    <table class="uk-table uk-table-condensed uk-table-striped uk-table-subscr">
        <?php foreach ($this->latest as $subscription): ?>
        <tr>
            <td width="20">
                <img data-uk-tooltip title="<?php echo JText::_($subscription->
state); ?>" src="<?php echo
JUri::root(true); ?>/components/com_joomsubscription/images/<?php echo
$subscription->img; ?>" alt="" /><br />
                <small><?php echo $subscription->id; ?></small>
            </td>
            <td>
                <?php echo JText::_($subscription->name); ?>
                <br />
                <small><?php echo $subscription->username; ?></small>
            </td>
            <td width="1%" nowrap="nowrap">
                <small>
                    <?php echo $subscription->gateway; ?>
                    <br />
                    <?php echo $subscription->gateway_id; ?>
                </small>
            </td>
            <td width="1%" nowrap="nowrap" align="right">
                <span class="subscr-date"><?php echo JoomsubscriptionHelper::
getFormattedDate($subscription->purchased, 'd M H:i'); ?></span><br />
                <div class="pull-right <?php echo $subscription->price > 0 ?
'price-pos' : 'price-neg'; ?>">
                    <?php echo JoomsubscriptionApi::getPrice($subscription->price, $subscription->
params); ?>
                </div>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>
<?php if ($this->activate): ?>
<div class="uk-panel uk-panel-box">
    <h3 class="uk-panel-title"><?php echo JText::_('ENOTACTIVESUBSCR'); ?> <span class="badge badge-important"><?php echo
count($this->activate); ?></span></h3>
    <div class="uk-alert uk-alert-danger uk-alert-small">
        <small>
					<?php echo JText::_('EADTIVATEDESCR'); ?>
				</small>
    </div>
    <form action="<?php echo JRoute::_('index.php?option=com_joomsubscription&view=emsales'); ?>" method="post" id="activate-form">
        <table class="uk-table uk-table-condensed uk-table-striped uk-table-subscr">
            <?php foreach ($this->activate as $subscription): ?>
            <tr valign="top">
                <td width="1%">
                    <?php echo $subscription->id; ?>
                </td>
                <td>
                    <a href="javascript:void(0);" class="uk-button uk-button-activate uk-button-mini uk-button-primary" data-subscr-id="<?php echo
$subscription->id; ?>">
                        <?php echo JText::_('EACTIVATE'); ?>
                    </a>
                    <?php echo JText::_($subscription->name); ?>
                    <br />
                    <small><?php echo $subscription->username; ?> [<b><?php echo
$subscription->user_id; ?></b>]</small>
                </td>
                <td width="1%" nowrap="nowrap">
                    <small>
									<?php echo $subscription->gateway; ?>
									<br /><?php echo $subscription->gateway_id; ?>
								</small>
                </td>
                <td width="1%" nowrap="nowrap" align="right">
                    <?php echo JoomsubscriptionApi::getPrice($subscription->price, $subscription->
params); ?>
                </td>
                <td width="1%" nowrap="nowrap"><img src="<?php echo JUri::root(true); ?>/components/com_joomsubscription/images/exclamation-diamond.png" alt="" /></td>
            </tr>
            <?php endforeach; ?>
        </table>
        <input type="hidden" name="cid[]" value="" />
        <input type="hidden" name="return" value="cpanel" />
        <input type="hidden" name="task" value="emsales.publish" />
        <?php echo JHtml::_('form.token'); ?>
    </form>
			<script type="text/javascript">
				(function($) {
					$.each($('.uk-button-activate'), function(k, v) {
						$(v).click(function() {
							$('input[name^="cid"]').val($(v).data('subscr-id'));
							$('#activate-form').submit();
						});
					});
				}(jQuery))
			</script>
</div>
<?php endif; ?>
        </div>
    </div>