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

class JoomsubscriptionGatewayAlipay extends JoomsubscriptionGateway
{
	function accept(&$subscription, $plan)
	{
		$this->log('Start check AliPay');

		if(!$this->_verifySign($_POST))
		{
			//$this->setError(JText::_('EMR_CANNOT_VERYFY'));
			$this->log('Alipay: Verification of sign failed', $_POST);
			//return FALSE;
		}

		if(!$this->_verifyNotification($_POST))
		{
			$this->setError(JText::_('EMR_CANNOT_VERYFY'));
			$this->log('Alipay: Verification of notification failed', $_POST);

			return FALSE;
		}

		$subscription->gateway_id = $this->get_gateway_id();

		$post = JFactory::getApplication()->input->post;
		if($post->get('trade_status') == 'TRADE_SUCCESS')
		{
			$subscription->published = 1;
		}
		if($post->get('trade_status') == 'TRADE_CLOSED')
		{
			$subscription->published = 0;
		}

		$this->log('End alipay check', $subscription);

		return TRUE;
	}

	function pay($amount, $name, $subscription, $plan)
	{

		$test = Array
		(
			//'option'              => 'com_joomsubscription',
			//'task'                => 'plans.create',
			//'Itemid'              => '1',
			//'processor'           => 'alipay',
			//'em_id'               => '782',
			'discount'            => '0.00',
			'payment_type'        => '1',
			'subject'             => '购买会员资格： 支付宝接口测试 在 技术支持',
			'trade_no'            => '2016063021001004470203636669',
			'buyer_email'         => 'baijianying@gmail.com',
			'gmt_create'          => '2016-06-30 07:55:58',
			'notify_type'         => 'trade_status_sync',
			'quantity'            => '1',
			'out_trade_no'        => '782',
			'seller_id'           => '2088221775408758',
			'notify_time'         => '2016-06-30 07:56:14',
			'body'                => '购买会员资格： 支付宝接口测试 在 技术支持',
			'trade_status'        => 'TRADE_SUCCESS',
			'is_total_fee_adjust' => 'N',
			'total_fee'           => '1.00',
			'gmt_payment'         => '2016-06-30 07:56:14',
			'seller_email'        => 'joomlagate@gmail.com',
			//'price'               => '1.00',
			'buyer_id'            => '2088802935981474',
			'notify_id'           => '1b10453331558fe748c56b1ad6b2f9djmm',
			'use_coupon'          => 'N',
			'sign_type'           => 'MD5',
			'sign'                => '91c252ac7a0928fc86d72df7dbf7ba84'
		);

		if(!$this->params->get('partner'))
		{
			$this->setError(JText::_('AP_ERR_NOPARENT'));

			return FALSE;
		}
		if(!JFile::exists($this->_getCertFile()))
		{
			$this->setError(JText::_('AP_ERR_NOCERT'));

			return FALSE;
		}

		$param = array(
			"service"        => $this->params->get('service'),
			"partner"        => $this->params->get('partner'),
			"return_url"     => $this->_get_return_url($subscription->id),
			"notify_url"     => $this->_get_notify_url($subscription->id),
			"subject"        => $name,
			"body"           => $name,
			"out_trade_no"   => $subscription->id,
			"currency"       => $this->params->get('currency'),
			"_input_charset" => $this->params->get('charset', 'utf-8'),
			"seller_email"   => $this->params->get('email'),
			"payment_type"   => "1",
		);
		switch($this->params->get('service'))
		{
			case "create_partner_trade_by_buyer":

				$param["price"]             = $amount;
				$param["quantity"]          = "1";
				$param["logistics_type"]    = "EXPRESS";
				$param["logistics_fee"]     = "0.00";
				$param["logistics_payment"] = "BUYER_PAY";
				$param["receive_name"]      = JFactory::getUser()->get('username');


				$invoice = new JoomsubscriptionModelsEmInvoiceTo();
				$data    = $invoice->getText($subscription->invoice_id);

				$param['receive_address'] = $data->fields->get('address', 'n/a') . ', ' . $data->fields->get('city') . ', ' . $data->fields->get('country');
				$param['receive_phone']   = $data->fields->get('phone', 'n/a');
				$param['receive_zip']     = $data->fields->get('zip', 'n/a');

				break;
			case "create_direct_pay_by_user":
			case "create_forex_trade":
				$param["total_fee"] = $amount;
				break;
		}


		$param["sign"]      = $this->_getSign($param);
		$param["sign_type"] = 'MD5';

		$url = strtolower($this->params->get('transport')) . "://www.alipay.com/cooperate/gateway.do?";
		if($this->params->get('demo'))
		{
			$url = "https://mapi.alipay.net/gateway.do?";
		}
		JFactory::getApplication()->redirect($url . http_build_query($param));
	}

