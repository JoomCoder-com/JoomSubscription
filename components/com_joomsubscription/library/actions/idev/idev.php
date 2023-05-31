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

class JoomsubscriptionActionIdev extends JoomsubscriptionAction
{
	public function onActive($subscription)
	{
		if(!$this->params->get('idev_url'))
		{
			return;
		}

		$url = $this->params->get('idev_url') . "/sale.php?profile=72198";
		$url .= "&idev_saleamt={$subscription->price}&idev_ordernum={$subscription->id}";
		$url .= "&ip_address=" . getenv('REMOTE_ADDR');

		$http = JHttpFactory::getHttp();
		$http->get($url);
	}

	public function getDescription()
	{
		$out = '';
		if($this->params->get('idev_url'))
		{
			$out .= $this->params->get('idev_url');
		}

		return $out;
	}
}
