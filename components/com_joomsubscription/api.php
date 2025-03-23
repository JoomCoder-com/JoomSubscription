<?php
/**
 * JoomSubscription by JoomCoder
 * a component for Joomla (http://www.joomla.org)
 * Author Website: https://www.joomcoder.com/
 *
 * @copyright Copyright (C) 2012 JoomCoder (https://www.joomcoder.com/). All rights reserved.
 * @license   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

use Joomla\CMS\HTML\HTMLHelper;

defined('_JEXEC') or die('Restricted access');

jimport('joomla.mail.helper');
jimport('joomla.mail.mail');
jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');
jimport('joomla.table.table');
jimport('mint.mvc.model.base');
jimport('mint.mvc.controller.base');
jimport('mint.mvc.view.base');
jimport('mint.helper');
jimport('mint.forms.helper');

//JHtml::_('bootstrap.framework');
//HTMLHelper::_('bootstrap.framework');

$app = JFactory::getApplication();

JLoader::discover('MModel', JPATH_LIBRARIES . '/mint/mvc/model');
JLoader::discover('MView', JPATH_LIBRARIES . '/mint/mvc/view');
JLoader::discover('MController', JPATH_LIBRARIES . '/mint/mvc/controller');

JLoader::registerPrefix('Joomsubscription', JPATH_ROOT . '/components/com_joomsubscription');

JTable::addIncludePath(JPATH_ROOT . '/components/com_joomsubscription/tables');
MModelBase::addIncludePath(JPATH_ROOT . '/components/com_joomsubscription/models');
JHtml::addIncludePath(JPATH_ROOT . '/administrator/components/com_joomsubscription/helpers/html');

foreach(glob(JPATH_ROOT . DIRECTORY_SEPARATOR . 'components/com_joomsubscription/helpers/*.php') as $filename)
{
	require_once $filename;
}

foreach(glob(JPATH_ROOT . DIRECTORY_SEPARATOR . 'components/com_joomsubscription/library/php/*.php') as $filename)
{
	require_once $filename;
}

JFactory::getLanguage()->load('com_joomsubscription');

/**
 * Member API file
 *
 * To use those functions you need to setup on your side like described here
 * http://support.mightyextensions.com/en/mighty-membership-subscription-joomla-component/ideas
 *
 */
class JoomsubscriptionApi
{
	/**
	 * Check if user has active subscription of one of the given subscription plan IDs
	 *
	 * @param array   $plans    - array of the plans
	 * @param string  $msg      - message to be displayed if access denied
	 * @param int     $user_id  - Id fo the user to tes. If 0 then current user is checked.
	 * @param boolean $count    - Update user subscription as used or not. Add one hit.
	 * @param bool    $redirect - Redirect to login or plans page or just return boolean
	 * @param null    $url      - Url what page user tries to access.
	 *
	 * @return bool
	 */
	public static function hasSubscription($plans, $msg, $user_id = 0, $count = TRUE, $redirect = TRUE, $url = NULL, $apply_count = TRUE)
	{
		settype($plans, 'array');
		$plans = array_unique($plans);
		\Joomla\Utilities\ArrayHelper::toInteger($plans);

		if(empty($plans))
		{
			return TRUE;
		}

		$plans = implode(',', $plans);


		$db = JFactory::getDBO();

		$query = $db->getQuery(TRUE);
		$query->select('id');
		$query->from('#__joomsubscription_plans');
		$query->where('published = 1');
		$query->where('id IN (' . $plans . ')');
		$db->setQuery($query);
		$plans = $db->loadColumn();

		if(empty($plans))
		{
			return TRUE;
		}

		$app     = JFactory::getApplication();
		$uri     = JUri::getInstance();
		$url     = $url ? $url : $uri->toString();
		$user_id = $user_id ? $user_id : JFactory::getUser()->get('id');
		$params  = JComponentHelper::getParams('com_joomsubscription');
		$itemid  = $params->get('iid_list', $app->input->getInt('Itemid'));

		if(!$user_id)
		{
			if($redirect)
			{
				JFactory::getSession()->set('joomsubscription_access_url', $url);
				JError::raiseWarning(403, JText::_($msg));
				$return = urlencode(base64_encode($url));
				$app->redirect(JoomsubscriptionApi::getLink('emlist', FALSE, $plans));
			}

			return FALSE;
		}

		$subscription = JoomsubscriptionHelper::userActiveSubscriptionsByPlans($plans, $user_id, $url, $count);
		$id           = @$subscription->id;

		if(empty($id))
		{
			if($redirect)
			{
				if($msg)
				{
					JError::raiseWarning(403, JText::_($msg));
				}
				JFactory::getSession()->set('joomsubscription_access_url', $url);
				$re = JoomsubscriptionApi::getLink('emlist', FALSE, $plans);
				$app->redirect($re);
			}

			return FALSE;
		}

		if($count && $id && $apply_count)
		{
			self::applyCount($id, $url);
		}

		return TRUE;
	}

