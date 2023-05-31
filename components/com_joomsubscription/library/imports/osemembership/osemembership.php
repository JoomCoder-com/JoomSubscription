<?php
/**
 * JoomSubscription by JoomCoder
 * a component for Joomla! 3.0 CMS (http://www.joomla.org)
 * Author Website: https://www.joomcoder.com/
 * @copyright Copyright (C) 2012 JoomCoder (https://www.joomcoder.com/). All rights reserved.
 * @license   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */
defined('_JEXEC') or die();

class JoomsubscriptionImportOsemembership extends JoomsubscriptionImport
{
	public $result = array();

	public function run($config)
	{
		$new_plan = JTable::getInstance('EmPlan', 'JoomsubscriptionTable');
		$db       = JFactory::getDbo();

		$db->setQuery("SELECT * FROM #__osemsc_ext WHERE `type` = 'payment'");
		$levels = $db->loadObjectList();

		foreach($levels AS $level)
		{

			$group_id = $this->_getGroupID($level->id);

			$level_params = json_decode($level->params);
			foreach($level_params AS $old_plan)
			{
				$params = array(
					'properties' => array(
						'price'     => $old_plan->price,
						'days'      => $old_plan->recurrence_num,
						'days_type' => $old_plan->recurrence_unit . 's',
					)
				);

				$save = array(
					'published'  => 1,
					'name'       => $old_plan->optionname,
					'group_id'   => $group_id,
					'params'     => json_encode($params),
					'ordering'   => $old_plan->ordering,
					'access'     => 1,
					'access_pay' => 1,
				);

				$new_plan->bind($save);
				$new_plan->check();
				if($new_plan->store())
				{
					$this->result['plans']++;
				}

				$this->getSubscritpions($level->id, $new_plan, $old_plan, $config);

				$new_plan->reset();
				$new_plan->id = NULL;
			}

		}

		return $this->result;
	}

	private function getSubscritpions($level, $plan, $old, $config)
	{
		$subscriptions = JTable::getInstance('EmSubscription', 'JoomsubscriptionTable');

		$db = JFactory::getDbo();
		$sql = "SELECT * FROM `#__osemsc_member` WHERE `msc_id` = " . (int)$level;

		if($config->get('only_active'))
		{
			$sql .= ' AND expired_date > NOW() AND state = 1';
		}

		$db->setQuery($sql);
		$subscrs = $db->loadObjectList();

		foreach($subscrs as $subscr)
		{
			$params = json_decode($subscr->params);

			$order = $this->_getOrderItem($params->order_item_id);

			if($order->params->msc_option != $old->id)
			{
				continue;
			}

			$inv_id = $this->_getUserBillTo($subscr->member_id);

			$save = array(
				'user_id'           => $subscr->member_id,
				'plan_id'           => $plan->id,
				'published'         => $subscr->state,
				'invoice_num'       => $params->order_id,
				'invoice_id'        => $inv_id,
				'ctime'             => $subscr->start_date,
				'extime'            => $subscr->expired_date,
				'created'           => $order->create_date,
				'gateway_id'        => $params->order_id,
				'gateway'           => 'OSE Import',
				'price'             => $order->payment_price,
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

	private function _getUserBillTo($id)
	{
		static $out = array();

		if(array_key_exists($id, $out))
		{
			return $out[$id];
		}

		$db = JFactory::getDbo();
		$db->setQuery("SELECT * FROM #__osemsc_billinginfo WHERE user_id = {$id}");
		$info = $db->loadObject();

		$out[$id] = 0;
		$pattern  = '{"billto":"%s","country":"%s","state":"","zip":"%s","address":"%s","tax_id":"%s","phone":"%s"}';
		if($info->vat_number)
		{
			$data = array(
				'id'      => NULL,
				'user_id' => $id,
				'fields'  => sprintf($pattern, $info->company, $this->_getCountry($info->country), $info->postcode,
					trim($info->addr1 . ' ' . $info->addr2), $info->vat_number, $info->telephone)
			);

			$invto = JTable::getInstance('EmInvoiceTo', 'JoomsubscriptionTable');
			$invto->save($data);

			$out[$id] = $invto->id;
		}

		return $out[$id];
	}

	private function _getCountry($id)
	{
		static $out = array();

		if(array_key_exists($id, $out))
		{
			return $out[$id];
		}

		$db = JFactory::getDbo();
		$db->setQuery("SELECT country_2_code FROM #__osemsc_country WHERE country_3_code = '{$id}'");
		$out[$id] = $db->loadResult();

		return $out[$id];
	}


	private function _getOrder($id)
	{
		$db = JFactory::getDbo();
		$db->setQuery("SELECT * FROM #__osemsc_order WHERE order_id = {$id}");

		$item = $db->loadObject();
		$item->params = json_decode($item->params);

		return $item;
	}
	private function _getOrderItem($id)
	{
		$db = JFactory::getDbo();
		$db->setQuery("SELECT * FROM #__osemsc_order_item WHERE order_item_id = {$id}");

		$item = $db->loadObject();
		$item->params = json_decode($item->params);

		return $item;
	}

	private function _getGroupID($id)
	{
		$db = JFactory::getDbo();
		$db->setQuery("SELECT * FROM #__osemsc_acl WHERE id = {$id}");

		$group = $db->loadObject();

		$save = array(
			'params'    => json_encode(array('properties' => array('template' => 'default'))),
			'ctime'     => JDate::getInstance()->toSql(),
			'access'    => 1,
			'language'  => '*',
			'ordering'  => 1,
			'name'      => $group->title,
			'published' => 1
		);

		$groups = JTable::getInstance('EmGroup', 'JoomsubscriptionTable');
		$groups->save($save);
		$id = $groups->id;
		$groups->reorder();

		return $id;
	}

	public function check()
	{
		$db = JFactory::getDbo();
		$db->setQuery('SHOW TABLES LIKE "%_osemsc_acl"');
		$result = $db->loadResult();
		if(!$result)
		{
			JError::raiseWarning(403, JText::_('OSE_TABLES_NOTEXIST'));

			return FALSE;
		}
		$db->setQuery('SELECT COUNT(*) FROM #__osemsc_acl');
		$result = $db->loadResult();
		if(!$result)
		{
			JError::raiseWarning(403, JText::_('JOOMSUBSCRIPTION8_TABLE_PLAN_EMPTY'));

			return FALSE;
		}

		return TRUE;
	}
}
