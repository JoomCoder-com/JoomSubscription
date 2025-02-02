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

class JoomsubscriptionControllerEmCron extends MControllerForm
{
	public function __construct($config = array())
	{
		$config = JComponentHelper::getParams('com_joomsubscription');

		parent::__construct($config);

		if(!$this->input)
		{
			$this->input = JFactory::getApplication()->input;
		}

		if(!$this->input->get('secret') || $config->get('cron_key', '123') != $this->input->get('secret'))
		{
			echo "Secret code is wrong. Add secret word in Joomsubscription global config and add <code>&secret=secretword</code> to URL.";
			JFactory::getApplication()->close();
		}
	}

	public function send_expire_alerts()
	{
		$db           = JFactory::getDbo();
		$subscr_table = JTable::getInstance('EmSubscription', 'JoomsubscriptionTable');

		$query = $db->getQuery(TRUE);
		$query->select('*');
		$query->from('#__joomsubscription_plans');
		$query->where('published = 1');
		$query->where('invisible = 0');
		$db->setQuery($query);
		$all_plans = $db->loadObjectList();

		if(empty($all_plans))
		{
			return;
		}

		foreach($all_plans as $plan)
		{
			$plan->params = new JRegistry($plan->params);

			if(!$plan->params->get('alerts.alert_enable_expire', FALSE))
			{
				continue;
			}
			if(!$plan->params->get('alerts.general_expire', FALSE))
			{
				continue;
			}

			$days = explode(',', $plan->params->get('alerts.general_expire'));
			\Joomla\Utilities\ArrayHelper::toInteger($days);
			sort($days);

			foreach($days as $day)
			{
				$query = $db->getQuery(TRUE);
				$query->select('*');
				$query->from('#__joomsubscription_subscriptions');
				$query->where('published = 1');
				$query->where('activated = 1');
				$query->where('plan_id = ' . $plan->id);
				$query->where("((access_limit = 0) OR (access_limit > 0 AND access_count < access_limit))");
				$query->where('extime > NOW()');
				$query->where('extime < NOW() + INTERVAL ' . $day . ' DAY');
				$query->where("(lastsent IS NULL OR lastsent = '0000-00-00 00:00:00' OR extime > INTERVAL {$day} DAY + lastsent)");
				$db->setQuery($query);
				$subscriptions = $db->loadObjectList();

				foreach($subscriptions as $subscription)
				{
					$result = JoomsubscriptionHelper::sendAlert('expire', $subscription, array('plan' => $plan, 'day' => $day));

					if($result)
					{
						$subscr_table->load($subscription->id);
						$subscr_table->lastsent = JDate::getInstance()->toSql();
						$subscr_table->store();
						$subscr_table->reset();
						$subscr_table->id = NULL;
					}
				}
			}
		}

		JFactory::getApplication()->close();
	}

	public function report()
	{
		$db = JFactory::getDbo();

		$query = $db->getQuery(TRUE);
		$query->select('s.*');
		$query->from('#__joomsubscription_subscriptions AS s');

		$query->where('s.created > NOW() - INTERVAL '.JFactory::getApplication()->input->getInt('limit', 90).' DAY');
		$query->where('s.published = 1');

		$query->select('p.name as plan_name');
		$query->leftJoin('#__joomsubscription_plans AS p ON p.id = s.plan_id');

		$query->select('g.name as group_name');
		$query->leftJoin('#__joomsubscription_plans_groups AS g ON g.id = p.group_id');

		$query->select('i.fields as invoice');
		$query->leftJoin('#__joomsubscription_invoice_to AS i ON i.id = s.invoice_id');

		$query->order('s.invoice_num DESC');

		$db->setQuery($query);

		$list = $db->loadObjectList();

		$out = array();
		foreach($list AS $l)
		{
			$invoice = new JRegistry($l->invoice);
			$user    = JFactory::getUser($l->user_id);

			if($invoice->get('country'))
			{
				$db->setQuery("SELECT name FROM #__joomsubscription_country WHERE id = '" . $invoice->get('country') . "'");
				$invoice->set('country', $db->loadResult());
			}

			if($invoice->get('state'))
			{
				$db->setQuery("SELECT label FROM #__joomsubscription_states WHERE id = '" . $invoice->get('state') . "'");
				$invoice->set('state', $db->loadResult());
			}

			$out[] = array(
				'ID'            => $l->user_id,
				'Order ID'      => $l->gateway_id,
				'Invoice Num'   => $l->invoice_num,
				//'Method'        => ($l->gateway == 'garanti' ? 'Kredi kartı' : ($l->gateway == 'offline') ? 'Banka Havalesi' : $l->gateway),
				'Method'        => $l->gateway,
				'Plan Name'     => $l->plan_name,
				'Group Name'    => $l->group_name,
				'Total'         => $l->price,
				'Start Date'    => $l->ctime,
				'End Date'      => $l->extime,
				'Created'       => $l->created,
				'Purchase Date' => $l->purchased,
				'Tax ID'        => $invoice->get('tax_id', 'n/a'),
				'Country'       => $invoice->get('country', 'n/a'),
				'ZIP'           => $invoice->get('zip', 'n/a'),
				'City'          => $invoice->get('city', 'n/a'),
				'State'         => $invoice->get('state', 'n/a'),
				'Company'       => $invoice->get('billto', 'n/a'),
				'Address'       => $invoice->get('address', 'n/a'),
				'Name'          => $user->get('name', 'Not found'),
				'Email'         => $user->get('email', 'Not found')
			);
		}

		$output = fopen("php://output", 'w') or die("Can't open php://output");
		header("Content-Type:application/csv");
		header("Content-Disposition:attachment;filename=report.csv");
		fputcsv($output, array_keys($out[0]));
		foreach($out as $product)
		{
			fputcsv($output, $product);
		}
		fclose($output) or die("Can't close php://output");
		JFactory::getApplication()->close();
	}
}
