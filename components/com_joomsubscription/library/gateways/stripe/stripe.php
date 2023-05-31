<?php
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.menu');

class JoomsubscriptionGatewayStripe extends JoomsubscriptionGateway
{
	function accept(&$subscription, $plan)
	{

		$ips = [
			'54.187.174.169',
			'54.187.205.235',
			'54.187.216.72',
			'54.241.31.99',
			'54.241.31.102',
			'54.241.34.107'
		];

		If(!in_array($_SERVER['REMOTE_ADDR'], $ips))
		{
			$this->log('Event rejected wrong ip', $_SERVER['REMOTE_ADDR']);
			JError::raiseError(500, 'IP not right');

			return FALSE;
		}

		include_once __DIR__ . '/stripe-php-3.11.0/init.php';

		\Stripe\Stripe::setApiKey($this->params->get('secret_key'));

		$event  = $this->_getEvent();
		$object = $event['data']['object'];
		$db     = JFactory::getDbo();

		switch($event['type'])
		{
			case "charge.refunded":
				$subscription->published = 0;
				break;
			case "charge.updated":
				$subscription->published = 1;

				if(
					@$object['status'] == 'pending' ||
					@$object['status'] == 'failed' ||
					@$object['user_report'] == 'fraudulent' ||
					@$object['stripe_report'] == 'fraudulent'
				)
				{
					$subscription->published = 0;
				}
				break;

			case "charge.dispute.created":
			case "charge.dispute.funds_withdrawn":
				$this->_disableSubscription($object['charge']);
				$subscription->published = 0;
				break;
			case "charge.dispute.funds_reinstated":
				$subscription->published = 1;
				break;
			case "charge.dispute.closed":
			case "charge.dispute.updated":
				$this->_disableSubscription($object['charge']);
				$subscription->published = 0;
				if($object['status'] == 'won')
				{
					$subscription->published = 1;
				}
				break;
			case "invoice.payment_succeeded":
				$subscr = $this->_getInvoiceSubsciption($object);
				$price  = explode('-', $subscr['plan']['id']);
				$price  = ($price[1] / 100);

				$subscription->add_new($plan, $object['id'], $price);
				$subscription->published = 1;
				$this->log('New subscription', $subscription);
				break;
			default:
				$this->log('Event not parsed', $event);
				break;
		}

		return TRUE;
	}

	public function popup($total, $name, $table, $plan)
	{
		$app = Jfactory::getApplication();
		$url = JUri::getInstance();
		$url->setVar('validation', '1');

		$app->redirect($url);
	}

