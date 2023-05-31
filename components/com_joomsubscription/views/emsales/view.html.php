<?php
/**
 * JoomSubscription by JoomCoder
 * a component for Joomla (http://www.joomla.org)
 * Author Website: https://www.joomcoder.com/
 * @copyright Copyright (C) 2012 JoomCoder (https://www.joomcoder.com/). All rights reserved.
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */
defined('_JEXEC') or die();

jimport('mint.mvc.view.base');
class JoomsubscriptionViewEmSales extends MViewBase
{
	function display($tpl = null)
	{
		$this->model = $this->getModel();
		$this->state = $this->get('State');
		$this->pagination = $this->get('Pagination');
		$this->menu = Mint::loadLayout('links', JPATH_COMPONENT .'/layouts');
		$this->buttons    = Mint::loadLayout('btn_list', JPATH_COMPONENT . '/layouts');

		$items = $this->get('Items');

		foreach($items as $item)
		{
			$item->plan_params = new JRegistry($item->plan_params);
			$item->params = new JRegistry($item->params);
			if($item->gateway == 'paypal' && $item->published == 1)
			{
				$recurring = $item->params->get('gateways.paypal.recurred');
				if($recurring == 1)
				{
					$item->paypal_email = $item->params->get('gateways.paypal.mail');
				}

			}
			$item->coupon_info = $this->model->getSubscriptionCouponInfo($item->sid);
			$item->img = 'active.png';
			$item->state = 'EM_ACTIVE';

			if($item->published == 0)
			{
				$item->state = 'EM_UNPUBLISHED';
				$item->img = 'block.png';
			}

			if($item->expired || (($item->access_limit > 0) && ($item->access_count >= $item->access_limit)))
			{
				$item->state = 'EM_USED';
				$item->class = 'text-error';
				$item->img = 'disabled.png';
			}

			if(!$item->active)
			{
				$item->class = 'muted';
				$item->state = 'EM_FUTURE';
				$item->img = 'clock--minus.png';
			}

			if($item->activated == 0)
			{
				$item->state = 'EM_INACTIVE';
				$item->img = 'exclamation-diamond.png';
			}

			$item->muaccess = ($item->img == 'active.png') ? $item->params->get('properties.muaccess', 0) : 0;

			$item->fields_list = JoomsubscriptionFieldsHelper::getSubscriptionFields($item);
		}

		$this->items = $items;
		$this->params = JComponentHelper::getParams('com_joomsubscription');

		$this->_prepareDocument();
		parent::display($tpl);
	}

	private function _prepareDocument()
	{
		$app = JFactory::getApplication();
		$doc = JFactory::getDocument();
		$pathway = $app->getPathway();
		$menus	= $app->getMenu();

		$this->mparams = new JRegistry($app->getMenu()->getActive());

		$title = JText::_('COM_JOOMSUBSCRIPTION_SALES');
		$pathway->addItem(strip_tags($title));

		$this->mparams->set('data.page_title', $title);
		$this->appParams = $app->getParams();

		// Check for empty title and add site name if param is set
		$menu = $menus->getActive();
		if ($menu)
		{
			$title .= ' - '.$menu->params->get('page_title', $menu->title);
			$this->appParams->def('page_heading', $title);
		}
		if (empty($title)) {
			$title = $app->getCfg('sitename');
		}
		elseif ($app->getCfg('sitename_pagetitles', 0) == 1) {
			$title = JText::sprintf('JPAGETITLE', $app->getCfg('sitename'), $title);
		}
		elseif ($app->getCfg('sitename_pagetitles', 0) == 2) {
			$title = JText::sprintf('JPAGETITLE', $title, $app->getCfg('sitename'));
		}
		$doc->setTitle($title);
	}
}

?>