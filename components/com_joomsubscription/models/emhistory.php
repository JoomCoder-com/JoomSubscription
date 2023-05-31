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
class JoomsubscriptionModelEmHistory extends MModelList
{

	public function getListQuery()
	{
		$user = JFactory::getUser();

		$db = $this->getDbo();
		$query = $db->getQuery(true);

		$query->select('u.gateway, u.fields, u.user_id, u.invoice_num, u.params, u.activated, u.gateway_id, u.id as sid, u.price, u.access_limit, u.access_count, u.published, u.ctime, u.extime,	u.extime as nfextime, u.parent, u.note');
		$query->select('IF(u.ctime <= NOW(), 1, 0) as active');
		$query->select('IF(u.extime < NOW() AND u.extime != "0000-00-00 00:00:00", 1, 0) AS expired');
		$query->select('IF(u.ctime <= NOW(), (TO_DAYS(u.extime) - TO_DAYS(NOW())), (TO_DAYS(u.extime) - TO_DAYS(u.ctime)) ) as days');
		$query->select('(TO_DAYS(u.extime) - TO_DAYS(u.ctime))  as days_enable');
		$query->from('#__joomsubscription_subscriptions AS u');

		$query->select('s.id, s.name, s.group_id, s.params as plan_params, s.invisible');
		$query->leftJoin('#__joomsubscription_plans AS s ON s.id = u.plan_id');

		$query->select('g.name as `group`');
		$query->leftJoin('#__joomsubscription_plans_groups as g on g.id = s.group_id');

		$query->where('s.published = 1');
		$query->where('s.invisible_in_history = 0');

		//if(!JoomsubscriptionHelper::isModer(JFactory::getUser()))
		{
			$query->where("u.user_id = " . $user->get('id'));
		}

		$group_id = (int)$this->getState('filter.group');
		if(!empty($group_id))
		{
			$query->where('s.group_id = ' . $group_id);
		}

		$orderCol = $this->state->get('list.ordering');
		$orderDirn = $this->state->get('list.direction');
		$query->order($db->escape($orderCol . ' ' . $orderDirn));

		return $query;
	}

	public function getStoreId($id = NULL)
	{
		$id .= ':' . $this->getState('list.start');
		$id .= ':' . $this->getState('list.limit');
		$id .= ':' . $this->getState('list.ordering');
		$id .= ':' . $this->getState('list.direction');
		$id .= ':' . $this->getState('filter.cat');

		return md5($this->context . ':' . $id);
	}

	protected function populateState($ordering = null, $direction = null)
	{
		$app = JFactory::getApplication();

		$cat = $app->getUserStateFromRequest($this->context . '.filter.cat_id', 'filter_cat');
		$this->setState('filter.cat', $cat);

		parent::populateState('u.ctime', 'desc');
	}

	public function getSubscriptionCouponInfo($sid)
	{
		$db = JFactory::getDBO();
		$sql = "SELECT ch.price, ch.discount, ch.discount_type, cc.value, cc.id
		FROM #__joomsubscription_coupons_history ch
		LEFT JOIN #__joomsubscription_coupons cc ON cc.id = ch.coupon_id
		WHERE ch.subscription_id = '$sid'";
		$db->setQuery($sql);
		return $db->loadObject();
	}

}
