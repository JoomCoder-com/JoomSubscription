<?php
/**
 * JoomSubscription by JoomCoder
 * a component for Joomla! 3.0 CMS (http://www.joomla.org)
 * Author Website: https://www.joomcoder.com/
 *
 * @copyright Copyright (C) 2012 JoomCoder (https://www.joomcoder.com/). All rights reserved.
 * @license   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */
defined('_JEXEC') or die();

class JoomsubscriptionActionGoogle extends JoomsubscriptionAction
{
	public function onActive($subscription)
	{
		if(!$this->params->get('ga_id'))
		{
			return;
		}
		$app         = JFactory::getApplication();
		$doc         = JFactory::getDocument();
		$plan_model  = MModelBase::getInstance('EmPlan', 'JoomsubscriptionModel');
		$group_model = MModelBase::getInstance('EmGroup', 'JoomsubscriptionModel');
		$plan        = $plan_model->getItem($subscription->plan_id);
		$group       = $group_model->getItem($plan->group_id);

		$output   = array();
		$output[] = "(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)})(window,document,'script','//www.google-analytics.com/analytics.js','ga');";

		$output[] = "ga('create', '" . $this->params->get('ga_id') . "', '" . str_replace(array('http://', 'https://'), '', JUri::root()) . "');";
		$output[] = "ga('require', 'ecommerce', 'ecommerce.js');";

		$output[] = "ga('ecommerce:addTransaction', {
			  'id': '{$subscription->gateway_id}',           // Transaction ID. Required.
			  'affiliation': '{$app->getCfg('sitename')}',   // Affiliation or store name.
			  'revenue': '{$subscription->price}'            // Grand Total.
			});";

		$output[] = "ga('ecommerce:addItem', {
			  'id': '{$subscription->gateway_id}', // Transaction ID. Required.
			  'name': '{$plan->name} [{$group->name}]', // Product name. Required.
			  'sku': '{$subscription->id}',        // SKU/code.
			  'category': '{$group->name}',        // Category or variation.
			  'price': '{$subscription->price}',   // Unit price.˚
			  'quantity': '1'                      // Quantity.
			});";

		$output[] = "ga('ecommerce:send');";

		$doc->addScriptDeclaration(implode("\n", $output));

	}

	public function getDescription()
	{
		$out = '';
		if($this->params->get('ga_id'))
		{
			$out .= $this->params->get('ga_id');
		}

		return $out;
	}
}
