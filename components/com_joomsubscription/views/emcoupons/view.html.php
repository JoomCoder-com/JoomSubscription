<?php
/**
 * JoomSubscription by JoomCoder
 * a component for Joomla! 3.0 CMS (http://www.joomla.org)
 * Author Website: https://www.joomcoder.com/
 *
 * @copyright Copyright (C) 2012 JoomCoder (https://www.joomcoder.com/). All rights reserved.
 * @license   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die();

class JoomsubscriptionViewEmCoupons extends MViewBase
{
	public function display($tpl = NULL)
	{
		$this->state      = $this->get('State');
		$this->items      = $this->get('Items');
		$this->pagination = $this->get('Pagination');
		$this->menu       = Mint::loadLayout('links', JPATH_COMPONENT . '/layouts');
		$this->buttons    = Mint::loadLayout('btn_list', JPATH_COMPONENT . '/layouts');

		$this->_prepareDocument();
		parent::display($tpl);
	}

	protected function getSortFields()
	{
		return array(
			'c.published' => \Joomla\CMS\Language\Text::_('JSTATUS'),
			'c.id'        => \Joomla\CMS\Language\Text::_('ID'),
			'c.value'     => \Joomla\CMS\Language\Text::_('X_VALUE'),
			'c.ctime'     => \Joomla\CMS\Language\Text::_('ECREATED')
		);
	}

	private function _prepareDocument()
	{
		$app     = \Joomla\CMS\Factory::getApplication();
		$doc     = \Joomla\CMS\Factory::getDocument();
		$menus   = $app->getMenu();
		$pathway = $app->getPathway();

		$title = \Joomla\CMS\Language\Text::_('ECOUPONS');
		$pathway->addItem(strip_tags($title));

		$this->appParams = $app->getParams();
		// Because the application sets a default page title,
		// we need to get it from the menu item itself
		$menu = $menus->getActive();
		if($menu)
		{
			$title .= ' - ' . $menu->getParams()->get('page_title', $menu->title);
			$this->appParams->def('page_heading', $title);
		}
		// Check for empty title and add site name if param is set
		if(empty($title))
		{
			$title = $app->getCfg('sitename');
		}
		elseif($app->getCfg('sitename_pagetitles', 0) == 1)
		{
			$title = \Joomla\CMS\Language\Text::sprintf('JPAGETITLE', $app->getCfg('sitename'), $title);
		}
		elseif($app->getCfg('sitename_pagetitles', 0) == 2)
		{
			$title = \Joomla\CMS\Language\Text::sprintf('JPAGETITLE', $title, $app->getCfg('sitename'));
		}
		$doc->setTitle($title);
	}
}