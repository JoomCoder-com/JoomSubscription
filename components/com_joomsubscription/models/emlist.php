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
class JoomsubscriptionModelEmList extends MModelList
{
	public $_processors = null;
	public $_plans = null;

	public function getPlans($id = null, $group_ids = array())
	{
		if($this->_plans === null)
		{
			$app = JFactory::getApplication();
			$user = JFactory::getUser();

			$query = $this->_db->getQuery(TRUE);

			$query->select('p.*');
			$query->from('#__joomsubscription_plans AS p');

			$query->select('g.name AS cname, g.description AS cdescr, g.params as cparams, g.id AS gid, g.image AS gimage');
			$query->leftJoin('#__joomsubscription_plans_groups AS g ON g.id = p.group_id');

			$query->where('p.published = 1');
			$query->where("p.access IN (" . implode(', ', $user->getAuthorisedViewLevels()) . ')');
			$query->where('p.invisible = 0');
			$query->where('g.published = 1');
			$query->where("g.access IN (" . implode(', ', $user->getAuthorisedViewLevels()) . ')');
			$query->order('g.ordering ASC');
			$query->order('p.ordering ASC');

			if(!empty($id))
			{
				if(!is_array($id))
				{
					$id = explode(',', $id);
				}
				\Joomla\Utilities\ArrayHelper::toInteger($id);
				$id = implode(',', $id);
				if($app->input->getInt('revert', 0) == 1)
				{
					$query->where("p.id NOT IN ($id)");
				}
				else
				{
					$query->where("p.id IN ($id)");
				}
			}

			if(!empty($group_ids))
			{
				if(is_array($group_ids))
				{
					$group_ids = implode(',', $group_ids);
				}
				$query->where("p.group_id IN ($group_ids)");
			}

			$this->_plans = $this->_getList($query);
		}

		return $this->_plans;
	}



	public function  getSubscrMUA($sid)
	{
		$sql = "SELECT sub.*, us.username, IF(sub.extime > NOW() OR sub.extime = '0000-00-00 00:00:00', 0, 1) AS expired FROM #__joomsubscription_subscriptions as sub
		JOIN #__users us ON sub.user_id = us.id WHERE sub.parent = '$sid' AND sub.published != -1";
		$this->_db->setQuery($sql);
		return $this->_db->loadObjectList();
	}

	/*public function getAlert($id, $cross, $params)
	{
		$db = JFactory::getDBO();
		$params = new JRegistry($params);
		$method = $params->get('calculate_method');
		$required = $params->get('required');
		$plansAct = $this->getUserActivePlans();
		//$res = ($required) ? array_diff(explode(",", $cross), $plansAct) : array();
		$res = (!$required) ? array() : array_diff(explode(",", $cross), $plansAct);
		$crossNames = $this->getCrossPlans(implode(",", $res));
		$crossPlans = '';
		$link = "index.php?option=com_joomsubscription&view=emplans&layout=form&id=" . implode(",", $res) . "&Itemid=" . JRequest::getInt('Itemid');
		if(count($crossNames) > 0)
		{
			foreach($crossNames as $val)
			{
				$crossPlans[] = "<a href='" . $link . "'>" . $val->name . "</a>";
			}
		}
		$alert = array();

		if(count($res) > 0)
		{
			$alert = $crossPlans;
		}

		if($required == 0)
		{
			if(count($res) == 0)
			{
				$alert = array();
			}
		}
		if($required == '1')
		{
			if(count($res) < count(explode(",", $cross)))
			{
				$alert = array();
			}
		}
		return $alert;

	}*/

	function grantPlans($parent)
	{
		$sql = "SELECT params FROM #__joomsubscription_plans WHERE id ='{$parent['sid']}'";
		$this->_db->setQuery($sql);
		$result = $this->_db->loadResult();
		$params = new JRegistry($result);
		$plans = $params->get('plans_grant');
		if($plans != '')
		{
			settype($plans, 'array');
			foreach($plans as $k => $plan)
			{
				$res = array('gateway' => 'granted', 'gateway_id' => $parent['gateway_id'], 'user_id' => $parent['user_id'], 'price' => 0, 'pay' => $parent['pay']);
				$res['sid'] = $plan;
				$this->store($res);
			}
		}
		return true;
	}

	function deactivatePlans($res)
	{
		$db = JFactory::getDBO();
		$user = JFactory::getUser();
		$sql = "SELECT params FROM #__joomsubscription_plans WHERE id = " . $res['sid'];
		$db->setQuery($sql);
		$params = $db->loadResult();
		$params = new JRegistry($params);
		$plans = $params->get('plans_deactivate');
		if(is_array($plans)) {
			$plans = implode(', ', $plans);
		}
		if($plans == '') {
			return;
		}
		$sql = "UPDATE #__joomsubscription_subscriptions SET published = '0' WHERE plan_id IN ({$plans}) AND user_id = '" . $res['user_id'] . "' AND ctime <= NOW()";
		$db->setQuery($sql);
		$db->query();

	}

	private function _getOffset($days_types, $days)
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

	function getCrossPeriod($plans, $uid)
	{
		$db = JFactory::getDBO();
		$sql = "SELECT extime FROM #__joomsubscription_subscriptions WHERE plan_id IN ({$plans}) AND user_id = '$uid'
		AND published = '1' AND extime > NOW()
		ORDER BY extime DESC LIMIT 1";
		$db->setQuery($sql);
		if($res = $db->loadResult())
		{
			return $res;
		}
		else
		{
			return false;
		}
	}

	function getMySQLDate($start, $offset, $type = 'SECOND')
	{
		$db = JFactory::getDBO();
		$sql = "SELECT '{$start}' + INTERVAL {$offset} {$type}";
		$db->setQuery($sql);
		return $db->loadResult();
	}
}
