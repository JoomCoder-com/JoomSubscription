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

class JoomsubscriptionActionAmigos extends JoomsubscriptionAction
{
	public function onActive($subscription)
	{
		if(!$this->params->get('amigos_url'))
		{
			return;
		}
		$app = JFactory::getApplication();

		$url = $this->params->get('amigos_url') . "/index.php?option=com_amigos&task=sale&amigos_id=" . $app->input->get('amigosid');
		$url .= "&amigos_ordertype=Joomsubscription&amigos_orderid={$subscription->id}&amigos_orderamount={$subscription->price}";
		$url .= "&amigos_ipaddress=" . getenv('REMOTE_ADDR');

		$http = JHttpFactory::getHttp();
		$http->get($url);
	}

	public function getDescription()
	{
		$out = '';
		if($this->params->get('amigos_url'))
		{
			$out .= $this->params->get('amigos_url');
		}

		return $out;
	}
}
