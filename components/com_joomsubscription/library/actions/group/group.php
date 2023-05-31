<?php
/**
 * JoomSubscription by JoomCoder
 * a component for Joomla! 3.0 CMS (http://www.joomla.org)
 * Author Website: https://www.joomcoder.com/
 *
 * @copyright Copyright (C) 2012 JoomCoder (https://www.joomcoder.com/). All rights reserved.
 * @license   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */
defined('_JEXEC') or die();

class JoomsubscriptionActionGroup extends JoomsubscriptionAction
{
	private static $_groups = array();

	public function onActive($subscription)
	{
		if($this->params->get('group_active'))
		{
			JUserHelper::addUserToGroup($subscription->user_id, $this->params->get('group_active'));
		}

		if($this->params->get('group_active_remove'))
		{
			JUserHelper::removeUserFromGroup($subscription->user_id, $this->params->get('group_active_remove'));
		}

		$this->user->clearAccessRights();
	}

	public function onDisactive($subscription)
	{
		if(!$this->params->get('group_disactive'))
		{
			return;
		}

		$user_subscrs = JoomsubscriptionHelper::getUserActiveSubscriptions(NULL, $subscription->id);
		$plan_ids     = array();
		foreach($user_subscrs as $subscr)
		{
			$plan_ids[] = $subscr->plan_id;
		}

		$actions       = JoomsubscriptionActionsHelper::getActions(array_values($plan_ids), 'group');
		$setted_groups = array();
		foreach($actions as $action)
		{
			$action          = new JRegistry($action->action);
			$setted_groups[] = $action->get('group_active', 0);
		}

		if(in_array($this->params->get('group_active'), $setted_groups))
		{
			return;
		}

		JUserHelper::removeUserFromGroup($subscription->user_id, $this->params->get('group_active'));

		if($this->params->get('group_after_active'))
		{
			JUserHelper::addUserToGroup($subscription->user_id, $this->params->get('group_after_active'));
		}

		$this->user->clearAccessRights();
	}

	public function getDescription()
	{
		$out = '';
		if($this->params->get('group_active'))
		{
			$out .= '<b>' . JText::_('X_GROUP_ACTIVE') . '</b><br />';
			$out .= $this->_getUserGroup($this->params->get('group_active')) . '<br/>';
		}

		if($this->params->get('group_disactive'))
		{
			$out .= '<b>' . JText::_('X_GROUP_DISACTIVE') . '</b><br />';
			$out .= $this->params->get('group_disactive') ? JText::_('X_YES') : JText::_('X_NO');
		}

		return $out;
	}

	private function _getUserGroup($id)
	{
		if(empty(self::$_groups))
		{
			$db    = JFactory::getDbo();
			$query = $db->getQuery(TRUE);
			$query->select('id, title');
			$query->from('#__usergroups');
			$db->setQuery($query);
			self::$_groups = $db->loadObjectList('id');
		}

		return self::$_groups[$id]->title;
	}
}