	function get_gateway_id()
	{
		$post = JFactory::getApplication()->input;

		return trim($post->get('trade_no'));
	}

	private function _getCertUrl()
	{
		return str_replace(JPATH_ROOT . '/', JUri::root(), $this->_getCertFile());
	}

	private function _getCertFile()
	{
		return str_replace('\\', '/', JPATH_ROOT . '/' . ltrim($this->params->get('cert'), '/\\'));
	}

	private function _verifyNotification($array)
	{
		if(empty($array["notify_id"]))
		{
			return FALSE;
		}
		$transport = 'http://notify.alipay.com/trade/notify_query.do?';
		if($this->params->get('transport') == 'https')
		{
			$transport = "https://mapi.alipay.com/gateway.do?service=notify_verify&";
		}
		$transport .= "partner=" . $this->params->get('partner') . "&notify_id=" . $array['notify_id'];

		$curl = curl_init($transport);

		curl_setopt($curl, CURLOPT_HEADER, 0);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, TRUE);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($curl, CURLOPT_CAINFO, $this->_getCertFile());

		$response = curl_exec($curl);
		curl_close($curl);

		if(preg_match("/true$/i", $response))
		{
			return TRUE;
		}
		$this->log('Alipay: verification response', $response);

		return FALSE;
	}

	private function _verifyNotification2($array)
	{
		if(empty($array["notify_id"]))
		{
			return FALSE;
		}

		$transport = 'http://notify.alipay.com/trade/notify_query.do?';
		if($this->params->get('transport') == 'https')
		{
			$transport = "https://mapi.alipay.com/gateway.do?service=notify_verify&";
		}
		$transport .= "partner=" . $this->params->get('partner') . "&notify_id=" . $array['notify_id'];

		$urlarr = parse_url($transport);
		$errno  = "";
		$errstr = "";

		if($urlarr["scheme"] == "https")
		{
			$transports     = "ssl://";
			$urlarr["port"] = "443";
		}
		else
		{
			$transports     = "tcp://";
			$urlarr["port"] = "80";
		}

		$fp = @fsockopen($transports . $urlarr['host'], $urlarr['port'], $errno, $errstr, $time_out);

		if(!$fp)
		{
			$this->log('Alipay: cannot open socket', "$errno - $errstr");

			return FALSE;
		}

		fputs($fp, "POST " . $urlarr["path"] . " HTTP/1.1\r\n");
		fputs($fp, "Host: " . $urlarr["host"] . "\r\n");
		fputs($fp, "Content-type: application/x-www-form-urlencoded\r\n");
		fputs($fp, "Content-length: " . strlen($urlarr["query"]) . "\r\n");
		fputs($fp, "Connection: close\r\n\r\n");
		fputs($fp, $urlarr["query"] . "\r\n\r\n");
		while(!feof($fp))
		{
			$info[] = @fgets($fp, 1024);
		}

		fclose($fp);
		$info = implode(",", $info);

		if(preg_match("/true$/i", $info))
		{
			return TRUE;
		}

		$this->log('Alipay: verification response', $info);

		return FALSE;
	}

	private function _verifySign($array)
	{
		//TODO: Never was able to solve it
		if(strtolower($this->_getSign($array)) == strtolower($array['sign']))
		{
			return TRUE;
		}

		return FALSE;
	}

	private function _getSign($array)
	{
		$array  = $this->_paramsFilter($array);
		$array  = $this->_paramsSort($array);
		$string = $this->_createLinkstring($array);

		return md5($string.$this->params->get('key'));
	}

	private function _createLinkstring($para)
	{
		/*
		while (list ($key, $val) = each ($para)) {
			@$arg.=$key."=".$val."&";
		}
		$prestr = substr($arg,0,count($arg)-2);

		return $prestr;
		*/

		$arg = array();
		foreach($para AS $key => $val)
		{
			$arg[] = "$key=$val";
		}
		$arg = implode("&", $arg);

		if(get_magic_quotes_gpc())
		{
			$arg = stripslashes($arg);
		}

		return $arg;
	}

	private function _paramsFilter($para)
	{
		$para_filter = array();
		while(list ($key, $val) = each($para))
		{
			if($key == "sign" || $key == "sign_type" || $val == "")
				continue;
			else    $para_filter[$key] = (string)$val;
		}

		return $para_filter;
	}

	private function _paramsSort($para)
	{
		ksort($para);
		reset($para);

		return $para;
	}
}