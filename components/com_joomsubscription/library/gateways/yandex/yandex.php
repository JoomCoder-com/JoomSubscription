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

class JoomsubscriptionGatewayYandex extends JoomsubscriptionGateway
{
	function accept(&$subscription, $plan)
	{
		$post    = JFactory::getApplication()->input->post;
		$gateway = $this->get_gateway_id();
		$date    = JDate::getInstance()->toISO8601();

		$subscription->gateway_id = $gateway;

		if($post->get('action') == 'checkOrder')
		{
			$this->log('Start CheckOrder');
			if($this->_checkHash() == FALSE)
			{
				echo '<?xml version="1.0" encoding="UTF-8"?><checkOrderResponse performedDatetime="' . $date . '" code="1" invoiceId="' .
					$gateway . '" shopId="' . $this->params->get('shopId') . '" message="Проверка подписи MD5 провалена!"/>';
			}
			else
			{
				echo '<?xml version="1.0" encoding="UTF-8"?><checkOrderResponse performedDatetime="' . $date . '" code="0" invoiceId="' .
					$gateway . '" shopId="' . $this->params->get('shopId') . '"/>';
			}
		}

		if($post->get('action') == 'cancelOrder')
		{
			$this->log('Start CancelOrder');
			$subscription->published = 0;

			echo '<?xml version="1.0" encoding="UTF-8"?><cancelOrderResponse performedDatetime="' . $date . '" code="0" invoiceId="' .
				$gateway . '" shopId="' . $this->params->get('shopId') . '"/>';
		}

		if($post->get('action') == 'paymentAviso')
		{

			$this->log('Start paymentAviso');
			if($this->_checkHash() == FALSE)
			{
				echo '<?xml version="1.0" encoding="UTF-8"?><paymentAvisoResponse performedDatetime="' . $date . '" code="1" invoiceId="' .
					$gateway . '" shopId="' . $this->params->get('shopId') . '" message="Проверка подписи MD5 провалена!"/>';
			}
			else
			{
				echo '<?xml version="1.0" encoding="UTF-8"?><paymentAvisoResponse performedDatetime="' . $date . '" code="0" invoiceId="' .
					$gateway . '" shopId="' . $this->params->get('shopId') . '"/>';
				$subscription->published = 1;
			}
		}

		return TRUE;
	}

	private function _checkHash()
	{
		$hash = ['action', 'orderSumAmount', 'orderSumCurrencyPaycash', 'orderSumBankPaycash', 'shopId', 'invoiceId', 'customerNumber'];
		$post = JFactory::getApplication()->input;

		foreach($hash AS $h)
		{
			if($h == 'shopId')
			{
				$array[] = $this->params->get('shopId');
			}
			else
			{
				$array[] = $post->get($h);
			}
		}
		$array[] = $this->params->get('shopPassword');

		$string = implode(';', $array);
		if(strtoupper($post->get('md5')) == strtoupper(md5($string)))
		{
			return TRUE;
		}
		$this->log('Hash fail: ' . strtoupper($post->get('md5')) . ' - ' . strtoupper(md5($string)) . ' - ' . $string);

		return FALSE;

	}

	function pay($amount, $name, $subscription, $plan)
	{
		$user = JFactory::getUser();
		$post = JFactory::getApplication()->input;

		if(!$this->params->get('shopId') || !$this->params->get('scid'))
		{
			$this->setError(JText::_("YA_NO_PARAM"));

			return FALSE;
		}

		$param['shopId']         = $this->params->get('shopId');
		$param['scid']           = $this->params->get('scid');
		$param['sum']            = $amount;
		$param['customerNumber'] = $user->get('id');
		$param['orderNumber']    = $subscription->id;
		$param['shopSuccessURL'] = $this->_get_return_url($subscription->id);
		$param['shopFailURL']    = $this->_get_return_url($subscription->id);
		$param['cps_email']      = $user->get('email');
		$param['paymentType']    = $post->get('ya_type');

		if($this->params->get('recurred'))
		{
		}

		$url = 'https://money.yandex.ru/eshop.xml?';

		if($this->params->get('demo'))
		{
			$url = 'https://demomoney.yandex.ru/eshop.xml?';
		}

		$url .= http_build_query($param);

		JFactory::getApplication()->redirect($url);
	}

	function get_gateway_id()
	{
		$post = JFactory::getApplication()->input;

		return $post->get('invoiceId');
	}

	function get_subscrption_id($who = NULL)
	{
		$post = JFactory::getApplication()->input;

		return $post->get('orderNumber');
	}
}