	public static function applyCountByPlan($plans, $user_id, $url)
	{
		if(is_array($plans))
		{
			$plans = implode(',', $plans);
		}

		$plans = JoomsubscriptionHelper::getUserActiveSubscriptions($user_id, 0, $plans);

		if(count($plans) == 0)
		{
			return;
		}

		$plan = array_shift($plans);

		self::applyCount($plan->id, $url);
	}

	/**
	 * apply count limit to user subscription.
	 *
	 * @param  int   $user_subscr_id - ID of user subscritpion
	 * @param string $url            - URL that is protected
	 */
	public static function applyCount($user_subscr_id, $url = NULL, $note = '')
	{
		$url = ($url ? $url : JUri::getInstance()->toString());
		$db  = JFactory::getDbo();

		include_once JPATH_ROOT . '/components/com_joomsubscription/tables/emsubscription.php';
		$subscr = new JoomsubscriptionTableEmSubscription($db);
		$subscr->load($user_subscr_id);

		if(!$subscr->id)
		{
			return;
		}

		if($note)
		{
			$subscr->note = $note;
			$subscr->store();
		}

		$subscr->access_count += 1;

		$history = array(
			'user_id'         => $subscr->user_id,
			'url'             => $url,
			'subscription_id' => $subscr->plan_id
		);

		$table = JTable::getInstance('EmHistory', 'JoomsubscriptionTable');
		$table->load($history);

		if(empty($table->id))
		{
			$history['ctime'] = JDate::getInstance()->toSql();
			$table->save($history);
		}
		else
		{
			// if unique access url count
			if($subscr->access_count_mode == 1)
			{
				return;
			}
		}

		$subscr->store();
	}

	public static function addSubscription($user_id, $plan_id, $published, $gateway, $price = NULL, $gateway_id = NULL,	$options = array())
	{
		$plan = self::getPreparedPlan($plan_id);

		// This plan is not allowed for this user.
		if(empty($plan->id))
		{
			JFactory::getApplication()->enqueueMessage(JText::_('EMR_PLANNOTALLOWED'), 'notice');

			return FALSE;
		}

		$gateway_id = ($gateway_id ? $gateway_id : substr(strtoupper(md5(time() . '-' . $plan->id)), 0, 8));

		JTable::addIncludePath(JPATH_ROOT . '/components/com_joomsubscription/tables');
		$subscr = JTable::getInstance('EmSubscription', 'JoomsubscriptionTable');
		$subscr->load(
			array(
				'gateway_id' => $gateway_id,
				'gateway'    => $gateway,
				'plan_id'    => $plan_id,
				'user_id'    => $user_id
			)
		);
		$subscr->bind($options);
		$subscr->gateway           = $gateway;
		$subscr->gateway_id        = $gateway_id;
		$subscr->user_id           = $user_id;
		$subscr->plan_id           = $plan_id;
		$subscr->price             = ($price === NULL ? $plan->price : $price);
		$subscr->created           = JDate::getInstance()->toSql();
		$subscr->access_count_mode = $plan->params->get('properties.count_limit_mode');
		$subscr->access_limit      = $plan->params->get('properties.count_limit');
		$subscr->published         = $published;

		if(!$subscr->params)
		{
			$subscr->params = json_encode(array(
				'properties' => array(
					'currency'     => $plan->params->get('properties.currency', 'USD'),
					'layout_price' => $plan->params->get('properties.layout_price', '00Sign')
				)
			));
		}

		unset($subscr->plan_name);

		$isnew = !$subscr->id;

		if($subscr->store())
		{
			if($isnew)
			{
				JoomsubscriptionActionsHelper::run('onCreate', $subscr);
			}

			JoomsubscriptionHelper::activateSubscription($subscr, $plan);

			return $subscr->id;
		}

		return FALSE;
	}

