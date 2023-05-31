<?php

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.menu');

class JoomsubscriptionGatewayGaranti extends JoomsubscriptionGateway
{
	public function accept(&$subscription, $plan)
	{
		$this->log('Start check Garanti', $_POST);

		$post = JFactory::getApplication()->input->post;

		if($post->get('response') == 'Error')
		{
			$this->setError('Status: ' . $post->get('mdstatus') . ', Message: ' . $post->getString('mderrormessage'));

			return FALSE;
		}

		if(!$this->_validate())
		{
			$this->setError(JText::_('GR_CANNOT_VERYFY'));
			$this->log('Cannot verify garanti');

			return FALSE;
		}

		$subscription->published = 0;

		if($post->get('response') == 'Approved')
		{
			$subscription->published = 1;
		}

		$status = $post->get("mdstatus");
		switch($status)
		{
			case 0:
				$this->log('Unknown status');
				break;
			case 1:
				$subscription->gateway_id = $this->get_gateway_id();
				$subscription->published  = 1;
				break;
			default:
				$subscription->note = $post->getString('mderrormessage', $post->getString('errmsg'));
		}

		JoomsubscriptionHelper::activateSubscription($subscription, $plan);
		$subscription->store();

		JoomsubscriptionHelper::redirect($plan, $subscription->published);

		return TRUE;
	}

	public function pay($amount, $name, $subscription, $plan)
	{
		if(!$this->params->get('merchant_id') || !$this->params->get('terminal_id'))
		{
			$this->setError(JText::_('GR_NOTALL'));

			return FALSE;
		}

		$default_lang = $this->params->get('lang');
		$lang         = strtolower(substr(JFactory::getLanguage()->getTag(), 0, 2));
		if(in_array($lang, array('tr', 'en', 'ro')))
		{
			$default_lang = $lang;
		}

		$param = Array(
			"secure3dsecuritylevel" => $this->params->get('level'),
			"customeremailaddress"  => JFactory::getUser($subscription->user_id)->get('email'),
			"txninstallmentcount"   => $this->params->get('installment', ''),
			"terminalprovuserid"    => 'PROVOOS',
			"terminalmerchantid"    => $this->params->get('merchant_id'),
			"customeripaddress"     => $_SERVER['REMOTE_ADDR'],
			"txncurrencycode"       => $this->params->get('currency'),
			"terminaluserid"        => $subscription->user_id,
			"txntimestamp"          => time(),
			"refreshtime"           => $this->params->get('refresh', 5),
			"companyname"           => $this->params->get('company'),
			"apiversion"            => 'v0.01',
			"terminalid"            => $this->params->get('terminal_id'),
			"successurl"            => $this->_get_notify_url($subscription->id),
			"txnamount"             => $amount . '00',
			"errorurl"              => $this->_get_notify_url($subscription->id),
			"txntype"               => 'sales',
			"orderid"               => $this->params->get('order_ref').$subscription->id,
			"lang"                  => strtolower($default_lang),
			"mode"                  => $this->params->get('demo') ? 'DEMO' : 'PROD',
		);

		$SecurityData = strtoupper(sha1($this->params->get('provision_pass') . '0' . $this->params->get('terminal_id')));

		$hash = $param['terminalid'] .
			$param['orderid'] .
			$param['txnamount'] .
			$param['successurl'] .
			$param['errorurl'] .
			$param['txntype'] .
			$param['txninstallmentcount'] .
			$this->params->get('store_key') .
			$SecurityData;

		$param['secure3dhash'] = strtoupper(sha1($hash));

		$url = 'https://sanalposprov.garanti.com.tr/servlet/gt3dengine';
		if($this->params->get('demo'))
		{
			$url = 'https://sanalposprovtest.garanti.com.tr/servlet/gt3dengine';
		}

		/*
		$c = JText::_('GR_WIT_TRANS');
		$c .= '<form action="' . $url . '" method="post" name="gb-form" id="gb-form">';
		foreach($param as $name => $value)
		{
			$c .= '<input type="hidden" name="' . $name . '" value="' . $value . '" />';
		}
		$c .= '<button>Go</button></form>';
		$c .= '<script>document.getElementById("gb-form").submit();</script>';

		echo $c;
		JFactory::getApplication()->close();
		*/

		JFactory::getApplication()->redirect($url . '?' . http_build_query($param));
	}

	public function get_subscrption_id($who = NULL)
	{
		$app = JFactory::getApplication();

		return preg_replace('/[^0-9]/iU', '', $app->input->get('em_id', $app->input->get('orderid', $app->input->get('oid'))));
	}

	public function get_gateway_id()
	{
		return JFactory::getApplication()->input->get('transid');
	}

	private function _validate()
	{
		$post        = JFactory::getApplication()->input->post;
		$hash_params = $post->getString('hashparams');
		$isValidHash = FALSE;

		if($hash_params !== NULL && $hash_params !== "")
		{
			$digestData = "";
			$paramList  = explode(":", $hash_params);

			foreach($paramList as $param)
			{
				$digestData .= $post->getString(strtolower($param) . '');
			}

			$digestData .= $this->params->get('store_key');
			$hashCalculated = base64_encode(pack('H*', sha1($digestData)));

			if($post->getString('hash') == $hashCalculated)
			{
				$isValidHash = TRUE;
			}
		}

		return $isValidHash;
	}
}