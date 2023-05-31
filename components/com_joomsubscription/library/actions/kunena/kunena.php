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

if(JFolder::exists(JPATH_ROOT . '/administrator/components/com_k2/tables'))
{
	include_once JPATH_ROOT . '/administrator/components/com_k2/tables/k2user.php';
	include_once JPATH_ROOT . '/administrator/components/com_k2/tables/k2usergroup.php';
}

JTable::addIncludePath(JPATH_ROOT . '/administrator/components/com_k2/tables');

class JoomsubscriptionActionKunena extends JoomsubscriptionAction
{
	public function onActive($subscription)
	{
		if(!$this->params->get('rank_active'))
		{
			return;
		}

		$db = JFactory::getDbo();
		$db->setQuery("UPDATE `#__kunena_users` SET rank = " . $this->params->get('rank_active') . " WHERE userid = " . $subscription->user_id);
		$db->execute();

		if(trim($this->params->get('message')))
		{
			JFactory::getApplication()->enqueueMessage(JText::sprintf($this->params->get('message'), $this->_getRank($this->params->get('rank_active'))));
		}
	}


	public function onDisactive($subscription)
	{
		if(!$this->params->get('rank_disactive'))
		{
			return;
		}


		$user_subscrs = JoomsubscriptionHelper::getUserActiveSubscriptions();
		$plan_ids     = array();
		foreach($user_subscrs as $subscr)
		{
			$plan_ids[] = $subscr->plan_id;
		}

		$actions      = JoomsubscriptionActionsHelper::getActions(array_values($plan_ids), 'kunena');
		$setted_ranks = array();
		foreach($actions as $action)
		{
			$action         = new JRegistry($action->action);
			$setted_ranks[] = $action->get('rank_active', 0);
		}

		if(in_array($this->params->get('rank_active'), $setted_ranks))
		{
			return;
		}

		$db = JFactory::getDbo();

		$db->setQuery("SELECT rank FROM `#__kunena_users` WHERE userid = " . $subscription->user_id);
		$rank = $db->loadResult();
		if(in_array($rank, $setted_ranks))
		{
			return;
		}

		$db->setQuery("UPDATE `#__kunena_users` SET rank = 0 WHERE userid = " . $subscription->user_id);
		$db->execute();
	}

	public function getDescription()
	{
		$out = array();
		if($this->params->get('rank_active'))
		{
			$out[] = JText::sprintf('X_KUNENA_DESC', $this->_getRank($this->params->get('rank_active')));
		}

		if($this->params->get('rank_disactive'))
		{
			$out[] = JText::sprintf('X_KUNENAACT_DESC', $this->params->get('rank_disactive') ? JText::_('X_YES') : JText::_('X_NO'));
		}

		if(trim($this->params->get('message')))
		{
			$out[] = JText::sprintf('X_KUNENA_DESCR_MSG', $this->params->get('message'));
		}

		return '<ul><li>' . implode('</li><li>', $out) . '</li></ul>';
	}

	private function _getRank($id)
	{
		$db = JFactory::getDbo();
		$db->setQuery("SELECT rank_title FROM `#__kunena_ranks` WHERE rank_id = " . $id);

		return $db->loadResult();
	}
}
