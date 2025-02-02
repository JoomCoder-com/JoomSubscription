<?php
/**
 * Cobalt by JoomCoder
 * a component for Joomla! 1.7 - 2.5 CMS (http://www.joomla.org)
 * Author Website: https://www.joomcoder.com/
 *
 * @copyright Copyright (C) 2012 JoomCoder (https://www.joomcoder.com/). All rights reserved.
 * @license   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die();
jimport('joomla.application.component.view');

/**
 * View information about cobalt.
 *
 * @package        Cobalt
 * @subpackage     com_cobalt
 * @since          6.0
 */
class JoomsubscriptionViewAbout extends JViewLegacy
{

	public function display($tpl = NULL)
	{
		$this->addToolbar();


		// Path to the XML manifest file
		$file = JPATH_COMPONENT_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'joomsubscription.xml';

		// Check if the file exists
		if (!file_exists($file)) {
			throw new InvalidArgumentException('The specified file does not exist.');
		}

		// Load and parse the XML file
		$xmlContent = simplexml_load_file($file);

		if (!$xmlContent) {
			throw new RuntimeException('Failed to load and parse the XML file.');
		}

		// Extract relevant data from the XML
		$this->data = [
			'version'      => (string) $xmlContent->version ?? '',
			'author'       => (string) $xmlContent->author ?? '',
			'authorEmail'  => (string) $xmlContent->authorEmail ?? '',
			'authorUrl'    => (string) $xmlContent->authorUrl ?? '',
			'copyright'    => (string) $xmlContent->copyright ?? '',
			'description'  => (string) $xmlContent->description ?? '',
		];

		$this->sidebar = JHtmlSidebar::render();

		parent::display($tpl);
	}

	protected function addToolbar()
	{
		JToolBarHelper::title(JText::_('XML_TOOLBAR_TITLE_ABOUT'), 'systeminfo.png');
		JHtmlSidebar::addEntry(
			'<img src="' . JUri::root(TRUE) . '/media/mint/icons/16/information.png" align="absmiddle"> ' .
			JText::_('About'),
			'index.php?option=com_joomsubscription&view=about',
			JFactory::getApplication()->input->getCmd('view', 'about') == 'about'
		);
		JHtmlSidebar::addEntry(
			'<img src="' . JUri::root(TRUE) . '/media/mint/icons/16/gear.png" align="absmiddle"> ' .
			JText::_('Configuration'),
			'index.php?option=com_config&view=component&component=com_joomsubscription'
		);
		JHtmlSidebar::addEntry(
			'<img src="' . JUri::root(TRUE) . '/media/mint/icons/16/lifebuoy.png" align="absmiddle"> ' .
			JText::_('Forum'),
			'http://support.mintjoomla.com/en/'
		);
	}
}
