<?php
/**
 * JoomSubscription by JoomCoder
 * a component for Joomla! 3.0 CMS (http://www.joomla.org)
 * Author Website: https://www.joomcoder.com/
 *
 * @copyright Copyright (C) 2012 JoomCoder (https://www.joomcoder.com/). All rights reserved.
 * @license   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die('Restricted access');

class JoomsubscriptionModelEmPayment extends MModelList
{
	public function getPlan($id)
	{
		$user  = JFactory::getUser();
		$query = $this->_db->getQuery(TRUE);

		$query->select('p.*');
		$query->from('#__joomsubscription_plans AS p');

		$query->select('g.name AS cname, g.description AS cdescr, g.params as cparams, g.id AS gid');
		$query->leftJoin('#__joomsubscription_plans_groups AS g ON g.id = p.group_id');

		$query->where('p.published = 1');
		$query->where('p.invisible = 0');
		$query->where('g.published = 1');
		$query->where("g.access IN (" . implode(', ', $user->getAuthorisedViewLevels()) . ')');
		$query->where("p.id = {$id}");

		$this->_db->setQuery($query);

		return $this->_db->loadObject();
	}

	public function getAddonFields($plan, $defaults = array())
	{
		if(!$plan->params->get('properties.fields'))
		{
			return array();
		}

		$db  = JFactory::getDbo();
		$app = JFactory::getApplication();

		$db->setQuery("SELECT * FROM `#__joomsubscription_fields`
			WHERE id IN (" . implode(',', $plan->params->get('properties.fields')) . ")
			ORDER BY ordering ASC");

		$list = $db->loadObjectList();
		$out  = array();
		$home = JPATH_ROOT . '/components/com_joomsubscription/library/fields/';
		if(empty($defaults))
		{
			$defaults = $app->input->get('fields', array(), 'array');
		}

		foreach($list AS $item)
		{
			$file = $home . $item->type . "/" . $item->type . ".php";

			if(!JFile::exists($file))
			{
				continue;
			}

			include_once $file;
			$class = "JoomsubscriptionField" . ucfirst($item->type);

			if(!class_exists($class))
			{
				continue;
			}

			$key = 'com_joomsubscription.payment' . $plan->id . '.' . $item->id;

			$old_val = $app->getUserState($key, NULL);

			if($app->input->get('postprocess'))
			{
				$app->setUserState($key, @$defaults[$item->id]);
				$old_val = @$defaults[$item->id];
			}

			$obj          = new $class($item);
			$obj->root    = dirname($file);
			$obj->plan    = $plan;
			$obj->default = $old_val;

			$out[$item->ordering] = $obj;
		}

		return $out;
	}

	public function getCouponsNumber($plan_id)
	{
		$db  = JFactory::getDBO();
		$sql = "SELECT * FROM #__joomsubscription_coupons WHERE
			published = '1'
			AND (extime = '0000-00-00 00:00:00' OR extime > NOW())
			AND trash = 0";
		$db->setQuery($sql);

		$all   = $db->loadObjectList();
		$user  = JFactory::getUser();
		$total = 0;

		foreach($all AS $coupon)
		{
			if($coupon->user_ids && $coupon->user_ids != $user->get('id'))
			{
				continue;
			}

			if($coupon->plan_ids)
			{
				$pids = json_decode($coupon->plan_ids, TRUE);
				if(!in_array($plan_id, $pids))
				{
					continue;
				}
			}

			if($coupon->use_num > 0 && $coupon->used_num >= $coupon->use_num)
			{
				continue;
			}

			$total++;
		}

		return $total;
	}

}