	function pay($amount, $name, $subscription, $plan)
	{

		$user     = JFactory::getUser();
		$app      = Jfactory::getApplication();
		$activate = FALSE;
		$error    = FALSE;

		include_once __DIR__ . '/stripe-php-3.11.0/init.php';

		\Stripe\Stripe::setApiKey($this->params->get('secret_key'));

		$customer_id = $this->_get_customer_id($subscription->user_id);

		if($this->params->get('recurre'))
		{
			if(!$plan->days_type || !$plan->days)
			{
				JError::raiseWarning(100, JText::_('SP_PLAN_RECURR_NO_SUPPORT'));

				return;
			}

			try
			{

				$plan_id = $this->_update_plan($plan);

				if($amount < $plan->total)
				{
					$discount = (($plan->total - $amount) * 100);

					\Stripe\InvoiceItem::create(array(
							"customer"    => $customer_id,
							"amount"      => "-{$discount}",
							"currency"    => strtolower(substr($this->params->get('currency', 'usd'), 0, 3)),
							"description" => "First charge discount"
						)
					);
				}

				$cu = \Stripe\Customer::retrieve($customer_id);
				$s  = $cu->subscriptions->create(array("plan" => $plan_id));

				if($s->id)
				{
					//$inv = \Stripe\Invoice::all(array("limit" => 1, 'customer' => $customer_id));

					$oreder_id = $s->id; // . ' - ' . $inv->data[0]->id;
					$activate  = TRUE;
				}
			}
			catch(Exception $e)
			{
				$error = $e;
			}
		}
		else
		{
			try
			{
				$charge_array = array(
					'customer'      => $customer_id,
					'amount'        => $amount * 100,
					'description'   => $name,
					'receipt_email' => $user->get('email'),
					'currency'      => strtolower(substr($this->params->get('currency', 'usd'), 0, 3)),
					'metadata'      => [
						'user_login'      => $user->get('username'),
						'user_email'      => $user->get('email'),
						'plan_id'         => $plan->id,
						'subscription_id' => $subscription->id,
					]
				);

				$charge = \Stripe\Charge::create($charge_array);

				if($charge->paid == TRUE)
				{
					$oreder_id = $charge->id;
					$activate  = TRUE;
				}
			}
			catch(Exception $e)
			{
				$error = $e;
			}
		}

		if($activate == TRUE)
		{
			$subscription->published  = 1;
			$subscription->gateway_id = $oreder_id;

			JoomsubscriptionHelper::activateSubscription($subscription, $plan);
			$subscription->store();

			$app->enqueueMessage(JText::_('SP_SUCCESS_PAYMENT'));
			$app->redirect(JoomsubscriptionApi::getLink('emhistory', FALSE));
		}
		elseif($error)
		{
			$app->enqueueMessage($error->getMessage(), 'warning');
			$app->redirect(JoomsubscriptionApi::getLink('emlist', FALSE, $plan->id));
		}
		else
		{
			$app->enqueueMessage(JText::_('SP_FAIL_PAYMENT'), 'warning');

			if(@$charge->failure_message)
			{
				$app->enqueueMessage($charge->failure_message, 'warning');
			}
			$app->redirect(JoomsubscriptionApi::getLink('emlist', FALSE, $plan->id));
		}
	}

