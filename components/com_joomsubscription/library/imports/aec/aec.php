<?php
/**
 * JoomSubscription by JoomCoder
 * a component for Joomla! 3.0 CMS (http://www.joomla.org)
 * Author Website: https://www.joomcoder.com/
 *
 * @copyright Copyright (C) 2012 JoomCoder (https://www.joomcoder.com/). All rights reserved.
 * @license   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */
defined('_JEXEC') or die();

class JoomsubscriptionImportAec extends JoomsubscriptionImport
{
	public  $result            = array();
	private $coupons           = [];
	private $plans             = [];
	private $plans_amount      = [];
	private $subscr            = [];
	private $subscr_plans      = [];
	private $coupons_disc      = [];
	private $coupons_disc_type = [];

	public function run($params)
	{
		$this->params = $params;
		$plans        = JTable::getInstance('EmPlan', 'JoomsubscriptionTable');
		$db           = JFactory::getDbo();
		$period_types = [
			'Y' => 'YEAR',
			'D' => 'DAY',
			'M' => 'MONTH',
			'W' => 'WEEK'
		];

		$db->setQuery('SELECT * FROM #__acctexp_plans');
		$levels = $db->loadObjectList();

		$group_id = $this->getGroupId();

		foreach($levels AS $level)
		{
			$p = unserialize(base64_decode($level->params));

			$params = array(
				'properties'   => array(
					'price'     => $p['full_amount'],
					'days'      => $p['full_period'],
					'days_type' => $period_types[$p['full_periodunit']],
				),
				'descriptions' => array(
					'description' => $level->desc
				)
			);

			$save = array(
				'name'      => $level->name,
				'group_id'  => $group_id,
				'published' => $level->active,
				'ctime'     => JDate::getInstance()->toSql(),
				'access'    => 1,
				'invisible' => $level->visible == 1 ? 0 : 1,
				'params'    => json_encode($params),
				'ordering'  => $level->ordering
			);

			$plans->bind($save);
			$plans->check();
			if($plans->store())
			{
				@$this->result['plans']++;
			}

			$this->plans[$level->id]        = $plans->id;
			$this->plans_amount[$plans->id] = $p['full_amount'];

			$plans->reset();
			$plans->id = NULL;
		}

		$this->getSubscritpions();
		$this->getCoupons();
		$this->getCouponsHistory();

		return $this->result;
	}

	private function getCouponsHistory()
	{
		$ctable = JTable::getInstance('EmCouponhistory', 'JoomsubscriptionTable');

		$db = JFactory::getDbo();

		$db->setQuery("SELECT * FROM `#__acctexp_couponsxuser`");
		$list = $db->loadObjectList();

		foreach($list as $item)
		{

			$p = unserialize(base64_decode($item->params));
			$i = explode(',', $p['invoices']);


			$i = @$i[0];
			if(!$i)

			{
				continue;
			}

			$invoice = $this->getInvoice($i);

			if(!$invoice->id)
			{
				continue;
			}

			if(empty($this->coupons[$item->coupon_type][$item->coupon_id]))
			{
				continue;
			}

			$new_plan = $this->subscr_plans[$invoice->subscr_id];
			$amount   = $this->plans_amount[$new_plan];

			$save = [
				'user_id'         => $item->userid,
				'coupon_id'       => $this->coupons[$item->coupon_type][$item->coupon_id],
				'plan_id'         => $new_plan,
				'subscription_id' => $this->subscr[$invoice->subscr_id],
				'price'           => $amount,
				'discount'        => $this->_calcDiscount(
					$amount,
					@$this->coupons_disc_type[$item->coupon_type][$item->coupon_id],
					@$this->coupons_disc[$item->coupon_type][$item->coupon_id]
				),
				'discount_type'   => $this->coupons_disc_type[$item->coupon_type][$item->coupon_id],
				'ctime'           => $invoice->created_date

			];

			$ctable->bind($save);
			if($ctable->check())
			{
				if($ctable->store())
				{
					@$this->result['coupons']++;
				}
			}

			$ctable->reset();
			$ctable->id = NULL;
		}
	}

	private function _calcDiscount($amount, $type, $discount)
	{
		$out = 0;
		if(strtolower($type) == 'procent')
		{
			$out = ($amount * ($discount / 100));
			$out = round($out, 2);
		}
		elseif(strtolower($type) == 'sum')
		{
			$out = $discount;
		}

		return (int)$out;
	}

	private function getCoupons()
	{
		$this->_processCoupons(0);
		$this->_processCoupons(1);
	}

	private function _processCoupons($type)
	{
		$ctable = JTable::getInstance('EmCoupon', 'JoomsubscriptionTable');

		$db = JFactory::getDbo();

		$db->setQuery("SELECT * FROM `#__acctexp_coupons" . ($type == 1 ? '_static' : '') . "`");
		$list = $db->loadObjectList();


		foreach($list as $coupon)
		{
			$rest = unserialize(base64_decode($coupon->restrictions));
			$disc = unserialize(base64_decode($coupon->discount));

			$save = array(
				'value'         => $coupon->coupon_code,
				'discount'      => $disc['amount_percent_use'] ? $disc['amount_percent'] : $disc['amount'],
				'discount_type' => $disc['amount_percent_use'] ? 'PROCENT' : 'SUM',
				'user_ids'      => '',
				'strict'        => 0,
				'ctime'         => $rest['has_start_date'] ? $rest['start_date'] : '0000-00-00',
				'extime'        => $rest['has_expiration'] ? $rest['expiration'] : '0000-00-00',
				'use_num'       => $rest['has_max_reuse'] ? $rest['max_reuse'] : 0,
				'used_num'      => $coupon->usecount,
				'trash'         => 0,
				'published'     => $coupon->active,
				'plan_ids'      => '[' . ($rest['usage_plans_enabled'] ? implode(',', $rest['usage_plans']) : '') . ']',
				'use_user'      => $rest['has_max_peruser_reuse'] ? $rest['max_peruser_reuse'] : 0
			);

			$ctable->bind($save);
			if($ctable->check())
			{
				if($ctable->store())
				{
					@$this->result['coupons']++;

					$this->coupons[$type][$coupon->id]           = $ctable->id;
					$this->coupons_disc[$type][$coupon->id]      = $ctable->discount;
					$this->coupons_disc_type[$type][$coupon->id] = $ctable->discount_type;
				}
			}

			$ctable->reset();
			$ctable->id = NULL;
		}
	}

