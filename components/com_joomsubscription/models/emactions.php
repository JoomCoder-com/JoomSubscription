<?php
/**
 * JoomSubscription by JoomCoder
 * a component for Joomla! 3.0 CMS (http://www.joomla.org)
 * Author Website: https://www.joomcoder.com/
 * @copyright Copyright (C) 2012 JoomCoder (https://www.joomcoder.com/). All rights reserved.
 * @license   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die('Restricted access');

class JoomsubscriptionModelsEmActions extends JModelBase
{
	public function getActionList()
	{
		$path    = JPATH_COMPONENT . DIRECTORY_SEPARATOR . 'library' . DIRECTORY_SEPARATOR . 'actions';
		$acts    = JFolder::folders($path);
		$options = array();

		foreach($acts AS $a)
		{
			$options[] = JHtml::_('select.option', $a, JoomsubscriptionActionsHelper::get_action_name($a));
		}

		array_unshift($options, JHtml::_('select.option', '', JText::_('ESELECT_ACTION')));

		return $options;
	}

	public function getActions($plan_id)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(TRUE);

		$query->select('*');
		$query->from('#__joomsubscription_plans_actions');
		$query->where('plan_id = ' . (int)$plan_id);
		$db->setQuery($query);

		return $db->loadObjectList();
	}
}