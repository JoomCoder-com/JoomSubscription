<?php
/**
 * JoomSubscription by JoomCoder
 * a component for Joomla! 3.0 CMS (http://www.joomla.org)
 * Author Website: https://www.joomcoder.com/
 * @copyright Copyright (C) 2012 JoomCoder (https://www.joomcoder.com/). All rights reserved.
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die();

class JoomsubscriptionViewEmImports extends MViewBase
{
	public function display($tpl = null)
	{
		$path = JPATH_COMPONENT.DIRECTORY_SEPARATOR.'library'.DIRECTORY_SEPARATOR.'imports'.DIRECTORY_SEPARATOR;
		$imports = JFolder::folders($path);
		$lang = JFactory::getLanguage();
		$tag  = $lang->getTag();
		foreach ($imports as $import)
		{
			if($tag != 'en-GB')
			{
				if(!JFile::exists(JPATH_BASE . "/language/{$tag}/{$tag}.com_joomsubscription_import_{$import}.ini"))
				{
					$tag == 'en-GB';
				}
			}
			$lang->load('com_joomsubscription_import_' . $import, JPATH_ROOT, $tag, TRUE);

			$xml = new SimpleXMLElement($path.'/'.$import.'/'.$import.'.xml', null, true);
			$i = new stdClass();
			$i->name = $import;
			$i->title = JText::_($xml->name);
			$i->icon = isset($xml->icon)? JUri::root(true).'/components/com_joomsubscription/library/imports/'.$import.'/'.$xml->icon : false;

			$this->items[] = $i;
		}
		$this->menu = Mint::loadLayout('links', JPATH_COMPONENT .'/layouts');
		$this->canImport = $this->_canImport();

		$this->_prepareDocument();
		parent::display($tpl);
	}

	private function _canImport()
	{
		$db = JFactory::getDbo();
		$db->setQuery('SELECT COUNT(*) FROM #__joomsubscription_subscriptions');
		$result = $db->loadResult();

		if ($result) return false;

		$db = JFactory::getDbo();
		$db->setQuery('SELECT COUNT(*) FROM #__joomsubscription_plans');
		$result = $db->loadResult();

		if ($result) return false;

		return true;
	}

	private function _prepareDocument()
	{
		$app	= JFactory::getApplication();
		$doc = JFactory::getDocument();
		$menus	= $app->getMenu();
		$pathway = $app->getPathway();

		$title = JText::_('EIMPORT');
		$pathway->addItem(strip_tags($title));

		$this->appParams = $app->getParams();
		// Because the application sets a default page title,
		// we need to get it from the menu item itself
		$menu = $menus->getActive();
		if ($menu)
		{
			$title .= ' - '.$menu->params->get('page_title', $menu->title);
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
		$doc->setTitle($title);
	}
}
