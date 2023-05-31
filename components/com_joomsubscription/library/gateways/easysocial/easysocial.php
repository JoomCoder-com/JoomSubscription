<?php

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.menu');

class JoomsubscriptionGatewayEasysocial extends JoomsubscriptionGateway
{

	public function getButton($plan, $total)
	{
		require_once(JPATH_ADMINISTRATOR . '/components/com_easysocial/includes/foundry.php');

		$out = sprintf('<button type="button" style="width: 210px" class="btn btn-warning"	data-payment-gateway="%s"><small>%s</small></button>',
			$this->type,
			sprintf('<div class="lead" style="margin-bottom:0">%s</div>%s', JText::_('EC_PAYWITHPOINTS'), JText::sprintf('EC_PONTCOUNT', Foundry::user()->getPoints(), $this->_convert($total)))
		);

		return $out;
	}

	private function _convert($total)
	{
		return $total * $this->params->get('convert');
	}

	function accept(&$subscription, $plan)
	{
		require_once(JPATH_ADMINISTRATOR . '/components/com_easysocial/includes/foundry.php');

		$app          = JFactory::getApplication();
		$user_points  = Foundry::user()->getPoints();
		$price_points = $this->_convert($subscription->price);

		if($price_points > $user_points)
		{
			$app->enqueueMessage(JText::sprintf('EC_NOTENOUGHTPOINT', $price_points, $user_points), 'warning');
			$app->redirect(JRoute::_('index.php?option=com_joomsubscription&view=empayment&sid='.$plan->id, FALSE));

			return FALSE;
		}

		$subscription->gateway_id = $this->get_gateway_id();
		$subscription->published  = 1;

		Foundry::points()->assignCustom($subscription->user_id, "-{$price_points}", JText::sprintf('ES_POINTS_MSG', $plan->name));

		JoomsubscriptionHelper::activateSubscription($subscription, $plan);
		$subscription->store();

		$app->enqueueMessage(JText::sprintf('ES_SUCCESS', $plan->name, $price_points));
		JoomsubscriptionHelper::redirect($plan, $subscription->published);

		return TRUE;
	}

	function pay($amount, $name, $subscription, $plan)
	{

		require_once(JPATH_ADMINISTRATOR . '/components/com_easysocial/includes/foundry.php');

		$user_points  = Foundry::user()->getPoints();
		$price_points = $this->_convert($amount);

		if($price_points > $user_points)
		{
			$this->setError(JText::sprintf('EC_NOTENOUGHTPOINT', $price_points, $user_points));

			return FALSE;
		}

		JFactory::getApplication()->redirect($this->_get_notify_url($subscription->id));
	}

	function get_gateway_id()
	{
		return strtoupper(substr(md5(JFactory::getApplication()->input->get('em_id')), 0, 10));
	}
}