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

class JoomsubscriptionGatewayPaypal_ec extends JoomsubscriptionGateway
{
	function accept(&$subscription, $plan)
	{
		$this->log('Start check PayPal');

		if(!$this->_IPNcheck())
		{
			$this->setError(JText::_('EMR_CANNOT_VERYFY'));
			$this->log('PayPal: Verification failed', $_POST);

			return FALSE;
		}

		$post = JFactory::getApplication()->input->post;

		$gateway = $this->get_gateway_id();

		switch($post->get('txn_type'))
		{
			/*
			 case "subscr_signup" :
				if ($post->get('mc_amount1') !== NULL) {
					if ($post->get('mc_amount1') > 0) {
						break;
					} else {
						$post->set('payment_status', 'Completed');
					}
				}
				break;
			*/

			case "subscr_payment" :
				$subscription->add_new($plan, $gateway, $post->get('amount3', $subscription->price));
			case "send_money":
			case "web_accept":
			case "cart":
			case "express_checkout":
				$subscription->gateway_id = $gateway;
				switch($post->get('payment_status'))
				{
					case 'Processed' :
					case 'Completed' :
						$subscription->published = 1;
						break;

					case 'Refunded' :
						$subscription->published = 0;
						break;
				}
				if($post->get('payment_status') == 'Pending' && $post->get('pending_reason') != 'PaymentReview')
				{
					$subscription->published = 1;
				}
				break;

			case "new_case" :
				$subscription->published = 0;
				break;

			case "adjustment" :
				$subscription->published = 1;
				break;

			case 'recurring_payment':
			case "subscr_failed" :
			case "subscr_eot" :
			case "subscr_cancel" :
			default:
				// TODO may be do somethign with this.
				return FALSE;
				break;
		}
		$this->log('End paypal check', $subscription);

		return TRUE;
	}

	function pay($amount, $name, $subscription, $plan)
	{
		include_once 'default.php';

		return TRUE;
	}

	private function _IPNcheck()
	{
		$raw_post_data  = file_get_contents('php://input');
		$raw_post_array = explode('&', $raw_post_data);
		$myPost         = array();
		foreach($raw_post_array as $keyval)
		{
			$keyval = explode('=', $keyval);
			if(count($keyval) == 2)
			{
				// Since we do not want the plus in the datetime string to be encoded to a space, we manually encode it.
				if($keyval[0] === 'payment_date')
				{
					if(substr_count($keyval[1], '+') === 1)
					{
						$keyval[1] = str_replace('+', '%2B', $keyval[1]);
					}
				}
				$myPost[$keyval[0]] = urldecode($keyval[1]);
			}
		}
		// Build the body of the verification post request, adding the _notify-validate command.
		$req                     = 'cmd=_notify-validate';
		$get_magic_quotes_exists = FALSE;
		if(function_exists('get_magic_quotes_gpc'))
		{
			$get_magic_quotes_exists = TRUE;
		}
		foreach($myPost as $key => $value)
		{
			if($get_magic_quotes_exists == TRUE && get_magic_quotes_gpc() == 1)
			{
				$value = urlencode(stripslashes($value));
			}
			else
			{
				$value = urlencode($value);
			}
			$req .= "&$key=$value";
		}


		$request = curl_init();
		$options = array(
			CURLOPT_URL            => 'https://ipnpb.' . ($this->params->get('sandbox') == 'sandbox' ? 'sandbox.' : NULL) . 'paypal.com/cgi-bin/webscr',
			CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
			CURLOPT_POST           => 1,
			CURLOPT_POSTFIELDS     => $req,
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_FORBID_REUSE   => 1,
			CURLOPT_SSL_VERIFYPEER => 1,
			CURLOPT_SSLVERSION     => 6,
			CURLOPT_SSL_VERIFYHOST => 2,
			CURLOPT_CAINFO         => __DIR__ . '/cacert.pem',
			CURLOPT_HTTPHEADER     => array(
				"Content-Type: application/x-www-form-urlencoded",
				"User-Agent: Joomsubscription-IPN-Validator",
				"Content-Length: " . strlen($req)
			),
			CURLOPT_CONNECTTIMEOUT => 30
		);
		
		curl_setopt_array($request, $options);

		$this->log('send CURL confirm:', $_POST);

		$response = curl_exec($request);
		$status   = curl_getinfo($request, CURLINFO_HTTP_CODE);
		curl_close($request);

		if(strpos($response, "VERIFIED") !== FALSE)
		{
			$this->log('transaction verified', $response);

			return TRUE;
		}
		else
		{
			$this->log('transaction verification invalid', $response);

			return FALSE;
		}
	}

	function get_plan_id()
	{
		return JFactory::getApplication()->input->get('order_id');
	}

	function get_gateway_id()
	{
		$post = JFactory::getApplication()->input;

		return trim($post->get('subscr_id') . ' ' . $post->get('tx', $post->get('txn_id')));
	}

	function get_amount()
	{
		$post = JFactory::getApplication()->input;

		return (float)$post->get('amount', $post->get('mc_amount3', $post->get('mc_gross')));
	}

	function get_user_id()
	{
		$post = JFactory::getApplication()->input;

		return $post->getInt('cm', $post->getInt('custom'));
	}
}
