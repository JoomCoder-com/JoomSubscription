<?php
/**
 * Card Not Present Test Account
 * API Login ID: 2Jfv4PZ6WgPG
 * Transaction Key: 9C8Dc46mqtf9BA6D
 */
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.menu');

class JoomsubscriptionGatewayPayu_in extends JoomsubscriptionGateway
{

	function accept(&$subscription, $plan)
	{
		$this->log('Start check PayU India');

		$post = JFactory::getApplication()->input;

		$string = sprintf("%s|%s|||||||||||%s|%s|%s|%s|%s|%s",
			$this->params->get('salt'), $post->get('status'), $post->get('email'), $post->get('firstname'),
			$post->get('productinfo'), $post->get('amount'), $post->get('txnid'), $post->get('merchant')
		);

		$this->log('Accepted values', $_POST);

		if(strtoupper(hash('sha512', $string)) != strtoupper($post->get('hash')))
		{
			$this->log("Does not match");

			return FALSE;
		}

		$subscription->gateway_id = $this->get_gateway_id();
		$subscription->published = ($post->get('status') == 'SUCCESS' ? 1 : 0);

		return TRUE;
	}

	function pay($amount, $name, $subscription, $plan)
	{

		if(!$this->params->get('merchant') || !$this->params->get('salt'))
		{
			$this->setError(JText::_('PU_REQUIREDPARAMS'));

			return FALSE;
		}


		$user = JFactory::getUser();

		$param['key']         = $this->params->get('merchant');
		$param['txnid']       = $subscription->id;
		$param['amount']      = $amount;
		$param['productinfo'] = $name;
		$param['firstname']   = $user->get('name');
		$param['email']       = $user->get('email');

		$param['hash'] = hash('sha512', implode('|', $param) . '|||||||||||' . $this->params->get('salt'));


		if($subscription->invoice_id)
		{
			$model = new JoomsubscriptionModelsEmInvoiceTo();
			$data  = $model->getText(JFactory::getApplication()->input->getInt('id'));

			$param['address1'] = $data->fields->get('address');
			$param['zipcode']  = $data->fields->get('zip');
			$param['city']     = $data->fields->get('city');
			$param['state']    = $data->fields->get('state');
			$param['country']  = $data->fields->get('country');
		}

		$param['phone'] = '9999999999';
		$param['surl']  = $this->_get_notify_url($subscription->id);
		$param['furl']  = $this->_get_return_url($subscription->id);
		$param['curl']  = $this->_get_return_url($subscription->id);
		$param['pg']    = $this->params->get('pg');

		$url = 'https://secure.payu.in/_payment?' . http_build_query($param);

		JFactory::getApplication()->redirect($url);
	}

	function get_gateway_id()
	{
		$post = JFactory::getApplication()->input;

		return trim($post->get('mihpayid'));
	}

	function get_subscrption_id($who)
	{
		$post = JFactory::getApplication()->input;

		return $post->getInt('em_id', $post->getInt('txnid'));
	}
}