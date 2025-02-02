<?php
/**
 * JoomSubscription by JoomCoder
 * a component for Joomla! 3.0 CMS (http://www.joomla.org)
 * Author Website: https://www.joomcoder.com/
 * @copyright Copyright (C) 2012 JoomCoder (https://www.joomcoder.com/). All rights reserved.
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die();

class JoomsubscriptionViewEmPlan extends MViewBase
{

	protected $state;

	protected $item;

	protected $form;

	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{
		$this->state = $this->get('State');
		$this->item = $this->get('Item');
		$this->form = $this->get('Form');
		$this->user = JFactory::getUser();

		$params = new JForm('params', array('control' => 'params'));
		$params->loadFile(JPATH_COMPONENT. '/models/forms/plan.params.xml');
		$this->params_form = $params;

		// Check for errors.
		if(count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}


		$this->_prepareDocument();
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since	1.6
	 */

	private function _prepareDocument()
	{
		$app	= JFactory::getApplication();
		$doc = JFactory::getDocument();
		$menus	= $app->getMenu();
		$pathway = $app->getPathway();
		$title = FALSE;

		if($this->item->id)
		{
			$title = JText::sprintf('EEDITPLAN', $this->item->name);
			$pathway->addItem(strip_tags($title));
		}
		else
		{
			$title = JText::_('ENEWPLAN');
			$pathway->addItem(strip_tags($title));
		}

		$this->appParams = $app->getParams();
		// Because the application sets a default page title,
		// we need to get it from the menu item itself
		$menu = $menus->getActive();
		if ($menu)
		{
			$title .= ' - '.$menu->getParams()->get('page_title', $menu->title);
			$this->appParams->def('page_heading', $title);
		}
		// Check for empty title and add site name if param is set
		if (empty($title)) {
			$title = $app->getCfg('sitename');
		}
		elseif ($app->getCfg('sitename_pagetitles', 0) == 1) {
			$title = JText::sprintf('JPAGETITLE', $app->getCfg('sitename'), $title);
		}
		elseif ($app->getCfg('sitename_pagetitles', 0) == 2) {
			$title = JText::sprintf('JPAGETITLE', $title, $app->getCfg('sitename'));
		}
		if (empty($title)) {
			$title = $this->item->name;
		}
		$doc->setTitle($title);
	}
}
