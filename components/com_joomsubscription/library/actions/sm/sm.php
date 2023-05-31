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

class JoomsubscriptionActionSm extends JoomsubscriptionAction
{
	public function onActive($subscription)
	{
		$user = JFactory::getUser();
		$id   = $this->userExists($user->get('id'));

		$fields['action']     = 'create';
		$fields['email']      = $user->get('email');
		$fields['first_name'] = $user->get('username');
		if($id)
		{
			$fields['action'] = 'update';
			$fields['id']     = $id;
		}

		if($this->params->get('delivery_id'))
		{
			$fields['delivery_id'] = $this->params->get('delivery_id');
		}
		if($this->params->get('track_id'))
		{
			$fields['track_id'] = $this->params->get('track_id');
		}
		if($this->params->get('group_id'))
		{
			$fields['group_id'] = $this->params->get('group_id');
		}

		$this->makeApiCall($fields);
	}

	public function onDisactive($subscription)
	{
		if(!$this->params->get('disactivate'))
		{
			return;
		}

		$user = JFactory::getUser();
		$id   = $this->userExists($user->get('id'));

		if(!$id)
		{
			return TRUE;
		}

		$fields['action']      = 'update';
		$fields['id']          = $id;
		$fields['email']       = $user->get('email');
		$fields['first_name']  = $user->get('username');
		$fields['delivery_id'] = 0;
		$fields['track_id']    = 0;
		$fields['group_id']    = 0;

		$this->makeApiCall($fields);
	}

	private function userExists($email)
	{
		$fields = array(
			'action'        => 'list',
			'search[email]' => $email
		);

		$result = $this->makeApiCall($fields);

		return @$result['list']['elements'][0]['id'];
	}

	private function getHash($array)
	{
		$str = array();

		foreach($array AS $name => $val)
		{
			$str[] = "{$name}={$val}";
		}
		$str[] = "password=" . $this->params->get('api_pass');

		return md5(implode(':', $str));
	}

	private function makeApiCall($fields)
	{
		$fields['format'] = 'json';
		$fields['api_id'] = $this->params->get('api_id');
		if($this->params->get('api_key'))
		{
			$fields['api_key'] = $this->params->get('api_key');
		}
		else
		{
			$fields['hash'] = $this->getHash($fields);
		}


		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, 'http://api.smartresponder.ru/subscribers.html');
		curl_setopt($ch, CURLOPT_POST, count($fields));
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

		$result = curl_exec($ch);
		curl_close($ch);

		$result = json_decode($result, TRUE);

		if($result['result'] == 0)
		{
			JError::raiseWarning(100, 'Smartresponder API error: ' . $result['error']['message']);

			return;
		}

		return $result;

	}

	public function getDescription()
	{
		return JText::_('X_SMART_ACTIVE');
	}
}