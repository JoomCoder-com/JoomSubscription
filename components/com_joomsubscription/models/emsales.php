<?php
/**
 * JoomSubscription by JoomCoder
 * a component for Joomla (http://www.joomla.org)
 * Author Website: https://www.joomcoder.com/
 *
 * @copyright Copyright (C) 2012 JoomCoder (https://www.joomcoder.com/). All rights reserved.
 * @license   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */
defined('_JEXEC') or die();

jimport('mint.mvc.model.base');

class JoomsubscriptionModelEmSales extends MModelList
{
	public function __construct($config = array())
	{
		if(empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				's.id',
				's.gateway',
				's.published',
				's.access_limit',
				's.access_count',
				's.created',
				's.price',
				'days',
				'group_name'
			);
		}

		parent::__construct($config);
	}

	public function getListQuery()
	{
		$user = JFactory::getUser();

		$db    = $this->getDbo();
		$query = $db->getQuery(TRUE);

		$query->select('s.*, s.id as sid');
		$query->select('IF(s.ctime <= NOW(), 1, 0) as active');
		$query->select('IF(s.extime < NOW() AND s.extime != "0000-00-00 00:00:00", 1, 0) AS expired');
		$query->select('IF(s.ctime <= NOW(), (TO_DAYS(s.extime) - TO_DAYS(NOW())), (TO_DAYS(s.extime) - TO_DAYS(s.ctime)) ) as days');
		$query->select('(TO_DAYS(s.extime) - TO_DAYS(s.ctime))  as days_enable');
		$query->from('#__joomsubscription_subscriptions AS s');

		$query->select('p.id as pid, p.name, p.group_id, p.params as plan_params, p.invisible');
		$query->leftJoin('#__joomsubscription_plans AS p ON p.id = s.plan_id');

		$query->select('g.name as group_name');
		$query->leftJoin('#__joomsubscription_plans_groups as g ON g.id = p.group_id');

		$query->select('u.name as uname, u.username, u.email, u.id as uid');
		$query->leftJoin('#__users as u ON u.id = s.user_id');

		$search = $this->getState('filter.search');
		if($search)
		{
			switch(substr($search, 0, 4))
			{
				case 'cpn:':
					$query->leftJoin('#__joomsubscription_coupons_history as ch ON ch.subscription_id = s.id');
					$query->where('ch.coupon_id = ' . (int)str_replace('cpn:', '', $search));
					break;

				default;
					$w[] = "s.price    LIKE '%" . $search . "%'";
					$w[] = "u.name     LIKE '%" . $search . "%'";
					$w[] = "u.username LIKE '%" . $search . "%'";
					$w[] = "u.email    LIKE '%" . $search . "%'";
					$w[] = "s.gateway  LIKE '%" . $search . "%'";
					$w[] = "s.gateway_id = '" . $search . "'";
					$w[] = "s.id = '" . $search . "'";

					$query->leftJoin('#__joomsubscription_invoice_to as i ON i.id = s.invoice_id');
					$w[] = "i.fields    LIKE '%" . $search . "%'";

					$query->where('(' . implode(' OR ', $w) . ')');
			}
		}


		$group_id = (int)$this->getState('group_id');
		if(!empty($group_id))
		{
			$query->where('p.group_id = ' . $group_id);
		}

		$group_id = (int)$this->getState('plan_id');
		if(!empty($group_id))
		{
			$query->where('p.id = ' . $group_id);
		}

		$state = (int)$this->getState('state');
		if(!empty($state))
		{
			switch($state)
			{
				case 1:
					$query->where('s.activated = 1');
					$query->where("(s.extime > NOW() OR s.extime = '0000-00-00 00:00:00')");
					$query->where("s.ctime < NOW()");
					$query->where("s.published = 1");
					$query->select("IF(s.access_limit > 0 AND s.access_count_mode > 0,
							IF(s.access_count >= s.access_limit, 0, 1),
						1) AS cl");
					$query->having("cl > 0");
					break;
				case 2:
					$query->where('activated = 0');
					break;
			}
		}

		$orderCol  = $this->state->get('list.ordering');
		$orderDirn = $this->state->get('list.direction');
		$query->order($db->escape($orderCol . ' ' . $orderDirn));

		//echo $query;

		return $query;
	}

	public function getStoreId($id = NULL)
	{
		$id .= ':emsales';
		$id .= ':' . $this->getState('list.start');
		$id .= ':' . $this->getState('list.limit');
		$id .= ':' . $this->getState('list.ordering');
		$id .= ':' . $this->getState('list.direction');
		$id .= ':' . $this->getState('filter.group');

		return md5($this->context . ':' . $id);
	}

	protected function populateState($ordering = NULL, $direction = NULL)
	{
		$app = JFactory::getApplication();

		$group_id = $app->getUserStateFromRequest($this->context . '.filter.group_id', 'filter_group');
		$this->setState('group_id', $group_id);

		$plan_id = $app->getUserStateFromRequest($this->context . '.filter.plan_id', 'filter_plan');
		$this->setState('plan_id', $plan_id);

		$state = $app->getUserStateFromRequest($this->context . '.filter.state', 'filter_state');
		$this->setState('state', $state);

		$search = $app->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		parent::populateState('s.created', 'desc');
	}

	public function getSubscriptionCouponInfo($sid)
	{
		$db  = JFactory::getDBO();
		$sql = "SELECT ch.price, ch.discount, ch.discount_type, cc.value, cc.id
		FROM #__joomsubscription_coupons_history ch
		LEFT JOIN #__joomsubscription_coupons cc ON cc.id = ch.coupon_id
		WHERE ch.subscription_id = '$sid'";
		$db->setQuery($sql);

		return $db->loadObject();
	}

	public function getSt()
	{
		$options = array(
			JHtml::_('select.option', '0', JText::_('JALL')),
			JHtml::_('select.option', '1', JText::_('EMR_ONLYACTIVE')),
			JHtml::_('select.option', '2', JText::_('EMR_WAITINGAPROVE'))
		);

		return $options;
	}

	public function getGroups($empty_value = FALSE)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(TRUE);
		$query->select('id as value, name as text');
		$query->from('#__joomsubscription_plans_groups');
		$query->order('text');
		$db->setQuery($query);
		$result = $db->loadObjectList();

		if($empty_value)
		{
			$empty_obj        = new stdClass();
			$empty_obj->value = '';
			$empty_obj->text  = 'JOPTION_SELECT_GROUP';
			array_unshift($result, $empty_obj);
		}

		return $result;
	}

	public function getPlans($empty_value = FALSE)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(TRUE);
		$query->select('id as value, name as text');
		$query->from('#__joomsubscription_plans');
		$query->order('text');
		$db->setQuery($query);
		$result = $db->loadObjectList();

		if($empty_value)
		{
			$empty_obj        = new stdClass();
			$empty_obj->value = '';
			$empty_obj->text  = 'JOPTION_SELECT_PLAN';
			array_unshift($result, $empty_obj);
		}

		return $result;
	}
}

?>