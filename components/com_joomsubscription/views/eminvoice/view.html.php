<?php
/**
 *
 * @license   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 *
 */
defined('_JEXEC') or die('Restricted access');

class JoomsubscriptionViewEmInvoice extends MViewBase
{
	function display($tpl = NULL)
	{
		$app  = JFactory::getApplication();
		$user = JFactory::getUser();
		$id   = $app->input->getInt('id', 0);

		if(!$user->get('id'))
		{
			$app->enqueueMessage('E_NOLOGIN', 'error');

			return;
		}
		if(!$id)
		{
			$app->enqueueMessage('E_NOSUBSCR', 'error');

			return;
		}

		$joomsubscription_params = JComponentHelper::getParams('com_joomsubscription');

		$this->subscr = JTable::getInstance('EmSubscription', 'JoomsubscriptionTable');
		$this->subscr->load($id);
		$this->subscr->params = new JRegistry($this->subscr->params);

		if($this->subscr->user_id != $user->get('id') && !in_array($joomsubscription_params->get('moderate'), $user->getAuthorisedViewLevels()))
		{
			$app->enqueueMessage('E_ANOTHER_USER_INVOICE', 'error');

			return;
		}

		if(empty($this->subscr->invoice_id))
		{
			$this->_address();
		}
		else
		{
			$this->_invoice();
		}

		parent::display($tpl);
	}

	protected function _address()
	{
		$this->setLayout('address');
		$app = JFactory::getApplication();

		if($app->input->get('add_address', 0) && JoomsubscriptionApi::addInvoceTo($this->subscr))
		{
			if(!$this->subscr->invoice_num)
			{
				$this->subscr->invoice_num = JoomsubscriptionHelper::getInvoiceNum();
			}
			$this->subscr->store();
			$app->redirect(JRoute::_("index.php?option=com_joomsubscription&view=eminvoice&tmpl=component&id=" . $this->subscr->id, FALSE));
		}

		$inv_model      = new JoomsubscriptionModelsEmInvoiceTo();
		$this->inv_list = $inv_model->getList($this->subscr->user_id);
	}

	protected function _invoice()
	{
		$app           = JFactory::getApplication();
		$db            = JFactory::getDbo();
		$model_invoice = new JoomsubscriptionModelsEmInvoiceTo();
		$this->invoice = $model_invoice->getText($this->subscr->invoice_id);

		if(!$this->invoice)
		{
			$app->enqueueMessage('E_NOINVOICETO', 'error');

			return;
		}

		$this->plan         = JoomsubscriptionApi::getPlan($this->subscr->plan_id);

		$query = $db->getQuery(TRUE);
		$query->select('ch.*, c.value');
		$query->from('#__joomsubscription_coupons_history AS ch');
		$query->leftJoin('#__joomsubscription_coupons AS c ON ch.coupon_id = c.id');
		$query->where('subscription_id=' . $this->subscr->id);
		$db->setQuery($query);
		$this->coupon = $db->loadObject();

		$this->discount = 0;

		if(!empty($this->coupon->discount))
		{
			$this->discount = $this->coupon->discount;
			$this->discount_type = 'coupon';
		}
		elseif(!empty($this->subscr->discount))
		{
			$this->discount = $this->subscr->discount;
			$this->discount_type = $this->subscr->discount_type;
		}

		$this->items = array();
		$this->items_total = 0;
		if($this->plan->params->get('properties.fields'))
		{
			$fields = MModelBase::getInstance('EmPayment', 'JoomsubscriptionModel')->getAddonFields($this->plan, json_decode($this->subscr->fields, TRUE));
			foreach($fields AS $field)
			{
				if($add = $field->affectPrice()) {
					$this->items[] = array(
						'name' => $field->name,
						'price' => $add
					);

					$this->items_total += $add;
				}
			}
		}

		$this->params = JComponentHelper::getParams('com_joomsubscription');

		$this->tax = JoomsubscriptionHelper::getTax($this->invoice->fields);
	}
}