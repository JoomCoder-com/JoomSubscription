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

require_once __DIR__ . '/api/includes.php';

class JoomsubscriptionGatewayPaysera extends JoomsubscriptionGateway
{
	function accept(&$subscription, $plan)
	{
		$response = WebToPay::checkResponse($_GET, array(
			'projectid'     => 0,
			'sign_password' => 'd41d8cd98f00b204e9800998ecf8427e',
		));

		$subscription->gateway_id = $this->get_gateway_id();
		echo 'OK';

		if ($response['test'] !== '0') {
			$this->setError(JText::_('EMR_CANNOT_VERYFY'));
			$this->log('Paysera: Testing, real payment was not made', $response);

			return FALSE;
		}
		if ($response['type'] !== 'macro') {
			$this->setError(JText::_('EMR_CANNOT_VERYFY'));
			$this->log('Paysera: Only macro payment callbacks are accepted', $response);

			return FALSE;
		}

		$subscription->published = 1;

		return TRUE;
	}

	function pay($amount, $name, $subscription, $plan)
	{
		$user = JFactory::getUser();
		try {
			$param = array(
				'projectid' => $this->params->get('projectid'),
				'sign_password' => $this->params->get('sign_password'),
				'orderid' => $subscription->id,
				'amount' => str_replace('.', '', $amount),
				'currency' => $this->params->get('currency'),
				'lang' => $this->params->get('lang'),
				'accepturl' => $this->_get_return_url($subscription->id),
				'cancelurl' => $this->_get_return_url($subscription->id),
				'callbackurl' => $this->_get_notify_url($subscription->id),
				'test' => $this->params->get('demo'),
				'p_email' => $user->get('email'),
			);

			if ($subscription->invoice_id) {
				$invoice = new JoomsubscriptionModelsEmInvoiceTo();
				$data = $invoice->getText($subscription->invoice_id);

				$param['p_street'] = $data->get('address');
				$param['p_city'] = $data->get('city');
				$param['p_countrycode'] = $data->get('country');
				$param['p_state'] = $data->get('state');
				$param['p_zip'] = $data->get('zip');
			}

			WebToPay::redirectToPayment($param);
		} catch (WebToPayException $e) {
			$this->setError(JText::_("PS_ERROR") . ': ' . $e->getMessage());
			return FALSE;
		}
	}

	function get_gateway_id()
	{
		$post = JFactory::getApplication()->input;

		return $post->get('orderid');
	}
}