<?php
/**
 * JoomSubscription by JoomCoder
 * a component for Joomla (http://www.joomla.org)
 * Author Website: https://www.joomcoder.com/
 * @copyright Copyright (C) 2012 JoomCoder (https://www.joomcoder.com/). All rights reserved.
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */
defined('_JEXEC') or die();
$user = JFactory::getUser();
$plans = $this->plans;
$width = round(100 / count($plans), 2);
?>
<style type="text/css">
	.table-plans {
		border-collapse: separate;
		border-spacing: 6px;
	}
</style>

<table class="table-plans" width="100%">
	<tr>
		<?php foreach($plans AS $item):?>
			<td align="center" width="<?php echo $width; ?>%">
				<h3>
					<?php echo $item->name;?>
				</h3>
			</td>
		<?php endforeach; ?>
	</tr>
	<tr valign="top">
		<?php foreach($plans AS $item):?>
			<td align="center" width="<?php echo $width; ?>%" class="well well-small">
				<h2>
					<?php if($item->is_donation): ?>
						<?php echo JText::_('EMINIMUM'); ?>
					<?php endif; ?>
					<?php echo JoomsubscriptionApi::getPrice($item->price, $item->params);?>
				</h2>

				<?php if($item->total > $item->price): ?>
					<div style="color: red">
						<s><?php echo JoomsubscriptionApi::getPrice($item->total, $item->params);?></s>
						<?php echo JText::_('SUBSCRIPTION_DISCOUNT_'.$item->discount_type); ?>
					</div>
				<?php endif; ?>

				<?php  echo $item->period;?>
			</td>
		<?php endforeach; ?>
	</tr>
	<tr>
		<?php foreach($plans AS $item):?>
			<td align="center" width="<?php echo $width; ?>%">
				<?php echo $item->description;?>

				<div class="alert alert-info alert-plan">
					<small>
						<?php if($item->left): ?>
							<p><?php echo JText::plural('SUBCRIPTIONS_AMOUNT_LEFT_ALERT', $item->left); ?></p>
						<?php endif; ?>
						<?php if($item->user_left): ?>
							<p><?php echo JText::plural('SUBCRIPTIONS_AMOUNT_USERLEFT_ALERT', $item->user_left); ?></p>
						<?php endif; ?>
						<?php if($item->params->get('properties.muaccess')): ?>
							<p><?php echo JText::plural('SUBCRIPTIONS_MUA', $item->params->get('properties.muaccess')); ?></p>
						<?php endif; ?>
						<?php if($item->grant): ?>
							<p>
								<?php echo JText::_('SUBCRIPTIONS_GRANTYOU'); ?>
								<ul class="unstyled">
									<li><?php echo implode('</li><li>', $item->grant); ?></li>
								</ul>
							</p>
						<?php endif; ?>
					</small>
				</div>
			</td>
		<?php endforeach; ?>
	</tr>

	<tr>
		<?php foreach($plans AS $item):?>
			<td align="center" valign="top">
				<?php if(!empty($item->subscr_left)): ?>
					<div><small><?php echo $item->subscr_left; ?></small></div>
				<?php endif; ?>
				<?php if(!empty($item->mua)): ?>
					<div><small><?php echo JText::sprintf('You may share this cubscription with %d users', $item->mua); ?></small></div>
				<?php endif; ?>
			</td>
		<?php endforeach; ?>
	</tr>

	<tr>
		<?php foreach($plans AS $item):?>
			<td align="center" width="<?php echo $width; ?>%">
				<?php if($item->require_one_of): ?>
					<div class="alert alert-danger"><?php echo str_replace('[PLANS]', implode(', ', $item->require_one_of), JText::plural('SUBSCRIPTION_ONE_OF', count($item->require_one_of))); ?></div>
				<?php elseif($item->require_all_of): ?>
					<div class="alert alert-danger"><?php echo str_replace('[PLANS]', implode(', ', $item->require_all_of), JText::plural('SUBSCRIPTION_ALL_OF', count($item->require_all_of))); ?></div>
				<?php elseif($item->price <= 0 && !$item->params->get('properties.terms')): ?>
					<a class="btn btn-success" href="<?php echo JRoute::_('index.php?option=com_joomsubscription&task=empayment.send&sid='.$item->id); ?>"><?php echo JText::_('EBTNGETNOW'); ?></a>
				<?php elseif(!$item->is_donation): ?>
					<a href="<?php echo JRoute::_('index.php?option=com_joomsubscription&view=empayment&sid='.$item->id); ?>" class="btn btn-warning">
						<?php echo JText::_('EBTNBUYNOW'); ?></a>
				<?php else: ?>
					<a href="<?php echo JRoute::_('index.php?option=com_joomsubscription&view=empayment&sid='.$item->id); ?>" class="btn btn-primary">
						<?php echo JText::_('EBTNDONATE'); ?></a>
				<?php endif; ?>
			</td>
		<?php endforeach; ?>
	</tr>
</table>

<script type="text/javascript">
	(function($){
		$.each($('.alert-plan'), function(){
			if(!$.trim($(this).text()))	{
				$(this).hide();
			}
		});
	}(jQuery))
</script>
