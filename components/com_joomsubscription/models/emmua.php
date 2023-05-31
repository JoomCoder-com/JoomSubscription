<?php
/**
 * JoomSubscription by JoomCoder
 * a component for Joomla (http://www.joomla.org)
 * Author Website: https://www.joomcoder.com/
 * @copyright Copyright (C) 2012 JoomCoder (https://www.joomcoder.com/). All rights reserved.
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */
defined('_JEXEC') or die();

jimport('mint.mvc.model.base');
class JoomsubscriptionModelEmMua extends MModelList
{
	public function  getSubscrMUA($sid)
	{
		$sql = "SELECT sub.*, us.username, IF(sub.extime > NOW() OR sub.extime = '0000-00-00 00:00:00', 0, 1) AS expired FROM #__joomsubscription_subscriptions as sub
		JOIN #__users us ON sub.user_id = us.id WHERE sub.parent = '$sid'";
		$this->_db->setQuery($sql);
		return $this->_db->loadObjectList();
	}
}