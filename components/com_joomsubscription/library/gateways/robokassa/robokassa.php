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

class JoomsubscriptionGatewayRobokassa extends JoomsubscriptionGateway
{
	function accept(&$subscription, $plan)
	{
		$post = JFactory::getApplication()->input;

		$key[] = $post->get('OutSum');
		$key[] = $post->getInt('InvId');
		$key[] = $this->params->get('merpas2');

		$hash = strtoupper(md5(implode(':', $key)));

		if($hash != strtoupper($post->get('SignatureValue')))
		{
			$this->setError(JText::_('EMR_CANNOT_VERYFY'));
			$this->log('Robokassa: Verification failed', $_POST);

			return FALSE;
		}

		echo 'OK'.$post->get('InvId');

		$subscription->gateway_id = $this->get_gateway_id();
		$subscription->published = 1;

		return true;
	}

	function pay($amount, $name, $subscription, $plan)
	{
		if(!$this->params->get('shopid'))
		{
			$this->setError(JText::_("RK_NOT_ALL_SET"));

			return FALSE;
		}

		$InvId = $subscription->id;
		if((int)$this->params->get('increase', 0) > 0)
		{
			//$InvId = $InvId * (int)$this->params->get('increase', 0);
		}
		$param['MerchantLogin'] = $this->params->get('shopid');
		$param['OutSum']        = $amount;
		$param['InvId']         = $InvId;
		$param['InvDesc']       = $name;
		//$param['IncCurrLabel']  = $this->params->get('curr');
		$param['Email']         = JFactory::getUser()->get('email');
		$param['Culture']       = $this->params->get('lang');

		$param['SignatureValue'] = md5($param['MerchantLogin'].':'.$param['OutSum'].':'.$param['InvId'].':'.$this->params->get('merpas1'));

		$url = 'https://auth.robokassa.ru/Merchant/Index.aspx?' . http_build_query($param);

		if($this->params->get('test_mode', 0))
		{
			$url = 'http://test.robokassa.ru/Index.aspx?' . http_build_query($param);
		}

		JFactory::getApplication()->redirect($url);
	}

	function get_gateway_id()
	{
		return time();
	}

	function get_subscrption_id($who = NULL)
	{
		$app = JFactory::getApplication();

		$id = $app->input->getInt('InvId');

		if((int)$this->params->get('increase', 0) > 0)
		{
			$id = $app->input->getInt('InvId') / (int)$this->params->get('increase', 0);
		}

		return $id;
	}
}