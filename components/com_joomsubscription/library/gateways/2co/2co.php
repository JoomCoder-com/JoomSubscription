<?php
/**
 * Joomsubscription Payment Plugin by JoomCoder
 * a plugin for Joomla! 1.7 CMS (http://www.joomla.org)
 * Author Website: https://www.joomcoder.com/
 *
 * @copyright Copyright (C) 2012 JoomCoder (https://www.joomcoder.com/). All rights reserved.
 * @license   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.menu');

class JoomsubscriptionGateway2co extends JoomsubscriptionGateway
{
	function accept(&$subscription, $plan)
	{
		$post    = JFactory::getApplication()->input;
		$gateway = $this->get_gateway_id();

		$hash  = $this->params->get('sword') . $this->params->get('vendor') . $gateway . $post->get('total');
		$check = strtoupper(md5($hash));

		if($check != $post->get('key', $post->get('md5_hash')))
		{
			$check = strtoupper(md5($post->get('sale_id') . $this->params->get('vendor') . $post->get('invoice_id') . $this->params->get('sword')));
			if($check != $post->get('key', $post->get('md5_hash')))
			{
				$this->setError(JText::_('EMR_CANNOT_VERYFY'));
				$this->log('2CO: Verification failed', $_POST);

				return FALSE;
			}
		}

		$this->log('Verified:', $_POST);

		$subscription->gateway_id = $gateway;

		switch($post->get('message_type'))
		{
			case 'ORDER_CREATED':
				if($this->params->get('block') == 0)
				{
					$subscription->published = 1;
				}
				if(($post->get('invoice_status') == 'deposited' || $post->get('invoice_status') == 'approved') && $post->get('fraud_status') == 'pass')
				{
					$subscription->published = 1;
				}
				break;

			case 'FRAUD_STATUS_CHANGED':
				$subscription->published = 0;
				if($post->get('fraud_status') == 'pass')
				{
					$subscription->published = 1;
				}
				break;

			case 'INVOICE_STATUS_CHANGED':
				$subscription->published = 0;
				if($post->get('invoice_status') == 'deposited' || $post->get('invoice_status') == 'approved')
				{
					$subscription->published = 1;
				}
				break;

			case 'REFUND_ISSUED':
				$subscription->published = 0;
				break;

			case 'RECURRING_INSTALLMENT_SUCCESS':
				$subscription->price = $this->get_amount();
				$subscription->add_new($plan, $this->get_gateway_id());
				break;

			case 'SHIP_STATUS_CHANGED':
			case 'RECURRING_INSTALLMENT_FAILED':
			case 'RECURRING_STOPPED':
			case 'RECURRING_COMPLETE':
			case 'RECURRING_RESTARTED':
				break;

			// Not INS but direct return
			default:
				if($post->get('credit_card_processed') == 'Y')
				{
					$subscription->published = 1;
				}
				break;
		}

		return TRUE;
	}

	function pay($amount, $name, $subscription, $plan)
	{
		$user = JFactory::getUser();

		if(!$this->params->get('vendor'))
		{
			$this->setError(JText::_("CO_NOT_ALL_SET"));

			return FALSE;
		}

		$param['sid']  = $this->params->get('vendor');
		$param['mode'] = '2CO';

		$param['li_0_type']       = 'product';
		$param['li_0_name']       = $name;
		$param['li_0_price']      = $amount;
		$param['li_0_quantity']   = '1';
		$param['li_0_tangible']   = 'N';
		$param['li_0_product_id'] = $plan->id;


		$param['lang']               = $this->params->get('lang', 'en');
		$param['demo']               = $this->params->get('demo', 'N');
		$param['user_id']            = $user->get('id');
		$param['currency_code']      = $this->params->get('currency', 'USD');
		$param['merchant_order_id']  = $subscription->id;
		$param['x_receipt_link_url'] = $this->_get_return_url($subscription->id);

		if($this->params->get('recurred'))
		{
			$param['li_0_duration']   = $this->params->get('recurred_period', 'Forever');
			$param['li_0_recurrence'] = $plan->days . ' ' . ucfirst(preg_replace('/s$/', '', $plan->days_type));

			if($plan->total > $amount)
			{
				$param['li_0_price']       = $plan->total;
				$param['li_0_startup_fee'] = $amount;
			}
		}

		$url = 'https://www.2checkout.com/checkout/purchase?' . http_build_query($param);

		JFactory::getApplication()->redirect($url);
	}

	function get_plan_id()
	{
		//$post = JFactory::getApplication()->input;
		//return $post->get('vendor_order_id', $post->get('merchant_order_id'));
		return JFactory::getApplication()->input->getInt('cart_order_id');
	}

	function get_user_id()
	{
		return JFactory::getApplication()->input->getInt('user_id');
	}

	function get_amount()
	{
		$post = JFactory::getApplication()->input;

		return $post->get('total', $post->get('item_list_amount_1'));
	}

	function get_gateway_id()
	{
		$post = JFactory::getApplication()->input;

		return $post->get('order_number', $post->get('invoice_id'));
	}

	function get_subscrption_id($who = NULL)
	{
		// If it is IPN asks for subscription ID work as usual.
		if($who == 'NOTIFY_URL')
		{
			$post = JFactory::getApplication()->input;

			return $post->get('vendor_order_id', $post->get('merchant_order_id'));
		}

		return parent::get_subscrption_id($who);
	}
}