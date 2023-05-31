<?php
/**
 * JoomSubscription by JoomCoder
 * a component for Joomla (http://www.joomla.org)
 * Author Website: https://www.joomcoder.com/
 * @copyright Copyright (C) 2012 JoomCoder (https://www.joomcoder.com/). All rights reserved.
 * @license   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */
defined('_JEXEC') or die();

jimport('mint.mvc.view.base');

class JoomsubscriptionViewEmHistory extends MViewBase
{
	function display($tpl = NULL)
	{
		$app        = JFactory::getApplication();
		$this->user = JFactory::getUser();

		$this->menu = Mint::loadLayout('links', JPATH_COMPONENT . '/layouts');

		$render_items = array();
		$model        = $this->getModel();

		$items = $this->get('Items');

		foreach($items as &$item)
		{
			$item->params      = new JRegistry($item->params);
			$item->plan_params = new JRegistry($item->plan_params);
			if($item->gateway == 'paypal' && $item->published == 1)
			{
				$recurring = $item->params->get('gateways.paypal.recurred');
				if($recurring == 1)
				{
					$item->paypal_email = $item->params->get('gateways.paypal.email');
				}

			}
			$item->coupon_info = $model->getSubscriptionCouponInfo($item->sid);
			$item->img         = 'active.png';
			$item->state       = 'EM_ACTIVE';
			$item->is_active   = TRUE;

			if($item->published == 0)
			{
				$item->state     = 'EM_INACTIVE';
				$item->img       = 'block.png';
				$item->is_active = FALSE;
			}
			if($item->expired)
			{
				$item->class     = 'red';
				$item->state     = 'EM_INACTIVE';
				$item->img       = 'block.png';
				$item->is_active = FALSE;
			}
			else
			{
				$item->class = 'green';
			}

			if(!$item->active || ($item->access_limit > 0 && $item->access_limit <= $item->access_count))
			{
				$item->class     = 'grey';
				$item->state     = 'EM_DISABLED';
				$item->img       = 'disabled.png';
				$item->is_active = FALSE;
			}

			if(strtotime($item->ctime) > time())
			{
				$item->class     = 'green';
				$item->state     = 'EM_WAIT';
				$item->img       = 'wait.png';
				$item->is_active = FALSE;
			}

			$plan         = JoomsubscriptionApi::getPlan($item->id);
			$plan->params = $item->plan_params;

			$fields = MModelBase::getInstance('EmPayment', 'JoomsubscriptionModel')
				->getAddonFields($plan, json_decode($item->fields, TRUE));

			$item->additions = array();
			if($fields)
			{
				foreach($fields AS $field)
				{
					if($onh = $field->onHistory($item))
					{
						$item->additions[$field->getLabel()] = $onh;
					}
				}
			}

			$item->muaccess = ($item->img == 'active.png') ? $item->plan_params->get('properties.muaccess', 0) : 0;

			$render_items[$item->group][] = $item;
		}

		$this->items      = $render_items;
		$this->params     = JComponentHelper::getParams('com_joomsubscription');
		$this->pagination = $model->getPagination();

		$this->_prepareDocument();
		parent::display($tpl);
	}

	private function _prepareDocument()
	{
		$app = JFactory::getApplication();
		$doc = JFactory::getDocument();

		$this->mparams = new JRegistry($app->getMenu()->getActive());


		$this->mparams->set('data.page_title', $this->mparams->get('data.page_title', JText::_('EMR_TITLEHISTROOY')));
		$doc->setTitle($this->mparams->get('data.page_title'));

		//$pathway = $app->getPathway();
		//$pathway->addItem($this->mparams->get('data.page_title'));
	}
}

?>