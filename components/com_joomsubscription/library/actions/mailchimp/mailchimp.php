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

class JoomsubscriptionActionMailchimp extends JoomsubscriptionAction
{
	public function onActive($subscription)
	{
		$this->_makeAPICall('lists/subscribe', array(
			"id"              => $this->params->get('list_id'),
			"email"           => array('email' => JFactory::getUser()->get('email')),
			"update_existing" => 1,
			"merge_vars"      => array("FNAME" => JFactory::getUser()->get('name'))
		));

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

		$actions     = JoomsubscriptionActionsHelper::getActions(array_values($plan_ids), 'mailchimp');
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
			case 2:
				$this->_makeAPICall('lists/unsubscribe', array(
					"id"            => $this->params->get('list_id'),
					"email"         => array('email' => JFactory::getUser()->get('email')),
					"delete_member" => $this->params->get('deactivate') == 1 ? 0 : 1
				));
				break;
			case 3:
				if($this->params->get('move_id'))
				{
					$this->_makeAPICall('lists/unsubscribe', array(
						"id"    => $this->params->get('list_id'),
						"email" => array('email' => JFactory::getUser()->get('email'))
					));

					$this->_makeAPICall('lists/subscribe', array(
						"id"              => $this->params->get('move_id'),
						"email"           => array('email' => JFactory::getUser()->get('email')),
						"update_existing" => 1,
						"merge_vars"      => array("FNAME" => JFactory::getUser()->get('name'))
					));
				}
				break;
		}
	}

	public function getlists()
	{
		$app = JFactory::getApplication();
		$this->params->set('api_key', $app->input->getString('api_key'));

		$lists = $this->_makeAPICall('lists/list', array('sort_field' => 'web'));

		if(empty($lists['data']))
		{
			return JText::_('MCH_NOLISTS');
		}

		$options = array();
		foreach($lists['data'] as $list)
		{
			$options[] = JHtml::_('select.option', $list['id'], $list['name']);
		}

		return JHtml::_('select.genericlist', $options, $app->input->getString('name'));

	}

	private function _makeAPICall($what, $fields = array())
	{
		require_once __DIR__ . '/lib/mailchimplib.php';

		$client = new MailChimp($this->params->get('api_key'));

		try
		{
			$result = $client->call($what, $fields);
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
		return JText::_('MCH_DESCRIPTION');
	}
}
