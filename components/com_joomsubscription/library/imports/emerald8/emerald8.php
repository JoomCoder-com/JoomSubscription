<?php
/**
 * JoomSubscription by JoomCoder
 * a component for Joomla! 3.0 CMS (http://www.joomla.org)
 * Author Website: https://www.joomcoder.com/
 * @copyright Copyright (C) 2012 JoomCoder (https://www.joomcoder.com/). All rights reserved.
 * @license   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */
defined('_JEXEC') or die();

class JoomsubscriptionImportJoomsubscription8 extends JoomsubscriptionImport
{
	public function run($params)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(TRUE);
		$query->select('*');
		$query->from('#__jcs_plans');
		if($params->get('only_active'))
		{
			$query->where('published = 1');
		}
		$db->setQuery($query);
		$plans = $db->loadObjectList('id');

		$table_group        = JTable::getInstance('EmGroup', 'JoomsubscriptionTable');
		$table_plan         = JTable::getInstance('EmPlan', 'JoomsubscriptionTable');
		$table_subscription = JTable::getInstance('EmSubscription', 'JoomsubscriptionTable');

		$table_group->name      = 'Import Joomsubscription8';
		$table_group->published = 0;
		$table_group->access    = 1;
		$table_group->ctime     = JDate::getInstance()->toSql();
		$table_group->check();
		$table_group->store();
		$group_id = $table_group->id;
		$plan_ids = array();
		$result   = array('plans' => 0, 'subscriptions' => 0);

		foreach($plans as $plan)
		{
			$to_save                         = array();
			$to_save['name']                 = $plan->name;
			$to_save['group_id']             = $group_id;
			$to_save['published']            = $plan->published;
			$to_save['ctime']                = $plan->ctime;
			$to_save['grant_reg']            = $plan->grant_reg;
			$to_save['grant_new']            = $plan->grant_new;
			$to_save['access']               = 1;
			$to_save['invisible']            = $plan->invisible;
			$to_save['invisible_in_history'] = $plan->invisible_in_history;

			$table_plan->bind($to_save);
			$table_plan->check();
			if($table_plan->store())
			{
				$result['plans']++;
			}
			$plan_ids[$plan->id] = $table_plan->id;
			$table_plan->reset();
			$table_plan->id = NULL;
		}

		$query = 'SELECT * FROM #__jcs_user_subscr WHERE subscription_id IN(' . implode(',', array_keys($plan_ids)) . ')';
		$db->setQuery($query);
		$subscrs = $db->loadObjectList();

		foreach($subscrs as $subscr)
		{
			$to_save                 = array();
			$to_save['user_id']      = $subscr->user_id;
			$to_save['plan_id']      = $plan_ids[$subscr->subscription_id];
			$to_save['published']    = $subscr->published;
			$to_save['ctime']        = $subscr->ctime;
			$to_save['extime'] 		 = $subscr->extime;
			$to_save['created']      = $subscr->created;
			$to_save['gateway_id']   = $subscr->gateway_id;
			$to_save['gateway']      = $subscr->gateway;
			$to_save['price']        = $subscr->price;
			$to_save['access_limit'] = $subscr->access_limit;
			$to_save['access_count'] = $subscr->access_count;
			$to_save['activated']    = $subscr->never_activated ? 0 : 1;

			$plan_params = new JRegistry($plans[$subscr->subscription_id]->params);
			$to_save['access_count_mode'] = $plan_params->get('count_limit_mode');


			$table_subscription->bind($to_save);
			$table_subscription->check();
			if($table_subscription->store())
			{
				$result['subscriptions']++;
			}
			$table_subscription->reset();
			$table_subscription->id = NULL;
		}

		return $result;
	}

	public function check()
	{
		$db = JFactory::getDbo();
		$db->setQuery('SHOW TABLES LIKE "%_jcs_plans"');
		$result = $db->loadResult();
		if(!$result)
		{
			JError::raiseWarning(403, JText::_('JOOMSUBSCRIPTION8_TABLES_NOTEXIST'));

			return FALSE;
		}
		$db->setQuery('SELECT COUNT(*) FROM #__jcs_plans');
		$result = $db->loadResult();
		if(!$result)
		{
			JError::raiseWarning(403, JText::_('JOOMSUBSCRIPTION8_TABLE_PLAN_EMPTY'));

			return FALSE;
		}

		return TRUE;
	}
}
