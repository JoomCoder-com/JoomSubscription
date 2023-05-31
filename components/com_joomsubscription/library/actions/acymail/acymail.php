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

class JoomsubscriptionActionAcymail extends JoomsubscriptionAction
{
	public function onSuccess($subscription)
	{
		if(!$this->params->get('mail_list'))
		{
			return;
		}

		if(!$this->_load_api())
		{
			return;
		}


		$subscr[$this->params->get('mail_list')] = array('status' => 1);
		if($this->params->get('del_active'))
		{
			$subscr[$this->params->get('del_active')] = array('status' => 0);
		}

		$user      = JFactory::getUser($subscription->user_id);
		$userClass = acymailing_get('class.subscriber');
		$subid     = $userClass->subid($user->get('id'));

		if(empty($subid))
		{
			$myUser        = new stdClass();
			$myUser->email = $user->get('email');
			$myUser->name  = $user->get('name');

			$subscriberClass = acymailing_get('class.subscriber');

			$subid = $subscriberClass->save($myUser);
		}

		$userClass->saveSubscription($subid, $subscr);

		if(trim($this->params->get('message')))
		{
			JFactory::getApplication()->enqueueMessage(JText::sprintf($this->params->get('message'), $this->_list_name($this->params->get('mail_list'))), 'notice');
		}
	}

	public function onDisactive($subscription)
	{
		if(!$this->params->get('deactivate'))
		{
			return;
		}

		if(!$this->_load_api())
		{
			return;
		}

		$user_subscrs = JoomsubscriptionHelper::getUserActiveSubscriptions();
		$plan_ids     = array();
		foreach($user_subscrs as $s)
		{
			$plan_ids[] = $s->plan_id;
		}

		$actions = JoomsubscriptionActionsHelper::getActions(array_values($plan_ids), 'acymail');
		$allowed = array();
		foreach($actions as $action)
		{
			$action    = new JRegistry($action->action);
			$allowed[] = $action->get('mail_list', 0);
		}

		if(in_array($this->params->get('mail_list'), $allowed))
		{
			return;
		}

		$subscr_tosave[$this->params->get('mail_list')] = array('status' => 0);
		if($this->params->get('deactive'))
		{
			$subscr_tosave[$this->params->get('deactive')] = array('status' => 1);
		}


		$user      = JFactory::getUser($subscription->user_id);
		$userClass = acymailing_get('class.subscriber');
		$subid     = $userClass->subid($user->get('id'));

		if(empty($subid))
		{
			return;
		}

		$userClass->saveSubscription($subid, $subscr_tosave);
	}

	public function getDescription()
	{
		$out = array();
		if($this->params->get('mail_list'))
		{
			$out[] = JText::sprintf('X_ACY_DESCR_LIST', $this->_list_name($this->params->get('mail_list')));
		}
		$out[] = JText::_($this->params->get('deactivate') ? 'X_ACY_DESCR_DEA1' : 'X_ACY_DESCR_DEA0');

		if(trim($this->params->get('message')))
		{
			$out[] = JText::sprintf('X_ACY_DESCR_MSG', $this->params->get('message'));
		}

		return '<ul><li>' . implode('</li><li>', $out) . '</li></ul>';
	}


	private function _list_name($id)
	{
		if($this->_load_api())
		{
			$listClass = acymailing_get('class.list');

			$allLists = $listClass->getLists();
			foreach($allLists AS $list)
			{
				if($list->listid == $id)
				{
					return $list->name;
				}
			}
		}

		return $id;
	}

	private function _load_api()
	{
		$api = JPATH_ADMINISTRATOR . '/components/com_acymailing/helpers/helper.php';
		if(!JFile::exists($api))
		{
			return FALSE;
		}

		include_once $api;

		return TRUE;
	}
}
