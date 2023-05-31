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

class JFormFieldEdb extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var        string
	 * @since    1.6
	 */
	protected $type = 'Edb';

	/**
	 * Method to get the field input markup.
	 *
	 * @return    string    The field input markup.
	 * @since    1.6
	 */
	protected function getOptions()
	{
		if(!JFile::exists(JPATH_ROOT . '/components/com_easydiscuss/easydiscuss.php'))
		{
			return array(JHtml::_('select.option', '', 'EasyDiscuss is not installed'));
		}

		$db      = JFactory::getDbo();
		$options = parent::getOptions();

		$db->setQuery("SELECT id, title FROM #__discuss_badges WHERE published = 1");

		$badges = $db->loadObjectList();

		foreach($badges AS $list)
		{
			$options[] = JHtml::_('select.option', $list->id, $list->title);
		}

		return $options;
	}
}