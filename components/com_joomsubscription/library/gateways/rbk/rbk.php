<?php
/**
 * Joomsubscription Payment Plugin by JoomCoder
 * a plugin for Joomla! 1.7 CMS (http://www.joomla.org)
 * Author Website: https://www.joomcoder.com/
 * @copyright Copyright (C) 2012 JoomCoder (https://www.joomcoder.com/). All rights reserved.
 * @license   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.menu');

class JoomsubscriptionGatewayRbk extends JoomsubscriptionGateway
{

	function accept(&$subscription, $plan)
	{
		$post = JFactory::getApplication()->input;

		$key[] =  $this->params->get('eshopid');
		$key[] = $post->get('orderId');
		$key[] = $post->get('serviceName');
		$key[] = $post->get('eshopAccount');
		$key[] = $post->get('recipientAmount');
		$key[] = $post->get('recipientCurrency');
		$key[] = $post->get('paymentStatus');
		$key[] = $post->get('userName');
		$key[] = $post->get('userEmail');
		$key[] = $post->get('paymentData');
		$key[] = $this->params->get('secret');

		$hash = strtolower(md5(implode('::', $key)));

		if($hash != strtolower($post->get('hash')))
		{
			$this->setError(JText::_('EMR_CANNOT_VERYFY'));
			$this->log('RBK: Verification failed', $_POST);

			return FALSE;
		}

		$subscription->gateway_id = $this->get_gateway_id();
		$subscription->published = $post->get('paymentStatus') == 5 ? 1 : 0;

		return true;

	}

	function pay($amount, $name, $subscription, $plan)
	{
		if(!$this->params->get('eshopid'))
		{
			$this->setError(JText::_("RBK_NOT_ALL_SET"));

			return FALSE;
		}
		$user = JFactory::getUser();

		$param['eshopId'] = $this->params->get('eshopid');
		$param['orderId'] = $subscription->id;
		$param['serviceName'] = $name;
		$param['recipientAmount'] = $amount;
		$param['recipientCurrency'] = $this->params->get('currency', 'RUR');
		$param['user_email'] = $user->get('email');
		$param['successUrl'] = $this->_get_return_url($subscription->id);
		$param['failUrl'] = $this->_get_return_url($subscription->id);
		$param['language'] = $this->params->get('lang', 'ru');

		$url = 'https://rbkmoney.ru/acceptpurchase.aspx?' . http_build_query($param);

		JFactory::getApplication()->redirect($url);
	}

	function get_gateway_id()
	{
		$post = JFactory::getApplication()->input;

		return $post->get('paymentId');
	}

	function get_subscrption_id($who = null)
	{
		$app = JFactory::getApplication();

		return $app->input->get('orderId', $app->input->get('em_id'));
	}
}
