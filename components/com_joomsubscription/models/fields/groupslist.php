<?php
/**
 * JoomSubscription by JoomCoder
 * a component for Joomla! 5.0 CMS (http://www.joomla.org)
 * Author Website: https://www.joomcoder.com/
 * @copyright Copyright (C) 2012-2025 JoomCoder (https://www.joomcoder.com/). All rights reserved.
 * @license   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

class JFormFieldGroupslist extends ListField
{
	/**
	 * The form field type.
	 *
	 * @var  string
	 * @since  1.6
	 */
	protected $type = 'Groupslist';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 * @since   1.6
	 */
	protected function getOptions()
	{
		$db = Factory::getDbo();
		$query = $db->getQuery(true);

		$query->select('id AS value, name AS text');
		$query->from('#__joomsubscription_plans_groups');

		if (!isset($this->element['all']))
		{
			$query->where('published = 1');
		}

		$query->order('name');
		$db->setQuery($query);

		$options = $db->loadObjectList();

		// Add "All Groups" option for single select
		if (!$this->multiple)
		{
			array_unshift($options, HTMLHelper::_('select.option', '', Text::_('EM_ALL_GROUPS')));
		}

		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}
}