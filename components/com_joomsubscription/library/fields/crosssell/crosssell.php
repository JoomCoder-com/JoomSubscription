<?php
/**
 * Created by PhpStorm.
 * User: Sergey
 * Date: 1/27/15
 * Time: 14:21
 */

defined('_JEXEC') or die('Restricted access');


class JoomsubscriptionFieldCrosssell extends JoomsubscriptionField
{
	public function getField()
	{
		$note = Mint::_($this->params->get('params.note'));

		$plan       = JoomsubscriptionApi::getPlan($this->params->get('params.plan_to_sell'));
		$current    = JoomsubscriptionApi::getPlan(JFactory::getApplication()->input->get('sid'));
		$sell_price = $this->params->get('params.price');
		$save_a     = $plan->params->get('properties.price') - $this->params->get('params.price');
		$save_p     = round($save_a / ($plan->params->get('properties.price') / 100));

		$note = str_replace(array(
			'[CROSSSELL]',
			'[CURRENT]',
			'[ORIGINALPRICE]',
			'[SAVE_AMOUNT]',
			'[SAVE_PERCENT]',
			'[PRICE]'
		), array(
			$plan->name,
			$current->name,
			JoomsubscriptionApi::getPrice($plan->params->get('properties.price'), $plan->params),
			JoomsubscriptionApi::getPrice($save_a, $plan->params),
			$save_p,
			JoomsubscriptionApi::getPrice($sell_price, $plan->params)
		), $note);

		$this->note = Mint::markdown($note);

		return $this->_load_template('input');
	}

	public function getPaymentLabel()
	{
		$plan = JoomsubscriptionApi::getPlan($this->params->get('params.plan_to_sell'));

		return JText::sprintf('EMF_ALSOBUY', sprintf('%s [%s]', $plan->name, $plan->cname));
	}

	public function onActive($subscription)
	{
		if($this->default == 1)
		{
			JoomsubscriptionApi::addSubscription(JFactory::getUser()->get('id'), $this->params->get('params.plan_to_sell'), 1,
				'CrossSell', $this->params->get('params.price'),
				$subscription->id . '-' . $subscription->gateway_id);

			$table = JTable::getInstance('EmSubscription', 'JoomsubscriptionTable');
			$table->load($subscription->id);
			$table->price -= $this->params->get('params.price');
			$table->store();
		}
	}

	public function affectPrice()
	{
		if($this->default == 1)
		{
			return $this->params->get('params.price');
		}
	}
}
