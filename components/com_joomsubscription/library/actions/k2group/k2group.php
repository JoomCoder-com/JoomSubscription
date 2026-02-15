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

if(is_dir(JPATH_ROOT . '/administrator/components/com_k2/tables'))
{
	include_once JPATH_ROOT . '/administrator/components/com_k2/tables/k2user.php';
	include_once JPATH_ROOT . '/administrator/components/com_k2/tables/k2usergroup.php';
}

\Joomla\CMS\Table\Table::addIncludePath(JPATH_ROOT . '/administrator/components/com_k2/tables');

class JoomsubscriptionActionK2group extends JoomsubscriptionAction
{
	public function onActive($subscription)
	{
		if(!$this->params->get('group_active'))
		{
			return;
		}

		$table = \Joomla\CMS\Table\Table::getInstance('User', 'TableK2');

		if(!is_object($table))
		{
			return;
		}

		$table->load(array('userID' => $subscription->user_id, 'group' => $this->params->get('group_active')));


		if(!$table->id)
		{
			$user = \Joomla\CMS\Factory::getUser($subscription->user_id);
			$data = array(
				'id'          => NULL,
				'userID'      => $user->get('id'),
				'userName'    => $user->get('name'),
				'group'       => $this->params->get('group_active'),
				'ip'          => $_SERVER['REMOTE_ADDR'],
				'hostname'    => '',
				'notes'       => \Joomla\CMS\Language\Text::_('K2_ADD_EMER'),
				'description' => ''
			);
			$table->save($data);
		}

		if(trim($this->params->get('message')))
		{
			\Joomla\CMS\Factory::getApplication()->enqueueMessage(\Joomla\CMS\Language\Text::sprintf($this->params->get('message'), $this->_getUserGroup($this->params->get('group_active'))));
		}
	}


	public function onDisactive($subscription)
	{
		if(!$this->params->get('group_disactive'))
		{
			return;
		}

		$table = \Joomla\CMS\Table\Table::getInstance('User', 'TableK2');

		if(!is_object($table))
		{
			return;
		}

		$user_subscrs = JoomsubscriptionHelper::getUserActiveSubscriptions();
		$plan_ids     = array();
		foreach($user_subscrs as $subscr)
		{
			$plan_ids[] = $subscr->plan_id;
		}

		$actions       = JoomsubscriptionActionsHelper::getActions(array_values($plan_ids), 'group');
		$setted_groups = array();
		foreach($actions as $action)
		{
			$action          = new \Joomla\Registry\Registry($action->action);
			$setted_groups[] = $action->get('group_active', 0);
		}

		if(in_array($this->params->get('group_active'), $setted_groups))
		{
			return;
		}

		$table->load(array('userID' => $subscription->user_id, 'group' => $this->params->get('group_active')));
		$table->delete();

	}

	public function getDescription()
	{
		$out = array();
		if($this->params->get('group_active'))
		{
			$out[] = \Joomla\CMS\Language\Text::sprintf('X_K2GROUP_DESC', $this->_getUserGroup($this->params->get('group_active')));
		}

		if($this->params->get('group_disactive'))
		{
			$out[] = \Joomla\CMS\Language\Text::sprintf('X_K2ACT_DESC', $this->params->get('group_disactive') ? \Joomla\CMS\Language\Text::_('X_YES') : \Joomla\CMS\Language\Text::_('X_NO'));
		}

		if(trim($this->params->get('message')))
		{
			$out[] = \Joomla\CMS\Language\Text::sprintf('X_K2_DESCR_MSG', $this->params->get('message'));
		}

		return '<ul><li>' . implode('</li><li>', $out) . '</li></ul>';
	}

	private function _getUserGroup($id)
	{
		$table = \Joomla\CMS\Table\Table::getInstance('UserGroup', 'TableK2');

		if(!is_object($table))
		{
			return;
		}

		$table->load($id);

		return $table->name;
	}
}
