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
	.plan-price .btn {
		margin-top: 10px;
		font-weight: normal;
	}
	.plan-price {
		font-size: 28px;
		font-weight: 100;
		text-align: center;
	}
	.plan-price .alert {
		font-size: 10px;
		font-weight: normal;
		margin-top: 10px;
	}
	.plan-dscount {
		color: red;
		font-size: 14px;
		font-weight: normal;
		margin-top: 5px;
	}
	.plan-list {
		margin-bottom: 20px;
	}
	.plan-list h3 {
		margin-top: 0;
	}
	.em-min {
		font-size: 11px;
		font-weight: 300;
	}
</style>
<br>
<?php foreach($plans AS $item):?>
	<div class="row-fluid plan-list">
		<div class="span8">
			<h3>
				<?php echo $item->name;?>
				<small>
					<?php echo $item->period;?>
				</small>
			</h3>
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
						<ul>
							<li><?php echo implode('</li><li>', $item->grant); ?></li>
						</ul>
						</p>
					<?php endif; ?>
				</small>
			</div>
		</div>
		<div class="span4 plan-price">
			<?php if($item->is_donation): ?>
				<small class="em-min"><?php echo JText::_('EMINIMUM'); ?></small>
			<?php endif; ?>
			<?php echo JoomsubscriptionApi::getPrice($item->price, $item->params); ?>
			<br>
			<?php if($item->total > $item->price): ?>
				<br>
				<div class="plan-dscount">
					<s><?php echo JoomsubscriptionApi::getPrice($item->total, $item->params); ?></s><br>
					<?php echo JText::_('SUBSCRIPTION_DISCOUNT_'.$item->discount_type); ?>
				</div>
			<?php endif; ?>

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
		</div>
	</div>
	<hr>
<?php endforeach; ?>

<script type="text/javascript">
	(function($){
		$.each($('.alert-plan'), function(){
			if(!$.trim($(this).text()))	{
				$(this).hide();
			}
		});
	}(jQuery))
</script>
