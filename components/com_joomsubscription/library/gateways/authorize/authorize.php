<?php
/**
 * Card Not Present Test Account
 * API Login ID: 2Jfv4PZ6WgPG
 * Transaction Key: 9C8Dc46mqtf9BA6D
 */
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.menu');

class JoomsubscriptionGatewayAuthorize extends JoomsubscriptionGateway
{

	function accept(&$subscription, $plan)
	{
		$post = JFactory::getApplication()->input->post;

		$md5 = md5($this->params->get('md5hash') . $this->params->get('x_login') . $post->get('x_trans_id') . $post->get('x_amount'));
		if(strtoupper($md5) != strtoupper($post->get('x_MD5_Hash')))
		{
			$this->setError(JText::_('EMR_CANNOT_VERYFY'));
			$this->log('Cannot verify', $_POST);

			return FALSE;
		}

		if($post->get('x_response_code') == 3)
		{
			$this->setError(JText::_('A_TRANS_ERROR'));

			return FALSE;
		}

		$subscription->gateway_id = $this->get_gateway_id();
		if($post->get('x_response_code') == 1)
		{
			$subscription->published = 1;
		}

		return TRUE;
	}

	function pay($amount, $name, $subscription, $plan)
	{
		$user = JFactory::getUser();

		if(!$this->params->get('x_login') || !$this->params->get('transaction'))
		{
			$this->setError(JText::_('A_ERR_NOLOGIN'));

			return FALSE;
		}

		$param['x_fp_sequence']   = $subscription->id;
		$param['x_invoice_num']   = $plan->id . '-' . $subscription->id;
		$param['x_fp_timestamp']  = time();
		$param['x_amount']        = $amount;
		$param['x_login']         = $this->params->get('x_login');
		$param['x_currency_code'] = $this->params->get('currency');
		$param['x_test_request']  = $this->params->get('demo');
		$param['x_show_form']     = 'PAYMENT_FORM';
		$param['x_method']        = 'CC';
		$param['x_email']         = $user->get('email');
		$param['x_cust_id']       = $user->get('id');
		$param['x_description']   = $name;
		$param['x_version']       = '3.1';

		$param['x_relay_url']           = $this->_get_notify_url($subscription->id);
		$param['x_relay_response']      = 'TRUE';
		$param['x_relay_always']        = 'TRUE';
		$param['x_receipt_link_method'] = 'LINK';
		$param['x_receipt_link_text']   = JText::_('A_AUTHORIZE_RETRNTOWEBSITE');
		$param['x_receipt_link_url']    = $this->_get_return_url($subscription->id);
		$param['x_cancel_url']          = $this->_get_return_url($subscription->id);

		$hash = $param['x_login'] . "^" . $param['x_fp_sequence'] . "^" . $param['x_fp_timestamp'] . "^" . $param['x_amount'] . "^".$param['x_currency_code'];

		if(function_exists('hash_hmac'))
		{
			$param['x_fp_hash'] = hash_hmac("md5", $hash, $this->params->get('transaction'));
		}
		else
		{
			$param['x_fp_hash'] = bin2hex(mhash(MHASH_MD5, $hash, $this->params->get('transaction')));
		}

		$url = 'https://secure.authorize.net/gateway/transact.dll?';
		if($this->params->get('demo') == 1)
		{
			$url = 'https://test.authorize.net/gateway/transact.dll?';
		}
		$url .= http_build_query($param);

		JFactory::getApplication()->redirect($url);
	}

	function get_subscrption_id($who)
	{
		return JFactory::getApplication()->input->getInt('em_id', JFactory::getApplication()->input->getInt('x_invoice_num'));
	}

	function get_plan_id()
	{
		$ids = explode('-', JFactory::getApplication()->input->getInt('x_invoice_num'));

		return $ids[0];
	}

	function get_user_id()
	{
		return JFactory::getApplication()->input->getInt('x_cust_id');
	}

	function get_amount()
	{
		return JFactory::getApplication()->input->get('x_amount');
	}

	function get_gateway_id()
	{
		return JFactory::getApplication()->input->get('x_trans_id');
	}
}