	public static function getPrice($price, $params, $p = NULL)
	{
		$cnf = JComponentHelper::getParams('com_joomsubscription');
		$cur = $params->get('properties.currency', 'USD');
		$lay = $params->get('properties.layout_price', '00Sign');

		if(is_object($p))
		{
			if($p->get('properties.currency'))
			{
				$cur = $p->get('properties.currency');
			}
			if($p->get('properties.layout_price'))
			{
				$lay = $p->get('properties.layout_price');
			}
		}

		return str_replace(array(
			'Sign',
			'00'
		),
			array(
				$cur,
				number_format((float)$price, $cnf->get('price_dec', 2), $cnf->get('price_point', '.'),
					$cnf->get('price_sep', ','))
			),
			$lay);
	}

	public static function getLink($type, $rout = TRUE, $ids = NULL, $full = TRUE)
	{
		$db = JFactory::getDbo();

		if(!empty($ids))
		{
			if(!is_array($ids))
			{
				settype($ids, 'array');
			}

			$sql = "SELECT `id` FROM `#__joomsubscription_plans` WHERE `invisible` = 0 AND `id` IN(" . implode(',', $ids) . ")";
			$db->setQuery($sql);
			$ids = $db->loadColumn();
		}


		settype($ids, 'array');

		if(in_array($type, array(
				'emlist',
				'list'
			)) && count($ids) == 1
		)
		{
			$sid  = implode('', $ids);
			$type = 'empayment';
		}

		if(is_int($ids) && $type == 'empayment')
		{
			$sid = $ids;
		}

		$url = 'index.php?option=com_joomsubscription&view=' . $type;
		$iid = JComponentHelper::getParams('com_joomsubscription')->get('iid_' . str_replace('em', '', $type),
			JFactory::getApplication()->input->getInt('Itemid'));
		$url .= '&Itemid=' . $iid;


		$ids = implode(',', $ids);

		if($ids)
		{
			$url .= '&id=' . $ids;
		}

		if(!empty($sid))
		{
			$url .= '&sid=' . $sid;
		}

		$url = JRoute::_($url, $rout, $full ? -1 : 0);

		if($full)
		{
			$scheme = JUri::getInstance()->toString(array('scheme'));
			$url    = str_replace('http://', $scheme, $url);
		}

		return $url;
	}


	public static function getPreparedPlan($plan_id)
	{
		static $out = array();

		if(array_key_exists($plan_id, $out))
		{
			return $out[$plan_id];
		}

		$out[$plan_id] = JoomsubscriptionHelper::preparePlan(self::getPlan($plan_id));
		return $out[$plan_id];
	}

	public static function getPlan($plan_id, $prepare = FALSE)
	{
		static $out = array();

		$key = $plan_id . '-' . (int)$prepare;

		if(array_key_exists($key, $out))
		{
			return $out[$key];
		}

		$out[$key] = NULL;

		$plan = self::getPlans($plan_id);

		if(is_array($plan) && !empty($plan))
		{
			$plan         = array_shift($plan);
			$plan->params = new JRegistry($plan->params);
			if($prepare)
			{
				$plan = JoomsubscriptionHelper::getPlanDetails($plan);
			}

			$out[$key] = $plan;
		}

		return $out[$key];
	}

