<?php
/**
 * Joomsubscription Payment Plugin by JoomCoder
 * a plugin for Joomla! 1.7 CMS (http://www.joomla.org)
 * Author Website: https://www.joomcoder.com/
 * @copyright Copyright (C) 2012 JoomCoder (https://www.joomcoder.com/). All rights reserved.
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.menu');

class JoomsubscriptionGatewaygoogleco extends JoomsubscriptionGateway
{

	function accept(&$subscription, $plan)
	{
		$subscription->gateway_id = $this->get_gateway_id();
		$post = JFactory::getApplication()->input;

		switch($post->get('_type'))
		{
			case 'new-order-notification':
				if($post->get('financial-order-state') == 'CHARGED')
				{
					$subscription->published = 1;
				}
				break;

			case 'order-state-change-notification':
				if($post->get('new-financial-order-state') == 'CHARGED')
				{
					$subscription->published = 1;
				}
				break;

			case 'charge-amount-notification':
				$subscription->published = 1;
				break;

			case 'refund-amount-notification':
			case 'chargeback-amount-notification':
				$subscription->published = 0;
				break;
		}

		return true;
	}

	function pay($amount, $name, $subscription, $plan)
	{
		if(!$this->params->get('merchant'))
		{
			$this->setError(JText::_("GC_NOT_ALL_SET"));

			return FALSE;
		}
		$params = $this->params;

		$param['item_name_1'] = $name;
		$param['item_description_1'] = $name;
		$param['item_quantity_1'] = 1;
		$param['item_price_1'] = $amount;
		$param['item_currency_1'] = $params->get('currency');
		$param['continue_url'] = $this->_get_return_url($subscription->id);
		$param['_charset_'] = 'utf-8';
		$param['tax'] = $params->get('tax');
		$param['shopping-cart.items.item-1.merchant-item-id'] = $subscription->id;
		$param['shopping-cart.items.item-1.item-description'] = $name;
		$param['shopping-cart.items.item-1.item-name'] = $name;
		$param['shopping-cart.items.item-1.quantity'] = 1;
		$param['shopping-cart.items.item-1.unit-price'] = $amount;
		$param['shopping-cart.items.item-1.digital-content.display-disposition'] = "OPTIMISTIC";
		$param['shopping-cart.items.item-1.digital-content.description'] = "May take up to 20 minutes to set up your subscription.";

		$url = sprintf('https://%scheckout.google.com/api/checkout/v2/checkoutForm/Merchant/%s',
			($params->get('sandbox') ? 'sandbox.' : ''), $this->params->get('merchant')
		);

		$form[] = '<form id="googlecoform" method="POST" action="'.$url.'" accept-charset="utf-8">';
		foreach($param AS $key => $val)
			$form[] = sprintf('<input type="hidden" name="%s" value="%s"/>', $key, $val);
		$form[] = '</form><script>document.getElementById("googlecoform").submit();</script>';

		//echo implode("\n", $form);

		$url .= '?'.http_build_query($param);

		JFactory::getApplication()->redirect($url);
	}

	function get_gateway_id()
	{
		$post = JFactory::getApplication()->input;
		return $post->get('google-order-number');
	}

	function get_subscrption_id($who)
	{
		if($who == 'NOTIFY_URL')
		{
			if(JFactory::getApplication()->input->get('_type') == 'new-order-notification')
			{
				return JFactory::getApplication()->input->get('shopping-cart.items.item-1.merchant-item-id');
			}
			else
			{
				$db = JFactory::getDbo();
				$db->setQuery("SELECT id FROM #__joomsubscription_subscriptions WHERE gateway_id = '".$this->get_gateway_id()."' and gateway = 'google'");
				return $db->loadResult();
			}
		}

		return parent::get_subscrption_id($who);
	}

	private function getAuthenticationHeaders() {
		$headers = array(
			"Authorization: Basic " .
			base64_encode($this->params->get('merchant').':'.$this->params->get('merchant_key')),
			"Content-Type: application/xml; charset=UTF-8",
			"Accept: application/xml; charset=UTF-8",
			"User-Agent: Joomsubscription - Joomla membership extension");
		return $headers;
	}
}