<?php

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.menu');

class JoomsubscriptionGatewaJomsocial extends JoomsubscriptionGateway
{

	public function getButton($plan, $total)
	{
		$out = sprintf('<button type="button" style="width: 210px" class="btn btn-warning"	data-payment-gateway="%s"><small>%s</small></button>',
			$this->type,
			sprintf('<div class="lead" style="margin-bottom:0">%s</div>%s', JText::_('JS_PAYWITHPOINTS'), JText::sprintf('JS_PONTCOUNT', $this->_getuserpoints(JFactory::getUser()->get('id')), $this->_convert($total)))
		);

		return $out;
	}

	private function _convert($total)
	{
		return $total * $this->params->get('convert');
	}

	private function _setuserpoints($user_id, $points)
	{
		require_once JPATH_ROOT . '/components/com_community/libraries/core.php';
		require_once JPATH_ROOT . '/components/com_community/libraries/karma.php';

		if(!class_exists('CFactory'))
		{
			return 0;
		}

		$user = CFactory::getUser($user_id);
		$points += $user->getKarmaPoint();
		$user->_points = $points;
		$user->save();
	}
	private function _getuserpoints($user_id = null)
	{
		require_once JPATH_ROOT . '/components/com_community/libraries/core.php';
		require_once JPATH_ROOT . '/components/com_community/libraries/karma.php';

		if(!class_exists('CFactory'))
		{
			return 0;
		}
		$user = CFactory::getUser($user_id);
		return $user->getKarmaPoint();
	}

	function accept(&$subscription, $plan)
	{
		$app          = JFactory::getApplication();
		$user_points  = $this->_getuserpoints($subscription->user_id);
		$price_points = $this->_convert($subscription->price);

		if($price_points > $user_points)
		{
			$app->enqueueMessage(JText::sprintf('JS_NOTENOUGHTPOINT', $price_points, $user_points), 'warning');
			$app->redirect(JRoute::_('index.php?option=com_joomsubscription&view=empayment&sid='.$plan->id, FALSE));

			return FALSE;
		}

		$subscription->gateway_id = $this->get_gateway_id();
		$subscription->published  = 1;

		$this->_setuserpoints($subscription->user_id, (int)"-{$price_points}");

		JoomsubscriptionHelper::activateSubscription($subscription, $plan);
		$subscription->store();

		$app->enqueueMessage(JText::sprintf('JS_SUCCESS', $plan->name, $price_points));
		JoomsubscriptionHelper::redirect($plan, $subscription->published);

		return TRUE;
	}

	function pay($amount, $name, $subscription, $plan)
	{
		$user_points  = $this->_getuserpoints();
		$price_points = $this->_convert($amount);

		if($price_points > $user_points)
		{
			$this->setError(JText::sprintf('JS_NOTENOUGHTPOINT', $price_points, $user_points));

			return FALSE;
		}

		JFactory::getApplication()->redirect($this->_get_notify_url($subscription->id));
	}

	function get_gateway_id()
	{
		return strtoupper(substr(md5(JFactory::getApplication()->input->get('em_id')), 0, 10));
	}
}