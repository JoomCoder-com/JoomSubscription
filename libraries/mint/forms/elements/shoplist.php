<?php
/**
 * Cobalt by MintJoomla
 * a component for Joomla! 1.7 - 2.5 CMS (http://www.joomla.org)
 * Author Website: http://www.mintjoomla.com/
 * @copyright Copyright (C) 2012 MintJoomla (http://www.mintjoomla.com). All rights reserved.
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */
defined('JPATH_PLATFORM') or die();

JFormHelper::loadFieldClass('list');

class JFormFieldShopList extends JFormFieldList
{

	protected function getOptions()
	{
		$db = JFactory::getDBO();

		$query = $db->getQuery(true);
		$query->select('id as value, label as text');
		$query->from('#__onyx_shop');
		$query->where('published = 1');
		$db->setQuery($query);
		$options = $db->loadObjectList();

		return $options;
	}
}
?>