	public static function getPlans($plans)
	{
		if(is_array($plans))
		{
			$plans = implode(', ', $plans);
		}
		$db = JFactory::getDBO();

		$query = $db->getQuery(TRUE);

		$query->select('p.*');
		$query->from('#__joomsubscription_plans AS p');

		$query->select('g.name AS cname, g.description AS cdescr, g.params as cparams, g.id AS gid');
		$query->leftJoin('#__joomsubscription_plans_groups AS g ON g.id = p.group_id');

		if($plans)
		{
			$query->where("p.id IN ({$plans})");
		}

		$db->setQuery($query);

		return $db->loadObjectList();
	}

	public static function send($user_id, $plan_id, $processor, $coupon, $redirect = TRUE)
	{
		$table = JTable::getInstance('EmSubscription', 'JoomsubscriptionTable');
		$plan  = self::getPreparedPlan($plan_id);
		$app   = JFactory::getApplication();
		$db    = JFactory::getDbo();

		if(empty($plan->id))
		{
			JError::raiseWarning(100, JText::_('EMR_NOSUSCR'));
			if($redirect)
			{
				$app->redirect(self::getLink('emlist', FALSE));
			}

			return;
		}

		if(!$user_id)
		{
			$app->enqueueMessage(JText::_('EMR_REDIRECT'), 'warning');
			if($redirect)
			{
				$app->redirect(JRoute::_(JComponentHelper::getParams('com_joomsubscription')->get('general_login_url') . '&return=' . urlencode(base64_encode(JUri::getInstance()->toString())),
					FALSE));
			}

			return;
		}

		if($plan->params->get('properties.terms') && $redirect && !$app->input->getInt('terms'))
		{
			$app->enqueueMessage(JText::sprintf('EMR_YOU_HAVE_TO_AGREE', $plan->terms->title), 'warning');
			$app->redirect(JRoute::_('index.php?option=com_joomsubscription&view=empayment&sid=' . $plan->id, FALSE));
		}

		$coupon = JoomsubscriptionHelperCoupon::getCoupon($coupon, $plan->id, $plan->total, TRUE, FALSE);
		$total  = $plan->total;

		$load = array(
			'plan_id'   => $plan->id,
			'user_id'   => $user_id,
			'published' => 0,
			'activated' => 0
		);

		$table->load($load);


		if($plan->params->get('properties.fields'))
		{
			$fields = MModelBase::getInstance('EmPayment', 'JoomsubscriptionModel')->getAddonFields($plan);
			foreach($fields AS $field)
			{
				if($field->required && empty($field->default))
				{
					$app->enqueueMessage(JText::sprintf('EMR_REQUIRED_FIELD_MISS', $field->data->name), 'warning');
					$app->redirect(JRoute::_('index.php?option=com_joomsubscription&view=empayment&sid=' . $plan->id, FALSE));
				}
				if($add = $field->affectPrice($plan))
				{
					$total += $add;
				}

				if($field->getError(0))
				{
					$app->enqueueMessage($field->getError(), 'error');
					if($redirect)
					{
						$app->redirect(JUri::getInstance()->toString());
					}

					return;
				}
			}
		}

		$db->setQuery("SELECT NOW()");
		$load['created'] = $db->loadResult();
		if(!empty($coupon->mua) && !empty($coupon->parent))
		{
			$load['parent'] = $coupon->parent;
		}

		$d  = 0;
		$dt = 'none';
		if(!empty($coupon->discount_total))
		{
			$total -= $coupon->discount_total;
			$d  = $coupon->discount_total;
			$dt = 'coupon';
		}
		elseif($plan->discount)
		{
			$total -= $plan->discount;
			$d  = $plan->discount;
			$dt = $plan->discount_type;
		}

		$params = $app->input->get('params', array(), 'array');

		$params["properties"] = array(
			'currency'     => $plan->params->get('properties.currency', 'USD'),
			'layout_price' => $plan->params->get('properties.layout_price', '00Sign')
		);

		$load['price']             = $total;
		$load['discount']          = $d;
		$load['discount_type']     = $dt;
		$load['gateway']           = $processor;
		$load['access_limit']      = $plan->params->get('properties.count_limit');
		$load['access_count_mode'] = $plan->params->get('properties.count_limit_mode');
		$load['params']            = json_encode($params);
		$load['upgrade_from']      = $plan->upgrade_from;
		$load['fields']            = json_encode($app->input->get('fields', array(), 'array'));

		$table->bind($load);

		if(JComponentHelper::getParams('com_joomsubscription')->get('use_invoice', 0) && $redirect)
		{
			if(!self::addInvoceTo($table) && JComponentHelper::getParams('com_joomsubscription')->get('use_invoice', 0) == 1)
			{
				$app->redirect(JRoute::_('index.php?option=com_joomsubscription&view=empayment&sid=' . $plan->id, FALSE));
			}
		}

		$isnew = !$table->id;
		$table->store();

		if($isnew)
		{
			JoomsubscriptionActionsHelper::run('onCreate', $table);
		}

		if(!empty($coupon->id))
		{
			self::applyCoupon($coupon, $plan, $table, $user_id);
		}


		if($total == 0)
		{
			$table->gateway    = (!empty($coupon->discount_total) ? 'coupon' : ($plan->discount ? 'discount' : 'free'));
			$table->published  = 1;
			$table->gateway_id = time();

			JFactory::getApplication()->enqueueMessage(JText::_('EMR_ACTIVATED_SUCCESS'));

			JoomsubscriptionHelper::activateSubscription($table, $plan);
			$table->store();

			JoomsubscriptionHelper::redirect($plan, $table->published);
		}


		$processor_file = JPATH_ROOT . '/components/com_joomsubscription/library/gateways/' . $processor . '/' . $processor . '.php';
		if(!JFile::exists($processor_file))
		{
			JError::raiseWarning(500, JText::sprintf('EMR_PROCNOTFOUND', $processor_file));
			if($redirect)
			{
				$app->redirect(JoomsubscriptionApi::getLink('emlist', FALSE));
			}

			return;
		}

		include_once $processor_file;

		$class = 'JoomsubscriptionGateway' . ucfirst($processor);
		$class = new $class($processor, $plan->params->get('gateways.' . $processor));

		if(!$class->params->get('enable'))
		{
			JError::raiseWarning(500, JText::sprintf('EMR_PROCDISABLE', $processor));
			if($redirect)
			{
				$app->redirect(JoomsubscriptionApi::getLink('emlist', FALSE));
			}

			return;
		}

		$site_name = $app->getCfg('sitename');
		$name      = JText::sprintf('EMR_SURCHASENAME', $plan->name, $plan->cname);
		//$name     = JText::sprintf('EMR_SURCHASEDESCR', $plan->name, $plan->cname, $plan->period, $site_name);

		$plan->coupon   = $coupon;
		$process_method = $app->input->get('postprocess', 'pay');
		if(!method_exists($class, $process_method))
		{
			$app->enqueueMessage(JText::sprintf('EM_METHOSGATEWAYNOTFOUND', ucfirst($processor),
				$app->input->get('postprocess', 'pay')), 'warning');
			if($redirect)
			{
				$app->redirect(JUri::getInstance()->toString());
			}
		}
		if(!$class->{$process_method}($total, $name, $table, $plan))
		{
			JError::raiseWarning(500, $class->getError());
			if($redirect)
			{
				$app->redirect(JUri::getInstance()->toString());
			}

			return;
		}
	}

