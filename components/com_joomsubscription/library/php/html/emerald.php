<?php
/**
 * JoomSubscription by JoomCoder
 * a component for Joomla! 3.0 CMS (http://www.joomla.org)
 * Author Website: https://www.joomcoder.com/
 * @copyright Copyright (C) 2012 JoomCoder (https://www.joomcoder.com/). All rights reserved.
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die;


abstract class JHtmlJoomsubscription
{
	/**
	 * A cached array of the groups
	 *
	 * @var    array
	 */
	protected static $groups = null;
	protected static $plans = null;


	public static function groups()
	{
		if (empty(self::$groups))
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query->select('g.id AS value, g.name AS text');
			$query->from($db->quoteName('#__joomsubscription_plans_groups') . ' AS g');
			$query->order('g.name ASC');

			$db->setQuery($query);
			self::$groups = $db->loadObjectList();
		}

		return self::$groups;
	}

	public static function plans()
	{
		if (empty(self::$groups))
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query->select('p.id AS value, p.name AS text');
			$query->from($db->quoteName('#__joomsubscription_plans') . ' AS p');
			$query->order('p.name ASC');

			$db->setQuery($query);
			self::$groups = $db->loadObjectList();
		}

		return self::$groups;
	}

}