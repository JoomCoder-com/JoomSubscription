<?php
/**
 * JoomSubscription by JoomCoder
 * a component for Joomla! 3.0 CMS (http://www.joomla.org)
 * Author Website: https://www.joomcoder.com/
 * @copyright Copyright (C) 2012 JoomCoder (https://www.joomcoder.com/). All rights reserved.
 * @license   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die();
include_once JPATH_COMPONENT . '/helpers/imports.php';

class JoomsubscriptionViewEmImport extends MViewBase
{

	protected $state;

	protected $item;

	protected $form;

	/**
	 * Display the view
	 */
	public function display($tpl = NULL)
	{
		$app  = JFactory::getApplication();
		$type = $app->input->get('name');

		$this->import = JoomsubscriptionImportsHelper::createImportObject($type);

		$params = new JForm('import');
		$params->loadFile(JPATH_COMPONENT . DIRECTORY_SEPARATOR . 'library' . DIRECTORY_SEPARATOR . 'imports' . DIRECTORY_SEPARATOR . $type . DIRECTORY_SEPARATOR . $type . '.xml');
		$this->import->form = $params;
		$this->import->type = $type;

		$this->canImport = $this->import->check();

		$this->_prepareDocument();
		parent::display($tpl);
	}

	private function _prepareDocument()
	{
		$app     = JFactory::getApplication();
		$doc     = JFactory::getDocument();
		$menus   = $app->getMenu();
		$pathway = $app->getPathway();
		$type    = $app->input->get('name');
		$title   = FALSE;

		$title = JText::sprintf('EIMPORT_S', JText::_($this->import->title));
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
			$title = $app->get('sitename');
		}
		elseif($app->get('sitename_pagetitles', 0) == 1)
		{
			$title = JText::sprintf('JPAGETITLE', $app->get('sitename'), $title);
		}
		elseif($app->get('sitename_pagetitles', 0) == 2)
		{
			$title = JText::sprintf('JPAGETITLE', $title, $app->get('sitename'));
		}
		if(empty($title))
		{
			$title = $this->item->name;
		}
		$doc->setTitle($title);
	}
}
