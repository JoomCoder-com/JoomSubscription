<?php
/**
 *
 * @license   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 *
 */
defined('_JEXEC') or die('Restricted access');

class JoomsubscriptionViewEmBill extends MViewBase
{
	function display($tpl = null)
	{
		$app = JFactory::getApplication();
		$user = JFactory::getUser();
		$id = $app->input->getInt('id', 0);

		if (!$user->get('id'))
		{
			$app->enqueueMessage('E_NOLOGIN', 'error');
			return;
		}
		if (!$id)
		{
			$app->enqueueMessage('E_NOSUBSCR', 'error');
			return;
		}

		$joomsubscription_params = JComponentHelper::getParams('com_joomsubscription');
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('*');
		$query->from('#__joomsubscription_subscriptions');
		$query->where('id='.$id);
		$db->setQuery($query);
		$this->subscr = $db->loadObject();

		if ($this->subscr->user_id != $user->get('id') && !in_array($joomsubscription_params->get('moderate'), $user->getAuthorisedViewLevels()))
		{
			$app->enqueueMessage('E_ANOTHER_USER_INVOICE', 'error');
			return;
		}

		$model_invoice = new JoomsubscriptionModelsEmInvoiceTo();
		$this->invoice = $model_invoice->getText($this->subscr->invoice_id);

		if (!$this->invoice)
		{
			$app->enqueueMessage('E_NOINVOICETO', 'error');
			return;
		}

		$this->plan = JoomsubscriptionApi::getPlan($this->subscr->plan_id);

		$query = $db->getQuery(true);
		$query->select('ch.*, c.value');
		$query->from('#__joomsubscription_coupons_history AS ch');
		$query->leftJoin('#__joomsubscription_coupons AS c ON ch.coupon_id = c.id');
		$query->where('subscription_id='.$this->subscr->id);
		$db->setQuery($query);
		$this->coupon = $db->loadObject();

		$query = $db->getQuery(true);
		$query->select('*');
		$query->from('#__joomsubscription_taxes');
		$query->where('(country_id="'.$this->invoice->fields->get('country_id').'" AND state_id='.$this->invoice->fields->get('state_id', '""').')', 'OR');
		$query->where('(country_id="'.$this->invoice->fields->get('country_id').'" AND state_id IS NULL)', 'OR');
		$query->where('(country_id="*")', 'OR');
		$db->setQuery($query);
		$taxes = $db->loadObjectList();

		$tax = 0;
		foreach ($taxes as $t)
		{
			if($t->country_id && $t->state_id)
			{
				$tax = $t;
				break;
			}
			if($t->country_id != '*' && !$t->state_id)
			{
				$tax = $t;
			}
			if($t->country_id == '*' && !$t->state_id && !$tax)
			{
				$tax = $t;
			}
		}

		$this->tax = $tax;

		$this->params = JComponentHelper::getParams('com_joomsubscription');

		parent::display($tpl);
	}
}