<?php
/**
 * Created by JetBrains PhpStorm.
 * User: sergey
 * Date: 3/6/13
 * Time: 1:42 PM
 * To change this template use File | Settings | File Templates.
 *
 * @license   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die('Restricted access');

class JoomsubscriptionHelper
{

	public static function activateSubscription(&$subscription, $plan = NULL)
	{
		if(is_int($subscription))
		{
			$subscription_id = $subscription;
			$subscription    = JTable::getInstance('EmSubscription', 'JoomsubscriptionTable');
			$subscription->load($subscription_id);
		}

		if($subscription->activated == 1)
		{
			return;
		}

		if($subscription->published == 0)
		{
			if($subscription->gateway != 'offline')
			{
				JoomsubscriptionHelper::sendAlert('fail', $subscription);
			}

			return;
		}

		$db             = JFactory::getDbo();
		$joomsubscription_params = JComponentHelper::getParams('com_joomsubscription');

		if($joomsubscription_params->get('use_invoice', 0) && $subscription->price > 0 && $subscription->invoice_id && !$subscription->invoice_num)
		{
			$subscription->invoice_num = JoomsubscriptionHelper::getInvoiceNum();
		}

		if(!$plan)
		{
			//$plan = JoomsubscriptionApi::getPreparedPlan($subscription->plan_id);
		}


		if(!$plan)
		{
			$plan = JoomsubscriptionApi::getPlan($subscription->plan_id);
			$plan = JoomsubscriptionHelper::getPlanDetails($plan);
		}

		$grant = $plan->params->get('crossplans.grant_plans');
		settype($grant, 'array');
		JArrayHelper::toInteger($grant);

		if(!empty($grant))
		{
			foreach($grant as $plan_id)
			{
				if(empty($plan_id))
				{
					continue;
				}
				if($plan->id == $plan_id)
				{
					continue;
				}

				JoomsubscriptionApi::addSubscription($subscription->user_id, $plan_id, 1, 'grant_cross', 0);
			}
		}

		$unpublish = $plan->params->get('crossplans.plans_deactivate');
		settype($unpublish, 'array');
		JArrayHelper::toInteger($unpublish);

		if($plan->upgrade_from > 0)
		{
			$unpublish[] = $plan->upgrade_from;
		}

		if(!empty($unpublish))
		{
			$db->setQuery("UPDATE `#__joomsubscription_subscriptions` SET published = 0 WHERE plan_id IN (" . implode(',', $unpublish) . ") AND user_id = " . $subscription->user_id);
			$db->execute();
		}

		if($subscription->activated == 0)
		{
			JoomsubscriptionActionsHelper::run('onSuccess', $subscription);

			$suser = JFactory::getUser($subscription->user_id);
			if(
				$subscription->published == 1  &&
				JComponentHelper::getParams('com_joomsubscription')->get('activate') &&
				$suser->get('block') == 1 &&
				(int)$subscription->price > 0
			)
			{
				$suser->block = 0;
				$suser->activation = '';
				$suser->save(TRUE);
			}
		}

		$db->setQuery("SELECT NOW()");

		$subscription->activated = 1;
		$subscription->purchased = $db->loadResult();
		$subscription->set_period($plan);

		$subscription->store();

		if($subscription->published == 1)
		{
			JoomsubscriptionHelper::sendAlert('success', $subscription);
		}

		$dispatcher = JDispatcher::getInstance();
		JPluginHelper::importPlugin('system');
		$dispatcher->trigger('onAfterSubscriptionActivated', array($subscription, $plan));
	}

	public static function getFormattedDate($date, $format = NULL)
	{
		$date = new JDate($date);

		return $date->format($format ? $format : JComponentHelper::getParams('com_joomsubscription')->get('date_format'));
	}

	public static function getValues($val, $toint = FALSE)
	{
		$out  = array();
		$val  = str_replace(' ', '', $val);
		$vals = explode(',', $val);
		foreach($vals AS $v)
		{
			$range = explode('-', $v);
			if(count($range) == 2)
			{
				foreach(range($range[0], $range[1]) as $number)
				{
					$out[] = $number;
				}
			}
			else
			{
				$out[] = $v;
			}
		}

		if($toint)
		{
			JArrayHelper::toInteger($out);
		}

		return $out;
	}

	public static function redirect($plan, $success = TRUE)
	{
		$app      = JFactory::getApplication();
		$redirect = '';

		if(!is_object($plan->params))
		{
			$plan->params = new JRegistry($plan->params);
		}

		if($plan->params->get('properties.redirect') && $success)
		{
			$redirect = $plan->params->get('properties.redirect');
		}

		if($plan->params->get('properties.redirect_fail') && !$success)
		{
			$redirect = $plan->params->get('properties.redirect_fail');
		}

		if($app->input->get('return'))
		{
			$url = $app->input->getString('return');
			$url = str_replace(' ', '+', $url);
			$url = base64_decode($url);

			if(JUri::isInternal($url))
			{
				$redirect = $url;
			}
		}

		if(!$redirect)
		{
			$redirect = JoomsubscriptionApi::getLink('emhistory', FALSE);
		}

		if(JFactory::getSession()->get('joomsubscription_access_url') && $success)
		{
			$redirect = JFactory::getSession()->get('joomsubscription_access_url');
			JFactory::getSession()->set('joomsubscription_access_url', NULL);
		}

		$app->redirect($redirect);
	}

	/**
	 * Check is moderator user or not
	 *
	 * @param JUser|int user_id or object JUser
	 *
	 * @return boolean
	 */
	public static function isModer($user = NULL)
	{
		if(!($user instanceof JUser))
		{
			$user = JFactory::getUser($user);
		}
		$moderate = JComponentHelper::getParams('com_joomsubscription')->get('moderate');

		return in_array($moderate, $user->getAuthorisedViewLevels());
	}

	public static function userLastPlan($user_id, $plan_id)
	{

		$db  = JFactory::getDBO();
		$sql = "SELECT id, UNIX_TIMESTAMP(extime) AS etm
			FROM #__joomsubscription_subscriptions
			WHERE user_id = {$user_id}
			AND plan_id IN ($plan_id)
			AND activated = 1
			AND extime > NOW()
			ORDER BY extime DESC LIMIT 1";
		$db->setQuery($sql);

		return $db->loadObject();
	}

	public static function getUserSubscr($usid)
	{
		$db = JFactory::getDBO();

		$sql = "SELECT p.name,  p.params as plan_params, u.*,
		IF(u.extime > NOW() OR u.extime = '0000-00-00 00:00:00', 0, 1) AS expired,
		IF(u.ctime < NOW(), 1, 0) AS active
		FROM #__joomsubscription_plans AS p
		LEFT JOIN #__joomsubscription_subscriptions AS u ON u.plan_id = p.id
		WHERE p.published = '1' AND u.id = '$usid'";

		$db->setQuery($sql);

		return $db->loadObject();
	}

	public static function getLastUserActiveSubscription($user_id, $plan_id)
	{
		if(!$user_id)
		{
			$user_id = JFactory::getUser()->get('id');
		}

		$db    = JFactory::getDbo();
		$query = $db->getQuery(TRUE);
		$query->select("us.*");
		$query->from("#__joomsubscription_subscriptions AS us");
		$query->where("us.ctime < NOW()");
		$query->where("(us.extime > NOW() OR us.extime = '0000-00-00 00:00:00')");
		$query->where("((us.access_limit = 0) OR (us.access_limit > 0 AND us.access_count < us.access_limit))");
		$query->where("us.activated = 1");
		$query->where("us.published = 1");
		$query->where("us.user_id = " . $user_id);
		$query->where("us.plan_id IN({$plan_id})");
		$query->order('us.ctime DESC');

		$db->setQuery($query, 0, 1);

		return $db->loadObject();
	}



	public static function getUserActiveSubscriptions($user_id = FALSE, $exclude = 0, $plans = '', $start = TRUE)
	{
		if(!$user_id)
		{
			$user_id = JFactory::getUser()->get('id');
		}
		if(is_array($plans))
		{
			$plans = implode(',', $plans);
		}

		$db    = JFactory::getDbo();
		$query = $db->getQuery(TRUE);
		$query->select("us.*");
		$query->from("#__joomsubscription_subscriptions AS us");
		$query->select('p.name as plan_name');
		$query->leftJoin('#__joomsubscription_plans AS p ON p.id = us.plan_id');
		$query->where("(us.extime > NOW() OR us.extime = '0000-00-00 00:00:00')");
		$query->where("((us.access_limit = 0) OR (us.access_limit > 0 AND us.access_count < us.access_limit))");
		//$query->where("us.track_disactive = 0");
		$query->where("us.activated = 1");
		$query->where("us.published = 1");
		$query->where("us.user_id = " . $user_id);

		if($start)
		{
			$query->where("us.ctime < NOW()");
		}
		if($exclude)
		{
			$query->where("us.id != " . $exclude);
		}

		if($plans)
		{
			$query->where("us.plan_id IN({$plans})");
		}
		$query->order('us.extime ASC');

		$db->setQuery($query);

		return $db->loadObjectList('id');
	}

	static public function  getUserPlans($user_id = NULL, $only_active = NULL)
	{
		static $out = array();

		if(empty($user_id))
		{
			$user_id = JFactory::getUser()->get('id');
		}

		$key = $user_id . '-' . $only_active;

		if(isset($out[$key]))
		{
			return $out[$key];
		}


		if(empty($user_id))
		{
			return array();
		}

		$query = "SELECT plan_id FROM `#__joomsubscription_subscriptions` WHERE user_id = " . $user_id . " AND activated = 1";

		if($only_active)
		{
			$query = "SELECT plan_id FROM `#__joomsubscription_subscriptions`
				WHERE user_id = {$user_id}
					AND (extime > NOW() OR extime = '0000-00-00 00:00:00')
					AND ctime < NOW()
					AND ((access_limit > 0 AND access_limit > access_count) OR (access_limit = 0))
					AND activated = 1
					AND published = 1";
		}

		$db  = JFactory::getDBO();
		$sql = "SELECT p.id FROM `#__joomsubscription_plans` AS p WHERE p.id IN({$query})";
		$db->setQuery($sql);
		$result    = $db->loadColumn();
		$out[$key] = array_unique($result);


		return $out[$key];
	}

	static public function totalPlansPurchased($plan)
	{
		static $out = array();

		if(array_key_exists($plan->id, $out))
		{
			return $out[$plan->id];
		}

		$db = JFactory::getDBO();

		$query = $db->getQuery(TRUE);

		$query->select("count(*)");
		$query->from("#__joomsubscription_subscriptions");
		$query->where("activated = 1");
		$query->where("published = 1");
		$query->where("plan_id = {$plan->id}");

		if($plan->params->get('properties.purchase_limit_active'))
		{
			$query->where("ctime < NOW()");
			$query->where("(extime  > NOW() OR extime = '0000-00-00 00:00:00')");
			$query->where("((access_limit = 0) OR (access_limit > 0 AND access_count < access_limit))");
		}

		$db->setQuery($query);
		$out[$plan->id] = $db->loadResult();

		return $out[$plan->id];

	}

	static public function userAllSubscriptions($user_id, $plans = '')
	{
		static $out = array();

		if(array_key_exists($user_id, $out))
		{
			return $out[$user_id];
		}

		$db  = JFactory::getDBO();
		$sql = "SELECT * FROM `#__joomsubscription_subscriptions` WHERE user_id = {$user_id} AND activated = 1";
		if($plans)
		{
			$sql .= " AND plan_id IN({$plans})";
		}
		$db->setQuery($sql);
		$out[$user_id] = $db->loadObjectList();

		return $out[$user_id];

	}

	static public function userNeverActivated($user_id, $plans = '')
	{
		static $out = array();

		if(array_key_exists($user_id, $out))
		{
			return $out[$user_id];
		}

		$db  = JFactory::getDBO();
		$sql = "SELECT * FROM `#__joomsubscription_subscriptions` WHERE user_id = {$user_id} AND activated = 0";
		if($plans)
		{
			$sql .= " AND plan_id IN({$plans})";
		}
		$db->setQuery($sql);
		$out[$user_id] = $db->loadObjectList();

		return $out[$user_id];

	}

	static public function userPurchasedTotal($user_id)
	{
		$subscriptions = self::userAllSubscriptions($user_id);

		$out = 0;
		foreach($subscriptions AS $s)
		{
			$out += (float)$s->price;
		}

		return $out;
	}

	static public function userActiveSubscriptions($user_id)
	{
		$subscriptions = self::userAllSubscriptions($user_id);

		$out = array();
		foreach($subscriptions AS $s)
		{
			if(
				($s->extime == '0000-00-00 00:00:00' || strtotime($s->extime) > time()) &&
				strtotime($s->ctime) < time() &&
				($s->access_limit == 0 || ($s->access_limit > 0 && $s->access_count < $s->access_limit))
			)
			{
				$out[] = $s;
			}
		}

		return $out;
	}

	static public function userInactiveSubscriptions($user_id, $plans = array())
	{
		$subscriptions = self::userAllSubscriptions($user_id);
		$active        = self::userActiveSubscriptions($user_id);

		$check = array();
		foreach($active AS $a)
		{
			$check[] = $a->id;
		}

		$out = array();
		foreach($subscriptions AS $s)
		{
			if(in_array($s->id, $check))
			{
				continue;
			}
			if($plans && !in_array($s->plan_id, $plans))
			{
				continue;
			}

			$out[] = $s;
		}

		return $out;
	}

	static public function totalPlansPurchasedUser($plan_id, $user_id = NULL, $period = 0)
	{
		static $out = array();

		$user = JFactory::getUser($user_id);
		if(!$user->get('id'))
		{
			return 0;
		}

		if(array_key_exists($plan_id, $out))
		{
			return $out[$plan_id];
		}

		$db  = JFactory::getDBO();
		$sql = "SELECT count(*) FROM #__joomsubscription_subscriptions WHERE plan_id = {$plan_id} AND user_id = {$user->id} AND activated = 1";
		if($period)
		{
			$sql .= " AND ctime > NOW() - INTERVAL {$period}";
		}
		$db->setQuery($sql);
		$out[$plan_id] = $db->loadResult();

		return $out[$plan_id];

	}

	static public function preparePlan($plan)
	{
		$result = self::preparePlans(array($plan));

		return @array_shift(array_shift($result['plans']));
	}

	static public function preparePlans($items)
	{
		$out = $groups = array();
		$db  = JFactory::getDBO();

		foreach($items as $k => $plan)
		{

			$param        = (is_object($plan->params) ? $plan->params : new JRegistry($plan->params));
			$plan->params = $param;

			if(self::_isHidden($plan))
			{
				continue;
			}


			$plan->left      = 0;
			$plan->user_left = 0;
			$plan->discount  = 0;
			$plan->grant     = array();
			$plan->name      = JText::_($plan->name);

			$plan->require_one_of = array();
			$plan->require_all_of = array();

			if($plan->published == 0)
			{
				continue;
			}

			if($plan->params->get('properties.date_from') && $plan->params->get('properties.date_to'))
			{
				//$date_from = JDate::getInstance($plan->params->get('properties.date_to'), 'UTC')->toUnix();
				$date_to = JDate::getInstance($plan->params->get('properties.date_to'), 'UTC')->toUnix();
				$now = JDate::getInstance('now', 'UTC')->toUnix();

				if($now > $date_to)
				{
					continue;
				}
			}

			if($param->get('properties.purchase_limit') > 0)
			{
				if($param->get('properties.purchase_limit') <= self::totalPlansPurchased($plan))
				{
					continue;
				}
				else
				{
					$plan->left = $param->get('properties.purchase_limit') - self::totalPlansPurchased($plan);
				}
			}


			if($param->get('properties.purchase_limit_user') > 0)
			{
				$limit = self::totalPlansPurchasedUser($plan->id);
				if($param->get('properties.purchase_limit_user') <= $limit)
				{
					continue;
				}
				else
				{
					$plan->user_left = ($param->get('properties.purchase_limit_user') - $limit);
				}
			}

			if($param->get('properties.purchase_limit_user_period') > 0)
			{
				$limit = self::totalPlansPurchasedUser($plan->id, NULL, $param->get('properties.purchase_limit_period') . ' ' . $param->get('properties.purchase_limit_period_in'));
				if($param->get('properties.purchase_limit_user_period') <= $limit)
				{
					continue;
				}
				else
				{
					$plan->user_left = ($param->get('properties.purchase_limit_user') - $limit);
				}
			}


			// Check required plans
			if($param->get('crossplans.req_plans') && $param->get('crossplans.required'))
			{

				$user_plans = self::getUserPlans();

				$affect_plans = array_intersect($user_plans, $param->get('crossplans.req_plans'));
				$plans_diff   = array_diff($param->get('crossplans.req_plans'), $affect_plans);

				// require one of
				if($param->get('crossplans.required') == 1)
				{
					if(empty($affect_plans))
					{
						if($param->get('crossplans.required_behave') == 0)
						{
							continue;
						}
						$sql = "SELECT name FROM #__joomsubscription_plans WHERE id IN(" . implode(',', $param->get('crossplans.req_plans')) . ")";
						$db->setQuery($sql);
						$plan->require_one_of = $db->loadColumn();
					}
				}
				// require all of
				elseif($param->get('crossplans.required') == 2)
				{
					if(!empty($plans_diff))
					{
						if($param->get('crossplans.required_behave') == 0)
						{
							continue;
						}
						$sql = "SELECT name FROM #__joomsubscription_plans WHERE id IN(" . implode(',', $plans_diff) . ")";
						$db->setQuery($sql);
						$plan->require_all_of = $db->loadColumn();
					}
				}
			}

			$plan = self::getPlanDetails($plan);

			$out[$plan->gid][] = $plan;

			$gparams                           = new JRegistry(@$plan->cparams);
			$groups[$plan->gid]['description'] = JHtml::_('content.prepare', Mint::_(@$plan->cdescr));
			$groups[$plan->gid]['name']        = @$plan->cname;
			$groups[$plan->gid]['image']       = @$plan->gimage;
			$groups[$plan->gid]['template']    = $gparams->get('properties.template', 'default');

		}

		return array('cats' => $groups, 'plans' => $out);
	}

	public static function getPlanDetails($plan)
	{
		$db = JFactory::getDBO();

		if(!is_object($plan->params))
		{
			$plan->params = new JRegistry($plan->params);
		}

		$plan->days         = 0;
		$plan->days_type    = '';
		$plan->upgrade_from = 0;


		if($plan->params->get('properties.date_fixed'))
		{
			$plan->period = JText::_('XML_OPT_PERIOD' . $plan->params->get('properties.date_fixed'));
		}
		elseif($plan->params->get('properties.date_from') && $plan->params->get('properties.date_to'))
		{
			$plan->period = JoomsubscriptionHelper::getFormattedDate($plan->params->get('properties.date_from')) . ' - ' . JoomsubscriptionHelper::getFormattedDate($plan->params->get('properties.date_to'));
		}
		elseif($plan->params->get('properties.date_to'))
		{
			$plan->period = JText::sprintf('EMR_PERIOD_DATETO', JoomsubscriptionHelper::getFormattedDate($plan->params->get('properties.date_to')));
		}
		else
		{
			$plan->days      = $plan->params->get('properties.days');
			$plan->days_type = $plan->params->get('properties.days_type');

			if($plan->days >= 100 && $plan->days_type == 'years')
			{
				$plan->period = JText::_('XML_OPT_PERIOD1');
			}
			else
			{
				$plan->period = $plan->days . ' ' . JText::plural($plan->days_type, $plan->days);
			}
		}

		$plan->description = JHtml::_('content.prepare', Mint::_($plan->params->get('descriptions.description')));

		if($plan->params->get('crossplans.grant_plans') && $plan->params->get('crossplans.show_grant'))
		{
			$grants = JoomsubscriptionApi::getPlans($plan->params->get('crossplans.grant_plans'));
			foreach($grants AS $grant)
			{
				$plan->grant[] = JText::_($grant->name);
			}
		}

		$plan->total         = $plan->price = $plan->params->get('properties.price');
		$plan->discount      = 0;
		$plan->discount_type = '';

		// Upgrade plan recalculation
		$user_plans = self::getUserPlans(NULL, TRUE);

		if($user_plans)
		{
			$ug_discount = 0;

			foreach($user_plans AS $user_plan)
			{
				$ug_plan = JoomsubscriptionApi::getPlan($user_plan);

				if($ug_plan->params->get('crossplans.ud_plans') && in_array($plan->id, $ug_plan->params->get('crossplans.ud_plans')))
				{
					$subscription = self::getLastUserActiveSubscription(NULL, $user_plan);

					if($subscription->access_limit > 0)
					{
						continue;
					}

					if((int)$subscription->extime == 0)
					{
						continue;
					}

					if((int)$subscription->price == 0)
					{
						continue;
					}

					$now        = time();
					$start      = strtotime($subscription->ctime);
					$end        = strtotime($subscription->extime);
					$used_days  = ceil(($now - $start) / 86400);
					$steps_used = ceil($used_days / $ug_plan->params->get('crossplans.ud_price_step'));

					$steps_in_plan  = floor((($end - $start) / 86400) / $ug_plan->params->get('crossplans.ud_price_step'));
					$price_per_step = $subscription->price / $steps_in_plan;
					$unused_amount  = $price_per_step * ($steps_in_plan - $steps_used);

					if($unused_amount > $ug_discount)
					{
						$ug_discount        = round($unused_amount, 2);
						$plan->upgrade_from = $subscription->plan_id;
					}
				}
			}

			if($ug_discount > 0)
			{
				$plan->price -= $ug_discount;
				$plan->discount      = $ug_discount;
				$plan->discount_type = 'UPGRADE';
			}
		}

		if($plan->params->get('crossplans.plan_price') && $plan->params->get('crossplans.method') && $plan->discount == 0)
		{
			$user_plans   = self::getUserPlans(NULL, $plan->params->get('crossplans.plan_price_active'));
			$affect_plans = array_intersect($user_plans, $plan->params->get('crossplans.plan_price'));
			if(!empty($affect_plans))
			{
				$sql = "SELECT params FROM `#__joomsubscription_plans` WHERE id IN(" . implode(',', $affect_plans) . ")";

				$db->setQuery($sql);
				$list = $db->loadColumn();

				$subtract = 0;

				switch($plan->params->get('crossplans.method'))
				{
					// Subtract most expensive plan
					case 'or':
						$pricees = array();
						foreach($list AS $crossplan)
						{
							$cp_params = new JRegistry($crossplan);
							$pricees[] = $cp_params->get('properties.price');
						}
						sort($pricees, SORT_NUMERIC);
						$subtract = array_shift($pricees);
						break;

					// Subtract summ of plans
					case 'and':
						foreach($list AS $crossplan)
						{
							$cp_params = new JRegistry($crossplan);
							$subtract += $cp_params->get('properties.price');
						}
						break;
					case 'disc':
						$subtract = ($plan->price * ($plan->params->get('crossplans.plan_price_discount') / 100));
						break;

					case 'fix':
						$subtract = ($plan->params->get('crossplans.plan_price_discount') > $plan->price ? 0 : $plan->params->get('crossplans.plan_price_discount'));
						break;

				}

				$plan->price -= $subtract;
				$plan->discount      = $subtract;
				$plan->discount_type = 'CROSS';
			}
		}

		if($plan->params->get('properties.discount') && $plan->discount == 0)
		{
			$user_plans = self::getUserPlans();
			if(!in_array($plan->id, $user_plans))
			{
				$plan->price -= $plan->params->get('properties.discount');
				$plan->discount      = $plan->params->get('properties.discount');
				$plan->discount_type = 'FIRST';
			}
		}

		if($plan->price < 0)
		{
			$plan->price = 0;
		}

		/****** Donation chceking ******/

		$plan->is_donation     = FALSE;
		$plan->donation_prices = array();

		if($plan->params->get('properties.donation', 0))
		{
			$app = JFactory::getApplication();

			$max_price = $plan->params->get('properties.donation_max_price', 0);
			if($max_price && $max_price > $plan->price && !$plan->params->get('properties.donation_manual', 0))
			{
				$plan->is_donation = 1;

				$max_price = explode(',', $max_price);
				if(count($max_price) > 1)
				{
					array_unshift($max_price, $plan->price);
					$max_price             = array_unique($max_price);
					$plan->donation_prices = $max_price;
				}
				else
				{
					$max_price               = $max_price[0];
					$step                    = $plan->params->get('properties.donation_step', 5);
					$plan->donation_prices[] = $plan->price;
					$temp_price              = $plan->price < $step ? 0 : $plan->price;

					do
					{
						$temp_price += $step;
						if($temp_price > $max_price)
						{
							$temp_price = $max_price;
						}
						$plan->donation_prices[] = $temp_price;
					}
					while($temp_price < $max_price);
				}

			}

			if($plan->params->get('properties.donation_manual', 0))
			{
				$plan->is_donation = 2;
			}

			if($app->input->getInt('donation_amount', FALSE))
			{
				$plan->total = $app->input->getInt('donation_amount');
			}
		}

		/*******************************/

		$plan->cname = JText::_(@$plan->cname);

		$plan->terms = '';
		if($plan->params->get('properties.terms'))
		{
			if(JFactory::getApplication()->isSite())
			{
				include_once JPATH_ROOT . '/components/com_content/models/article.php';

			}
			else
			{
				include_once JPATH_ROOT . '/administrator/components/com_content/models/article.php';
			}
			$model = JModelLegacy::getInstance('Article', 'ContentModel');
			$terms = $model->getItem($plan->params->get('properties.terms'));

			$plan->terms = $terms;
		}

		return $plan;
	}

	public static function _isHidden($plan)
	{

		$fields = MModelBase::getInstance('EmPayment', 'JoomsubscriptionModel')->getAddonFields($plan);

		if($fields)
		{
			foreach($fields AS $field)
			{
				if(!$field->isReady())
				{
					return TRUE;
				}
			}
		}


		$user_plans = self::getUserPlans();

		if(empty($user_plans))
		{
			return FALSE;
		}

		static $ids = array();
		static $loaded = FALSE;

		if(!$loaded)
		{
			$db = JFactory::getDbo();
			$db->setQuery("SELECT id, params FROM #__joomsubscription_plans WHERE id IN (" . implode(',', $user_plans) . ")");
			$list = $db->loadAssocList('id', 'params');

			foreach($list AS $plan_id => $params)
			{
				$params = new JRegistry($params);
				if(!$params->get('crossplans.plans_hide'))
				{
					continue;
				}
				if($params->get('crossplans.plans_hide_active') == 1 && !JoomsubscriptionHelper::getUserActiveSubscriptions(FALSE, 0, $plan_id))
				{
					continue;
				}
				$ids = array_merge($ids, $params->get('crossplans.plans_hide'));
			}
			$loaded = TRUE;
		}

		if(in_array($plan->id, $ids))
		{
			return TRUE;
		}

		return FALSE;
	}

	public static function sendAlert($type, $subscription, $options = NULL)
	{
		if(!$type || !$subscription)
		{
			return FALSE;
		}
		if(!is_object($subscription))
		{
			$subscription_model = JModelLegacy::getInstance('EmSale', 'JoomsubscriptionModel');
			$subscription       = $subscription_model->getItem($subscription);
		}

		$config = JFactory::getConfig();
		$mail   = JFactory::getMailer();

		$plan = isset($options['plan']) ? $options['plan'] : FALSE;
		$day  = isset($options['day']) ? $options['day'] : 0;

		if(!isset($options['plan']))
		{
			$plan = JoomsubscriptionApi::getPlan($subscription->plan_id);
		}

		switch($type)
		{
			case 'expire':
				$body    = Mint::_($plan->params->get('alerts.msg_expiration', 'GENERAL_ALERT_EXPIRE'));
				$subject = JText::sprintf($plan->params->get('alerts.msg_expiration_sbj'), $day);
				break;
			case 'success':
				if(!$plan->params->get('alerts.alert_enable_success', FALSE))
				{
					return;
				}
				$body    = Mint::_($plan->params->get('alerts.msg_successful', 'GENERAL_ALERT_SUCCESS'));
				$subject = JText::sprintf($plan->params->get('alerts.msg_successful_sbj'), $day);
				break;
			case 'fail':
				if(!$plan->params->get('alerts.alert_enable_fail', FALSE))
				{
					return;
				}
				$body    = Mint::_($plan->params->get('alerts.msg_fail', 'GENERAL_ALERT_FAIL'));
				$subject = JText::sprintf($plan->params->get('alerts.msg_fail_sbj'), $day);
				break;
			case 'cancel':
				if(!$plan->params->get('alerts.alert_enable_cancel', FALSE))
				{
					return;
				}
				$body    = Mint::_($plan->params->get('alerts.msg_cancel', 'GENERAL_ALERT_DEACTIVATE'));
				$subject = JText::sprintf($plan->params->get('alerts.msg_cancel_sbj'), $day);
				break;

		}
		$body    = self::_prepareText($body, $subscription, $plan, $day);
		$body    = JHtml::_('content.prepare', $body);
		$subject = self::_prepareText($subject, $subscription, $plan, $day);

		$mail->IsHTML(TRUE);
		if($plan->params->get('alerts.send_as', 'text') == 'text')
		{
			$body = strip_tags(str_replace("<br />", "\n", $body));
			$mail->IsHTML(FALSE);
		}

		$sender[0] = $config->get('mailfrom');
		$sender[1] = $config->get('fromname');

		$mail->setSender($sender);
		$mail->AddAddress(JFactory::getUser($subscription->user_id)->email);
		if($plan->params->get('alerts.extra_emails', FALSE))
		{
			$emails = explode(',', $plan->params->get('alerts.extra_emails'));
			foreach($emails as $email)
			{
				$mail->addBCC(JMailHelper::cleanAddress($email));
			}
		}
		$mail->setBody(JMailHelper::cleanBody($body));
		$mail->setSubject(JMailHelper::cleanSubject($subject));

		return $mail->Send();
	}

	private static function _prepareText($body, $subscription, $plan, $day = 0)
	{
		$params = JComponentHelper::getParams('com_joomsubscription');

		$body = str_ireplace('[USER]', str_replace("\n", ' ', JFactory::getUser($subscription->user_id)->name), $body);
		$body = str_ireplace('[LOGINNAME]', str_replace("\n", ' ', JFactory::getUser($subscription->user_id)->username), $body);
		$body = str_ireplace('[EMAIL]', JFactory::getUser($subscription->user_id)->email, $body);
		$body = str_ireplace('[DAY]', $day, $body);
		$body = str_ireplace('[PLAN]', $plan->name, $body);
		$body = str_ireplace('[ORDER_ID]', $subscription->gateway_id, $body);
		$body = str_ireplace('[LINK]', JoomsubscriptionApi::getLink('emhistory'), $body);
		$body = str_ireplace('[AMOUNT]', JoomsubscriptionApi::getPrice($subscription->price, $plan->params), $body);
		$body = str_ireplace('[GROUPNAME]', $plan->cname, $body);
		$body = str_ireplace('[NOTE]', $subscription->note, $body);
		$body = str_ireplace('[START]', JHtml::_('date', $subscription->ctime, $params->get('date_format')), $body);
		$body = str_ireplace('[END]', JHtml::_('date', $subscription->extime, $params->get('date_format')), $body);
		$body = str_ireplace('[GATEWAY]', $subscription->gateway, $body);

		if(preg_match("/\[START([\+\-]{1})([0-9\.]*)\]/iU", $body, $m))
		{
			$time = strtotime($subscription->ctime);
			if($m[1] == '+')
			{
				$time += ($m[2] * 3600);
			}
			else
			{
				$time -= ($m[2] * 3600);
			}

			$body = str_ireplace($m[0], JHtml::_('date', JDate::getInstance($time)->toSql(), $params->get('date_format')), $body);
		}

		if(preg_match("/\[END([\+\-]{1})([0-9\.]*)\]/iU", $body, $m))
		{
			$time = strtotime($subscription->extime);
			if($m[1] == '+')
			{
				$time += ($m[2] * 3600);
			}
			else
			{
				$time -= ($m[2] * 3600);
			}

			$body = str_ireplace($m[0], JHtml::_('date', JDate::getInstance($time)->toSql(), $params->get('date_format')), $body);
		}

		$id = JFactory::getApplication()->input->cookie->get('i_want_to_prolong');
		if($id)
		{
			include_once JPATH_ROOT . '/components/com_cobalt/api.php';

			$record = ItemsStore::getRecord($id);
			$body   = str_ireplace('[PROLONG_TITLE]', $record->title, $body);
		}

		return $body;
	}

	public static function isActiveSubscription($subscription_id)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(TRUE);
		$query->select('id');
		$query->from('#__joomsubscription_subscriptions');
		$query->where('activated = 1');
		$query->where("((access_limit = 0) OR (access_limit > 0 AND access_count < access_limit))");
		$query->where('(extime > NOW() OR extime = "0000-00-00 00:00:00")');
		$query->where('id = ' . $subscription_id);
		$db->setQuery($query);

		return (bool)$db->loadColumn();
	}

	public static function userActiveSubscriptionsByPlans($plans, $user_id, $url = NULL, $count = TRUE)
	{
		static $out = array();

		if(is_array($plans))
		{
			$plans = implode(',', $plans);
		}

		$hasurl = JoomsubscriptionHelper::hasUrlInHistory($url, $user_id);

		$key = md5("$user_id-$hasurl-$plans");

		if(array_key_exists($key, $out))
		{
			return $out[$key];
		}

		$db    = JFactory::getDbo();
		$query = $db->getQuery(TRUE);

		$query->select('u.*');

		$query->from('#__joomsubscription_subscriptions AS u');
		$query->order("u.ctime ASC");

		$query->where("u.plan_id IN ({$plans})");
		$query->where("u.user_id = {$user_id}");
		$query->where("(u.extime > NOW() OR u.extime = '0000-00-00 00:00:00')");
		$query->where("u.ctime < NOW()");
		$query->where("u.published = 1");

		if($count)
		{
			$query->select("IF(u.access_limit > 0 AND u.access_count_mode > 0,
				IF(u.access_count >= u.access_limit,
					IF(u.access_count_mode = 1 AND {$hasurl}, 1, 0),
				1),
			1) AS cl");
			$query->having("cl > 0");
		}

		$db->setQuery($query, 0, 1);

		$out[$key] = $db->loadObject();

		return $out[$key];
	}

	public static function hasUrlInHistory($url, $user_id)
	{
		static $out = array();

		if(empty($url))
		{
			return 1;
		}

		$key = md5("$user_id-$url");

		if(array_key_exists($key, $out))
		{
			return $out[$key];
		}

		$db = JFactory::getDbo();
		$db->setQuery("SELECT id FROM #__joomsubscription_url_history WHERE url = '" . $db->escape($url) . "' AND user_id = $user_id LIMIT 1");

		$out[$key] = (int)!!$db->loadResult();

		return $out[$key];
	}

	static public function loadHead()
	{
		$document = JFactory::getDocument();
		if(!JFactory::getApplication()->isClient('administrator'))
		{
			$document->addScript(JRoute::_('index.php?option=com_joomsubscription&task=emajax.mainJS&Itemid=1'));
		}
	}

	static public function getIp()
	{
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

		return $ip;
	}

	static public function getTax($invoice)
	{

		$params = JComponentHelper::getParams('com_joomsubscription');
		$app    = JFactory::getApplication();
		$out    = array(
			'name'    => '',
			'percent' => 0
		);

		if($params->get('use_invoice') == 0)
		{
			return $out;
		}

		if(!$params->get('tax_id'))
		{
			$app->enqueueMessage(JText::_('E_NO_TAX_ID'), 'warning');

			return $out;
		}

		if(!$invoice->get('country_id'))
		{
			return $out;
		}

		/* START RULES NOT TO PAY TAX */

		if(JoomsubscriptionInvoiceHelper::_isEU($params->get('country')) &&
			JoomsubscriptionInvoiceHelper::_isEU($invoice->get('country_id')) &&
			$invoice->get('tax_id')
		)
		{
			return $out;
		}

		/* END RULES NOT TO PAY TAX */

		$db = JFactory::getDbo();

		$query = $db->getQuery(TRUE);
		$query->select('tax, tax_name');
		$query->from('#__joomsubscription_taxes');
		$query->where('country_id = "' . $invoice->get('country_id') . '" AND state_id = ' . (int)$invoice->get('state_id', 0));

		$db->setQuery($query);
		$result = $db->loadObject();

		$out['percent'] = @$result->tax;
		$out['name']    = @$result->tax_name;

		if(empty($out['percent']))
		{
			$query = $db->getQuery(TRUE);
			$query->select('tax, tax_name');
			$query->from('#__joomsubscription_taxes');
			$query->where('country_id = "' . $invoice->get('country_id') . '"');

			$db->setQuery($query);
			$result         = $db->loadObject();
			$out['percent'] = @$result->tax;
			$out['name']    = @$result->tax_name;
		}

		if(empty($out['percent']))
		{
			$query = $db->getQuery(TRUE);
			$query->select('tax, tax_name');
			$query->from('#__joomsubscription_taxes');
			$query->where('country_id = "*"');

			$db->setQuery($query);
			$result         = $db->loadObject();
			$out['percent'] = @$result->tax;
			$out['name']    = @$result->tax_name;
		}

		return $out;
	}

	public static function getInvoiceNum()
	{
		$db = JFactory::getDbo();
		$num_file    = JPATH_ROOT . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_joomsubscription' . DIRECTORY_SEPARATOR . 'invoice_num.txt';

		if(!JFile::exists($num_file))
		{
			$db->setQuery("SELECT max(invoice_num) FROM #__joomsubscription_subscriptions");
			$max_num     = $db->loadResult();
			$invoice_num = $max_num + 1;
			JFile::write($num_file, $invoice_num);
		}
		else
		{
			$content     = JFile::read($num_file);
			$invoice_num = $content + 1;
			JFile::write($num_file, $invoice_num);
		}

		return $invoice_num;

	}
}


class JoomsubscriptionAjaxHelper
{
	public static function error($msg)
	{
		$out = array(
			'success' => 0,
			'error'   => $msg
		);
		echo json_encode($out);
		JFactory::getApplication()->close();
	}

	public static function send($result, $key = 'result')
	{
		$out = array(
			'success' => 1,
			$key      => $result
		);
		echo json_encode($out);
		JFactory::getApplication()->close();
	}
}
