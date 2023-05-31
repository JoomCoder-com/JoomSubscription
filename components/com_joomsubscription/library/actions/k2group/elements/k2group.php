<?php
/**
 * JoomSubscription by JoomCoder
 * a component for Joomla! 3.0 CMS (http://www.joomla.org)
 * Author Website: https://www.joomcoder.com/
 * @copyright Copyright (C) 2012 JoomCoder (https://www.joomcoder.com/). All rights reserved.
 * @license   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die;

JFormHelper::loadFieldClass('list');

class JFormFieldK2group extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var        string
	 * @since    1.6
	 */
	protected $type = 'K2group';

	/**
	 * Method to get the field input markup.
	 *
	 * @return    string    The field input markup.
	 * @since    1.6
	 */
	protected function getOptions()
	{
		if(!JFolder::exists(JPATH_ROOT.'/components/com_k2'))
		{
			return array(JHtml::_('select.option', '', 'K2 is not installed'));
		}

		$db = JFactory::getDbo();
		$db->setQuery("SELECT id as value, name as text FROM #__k2_user_groups");
		return $db->loadObjectList();
	}
}