	private function _get_customer_id($user_id)
	{
		$app         = Jfactory::getApplication();
		$user        = JFactory::getUser($user_id);
		$db          = JFactory::getDbo();
		$token       = $app->input->get('stripe_token');
		$customer_id = NULL;


		if($user->get('id'))
		{
			$db->setQuery("SELECT profile_value
			FROM `#__user_profiles`
			WHERE user_id = " . $user->get('id') . "
			AND profile_key = 'joomsubscription.stripe'");
			$customer_id = $db->loadResult();
		}

		if($customer_id)
		{
			try
			{
				$cu         = \Stripe\Customer::retrieve($customer_id);
				$cu->source = $token;
				$cu->save();
			}
			catch(Exception $e)
			{
				if(substr($e->getMessage(), 0, 16) == 'No such customer')
				{
					$customer    = $this->_addCustomer($token, $user_id);
					$customer_id = $customer->id;
				}
			}

			if(!$customer_id)
			{
				throw new Exception("Stripe: Unknown error.");
			}

			return $customer_id;
		}

		$customer = $this->_addCustomer($token, $user_id);

		return $customer->id;
	}

	private function _addCustomer($token, $user_id)
	{
		$db   = JFactory::getDbo();
		$user = JFactory::getUser($user_id);

		if(!empty($_SERVER['HTTP_CLIENT_IP']))
		{
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		}
		elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
		{
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		}
		else
		{
			$ip = $_SERVER['REMOTE_ADDR'];
		}

		if($user->get('id'))
		{
			$save = array(
				'email'    => $user->get('email'),
				'source'     => $token,
				'metadata' => [
					'ip'         => $ip,
					'username'   => $user->get('username'),
					'name'       => $user->get('name'),
					'registered' => $user->get('registerDate'),
					'user_email' => $user->get('email')
				]
			);
		}
		else
		{
			$save = array(
				'description' => 'Public user',
				'source'     => $token,
				'metadata' => array(
					'ip' => $ip
				)
			);
		}
		$customer = \Stripe\Customer::create($save);

		$db->setQuery("DELETE FROM `#__user_profiles` WHERE `user_id` = '{$user->id}' AND `profile_key` = 'joomsubscription.stripe'");
		$db->execute();

		$db->setQuery("INSERT INTO `#__user_profiles` (`user_id`, `profile_key`, `profile_value`, `ordering`)
			VALUES ({$user->id}, 'joomsubscription.stripe', '{$customer->id}', 0)");
		$db->execute();

		return $customer;
	}

	private function _update_plan($plan)
	{
		$plan_id = $plan->id . '-' . ($plan->total * 100);

		try
		{
			$sp = \Stripe\Plan::retrieve($plan_id);
		}
		catch(Exception $e)
		{
			if($e->getMessage() == 'No such plan: ' . $plan_id)
			{
				$plan2save = [
					"amount"         => ($plan->total * 100),
					"interval"       => substr($plan->days_type, 0, -1),
					"interval_count" => $plan->days,
					"name"           => $plan->name,
					"currency"       => strtolower(substr($this->params->get('currency', 'usd'), 0, 3)),
					"id"             => $plan->id . '-' . ($plan->total * 100),
					"metadata"       => [
						"created"     => $plan->ctime,
						"description" => $plan->params->get('descriptions.description'),
					]
				];

				$out = \Stripe\Plan::create($plan2save);
			}
		}

		return $plan_id;
	}

	private function _getEvent()
	{
		static $event = NULL;
		if($event)
		{
			return $event;
		}

		$input = @file_get_contents("php://input");
		$event = json_decode($input, TRUE);

		return $event;
	}

	function get_subscrption_id($who = NULL)
	{
		include_once __DIR__ . '/stripe-php-3.11.0/init.php';

		\Stripe\Stripe::setApiKey($this->params->get('secret_key'));

		$event      = $this->_getEvent();
		$charge_id  = NULL;
		$invoice    = NULL;
		$gateway_id = NULL;

		switch($event['type'])
		{
			case "charge.refunded":
			case "charge.updated":
				$charge_id = $event['data']['object']['id'];
				break;
			case "charge.dispute.closed":
			case "charge.dispute.created":
			case "charge.dispute.funds_reinstated":
			case "charge.dispute.funds_withdrawn":
			case "charge.dispute.updated":
				$charge_id = $event['data']['object']['charge'];
				break;
			case "invoice.payment_succeeded":
				$invoice = $event['data']['object'];
				break;
			default:
				$this->log('Event type not accepted', $event['type']);
				JFactory::getApplication()->close();
		}

		if($invoice)
		{
			$subscription = $this->_getInvoiceSubsciption($invoice);

			if(!empty($subscription['id']))
			{
				$gateway_id = $subscription['id'];
			}
		}

		if($charge_id)
		{
			$gateway_id = $charge_id;
		}

		if($gateway_id)
		{
			$table = JTable::getInstance('EmSubscription', 'JoomsubscriptionTable');
			$table->load(array('gateway_id' => $gateway_id));

			return $table->id;
		}

		return NULL;
	}

	private function _disableSubscription($charge_id)
	{
		if(empty($charge_id))
		{
			return;
		}


		try
		{
			$charge = \Stripe\Charge::retrieve($charge_id);

			if(!empty($charge->invoice))
			{
				$db = JFactory::getDbo();
				$db->setQuery("UPDATE `#__joomsubscription_subscriptions` SET `published` = 0 WHERE `gateway_id` = '{$charge->invoice}'");
				$db->execute();
			}

		}
		catch(Exception $e)
		{

		}
	}

	private function _getInvoiceSubsciption($invoice)
	{
		if($invoice['lines']['total_count'] == 0)
		{
			return NULL;
		}

		foreach($invoice['lines']['data'] AS $item)
		{
			if(substr($item['id'], 0, 4) == 'sub_')
			{
				return $item;
			}
		}
	}
}