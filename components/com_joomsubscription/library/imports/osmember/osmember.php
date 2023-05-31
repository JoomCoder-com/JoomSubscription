<?php
/**
 * JoomSubscription by JoomCoder
 * a component for Joomla! 3.0 CMS (http://www.joomla.org)
 * Author Website: https://www.joomcoder.com/
 * @copyright Copyright (C) 2012 JoomCoder (https://www.joomcoder.com/). All rights reserved.
 * @license   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */
defined('_JEXEC') or die();

class JoomsubscriptionImportOsmember extends JoomsubscriptionImport
{
	public $result = array();

	public function run($config)
	{
		$new_plan = JTable::getInstance('EmPlan', 'JoomsubscriptionTable');
		$db       = JFactory::getDbo();

		$db->setQuery("SELECT * FROM #__osmembership_plans");
		$levels   = $db->loadObjectList();
		$group_id = $this->_getGroupID();

		foreach($levels AS $level)
		{
			$params = array(
				'properties' => array(
					'price'     => $level->price,
					'days'      => $level->subscription_length,
					'days_type' => 'days',
				)
			);

			$save = array(
				'published'  => $level->published,
				'name'       => $level->title,
				'group_id'   => $group_id,
				'params'     => json_encode($params),
				'ordering'   => $level->ordering,
				'access'     => $level->access,
				'access_pay' => 1,
			);

			$new_plan->bind($save);
			$new_plan->check();
			if($new_plan->store())
			{
				$this->result['plans']++;
			}

			$this->getSubscritpions($level, $new_plan, $config);

			$new_plan->reset();
			$new_plan->id = NULL;

		}

		return $this->result;
	}

	private function getSubscritpions($level, $plan, $config)
	{
		static $invnum = 0;

		$subscriptions = JTable::getInstance('EmSubscription', 'JoomsubscriptionTable');

		$db  = JFactory::getDbo();
		$sql = "SELECT * FROM `#__osmembership_subscribers` WHERE user_id > 0 AND `plan_id` = " . (int)$level->id . " ORDER BY payment_date ASC";

		if($config->get('only_active'))
		{
			$sql .= ' AND to_date > NOW()';
		}

		$db->setQuery($sql);
		$subscrs = $db->loadObjectList();


		foreach($subscrs as $subscr)
		{
			$save = array(
				'user_id'           => $subscr->user_id,
				'plan_id'           => $plan->id,
				'published'         => $subscr->published == 1 ? 1 : 0,
				'invoice_num'       => ++$invnum,
				'invoice_id'        => $this->_getUserBillTo($subscr),
				'ctime'             => $subscr->from_date,
				'extime'            => $subscr->to_date,
				'created'           => $subscr->created_date,
				'gateway_id'        => $subscr->transaction_id ? $subscr->transaction_id : strtoupper(substr(md5($subscr->id), 0, 8)),
				'gateway'           => $subscr->payment_method ? str_replace('os_', 'OS ', $subscr->payment_method) : 'OS Import',
				'price'             => $subscr->gross_amount,
				'activated'         => 1,
				'access_limit'      => 0,
				'access_count'      => 0,
				'access_count_mode' => 0
			);

			$subscriptions->bind($save);
			$subscriptions->check();
			if($subscriptions->store())
			{
				$this->result['subscriptions']++;
			}

			JoomsubscriptionHelper::activateSubscription($subscriptions);

			$subscriptions->reset();
			$subscriptions->id = NULL;
		}
	}

	private function _getUserBillTo($subscr)
	{
		static $out = array();

		if(!empty($out[$subscr->user_id]))
		{
			return $out[$subscr->user_id];
		}

		$pattern = '{"billto":"%s","country":"%s","state":"%s","zip":"%s","address":"%s","tax_id":"%s","phone":"%s"}';

		$data = array(
			'id'      => NULL,
			'user_id' => $subscr->user_id,
			'fields'  => sprintf($pattern, $subscr->first_name . ' ' . $subscr->last_name, $this->_getCountry($subscr->country), $subscr->state,
				$subscr->zip, trim($subscr->address . ' ' . $subscr->address2), '', $subscr->phone ? $subscr->phone : $subscr->fax)
		);

		$invto = JTable::getInstance('EmInvoiceTo', 'JoomsubscriptionTable');
		$invto->save($data);

		$out[$subscr->user_id] = $invto->id;

		return $out[$subscr->user_id];
	}

	private function _getCountry($id)
	{
		static $out = array();

		if(array_key_exists($id, $out))
		{
			return $out[$id];
		}

		$db = JFactory::getDbo();
		$db->setQuery("SELECT country_2_code FROM #__osmembership_countries WHERE `name` = '{$id}'");
		$out[$id] = $db->loadResult();

		return $out[$id];
	}


	private function _getOrder($id)
	{
		$db = JFactory::getDbo();
		$db->setQuery("SELECT * FROM #__osemsc_order WHERE order_id = {$id}");

		$item         = $db->loadObject();
		$item->params = json_decode($item->params);

		return $item;
	}

	private function _getGroupID()
	{
		static $id;

		if(empty($id))
		{

			$db = JFactory::getDbo();
			$db->setQuery("SELECT * FROM #__joomsubscription_plans_groups LIMIT 1");
			$groups = $db->loadObject();

			if(empty($groups->id))
			{
				$save = array(
					'params'    => json_encode(array('properties' => array('template' => 'default'))),
					'ctime'     => JDate::getInstance()->toSql(),
					'access'    => 1,
					'language'  => '*',
					'ordering'  => 1,
					'name'      => 'Default Group',
					'published' => 1
				);

				$groups = JTable::getInstance('EmGroup', 'JoomsubscriptionTable');
				$groups->save($save);
				$groups->reorder();
			}

			$id = $groups->id;
		}

		return $id;
	}

	public function check()
	{
		$db = JFactory::getDbo();
		$db->setQuery('SHOW TABLES LIKE "%_osmembership_plans"');
		$result = $db->loadResult();
		if(!$result)
		{
			JError::raiseWarning(403, JText::_('OS_TABLES_NOTEXIST'));

			return FALSE;
		}
		$db->setQuery('SELECT COUNT(*) FROM #__osmembership_subscribers');
		$result = $db->loadResult();
		if(!$result)
		{
			JError::raiseWarning(403, JText::_('OS_TABLES_NOTEXIST'));

			return FALSE;
		}

		return TRUE;
	}
}
