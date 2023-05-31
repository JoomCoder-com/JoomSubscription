<?php
/**
 * Card Not Present Test Account
 * API Login ID: 2Jfv4PZ6WgPG
 * Transaction Key: 9C8Dc46mqtf9BA6D
 */
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.menu');

class JoomsubscriptionGatewaySkrill extends JoomsubscriptionGateway
{

	function accept(&$subscription, $plan)
	{
		$this->log('Start check Skrill');

		$post = JFactory::getApplication()->input;

		$md5 = md5(
			$post->get('merchant_id') .
			$post->get('transaction_id') .
			strtoupper(md5($this->params->get('secret'))) .
			$post->get('mb_amount') .
			$post->get('mb_currency') .
			$post->get('status')
		);

		$this->log('Accepted values', $_POST);

		if(strtoupper($md5) != strtoupper($post->get('md5sig')))
		{
			$this->log("Does not match");

			return FALSE;
		}


		$subscription->gateway_id = $this->get_gateway_id();

		if($post->getInt('status') == 2)
		{
			$subscription->published = 1;
		}
		else
		{
			$subscription->published = 0;
		}

		return TRUE;
	}

	function pay($amount, $name, $subscription, $plan)
	{

		if(!$this->params->get('pay_to_email'))
		{
			$this->setError(JText::_('SK_ERR_NOEMAIL'));

			return FALSE;
		}

		$user = JFactory::getUser();

		$param['pay_to_email']        = $this->params->get('pay_to_email');
		$param['transaction_id']      = $subscription->id;
		$param['return_url']          = $this->_get_return_url($subscription->id);
		$param['return_url_text']     = JText::_('EM_SKRILL_RETURN_TO_HOME');
		$param['cancel_url']          = $this->_get_return_url($subscription->id);
		$param['amount']              = $amount;
		$param['detail1_description'] = JText::_('EM_SCRILL_PAYDESCR');
		$param['detail1_text']        = $name;


		$param['status_url']     = $this->_get_notify_url($subscription->id);
		$param['language']       = strtoupper(substr(JFactory::getLanguage()->getTag(), 0, 2));
		$param['pay_from_email'] = $user->get('email');
		$param['currency']       = $this->params->get('currency', 'USD');

		$url = 'https://www.moneybookers.com/app/payment.pl?' . http_build_query($param);
		if($this->params->get('demo') == 1)
		{
			$url = 'https://www.moneybookers.com/app/test_payment.pl.?' . http_build_query($param);
		}

		JFactory::getApplication()->redirect($url);
	}

	function get_gateway_id()
	{
		$post = JFactory::getApplication()->input;

		return trim($post->get('mb_transaction_id'));
	}

	function get_subscrption_id($who)
	{
		$post = JFactory::getApplication()->input;

		return $post->getInt('em_id') ? $post->getInt('em_id') : $post->getInt('cid', $post->getInt('transaction_id'));
	}
}