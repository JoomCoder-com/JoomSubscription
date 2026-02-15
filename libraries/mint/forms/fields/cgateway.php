<?php
/**
 * Cobalt by MintJoomla
 * a component for Joomla! 1.7 - 2.5 CMS (http://www.joomla.org)
 * Author Website: http://www.mintjoomla.com/
 * @copyright Copyright (C) 2012 MintJoomla (http://www.mintjoomla.com). All rights reserved.
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */
defined('_JEXEC') or die();
jimport('joomla.html.html');
jimport('joomla.form.formfield');

class JFormFieldCgateway extends JFormMEFieldList
{
	public $type = 'Cgateway';

	protected function getOptions()
	{
		$folders = JFolder::folders(JPATH_ROOT . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_cobalt' . DIRECTORY_SEPARATOR . 'gateways');
		
		$out[] = Joomla\CMS\HTML\HTMLHelper::_('select.option', '', '- ' . \Joomla\CMS\Language\Text::_('Select gateway') . ' -');
		if (count($folders))
		{
			foreach($folders as $folder)
			{
				$out[] = Joomla\CMS\HTML\HTMLHelper::_('select.option', $folder, $folder);
			}
		}
		return $out;
	}
}
?>