	public static function addInvoceTo(&$subscription)
	{
		if($subscription->price <= 0)
		{
			return TRUE;
		}

		$app   = JFactory::getApplication();
		$invto = $app->input->getInt('invoice');
		$app->setUserState('com_joomsubscription.invoiceto.selector', $invto);

		if($invto > 0)
		{
			$subscription->invoice_id = $invto;

			return TRUE;
		}
		elseif($invto == -1)
		{
			$subscription->invoice_id = self::saveBillAddress($subscription->user_id);

			if($subscription->invoice_id === FALSE)
			{
				return FALSE;
			}

			return TRUE;
		}
		else
		{
			if(JComponentHelper::getParams('com_joomsubscription')->get('use_invoice', 0) == 2)
			{
				return TRUE;
			}
			$app->enqueueMessage(JText::_('EMBILLTOREQUIRED'), 'warning');

			return FALSE;
		}
	}

	public static function  saveBillAddress($user_id = 0)
	{
		$app    = JFactory::getApplication();
		$post   = $app->input->get('invoiceto', array(), 'array');
		$fields = @$post['fields'];

		if(!$user_id)
		{
			$user_id = JFactory::getUser()->get('id');
		}

		$app->setUserState('com_joomsubscription.invoiceto.data', $post);

		$model = new JoomsubscriptionModelsEmInvoiceTo();
		$form  = $model->getForm();

		$result = $form->validate($post, 'fields');

		if($result instanceof Exception)
		{
			$app->enqueueMessage($result->getMessage(), 'warning');

			return FALSE;
		}

		if($result === FALSE)
		{
			foreach($form->getErrors() as $message)
			{
				$app->enqueueMessage($message->getMessage(), 'warning');
			}

			return FALSE;
		}

		if(!empty($fields['tax_id']) && !empty($fields['vies']) && JoomsubscriptionInvoiceHelper::_isEU($fields['country']))
		{
			$client = new SoapClient("http://ec.europa.eu/taxation_customs/vies/checkVatService.wsdl");
			$result = $client->checkVat(
				array(
					'countryCode' => ($fields['country'] == 'UK' ? 'GB' : $fields['country']),
					'vatNumber'   => str_replace($fields['country'], '', strtoupper($fields['tax_id']))
				)
			);

			if($result->valid == FALSE)
			{
				$app->enqueueMessage(JText::_('EM_CANNOTFERIFYVAT'), 'warning');

				return FALSE;
			}
		}
		else
		{
			$fields['vies'] = 0;
		}

		$data = array(
			'user_id' => $user_id,
			'fields'  => json_encode($fields),
			'ip'      => JoomsubscriptionHelper::getIp()
		);

		$table = JTable::getInstance('EmInvoiceTo', 'JoomsubscriptionTable');
		$table->load($data);

		if(empty($table->id))
		{
			$table->bind($data);
			$table->id = NULL;
			$table->store();
		}

		return $table->id;
	}

