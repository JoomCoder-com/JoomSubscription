<?php
defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.menu');

class JoomsubscriptionGatewaytbc extends JoomsubscriptionGateway
{
	public function accept(&$subscription, $plan)
	{
		$response = $this->_getResponse();

		$this->log('TBC: start accept', $response);

		$hash = MD5($response['MERCHANTTRANSACTIONID'] . $response['BANKTRANSACTIONID'] .
			$response['RESULT'] . strtoupper($response['RESULTCODE']) . $response['RRN'] .
			$response['CARDNUMBER'] . $this->params->get('sword'));

		if(strtoupper($response['SIGNATURE']) != strtoupper($hash))
		{
			$this->log('TBC: Verification failed');
			return FALSE;
		}

		$subscription->gateway_id = $this->get_gateway_id();

		$subscription->published = 0;
		if(strtoupper($response['RESULT']) == 'OK')
		{
			$subscription->published = 1;
		}

		echo '<register-payment-response><result><code>1</code><desc>OK</desc></result></register-payment-response>';

		return TRUE;
	}

	public function pay($amount, $name, $subscription, $plan)
	{
		if(!$this->params->get('merchantname'))
		{
			$this->setError(JText::_("TBC_NOT_ALL_SET"));

			return FALSE;
		}

		$amount = $amount * 100;

		$param['mtranzaction'] = $subscription->id;
		$param['currency']     = $this->params->get('currency', 'GEL');
		$param['langcode']     = $this->params->get('lang', 'GE');
		$param['amount']       = $amount;
		$param['hash']         = md5($subscription->id . $this->params->get('currency') . $this->params->get('lang') . $amount . $this->params->get('sword'));

		$url = 'https://payment.geopaysoft.com/result/' . $this->params->get('merchantname') . '/pay.php?' . http_build_query($param);

		JFactory::getApplication()->redirect($url);
	}

	public function get_gateway_id()
	{
		$response = $this->_getResponse();

		return $response['BANKTRANSACTIONID'];
	}

	public function get_subscrption_id($who = NULL)
	{
		$response = $this->_getResponse();

		$db = JFactory::getDbo();
		$db->setQuery("SELECT id FROM #__joomsubscription_subscriptions WHERE id = " . (int)$response['MERCHANTTRANSACTIONID']);

		$id = $db->loadResult();

		if(!$id) {
			echo '<register-payment-response><result><code>1</code><desc>OK</desc></result></register-payment-response>';
		}

		return $response['MERCHANTTRANSACTIONID'];
	}

	private function _getResponse()
	{
		try
		{
			$this->log('TBC: get XML', $_REQUEST['params']);
			$xml = new SimpleXMLElement($_REQUEST['params']);

			$result = array(
				'MERCHANTTRANSACTIONID' => (string)$xml->params->MERCHANTTRANSACTIONID,
				'BANKTRANSACTIONID'     => (string)$xml->params->BANKTRANSACTIONID,
				'RESULT'                => (string)$xml->params->RESULT,
				'RESULTCODE'            => (string)$xml->params->RESULTCODE,
				'RRN'                   => (string)$xml->params->RRN,
				'CARDNUMBER'            => (string)$xml->params->CARDNUMBER,
				'SIGNATURE'             => (string)$xml->params->SIGNATURE,
			);

			$this->log('TBC: XML result', $result);
		}
		catch(Exception $e)
		{
			$this->log('TBC: Bad XML', $_REQUEST);
			exit;
		}

		return $result;
	}
}
