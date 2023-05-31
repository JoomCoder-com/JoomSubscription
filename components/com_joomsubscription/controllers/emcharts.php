<?php
/**
 * JoomSubscription by JoomCoder
 * a component for Joomla (http://www.joomla.org)
 * Author Website: https://www.joomcoder.com/
 * @copyright Copyright (C) 2012 JoomCoder (https://www.joomcoder.com/). All rights reserved.
 * @license   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */
defined('_JEXEC') or die();

jimport('mint.mvc.controller.form');

class JoomsubscriptionControllerEmCharts extends MControllerForm
{
	public function sales()
	{
		$list = $this->_getData();

		$sales = $sorted = array();
		foreach($list as $item)
		{
			$sorted[$item->dt][] = $item->price;
		}

		if(empty($sorted))
		{
			JoomsubscriptionAjaxHelper::error(JText::_('EMR_CHART_NODATA'));
		}

		for($i = 0; $i < 30; $i++)
		{
			$date = mktime(0, 0, 0, date('m'), (date('d') - $i), date('Y'));
			$key = date('Y-m-d', $date);

			if(!empty($sorted[$key]) && is_array($sorted[$key]))
			{
				$sales[$i] = number_format(array_sum($sorted[$key]), 2, '.', '');
			}
			else
			{
				$sales[$i] = 0.00;
			}
		}

		krsort($sales);
		$sales = array_values($sales);

		JoomsubscriptionAjaxHelper::send($sales);
	}

	public function counts()
	{
		$list = $this->_getData();

		$counts = $sorted = array();
		foreach($list as $item)
		{
			$sorted[$item->dt][] = $item->price;
		}

		if(empty($sorted))
		{
			JoomsubscriptionAjaxHelper::error(JText::_('EMR_CHART_NODATA'));
		}

		for($i = 0; $i < 30; $i++)
		{
			$date = mktime(0, 0, 0, date('m'), (date('d') - $i), date('Y'));
			$key = date('Y-m-d', $date);

			if(!empty($sorted[$key]) && is_array($sorted[$key]))
			{
				$counts[$i] = count($sorted[$key]);
			}
			else
			{
				$counts[$i] = 0.00;
			}
		}

		krsort($counts);
		$counts = array_values($counts);

		JoomsubscriptionAjaxHelper::send($counts);
	}

	public function piemain()
	{
		$data = $this->_getData();

		if(empty($data))
		{
			JoomsubscriptionAjaxHelper::error(JText::_('EMR_CHART_NODATA'));
		}

		$out = array();

		foreach($data as $s)
		{
			@$out['groups']['list'][$s->gid]['plans'][$s->plan_id]['sum'] += $s->price;
			@$out['groups']['list'][$s->gid]['plans'][$s->plan_id]['count']++;
			@$out['groups']['list'][$s->gid]['plans'][$s->plan_id]['name'] = JText::_($s->plan_name);
			@$out['groups']['list'][$s->gid]['name'] = JText::_($s->group_name);
			@$out['groups']['list'][$s->gid]['sum'] += $s->price;
			@$out['groups']['list'][$s->gid]['count']++;
			@$out['groups']['sum'] += $s->price;
			@$out['groups']['count']++;
		}

		foreach($out['groups']['list'] AS $key =>  $group)
		{
			$out['groups']['list'][$key]['percent_sales'] = $out['groups']['sum'] ? $out['groups']['list'][$key]['sum'] / ($out['groups']['sum'] / 100) : 0;
			$out['groups']['list'][$key]['percent_count'] = $out['groups']['list'][$key]['count'] / ($out['groups']['count'] / 100);

			foreach($group['plans'] AS $pkey => $plans)
			{
				$out['groups']['list'][$key]['plans'][$pkey]['percent_sales'] =  $out['groups']['sum'] ? $out['groups']['list'][$key]['plans'][$pkey]['sum'] / ($out['groups']['sum'] / 100) : 0;
				$out['groups']['list'][$key]['plans'][$pkey]['percent_count'] =  $out['groups']['list'][$key]['plans'][$pkey]['count'] / ($out['groups']['count'] / 100);
			}
		}

		JoomsubscriptionAjaxHelper::send($out);

	}
	public function stack()
	{
		$list = $this->_getData();

		if(empty($list))
		{
			JoomsubscriptionAjaxHelper::error(JText::_('EMR_CHART_NODATA'));
		}

		$sales = $sorted = array();

		foreach($list as $item)
		{
			$sorted[$item->plan_id]['list'][$item->dt][] = $item->price;
			$sorted[$item->plan_id]['name'] = $item->plan_name;
		}

		foreach($sorted AS $plan_id => $plan)
		{
			for($i = 0; $i < 30; $i++)
			{
				$date = mktime(0, 0, 0, date('m'), (date('d') - $i), date('Y'));
				$key = date('Y-m-d', $date);

				if(!empty($plan['list'][$key]) && is_array($plan['list'][$key]))
				{
					$sales[$plan_id]['list'][$i] = number_format(array_sum($plan['list'][$key]), 2, '.', '');
					$sales[$plan_id]['count'][$i] = count($plan['list'][$key]);
				}
				else
				{
					$sales[$plan_id]['list'][$i] = 0.00;
					$sales[$plan_id]['count'][$i] = 0;
				}
			}
			krsort($sales[$plan_id]['list']);
			$sales[$plan_id]['list'] = array_values($sales[$plan_id]['list']);

			krsort($sales[$plan_id]['count']);
			$sales[$plan_id]['count'] = array_values($sales[$plan_id]['count']);

			$sales[$plan_id]['name'] = $plan['name'];
		}

		JoomsubscriptionAjaxHelper::send($sales);

	}

	private function _getData()
	{

		$db    = JFactory::getDbo();
		$query = $db->getQuery(TRUE);

		$query->select('s.price, s.plan_id, date(s.created) as dt');
		$query->from('#__joomsubscription_subscriptions AS s');
		$query->where('s.activated = 1');
		$query->where('s.created >= NOW() - INTERVAL 30 DAY');

		$query->select('p.name AS plan_name');
		$query->leftJoin('#__joomsubscription_plans AS p ON p.id = s.plan_id');

		$query->select('g.name AS group_name, g.id AS gid');
		$query->leftJoin('#__joomsubscription_plans_groups AS g ON g.id = p.group_id');

		$db->setQuery($query);
		$list = $db->loadObjectList();

		return $list;
	}

}