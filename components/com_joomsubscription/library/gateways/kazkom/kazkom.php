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

require_once __DIR__.'/kkbsign.class.php';

class JoomsubscriptionGatewayKazkom extends JoomsubscriptionGateway
{
	private $xml;

	function accept(&$subscription, $plan)
	{
		echo 0;

		if($this->isError())
		{
			return FALSE;
		}

		$subscription->gateway_id = $this->get_gateway_id();

		$kkb = new KKBSign();
		$kkb->invert();

		$pub_cert = JPATH_ROOT.'/components/com_joomsubscription/library/gateways/kazkom/keys/'.$this->params->get('public');

		$check = $kkb->check_sign64($this->xml->bank->asXml(), $this->xml->bank_sign, $pub_cert);

		if($check != 1)
		{
			$this->log('KK_NOTVERIFIED', $this->xml->asXml());
			return FALSE;
		}

		$subscription->published = 1;

		return TRUE;
	}

	public function pay($amount, $name, $subscription, $plan)
	{
		if(!$this->params->get('mcid') || !$this->params->get('mid') || !$this->params->get('name'))
		{
			$this->setError(JText::_("KK_NOT_ALL_SET"));

			return FALSE;
		}

		$user = JFactory::getUser();
		$kkb  = new KKBSign();
		$kkb->invert();

		$priv_cert = JPATH_ROOT.'/components/com_joomsubscription/library/gateways/kazkom/keys/'.$this->params->get('private');
		if(!$kkb->load_private_key($priv_cert, $this->params->get('pass')))
		{
			$this->setError(JText::_("KK_CANNOTLOADPRIVATE"));

			return FALSE;

		}

		$xml = sprintf('<merchant cert_id="%s" name="%s"><order order_id="%06d" amount="%s" currency="%s"><department merchant_id="%s" amount="%s"/></order></merchant>',
			$this->params->get('mcid'), $this->params->get('name'), $subscription->id, $amount, $this->params->get('currency'), $this->params->get('mid'), $amount);

		$sign = '<merchant_sign type="RSA">' . $kkb->sign64($xml) . '</merchant_sign>';
		$xml  = "<document>" . $xml . $sign . "</document>";

		$param['Signed_Order_B64'] = base64_encode($xml);

		$param['Language'] = $this->params->get('language', 'rus');
		$param['email']    = $user->get('email');
		$param['appendix'] = base64_encode(sprintf('<document><item number="%d" name="%s" quantity="1" amount="%s"/></document>', $subscription->id, $name, $amount));

		$param['BackLink']        = $this->_get_return_url($subscription->id);
		$param['FailureBackLink'] = $this->_get_return_url($subscription->id);
		$param['PostLink']        = $this->_get_notify_url($subscription->id);
		$param['FailurePostLink'] = $this->_get_notify_url($subscription->id);

		if($this->params->get('shop'))
		{
			$param['ShopID'] = $this->params->get('shop');
		}

		if($this->params->get('showusd'))
		{
			$param['ShowCurr'] = 'usd';
		}

		$url = 'https://epay.kkb.kz/jsp/process/logon.jsp';
		if($this->params->get('demo'))
		{
			$url = 'http://3dsecure.kkb.kz/jsp/process/logon.jsp';
		}

		$url .= '?' . http_build_query($param);

		JFactory::getApplication()->redirect($url);
	}

	public function get_gateway_id()
	{
		return (string)$this->xml->bank->results->payment['reference'];
	}

	private function isError()
	{
		$doc = JFactory::getApplication()->input->getString('response');
		$this->loadXml();

		if(!$this->xml instanceof SimpleXMLElement)
		{
			$this->log('Cannot load XML:', $doc);
			return true;
		}

		if(!empty($this->xml->error))
		{
			$this->log('Error: '.$this->xml->error, $doc);
			return true;
		}

		return false;
	}

	private function loadXml()
	{
		$doc = JFactory::getApplication()->input->getString('response');
		$this->xml = simplexml_load_string($doc);
	}
}