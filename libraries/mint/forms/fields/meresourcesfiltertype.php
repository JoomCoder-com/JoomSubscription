<?php
/**
 * Cobalt by MintJoomla
 * a component for Joomla! 1.7 - 2.5 CMS (http://www.joomla.org)
 * Author Website: http://www.mintjoomla.com/
 * @copyright Copyright (C) 2012 MintJoomla (http://www.mintjoomla.com). All rights reserved.
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die;
jimport('joomla.html.html');
jimport('joomla.form.formfield');

class JFormFieldMeresourcesfiltertype extends JFormMEFieldList
{
	public $type = 'Filtertypes';
	
	protected function getOptions()
	{
		Joomla\CMS\HTML\HTMLHelper::addIncludePath(JPATH_ROOT. DIRECTORY_SEPARATOR .'administrator'. DIRECTORY_SEPARATOR .'components'. DIRECTORY_SEPARATOR .'com_cobalt'. DIRECTORY_SEPARATOR .'library'. DIRECTORY_SEPARATOR .'php'. DIRECTORY_SEPARATOR .'html');
		$sections = Joomla\CMS\HTML\HTMLHelper::_('cobalt.filtertypes');
	
		$options = array();
		if ($this->element['select'] == 1)
		{
			$options[] = Joomla\CMS\HTML\HTMLHelper::_('select.option', '', \Joomla\CMS\Language\Text::_('Selet Filter Type'));
		}
		foreach ($sections as $type)
		{
			$options[] = Joomla\CMS\HTML\HTMLHelper::_('select.option', $type->value, $type->text);

		}
		return $options;
	}
}
?>