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

class JoomsubscriptionActionEasysocial extends JoomsubscriptionAction
{
	private static $_groups = array();

	public function onActive($subscription)
	{
		if(!$this->params->get('group_active'))
		{
			return;
		}
		$this->_changeProfile($this->params->get('group_active'), $subscription);
	}


	public function onDisactive($subscription)
	{
		if(!$this->params->get('group_deactive'))
		{
			return;
		}

		$user_subscrs = JoomsubscriptionHelper::getUserActiveSubscriptions();
		$plan_ids     = array();
		foreach($user_subscrs as $subscr)
		{
			$plan_ids[] = $subscr->plan_id;
		}

		$actions  = JoomsubscriptionActionsHelper::getActions(array_values($plan_ids), 'easysocial');
		$profiles = array();
		foreach($actions as $action)
		{
			$action     = new JRegistry($action->action);
			$profiles[] = $action->get('group_active', 0);
		}

		if(in_array($this->params->get('group_active'), $profiles))
		{
			return;
		}

		$this->_changeProfile($this->params->get('group_deactive'), $subscription);
	}

	public function getDescription()
	{
		$out = '';

		if($this->params->get('group_active'))
		{
			$out .= JText::sprintf('X_PROF_ACTIVE', $this->_getProfileName($this->params->get('group_active')));
			$out .= '<br />';
		}

		if($this->params->get('group_deactive'))
		{
			$out .= JText::sprintf('X_PROF_DEACTIVE', $this->_getProfileName($this->params->get('group_deactive')));
		}

		return $out;
	}

	private function _changeProfile($profile_id, $subscription)
	{
		require_once(JPATH_ADMINISTRATOR . '/components/com_easysocial/includes/foundry.php');

		if(!class_exists('Foundry'))
		{
			return;
		}

		$model = Foundry::model('Profiles');
		$model->updateUserProfile($subscription->user_id, $profile_id);
	}

	private function _getProfileName($id)
	{
		require_once(JPATH_ADMINISTRATOR . '/components/com_easysocial/includes/foundry.php');

		if(!class_exists('Foundry'))
		{
			return;
		}

		$model    = Foundry::model('Profiles');
		$profiles = $model->getProfiles();
		foreach($profiles AS $profile)
		{
			if($profile->id == $id)
			{
				return $profile->title;
			}
		}
	}
}
