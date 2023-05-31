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

class JFormFieldEmFields extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since	1.6
	 */
	protected $type = 'EmFieldType';

	/**
	 * Method to get the field input markup.
	 *
	 * @return	string	The field input markup.
	 * @since	1.6
	 */
	protected function getOptions()
	{
		$db = JFactory::getDbo();
		$db->setQuery("SELECT id as value, `name` as text FROM `#__joomsubscription_fields` ORDER By ordering");
		return $db->loadObjectList();

		$options[] = JHtml::_('select.option', '', JText::_('ESELECTFTYPE'));
		foreach (glob('components/com_joomsubscription/library/fields/*') as $filename)
		{
			if(is_dir($filename)) {
				$options[] = JHtml::_('select.option', basename($filename), basename($filename));
			}
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