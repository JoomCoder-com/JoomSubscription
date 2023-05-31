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

class JoomsubscriptionActionHook extends JoomsubscriptionAction
{
	public function onActive($subscription)
	{
		if(!$this->params->get('hook_active'))
		{
			return;
		}

		$this->_run($this->params->get('hook_active'), $subscription);
	}

	public function onDisactive($subscription)
	{
		if(!$this->params->get('notify_disactive'))
		{
			return;
		}
		$http = $this->_run($this->params->get('notify_disactive'), $subscription);
	}

	private function _run($url, $subscription)
	{
		$http   = JHttpFactory::getHttp();
		$method = $this->params->get('hook_method', 'post');

		$data    = $this->_getData($subscription);
		$headers = $this->_getHeaders();
		if($method == 'post')
		{
			$http->post($url, $data, $headers);
		}
		else
		{
			$uri = JUri::getInstance($url);
			$uri->setQuery(array_merge($uri->getQuery(TRUE), $data));
			$http->get($url, $headers);
		}
	}

	private function _getData($subscription)
	{
		$user                                       = JFactory::getUser();
		$data                                       = array();
		$data[$this->params->get('plan_id')]        = $subscription->plan_id;
		$data[$this->params->get('user_id')]        = $subscription->user_id;
		$data[$this->params->get('user_subscr_id')] = $subscription->id;
		$data[$this->params->get('price')]          = $subscription->price;
		$data[$this->params->get('gateway_id')]     = $subscription->gateway_id;
		$data[$this->params->get('gateway')]        = $subscription->gateway;
		$data[$this->params->get('start_date')]     = $subscription->ctime;
		$data[$this->params->get('end_date')]       = $subscription->extime;

		$data[$this->params->get('username')] = $user->get('username');
		$data[$this->params->get('name')]     = $user->get('name');
		$data[$this->params->get('email')]    = $user->get('email');

		return $data;
	}

	private function _getHeaders()
	{
		$headers       = array();
		$param_headers = explode("\n", $this->params->get('headers', ''));
		foreach($param_headers as $header)
		{
			$head = explode(':', $header);
			if(count($head) < 2)
			{
				continue;
			}

			$headers[$head[0]] = $head[1];
		}

		return $headers;
	}

	public function getDescription()
	{
		$out = '';
		if($this->params->get('hook_active'))
		{
			$out .= '<b>' . JText::_('X_HOOK_ACTIVE') . '</b><br />';
			$out .= $this->params->get('hook_active') . '<br/>';
		}

		if($this->params->get('hook_disactive'))
		{
			$out .= '<b>' . JText::_('X_HOOK_DISACTIVE') . '</b><br />';
			$out .= $this->params->get('hook_disactive');
		}

		return $out;
	}
}
