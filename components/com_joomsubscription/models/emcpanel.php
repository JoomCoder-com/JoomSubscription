<?php
/**
 * JoomSubscription by JoomCoder
 * a component for Joomla! 3.0 CMS (http://www.joomla.org)
 * Author Website: https://www.joomcoder.com/
 * @copyright Copyright (C) 2012 JoomCoder (https://www.joomcoder.com/). All rights reserved.
 * @license   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die('Restricted access');

class JoomsubscriptionModelEmCpanel extends MModelList
{

	public function getAnalytics()
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(TRUE);

		$query->select('s.price, date(s.created) as dt');
		$query->from('#__joomsubscription_subscriptions AS s');
		$query->where('s.activated = 1');
		$query->where('s.created >= NOW() - INTERVAL 30 DAY');

		$db->setQuery($query);
		$list = $db->loadObjectList();

		$sales = $counts = $sorted = array();
		foreach($list as $item)
		{
			$sorted[$item->dt][] = $item->price;
		}

		if(empty($sorted))
		{
			return null;
		}

		$max_count = $max_sales = 0;
		for($i = 0; $i < 30; $i++)
		{
			$date = mktime(0, 0, 0, date('m'), (date('d') - $i), date('Y'));
			$key = date('Y-m-d', $date);

			if(!empty($sorted[$key]) && is_array($sorted[$key]))
			{

				$sales[$i] = number_format(array_sum($sorted[$key]), 2, '.', '');
				$counts[$i] = count($sorted[$key]);

				$max_sales = ($sales[$i] > $max_sales ? $sales[$i] : $max_sales);
				$max_count = ($counts[$i] > $max_count ? $counts[$i] : $max_count);

			}
			else
			{
				$sales[$i] = $counts[$i] = 0;
			}
		}

		krsort($counts);
		krsort($sales);

		$max_sales *= 1.05;
		$max_count *= 1.3;

		return array('counts' => implode(',', $counts), 'amount' => implode(',', $sales), 'max_sales' => $max_sales, 'max_count' => $max_count);
	}

	public function getLatest()
	{

		$db    = $this->getDbo();
		$query = $db->getQuery(TRUE);

		$query->select('s.*, p.name, p.params, u.username');
		$query->select('IF(s.ctime <= NOW(), 1, 0) as active');
		$query->select('IF(s.extime < NOW() AND s.extime != "0000-00-00 00:00:00", 1, 0) AS expired');
		$query->from('#__joomsubscription_subscriptions AS s');
		$query->where('s.activated = 1');
		$query->order('s.purchased DESC');
		$query->leftJoin('#__joomsubscription_plans AS p ON p.id = s.plan_id');
		$query->leftJoin('#__users AS u ON u.id = s.user_id');

		$list = $this->_getList($query, 0, 10);

		foreach($list AS &$item)
		{
			$item->params = new JRegistry($item->params);
			$item->img    = 'active.png';
			$item->state  = 'EM_ACTIVE';

			if($item->published == 0)
			{
				$item->state = 'EM_UNPUBLISHED';
				$item->img   = 'block.png';
			}

			if($item->expired || (($item->access_limit > 0) && ($item->access_count >= $item->access_limit)))
			{
				$item->state = 'EM_USED';
				$item->class = 'text-error';
				$item->img   = 'disabled.png';
			}

			if(!$item->active)
			{
				$item->class = 'muted';
				$item->state = 'EM_FUTURE';
				$item->img   = 'clock--minus.png';
			}

			if($item->activated == 0)
			{
				$item->state = 'EM_INACTIVE';
				$item->img   = 'exclamation-diamond.png';
			}
		}

		return $list;
	}

	public function getActive()
	{

		$db    = $this->getDbo();
		$query = $db->getQuery(TRUE);

		$query->select('s.*, p.name, p.params, u.username');
		$query->from('#__joomsubscription_subscriptions AS s');
		$query->where('s.activated = 0');
		$query->order('s.created DESC');
		$query->leftJoin('#__joomsubscription_plans AS p ON p.id = s.plan_id');
		$query->leftJoin('#__users AS u ON u.id = s.user_id');

		$list = $this->_getList($query, 0, 10);

		foreach($list AS &$item)
		{
			$item->params = new JRegistry($item->params);
		}

		return $list;
	}
}