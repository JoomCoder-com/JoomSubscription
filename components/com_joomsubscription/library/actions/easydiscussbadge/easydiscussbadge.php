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

class JoomsubscriptionActionEasydiscussbadge extends JoomsubscriptionAction
{
	public function onActive($subscription)
	{
		if(!$this->params->get('add'))
		{
			return;
		}

		if(!$this->_load_api())
		{
			return;
		}

		$this->_badge_add($this->params->get('add'), $subscription);

		if(trim($this->params->get('message')))
		{
			JFactory::getApplication()->enqueueMessage(JText::sprintf($this->params->get('message'), $this->_badge_name($this->params->get('add'))), 'notice');
		}
	}

	public function onDisactive($subscription)
	{
		if(!$this->_load_api())
		{
			return;
		}

		if($this->params->get('del'))
		{
			$this->_badge_add($this->params->get('del'), $subscription);
		}

		if(!$this->params->get('deactivate'))
		{
			return;
		}


		$user_subscrs = JoomsubscriptionHelper::getUserActiveSubscriptions();
		$plan_ids     = array();
		foreach($user_subscrs as $subscr)
		{
			$plan_ids[] = $subscr->plan_id;
		}

		$actions = JoomsubscriptionActionsHelper::getActions(array_values($plan_ids), 'easydiscussbadge');
		$allowed = array();
		foreach($actions as $action)
		{
			$action    = new JRegistry($action->action);
			$allowed[] = $action->get('add', 0);
		}

		if(in_array($this->params->get('add'), $allowed))
		{
			return;
		}

		$db = JFactory::getDbo();
		$db->setQuery("DELETE FROM `#__discuss_badges_users` WHERE badge_id = " . $this->params->get('add') . " AND user_id = " . $subscription->user_id);
	}

	public function getDescription()
	{
		$out = array();
		if($this->params->get('add'))
		{
			$out[] = JText::sprintf('X_EDB_DESCR_LIST', $this->_badge_name($this->params->get('add')));
		}
		$out[] = JText::_($this->params->get('deactivate') ? 'X_EDB_DESCR_DEA1' : 'X_EDB_DESCR_DEA0');
		if($this->params->get('del'))
		{
			$out[] = JText::sprintf('X_EDB_DESCR_LIST2', $this->_badge_name($this->params->get('del')));
		}

		if(trim($this->params->get('message')))
		{
			$out[] = JText::sprintf('X_EDB_DESCR_MSG', $this->params->get('message'));
		}

		return '<ul><li>' . implode('</li><li>', $out) . '</li></ul>';
	}


	private function _badge_add($id, $subscription)
	{
		$db = JFactory::getDbo();

		$db->setQuery("SELECT id FROM `#__discuss_badges_users` WHERE badge_id = {$id} AND user_id = " . $subscription->user_id);
		if($db->loadResult())
		{
			return;
		}

		$sql = "INSERT INTO `#__discuss_badges_users` (`id`, `badge_id`, `user_id`, `created`, `published`, `custom`)
				VALUES (NULL, {$id}, {$subscription->user_id}, NOW(), 1, '')";
		$db->setQuery($sql);
		$db->execute();
	}

	private function _badge_name($id)
	{
		if($this->_load_api())
		{
			$db = JFactory::getDbo();
			$db->setQuery("SELECT title FROM `#__discuss_badges` WHERE id = " . $id);

			return $db->loadResult();
		}

		return $id;
	}

	private function _load_api()
	{

		$api = JPATH_ROOT . '/components/com_easydiscuss/helpers/helper.php';
		if(!JFile::exists($api))
		{
			return FALSE;
		}

		include_once $api;

		return TRUE;
	}
}
