<?php
/**
 * JoomSubscription by JoomCoder
 * a component for Joomla (http://www.joomla.org)
 * Author Website: https://www.joomcoder.com/
 *
 * @copyright Copyright (C) 2012 JoomCoder (https://www.joomcoder.com/). All rights reserved.
 * @license   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */
defined('_JEXEC') or die('Restricted access');

class JoomsubscriptionTableEmSubscription extends JTable
{

	function __construct(&$db)
	{
		parent::__construct('#__joomsubscription_subscriptions', 'id', $db);
	}

	public function check()
	{
		if(!$this->params)
		{
			$db = JFactory::getDbo();
			$db->setQuery("SELECT params FROM #__joomsubscription_plans WHERE id = ".$this->plan_id);
			$params = new JRegistry($db->loadResult());

			$this->params = json_encode(array('properties' => array(
				'currency' => $params->get('properties.currency', 'USD'),
				'layout_price' => $params->get('properties.layout_price', '00Sign')
			)));
		}
		return TRUE;
	}

	public function create($gateway, $gateway_id, $user_id, $plan_id, $price)
	{

		$this->load(array('gateway_id' => $gateway_id));

		if(empty($this->id))
		{
			$this->load(
				array(
					'plan_id'   => $plan_id,
					'user_id'   => $user_id,
					'published' => 0,
					'activated' => 0,
				)
			);

			if(empty($this->id))
			{
				$new = array(
					'created'    => JDate::getInstance()->toSql(),
					'plan_id'    => $plan_id,
					'user_id'    => $user_id,
					'published'  => 0,
					'price'      => $price,
					'activated'  => 0,
					'gateway_id' => $gateway_id,
					'gateway'    => $gateway
				);
				$this->save($new);
			}
		}
	}

	public function add_new($plan, $gateway, $price = NULL)
	{

		if(!empty($this->gateway_id) && !$this->load(array('gateway_id' => $gateway), FALSE))
		{
			$this->id              = NULL;
			$this->ctime           = NULL;
			$this->extime          = NULL;
			$this->created         = JDate::getInstance('now')->toSql();
			$this->plan_id         = $plan->id;
			$this->activated       = 0;
			$this->gateway_id      = $gateway;
			$this->access_count    = 0;
			$this->track_active    = 0;
			$this->track_disactive = 0;

			$price == NULL ? NULL : $this->price = $price;

			$this->store();
		}
	}

	public function set_period($plan)
	{
		$db = JFactory::getDbo();

		if((int)$this->ctime)
		{
			return;
		}

		if($this->parent > 0)
		{
			$sql = "SELECT ctime, extime FROM #__joomsubscription_subscriptions WHERE id = " . $this->parent;
			$db->setQuery($sql);
			$parent = $db->loadObject();

			$this->ctime  = $parent->ctime;
			$this->extime = $parent->extime;
		}
		elseif($plan->params->get('properties.date_from') && $plan->params->get('properties.date_to'))
		{
			$this->ctime  = $plan->params->get('properties.date_from') . ' 00:00:00';
			$this->extime = $plan->params->get('properties.date_to') . ' 23:59:59';
		}
		elseif($plan->params->get('properties.date_to'))
		{
			$start = $this->_get_start($plan);

			$db->setQuery("SELECT FROM_UNIXTIME($start)");
			$this->ctime  = $db->loadResult();
			$this->extime = $plan->params->get('properties.date_to') . ' 23:59:59';
		}
		elseif($plan->params->get('properties.date_fixed'))
		{
			$db->setQuery("SELECT NOW()");
			$this->ctime = $db->loadResult();

			$pattern = "%d-%d-%d 23:59:59";
			switch($plan->params->get('properties.date_fixed'))
			{
				case 1:
					//$endtime = mktime(0, 0, 0, date('n'), date('j'), date('Y') + 101);
					$this->extime = '0000-00-00 00:00:00';

					return;
					break;
				case 2:
					$endtime = mktime(0, 0, 0, date('n'), date('j'), date('Y'));
					break;
				case 3:
					$week    = 6 - date('w');
					$endtime = mktime(0, 0, 0, date('n'), date('j') + $week, date('Y'));
					break;
				case 4:
					$endtime = mktime(0, 0, 0, date('n') + 1, 0, date('Y'));
					break;
				case 5:
					$endtime = mktime(0, 0, 0, 12, 31, date('Y'));
					break;
			}
			$this->extime = sprintf($pattern, date('Y', $endtime), date('m', $endtime), date('d', $endtime));
		}
		else
		{
			$start = $this->_get_start($plan);

			//$end = ($start + $this->_get_offset($plan->days_type, $plan->days));

			$db->setQuery("SELECT FROM_UNIXTIME($start)");
			$this->ctime = $db->loadResult();

			if($plan->days >= 100 && $plan->days_type == 'years')
			{
				$this->extime = '0000-00-00 00:00:00';
			}
			else
			{
				$db->setQuery("SELECT FROM_UNIXTIME($start) + INTERVAL $plan->days " . rtrim($plan->days_type, 's'));
				$this->extime = $db->loadResult();
			}
		}


		if(!empty($this->fields))
		{
			$fields = json_decode($this->fields, TRUE);
			$fields_list = MModelBase::getInstance('EmPayment', 'JoomsubscriptionModel')->getAddonFields($plan, $fields);
			foreach($fields_list AS $field)
			{
				$dates = $field->affectDates($this);
				if(is_array($dates) && !empty($dates) && count($dates) == 2)
				{
					$this->ctime = $dates['ctime'];
					$this->extime = $dates['extime'];
				}
			}
		}
	}

	private function _get_start($plan)
	{
		$db   = JFactory::getDbo();
		$last = array();

		if($plan->params->get('crossplans.cp_plans'))
		{
			$last = $plan->params->get('crossplans.cp_plans');
		}
		if($plan->params->get('properties.cl_period') == 2)
		{
			$last[] = $this->plan_id;
		}

		\Joomla\Utilities\ArrayHelper::toInteger($last);

		$db->setQuery("SELECT UNIX_TIMESTAMP(NOW())");
		$start = $db->loadResult();

		if(!empty($last))
		{
			if($new_start = JoomsubscriptionHelper::userLastPlan($this->user_id, implode(',', $last)))
			{
				$start = $new_start->etm;
			}
		}

		return $start;
	}

	private function _get_offset($days_types, $days)
	{
		$offset = 0;

		switch($days_types)
		{
			case "weeks";
				$offset = ($days * 7) * 86400;
				break;

			case "months";
				$offset = mktime(date('H'), date('i'), date('s'), (date('m') + $days), date('d'), date('Y')) - time();
				break;

			case "years";
				$offset = ($days * 365) * 86400;
				break;

			case "hours";
				$offset = $days * 3600;
				break;

			case "days";
				$offset = $days * 86400;
		}

		return $offset;
	}

}
