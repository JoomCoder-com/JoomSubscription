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

//jimport('mint.mvc.view.base');

class JoomsubscriptionViewEmFields extends MViewBase
{
	public function display($tpl = NULL)
	{
		$model = MModelBase::getInstance('EmFields', 'JoomsubscriptionModel');
		$this->setModel($model, TRUE);

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
			'f.published' => \Joomla\CMS\Language\Text::_('JSTATUS'),
			'f.id'        => \Joomla\CMS\Language\Text::_('ID'),
			'f.name'      => \Joomla\CMS\Language\Text::_('ENAME'),
			'f.ctime'     => \Joomla\CMS\Language\Text::_('E_CREATED'),
			'f.ordering'  => \Joomla\CMS\Language\Text::_('EORDERING'),
			'f.type'      => \Joomla\CMS\Language\Text::_('EFIELDTYPE'),
			'f.access'    => \Joomla\CMS\Language\Text::_('EACCESS'),
		);
	}

	private function _prepareDocument()
	{
		$app     = \Joomla\CMS\Factory::getApplication();
		$doc     = \Joomla\CMS\Factory::getDocument();
		$menus   = $app->getMenu();
		$pathway = $app->getPathway();

		$title = \Joomla\CMS\Language\Text::_('COM_JOOMSUBSCRIPTION_FIELDS');
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