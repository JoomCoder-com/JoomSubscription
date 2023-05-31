<?php
/**
 * JoomSubscription by JoomCoder
 * a component for Joomla! 3.0 CMS (http://www.joomla.org)
 * Author Website: https://www.joomcoder.com/
 * @copyright Copyright (C) 2012 JoomCoder (https://www.joomcoder.com/). All rights reserved.
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die;

JFormHelper::loadFieldClass('list');

class JFormFieldEmCountryl extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since	1.6
	 */
	protected $type = 'EmCountryl';

	/**
	 * Method to get the field input markup.
	 *
	 * @return	string	The field input markup.
	 * @since	1.6
	 */
	protected function getOptions()
	{
		// Initialise variables.
		$options = array();
		$app_input = JFactory::getApplication()->input;

		$db = JFactory::getDbo();
		$query = $db->setQuery("SELECT id as value, name as text FROM #__joomsubscription_country ORDER BY name ASC");
		$options = $db->loadObjectList();

		if($this->element['show_default'])
		{
			array_unshift($options, JHtml::_('select.option', '*', JText::_('EANY')));
		}

		return $options;
	}

	/**
	 * Method to get the field input markup fora grouped list.
	 * Multiselect is enabled by using the multiple attribute.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   11.1
	 */
	protected function getInput()
	{
		$out = parent::getInput();

		return $out;
	}
}