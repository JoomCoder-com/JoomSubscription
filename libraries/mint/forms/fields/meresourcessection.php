<?php
/**
 * Cobalt by MintJoomla
 * a component for Joomla! 1.7 - 2.5 CMS (http://www.joomla.org)
 * Author Website: http://www.mintjoomla.com/
 * @copyright Copyright (C) 2012 MintJoomla (http://www.mintjoomla.com). All rights reserved.
 * @license   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

defined('JPATH_PLATFORM') or die;

jimport('joomla.html.html');
jimport('joomla.form.formfield');
JFormHelper::loadFieldClass('melist');

class JFormFieldMeresourcessection extends JFormMEFieldList
{

	public $type = 'Meresourcessection';

	protected function getOptions()
	{
		JHtml::addIncludePath(JPATH_ROOT . '/components/com_cobalt/library/php/html');
		$sections = JHtml::_('cobalt.sections');

		$options = array();
		if($this->element['select'] == 1)
		{
			$options[] = JHTML::_('select.option', '', JText::_('CSELECTSECTION'));
		}
		foreach($sections as $type)
		{
			$options[] = JHTML::_('select.option', $type->value . ($this->element['alias'] == 1 ? ':' . $type->alias : NULL), $type->text);

		}

		return $options;
	}

	protected function getInput()
	{
		$html = parent::getInput();
		if($this->element['prepend'])
		{
			$html = $this->element['prepend'] . "<br>$html";
		}
		if($this->element['append'])
		{
			$html .= '<br><br>' . $this->element['append'];
		}

		return $html;
	}
}