<?php
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.menu');

class JoomsubscriptionGatewayFuturePay extends JoomsubscriptionGateway
{

	function accept(&$subscription, $plan)
	{
		//http://www.worldpay.com/support/kb/bg/htmlredirect/rhtml5904.html
		$post = JFactory::getApplication()->input;

		if($post->get('futurePayStatusChange'))
		{
			return;
		}

		$this->log('Worldpay: start', $_POST);

		if($subscription->gateway_id && ($subscription->gateway_id != $this->get_gateway_id()))
		{
			$this->log('Worlpay: create new', $this->get_gateway_id());
			$subscription->add_new($plan, $this->get_gateway_id());
		}
		$subscription->gateway_id = $this->get_gateway_id();

		if($post->get('transStatus') == 'Y')
		{
			$subscription->published = 1;
			if($post->getString('callbackPW') != $this->params->get('password'))
			{
				$this->log('Worlpay: catnot verify', $post->getString('callbackPW'));
				$subscription->published = 0;
			}
			if(substr($post->get('wafMerchMessage'), 0, 4) == 'waf.')
			{
				$this->log('Worlpay: catnot verify', $post->get('callbackPW'));
				$subscription->published = 0;
			}
		}

		return TRUE;
	}

	function pay($amount, $name, $subscription, $plan)
	{
		$user = JFactory::getUser();

		if(!$this->params->get('installID'))
		{
			$this->setError(JText::_("WP_NOT_ALL_SET"));

			return FALSE;
		}

		$param['instId']   = $this->params->get('installID');
		$param['cartId']   = $subscription->id;
		$param['amount']   = $amount;
		$param['testMode'] = $this->params->get('demo');
		$param['currency'] = $this->params->get('currency');
		$param['name']     = $user->get('name');
		$param['email']    = $user->get('email');
		$param['descr']    = $name;
		$param['lang']     = $this->params->get('lang', 'en-GB');

		$param['MC_callback'] = $this->_get_notify_url($subscription->id);

		if($this->params->get('hideCurrency'))
		{
			$param['hideCurrency'] = 1;
		}

		if($this->params->get('recurring'))
		{
			switch($plan->days_type)
			{
				case "days":
					$multi = 1;
					break;
				case "weeks":
					$multi = 2;
					break;
				case "months":
					$multi = 3;
					break;
				case "years":
					$multi = 4;
					break;
			}
			$param['noOfPayments']   = $this->params->get('noOfPayments', 0);
			$param['futurePayType']  = "regular";
			$param['normalAmount']   = $plan->total;
			$param['intervalMult']   = $plan->days;
			$param['intervalUnit']   = $multi;
			$param["startDelayMult"] = $plan->days;
			$param["startDelayUnit"] = $multi;
			$param['option']         = 0;
			if($plan->total > $plan->price)
			{
				$param['initialAmount'] = $plan->price;
			}
		}

		$url = 'https://secure.worldpay.com/wcc/purchase?';
		if($this->params->get('demo') == 100)
		{
			$param['name'] = 'AUTHORISED';
			$url           = 'https://secure-test.worldpay.com/wcc/purchase?';
		}
		$url .= http_build_query($param);

		JFactory::getApplication()->redirect($url);
	}

	function get_plan_id()
	{
		return JFactory::getApplication()->input->getInt('cartId');
	}

	function get_user_id()
	{
		return JFactory::getApplication()->input->getInt('MC_userid');
	}

	function get_amount()
	{
		return JFactory::getApplication()->input->get('amount');
	}

	function get_gateway_id()
	{
		$post = JFactory::getApplication()->input;

		$out = $post->get('transId');

		if($this->params->get('recurring'))
		{
			$out = $post->get('futurePayId') . '-' . $post->get('transId');
		}

		return $out;
	}
}