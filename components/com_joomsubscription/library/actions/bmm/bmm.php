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

class JoomsubscriptionActionBmm extends JoomsubscriptionAction
{
	public function onActive($subscription)
	{

		$plan = JoomsubscriptionApi::getPlan($subscription->plan_id);
		$data = array(
			'email'     => JFactory::getUser()->get('email'),
			'firstname' => JFactory::getUser()->get('name'),
			'Extra 3'   => JFactory::getUser()->get('id'),
			'Extra 6'   => $plan->name . " [{$plan->cname}]",
			'Date 1'    => JHtml::_('date', $subscription->ctime, 'm/d/Y'),
			'Date 2'    => JHtml::_('date', $subscription->extime, 'm/d/Y')
		);

		$id = $this->_userExists();

		if($id)
		{
			$data['id'] = $id;
			$this->_makeAPICall('listUpdateContactDetails', $this->params->get('list_id'), (string)$id, $data);
		}
		else
		{
			$this->_makeAPICall('listAddContacts', $this->params->get('list_id'), array($data));
			if($this->params->get('msg'))
			{
				JFactory::getApplication()->enqueueMessage(JText::_($this->params->get('msg')));
			}
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

		$actions     = JoomsubscriptionActionsHelper::getActions(array_values($plan_ids), 'bmm');
		$allow_lists = array();
		foreach($actions as $action)
		{
			$action        = new JRegistry($action->action);
			$allow_lists[] = $action->get('list_id', 0);
		}

		if(in_array($this->params->get('list_id'), $allow_lists))
		{
			return;
		}

		switch($this->params->get('deactivate'))
		{
			case 1:
				$this->_makeAPICall('listUnsubscribeContacts', $this->params->get('list_id'), array(JFactory::getUser()->get('email')));
				break;
			case 2:
				$this->_makeAPICall('listDeleteEmailContact', $this->params->get('list_id'), JFactory::getUser()->get('email'));
				break;
			case 3:
				if($this->params->get('move_id'))
				{
					$this->_makeAPICall('listDeleteEmailContact', $this->params->get('list_id'), JFactory::getUser()->get('email'));

					$plan = JoomsubscriptionApi::getPlan($subscription->plan_id);
					$data = array(
						'email'     => JFactory::getUser()->get('email'),
						'firstname' => JFactory::getUser()->get('name'),
						'Extra 3'   => JFactory::getUser()->get('id'),
						'Extra 6'   => $plan->name . " [{$plan->cname}]",
						'Date 1'    => JHtml::_('date', $subscription->ctime, 'm/d/Y'),
						'Date 2'    => JHtml::_('date', $subscription->extime, 'm/d/Y')
					);

					$this->_makeAPICall('listAddContacts', $this->params->get('move_id'), array($data));
				}
				break;
		}
	}

	public function getlists()
	{
		$app = JFactory::getApplication();
		$this->params->set('api_key', $app->input->getString('name'));
		$this->params->set('api_pass', $app->input->getString('pass'));
		$contactLists = $this->_makeAPICall('listGet', "", 1, 100, "", "");

		foreach($contactLists as $rec)
		{
			$options[] = JHtml::_('select.option', $rec['id'], $rec['listname']);
		}

		return JHtml::_('select.genericlist', $options, $app->input->getString('fname'));

	}

	private function _userExists()
	{
		$user = $this->_getUser();

		return JArrayHelper::getValue($user, 'id', 0, 'INT');
	}

	private function _getUser()
	{
		//$user = $this->_makeAPICall('listGetContactDetails', $this->params->get('list_id'), JFactory::getUser()->get('email'));
		$user = $this->_makeAPICall('listGetContacts', $this->params->get('list_id'), JFactory::getUser()->get('email'), 1, 1, "", "");

		return array_key_exists(0, $user) ? $user[0] : array();
	}

	private function _makeAPICall()
	{
		require_once dirname(__FILE__) . '/lib/BMEAPI.class.php';
		$apiURL = 'http://www.benchmarkemail.com/api/1.0';
		$api    = new BMEAPI($this->params->get('api_key'), $this->params->get('api_pass'), $apiURL);

		$array = func_get_args();
		$what  = $array[0];
		unset($array[0]);

		try
		{
			$result = call_user_func_array(array($api, $what), $array);
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
		return JText::_('X_SMART_ACTIVE');
	}
}
