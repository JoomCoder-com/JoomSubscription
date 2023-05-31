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

class JoomsubscriptionActionGetresponse extends JoomsubscriptionAction
{
	public function onActive($subscription)
	{
		$id = $this->_userExists();

		if($id)
		{
			$this->_makeAPICall('move_contact', array(
				"contact"  => $id,
				"campaign" => $this->params->get('campaign_id')
			));
		}
		else
		{
			$this->_makeAPICall('add_contact', array(
				"campaign"  => $this->params->get('campaign_id'),
				"name"      => JFactory::getUser()->get('name'),
				"email"     => JFactory::getUser()->get('email'),
				"cycle_day" => 1,
				"ip"        => $_SERVER['REMOTE_ADDR']
			));
		}

		if($this->params->get('msg'))
		{
			JFactory::getApplication()->enqueueMessage(JText::_($this->params->get('msg')));
		}
	}

	public function onDisactive($subscription)
	{
		if($this->params->get('deactivate') == 0)
		{
			return;
		}

		$user_subscrs = JoomsubscriptionHelper::getUserActiveSubscriptions();
		$plan_ids     = array();
		foreach($user_subscrs as $subscr)
		{
			$plan_ids[] = $subscr->plan_id;
		}

		$actions     = JoomsubscriptionActionsHelper::getActions(array_values($plan_ids), 'getresponse');
		$allow_lists = array();
		foreach($actions as $action)
		{
			$action        = new JRegistry($action->action);
			$allow_lists[] = $action->get('campaign_id', 0);
		}

		if(in_array($this->params->get('campaign_id'), $allow_lists))
		{
			return;
		}

		$id = $this->_userExists();

		if(!$id)
		{
			return;
		}

		switch($this->params->get('deactivate'))
		{
			case 2:
				$this->_makeAPICall('delete_contact', array(
					"contact" => $id
				));
				break;
			case 3:
				if($this->params->get('move_id'))
				{
					$this->_makeAPICall('move_contact', array(
						"contact"  => $id,
						"campaign" => $this->params->get('move_id')
					));
				}
				break;
		}
	}

	public function getlists()
	{
		$app = JFactory::getApplication();
		$this->params->set('api_key', $app->input->getString('api_key'));

		$campaigns = $this->_makeAPICall('get_campaigns');

		$options = array();
		foreach($campaigns as $id => $rec)
		{
			$options[] = JHtml::_('select.option', $id, $rec['name']);
		}

		return JHtml::_('select.genericlist', $options, $app->input->getString('name'));

	}

	private function _userExists()
	{
		$user = $this->_makeAPICall('get_contacts', array(
			'email' => array('EQUALS' => JFactory::getUser()->get('email'))
		));

		if(count($user) == 0)
		{
			return 0;
		}

		$id = array_keys($user);
		$id = array_shift($id);

		return $id;
	}

	private function _makeAPICall()
	{
		require_once __DIR__ . '/lib/rpsclient.php';

		$client = new jsonRPCClient('http://api2.getresponse.com');

		$array    = func_get_args();
		$what     = $array[0];
		$array[0] = $this->params->get('api_key');

		try
		{
			$result = call_user_func_array(array($client, $what), $array);
		}
		catch(Exception $e)
		{
			JFactory::getApplication()->enqueueMessage($e->getMessage() . $what);

			return NULL;
		}

		return $result;
	}

	public function getDescription()
	{
		return JText::_('GR_DESCRIPTION');
	}
}
