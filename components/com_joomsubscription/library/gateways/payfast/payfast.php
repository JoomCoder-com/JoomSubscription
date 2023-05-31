<?php

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.menu');

class JoomsubscriptionGatewayPayfast extends JoomsubscriptionGateway
{
	public function accept(&$subscription, $plan)
	{
		$this->log('Start check Payfast');

		if(!$this->_validateITN() || !$this->_validateIP($_SERVER['REMOTE_ADDR']))
		{
			$this->setError(JText::_('PF_CANNOT_VERYFY'));

			return FALSE;
		}

		$post = JFactory::getApplication()->input;

		$subscription->gateway_id = $this->get_gateway_id();

		switch($post->get('payment_status'))
		{
			case 'COMPLETE':
				$subscription->published = 1;
				break;
			case 'FAILED':
			case 'PENDING':
			default:
				$subscription->published = 0;
				break;
		}

		echo '';

		return TRUE;
	}

	public function pay($amount, $name, $subscription, $plan)
	{
		$user = JFactory::getUser();

		if(!$this->params->get('merchant_id') || !$this->params->get('merchant_key'))
		{
			$this->setError(JText::_('PF_NO_MERCH'));

			return FALSE;
		}

		$param = Array(
			"merchant_id"   => $this->params->get('merchant_id'),
			"merchant_key"  => $this->params->get('merchant_key'),
			"return_url"    => $this->_get_return_url($subscription->id),
			"cancel_url"    => $this->_get_return_url($subscription->id),
			"notify_url"    => $this->_get_notify_url($subscription->id),
			"email_address" => $user->get('email'),
			"m_payment_id"  => $subscription->id,
			"amount"        => $amount,
			"item_name"     => $name
		);

		$hash = http_build_query($param);
		$hash = md5($hash);

		$param['signature'] = $hash;

		if($this->params->get('email_conf') && filter_var($this->params->get('email_conf'), FILTER_VALIDATE_EMAIL))
		{
			$param["email_confirmation"]   = '1';
			$param["confirmation_address"] = $this->params->get('email_conf');
		}

		$url = 'https://www.payfast.co.za/eng/process?' . http_build_query($param);
		if($this->params->get('sandbox'))
		{
			$url = 'https://sandbox.payfast.co.za/eng/process?' . http_build_query($param);
		}

		JFactory::getApplication()->redirect($url);
	}

	public function get_subscrption_id($who = NULL)
	{
		$app = JFactory::getApplication();

		return $app->input->get('em_id', $app->input->get('m_payment_id'));
	}

	public function get_gateway_id()
	{
		return JFactory::getApplication()->input->get('pf_payment_id');
	}

	private function _validateITN()
	{
		$url = 'https://www.payfast.co.za/eng/query/validate';
		if($this->params->get('sandbox'))
		{
			$url = 'https://sandbox.payfast.co.za/eng/query/validate';
		}

		$REQ = file_get_contents("php://input");
		$ch  = curl_init();

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Joomsubscription/9 (PHP ' . phpversion() . '; Payfast/9.0)');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $REQ);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		curl_setopt($ch, CURLOPT_HTTPHEADER,
			array(
				 "Content-Type: application/x-www-form-urlencoded",
				 "Content-Length: " . strlen($REQ)
			)
		);
		$this->log('send CURL confirm', $REQ);
		$response = curl_exec($ch);
		curl_close($ch);

		if(strpos($response, "VALID") !== FALSE)
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

	private function _validateIP($ip)
	{
		$validHosts = array(
			'www.payfast.co.za',
			'sandbox.payfast.co.za',
			'w1w.payfast.co.za',
			'w2w.payfast.co.za',
		);

		$validIps = array();

		foreach($validHosts as $pfHostname)
		{
			$ips = gethostbynamel($pfHostname);

			if($ips !== FALSE)
			{
				$validIps = array_merge($validIps, $ips);
			}
		}

		// Remove duplicates
		$validIps = array_unique($validIps);

		$this->log("Valid IPs", $validIps);

		if(in_array($ip, $validIps))
		{
			return (TRUE);
		}
		else
		{
			return (FALSE);
		}
	}
}