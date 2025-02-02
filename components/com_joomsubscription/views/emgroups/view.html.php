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

class JoomsubscriptionViewEmGroups extends MViewBase
{
	public function display($tpl = NULL)
	{
		$model = MModelBase::getInstance('EmGroups', 'JoomsubscriptionModel');
		$this->setModel($model, TRUE);

		$this->state      = $this->get('State');
		$this->items      = $this->get('Items');
		$this->pagination = $this->get('Pagination');
		$this->menu       = Mint::loadLayout('links', JPATH_COMPONENT . '/layouts');
		$this->buttons    = Mint::loadLayout('btn_list', JPATH_COMPONENT . '/layouts');

		$this->_prepareDocument();

		settype($this->items, 'array');
		parent::display($tpl);
	}

	protected function getSortFields()
	{
		return array(
			'g.published' => JText::_('JSTATUS'),
			'g.id'        => JText::_('ID'),
			'g.name'      => JText::_('EGROUPNAME'),
			'g.ctime'     => JText::_('EGROUPCREATED'),
			'g.ordering'  => JText::_('EGROUPORDERING'),
			'g.access'    => JText::_('EGROUPACCESS'),
		);
	}

	private function _prepareDocument()
	{
		$app     = JFactory::getApplication();
		$doc     = JFactory::getDocument();
		$menus   = $app->getMenu();
		$pathway = $app->getPathway();

		$title = JText::_('COM_JOOMSUBSCRIPTION_GROUPS');
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
			$title = JText::sprintf('JPAGETITLE', $app->getCfg('sitename'), $title);
		}
		elseif($app->getCfg('sitename_pagetitles', 0) == 2)
		{
			$title = JText::sprintf('JPAGETITLE', $title, $app->getCfg('sitename'));
		}
		$doc->setTitle($title);
	}
}