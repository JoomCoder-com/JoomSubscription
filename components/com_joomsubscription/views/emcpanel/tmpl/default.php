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
<div class="page-header border-bottom mb-3">
    <h1>
        <img src="<?php echo JUri::root(true); ?>/components/com_joomsubscription/images/cpanel/dashboard.png" />
        <strong><?php echo JText::_('ECPANEL'); ?></strong>
    </h1>
</div>

<div class="mb-3">
    <div class="row mb-3">
        <div class="col-2 text-center">
            <a class="d-block w-100 border rounded shadow-sm p-3" href="<?php echo JRoute::_('index.php?option=com_joomsubscription&view=emsales') ?>">
                    <span class="d-block">
                        <img src="<?php echo  JUri::root(true); ?>/components/com_joomsubscription/images/cpanel/sales.png"/>
                    </span>
				<?php echo JText::_('ESUBSCRIPTIONS') ?>
            </a>
        </div>

        <div class="col-2 text-center">
            <a class="d-block w-100 border rounded shadow-sm p-3" href="<?php echo JRoute::_('index.php?option=com_joomsubscription&view=emplans') ?>">
                    <span class="d-block">
                        <img src="<?php echo  JUri::root(true); ?>/components/com_joomsubscription/images/cpanel/plans.png"/>
                    </span>
				<?php echo JText::_('EPLANS') ?>
            </a>
        </div>

        <div class="col-2 text-center">
            <a class="d-block w-100 border rounded shadow-sm p-3" href="<?php echo JRoute::_('index.php?option=com_joomsubscription&view=emgroups') ?>">
                    <span class="d-block">
                        <img src="<?php echo  JUri::root(true); ?>/components/com_joomsubscription/images/cpanel/groups.png"/>
                    </span>
				<?php echo JText::_('EGROUPS') ?>
            </a>
        </div>

        <div class="col-2 text-center">
            <a class="d-block w-100 border rounded shadow-sm p-3" href="<?php echo JRoute::_('index.php?option=com_joomsubscription&view=emfields') ?>">
                    <span class="d-block">
                        <img src="<?php echo  JUri::root(true); ?>/components/com_joomsubscription/images/cpanel/fields.png"/>
                    </span>
				<?php echo JText::_('EFIELDS') ?>
            </a>
        </div>

        <div class="col-2 text-center">
            <a class="d-block w-100 border rounded shadow-sm p-3" href="<?php echo JRoute::_('index.php?option=com_joomsubscription&view=emcoupons') ?>">
                    <span class="d-block">
                        <img src="<?php echo  JUri::root(true); ?>/components/com_joomsubscription/images/cpanel/coupons.png"/>
                    </span>
				<?php echo JText::_('ECOUPONS') ?>
            </a>
        </div>

        <div class="col-2 text-center">
            <a class="d-block w-100 border rounded shadow-sm p-3" href="<?php echo JRoute::_('index.php?option=com_joomsubscription&view=emtaxes') ?>">
                    <span class="d-block">
                        <img src="<?php echo  JUri::root(true); ?>/components/com_joomsubscription/images/cpanel/taxes.png"/>
                    </span>
				<?php echo JText::_('ETAXES') ?>
            </a>
        </div>

    </div>
    <div class="row mb-3">

        <div class="col-2 text-center">
            <a class="d-block w-100 border rounded shadow-sm p-3" href="<?php echo JRoute::_('index.php?option=com_joomsubscription&view=emlist') ?>">
                    <span class="d-block">
                        <img src="<?php echo  JUri::root(true); ?>/components/com_joomsubscription/images/cpanel/purchase.png"/>
                    </span>
				<?php echo JText::_('EPLUNLIST') ?>
            </a>
        </div>

        <div class="col-2 text-center">
            <a class="d-block w-100 border rounded shadow-sm p-3" href="<?php echo JRoute::_('index.php?option=com_joomsubscription&view=emhistory') ?>">
                    <span class="d-block">
                        <img src="<?php echo  JUri::root(true); ?>/components/com_joomsubscription/images/cpanel/history.png"/>
                    </span>
				<?php echo JText::_('EPLUNHISTORY') ?>
            </a>
        </div>

        <div class="col-2 text-center">
            <a class="d-block w-100 border rounded shadow-sm p-3" href="<?php echo JRoute::_('index.php?option=com_joomsubscription&view=emstates') ?>">
                    <span class="d-block">
                        <img src="<?php echo  JUri::root(true); ?>/components/com_joomsubscription/images/cpanel/states.png"/>
                    </span>
				<?php echo JText::_('ESTATES') ?>
            </a>
        </div>

        <div class="col-2 text-center">
            <a class="d-block w-100 border rounded shadow-sm p-3" href="<?php echo JRoute::_('index.php?option=com_joomsubscription&view=emanalytics') ?>">
                    <span class="d-block">
                        <img src="<?php echo  JUri::root(true); ?>/components/com_joomsubscription/images/cpanel/analytics.png"/>
                    </span>
				<?php echo JText::_('ESTATES') ?>
            </a>
        </div>

    </div>
</div>

<?php if ($this->data): ?>
<div class="card mb-3">
    <div class="card-body">
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
            <a href="<?php echo JRoute::_('index.php?option=com_joomsubscription&view=emanalytics') ?>" class="btn btn-success btn-sm">
			    <?php echo
			    JText::_('EMOREDETAILS') ?>
            </a>
    </div>
</div>
<?php endif; ?>

<div class="card mb-3">
    <div class="card-header">
        <h3 class="card-title"><?php echo JText::_('ELASTSUBSCR'); ?></h3>
    </div>
    <div class="card-body">
        <table class="table table-condensed table-striped table-subscr">
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
                        <div class="float-end <?php echo $subscription->price > 0 ?
							'price-pos' : 'price-neg'; ?>">
							<?php echo JoomsubscriptionApi::getPrice($subscription->price, $subscription->
							params); ?>
                        </div>
                    </td>
                </tr>
			<?php endforeach; ?>
        </table>
    </div>
</div>

<?php if ($this->activate): ?>
    <div class="card mb-3">
        <div class="card-header">
            <h3 class="card-title m-0 p-0"><?php echo JText::_('ENOTACTIVESUBSCR'); ?> <span class="badge badge-important"><?php echo
					count($this->activate); ?></span></h3>
        </div>
        <div class="card-header">
            <div class="uk-alert uk-alert-danger uk-alert-small">
                <small>
					<?php echo JText::_('EADTIVATEDESCR'); ?>
                </small>
            </div>
            <form action="<?php echo JRoute::_('index.php?option=com_joomsubscription&view=emsales'); ?>" method="post" id="activate-form">
                <table class="table table-condensed table-striped table-subscr">
					<?php foreach ($this->activate as $subscription): ?>
                        <tr valign="top">
                            <td width="1%">
								<?php echo $subscription->id; ?>
                            </td>
                            <td>
                                <a href="javascript:void(0);" class="btn btn-activate btn-mini btn-primary" data-subscr-id="<?php echo
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
                    $.each($('.btn-activate'), function(k, v) {
                        $(v).click(function() {
                            $('input[name^="cid"]').val($(v).data('subscr-id'));
                            $('#activate-form').submit();
                        });
                    });
                }(jQuery))
            </script>
        </div>
    </div>
<?php endif; ?>