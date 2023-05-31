<?php
/**
 * JoomSubscription by JoomCoder
 * a component for Joomla (http://www.joomla.org)
 * Author Website: https://www.joomcoder.com/
 *
 * @copyright Copyright (C) 2012 JoomCoder (https://www.joomcoder.com/). All rights reserved.
 * @license   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */
defined('_JEXEC') or die();

jimport('mint.mvc.view.base');
include_once JPATH_ROOT . '/components/com_joomsubscription/models/eminvoiceto.php';

/**
 * @property mixed title
 */
class JoomsubscriptionViewEmPayment extends MViewBase
{
	function display($tpl = NULL)
	{
		$app   = JFactory::getApplication();
		$user  = JFactory::getUser();
		$model = $this->getModel();
		$id    = $app->input->getInt('sid');

		if(!$id)
		{
			$app->enqueueMessage(JText::_('EMR_ERR_NOPLANCONFIRM'), 'warning');
			$app->redirect(JoomsubscriptionApi::getLink('emlist', FALSE));
		}

		$this->plan       = JoomsubscriptionApi::getPreparedPlan($id);
		$this->com_params = JComponentHelper::getParams('com_joomsubscription');

		if(empty($this->plan->id))
		{
			JError::raiseNotice(100, JText::_('EMR_CANNOTPURCH'));
			$app->redirect(JoomsubscriptionApi::getLink('emlist', FALSE));
		}

		if(!in_array($this->plan->access_pay, $user->getAuthorisedViewLevels()))
		{
			$app->enqueueMessage(JText::_('EMR_CANNOTPURCHACCESS'), 'warning');
			$app->redirect(JoomsubscriptionApi::getLink('emlist', FALSE));
		}

		$this->title = JText::_('EMR_CONFIRMSUBSCR');

		$pathway = $app->getPathway();
		$pathway->addItem($this->title);

		$doc = JFactory::getDocument();
		$doc->setTitle($this->title);

		$this->params = JComponentHelper::getParams('com_joomsubscription');
		$this->user   = $user;

		if($this->params->get('use_invoice', 0) == 1 && $app->input->getInt('invoice') ==  -1)
		{
			$iid = JoomsubscriptionApi::saveBillAddress();
			$app->setUserState('com_joomsubscription.invoiceto.selector', $iid ? $iid : -1);
		}

		if($this->params->get('use_invoice', 0))
		{
			$inv_model      = new JoomsubscriptionModelsEmInvoiceTo();
			$this->inv_list = $inv_model->getList();
		}

		$this->fields = array();
		$this->addons = array();

		if($this->plan->params->get('properties.fields'))
		{
			$this->fields = $model->getAddonFields($this->plan);
		}

		foreach($this->fields AS $field)
		{
			if($add = $field->affectPrice($this->plan)) {
				$this->addons[$field->getPaymentLabel()] = $add;
			}
			if($field->getError(0))
			{
				$app->enqueueMessage($field->getError(), 'error');
			}
		}

		if(!$this->plan->is_donation)
		{
			if($app->input->get('coupon'))
			{
				$app->setUserState('last-joomsubscription-coupon', $app->input->get('coupon'));
			}
			$this->coupon  = JoomsubscriptionHelperCoupon::getCoupon($app->getUserState('last-joomsubscription-coupon'), $this->plan->id, $this->plan->total, TRUE);
			$this->coupons = $model->getCouponsNumber($id);
		}

		else
		{
			$this->coupon  = NULL;
			$this->coupons = 0;
		}

		parent::display($tpl);

	}
}