	static public function getDaysRemain($plans, $user_id = NULL)
	{
		$subscriptions = JoomsubscriptionHelper::getUserActiveSubscriptions($user_id, 0, $plans, FALSE);

		$date = 0;
		foreach($subscriptions AS $s)
		{
			$date = $date < strtotime($s->extime) ? strtotime($s->extime) : $date;
		}

		$left = $date - time();

		$out = round($left / 86400);
		$out = $out > 0 ? $out : 0;

		return $out;
	}

	protected function applyCoupon($coupon, $plan, $subscription, $user_id)
	{
		$db = JFactory::getDBO();

		$query = $db->getQuery(TRUE);
		$query->update('#__joomsubscription_coupons');
		$query->set('used_num = used_num + 1');
		$query->where('`id` = ' . $coupon->id);

		if($coupon->discount_type == 'sum')
		{
			$query->set('discount = discount - ' . (float)$plan->total);
		}

		$db->setQuery($query);
		$db->execute();

		$data = array(
			'user_id'         => $user_id,
			'coupon_id'       => $coupon->id,
			'ctime'           => JDate::getInstance()->toSql(),
			'plan_id'         => $plan->id,
			'price'           => $subscription->price,
			'discount'        => $coupon->discount_total,
			'discount_type'   => $coupon->discount_type,
			'subscription_id' => $subscription->id
		);

		$table = JTable::getInstance('EmCouponhistory', 'JoomsubscriptionTable');
		$table->load(array('subscription_id' => $subscription->id));
		$table->bind($data);
		$table->store();
	}
}