	private function getSubscritpions()
	{
		$subscriptions = JTable::getInstance('EmSubscription', 'JoomsubscriptionTable');

		$db = JFactory::getDbo();

		$query = $db->getQuery(TRUE);
		$query->select('*')
			->from('#__acctexp_subscr')
			->where("plan IS NOT NULL");
			//->where("params != ''");

		if($this->params->get('only_active'))
		{
			$query->where("status = 'Active'");
		}

		$db->setQuery($query);
		$subscrs = $db->loadObjectList();

		foreach($subscrs as $subscr)
		{

			//$p  = unserialize(base64_decode($subscr->params));
			$cp = unserialize(base64_decode($subscr->customparams));

			$invoice = $this->getInvoiceBySubscriptionId($subscr->id);

			$save = array(
				'user_id'           => $subscr->userid,
				'plan_id'           => $this->plans[$subscr->plan],
				'published'         => 1,
				'ctime'             => $subscr->signup_date,
				'purchased'         => $subscr->signup_date,
				'extime'            => $subscr->lifetime = 1 ? '0000-00-00' : $subscr->expiration,
				'created'           => $subscr->signup_date,
				'activated'         => 0,
				'access_limit'      => 0,
				'access_count'      => 0,
				'access_count_mode' => 0,
				'note'              => \Joomla\Utilities\ArrayHelper::getValue($cp, 'notes'),

				'invoice_num'       => $invoice->invoice_number_format,
				'gateway_id'        => $invoice->invoice_number,
				'gateway'           => str_replace('_subscription', '', $invoice->method),
				'price'             => $invoice->amount
			);

			$subscriptions->bind($save);
			$subscriptions->check();
			if($subscriptions->store())
			{
				@$this->result['subscriptions']++;

				JoomsubscriptionHelper::activateSubscription($subscriptions);

				$this->subscr[$subscr->id]       = $subscriptions->id;
				$this->subscr_plans[$subscr->id] = $this->plans[$subscr->plan];

				$subscriptions->reset();
				$subscriptions->id = NULL;
			}

		}
	}

	private function getInvoice($number)
	{
		$db = JFactory::getDbo();
		$db->setQuery("SELECT * FROM #__acctexp_invoices WHERE invoice_number = '{$number}'");
		$invoice = $db->loadObject();

		return $this->_processInvoice($invoice);
	}

	private function getInvoiceBySubscriptionId($id)
	{
		$db = JFactory::getDbo();
		$db->setQuery("SELECT * FROM #__acctexp_invoices WHERE subscr_id = '{$id}'");
		$invoice = $db->loadObject();

		return $this->_processInvoice($invoice);
	}

	private function getGroupId()
	{
		$db = JFactory::getDbo();

		$db->setQuery("SELECT id FROM #__joomsubscription_plans_groups LIMIT 1");
		$id = $db->loadResult();

		if($id)
		{
			return $id;
		}

		$save = array(
			'params'   => json_encode(array('properties' => array('template' => 'default'))),
			'ctime'    => JDate::getInstance()->toSql(),
			'access'   => 1,
			'language' => '*',
			'ordering' => 1
		);

		$save['name']      = 'Default group';
		$save['published'] = 1;

		$groups = JTable::getInstance('EmGroup', 'JoomsubscriptionTable');
		$groups->bind($save);
		$groups->check();
		$groups->store();
		$groups->reorder();

		@$this->result['groups']++;

		return $groups->id;
	}


	public function check()
	{
		$db = JFactory::getDbo();
		$db->setQuery('SHOW TABLES LIKE "%_acctexp_%"');
		$result = $db->loadResult();
		if(!$result)
		{
			JError::raiseWarning(403, JText::_('JOOMSUBSCRIPTION8_TABLES_NOTEXIST'));

			return FALSE;
		}
		$db->setQuery('SELECT COUNT(*) FROM #__acctexp_plans');
		$result = $db->loadResult();
		if(!$result)
		{
			JError::raiseWarning(403, JText::_('JOOMSUBSCRIPTION8_TABLE_PLAN_EMPTY'));

			return FALSE;
		}

		return TRUE;
	}

	private function _processInvoice($invoice)
	{
		if(!$invoice->id)
		{
			$i                        = new stdClass();
			$i->invoice_number_format = NULL;
			$i->invoice_number        = NULL;
			$i->method                = 'AEC';
		}

		if($invoice->params)
		{
			$invoice->params = unserialize(base64_decode($invoice->params));
		}

		if($invoice->transactions)
		{
			$invoice->transactions = unserialize(base64_decode($invoice->transactions));
		}
		if($invoice->coupons)
		{
			$invoice->coupons = unserialize(base64_decode($invoice->coupons));
		}

		return $invoice;
	}
}
