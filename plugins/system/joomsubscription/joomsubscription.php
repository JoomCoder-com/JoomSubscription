<?php
/**
 * Joomsubscription Restriction Plugin by JoomCoder
 * a plugin for Joomla! 1.7 CMS (http://www.joomla.org)
 * Author Website: https://www.joomcoder.com/
 *
 * @copyright Copyright (C) 2012 JoomCoder (https://www.joomcoder.com/). All rights reserved.
 * @license   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.menu');
jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');

class plgSystemJoomsubscription extends JPlugin
{
	function onAfterRoute()
	{
		if(JFactory::getApplication()->isClient('administrator'))
		{
			return;
		}

		$joomsubscription_file = JPATH_ROOT . '/components/com_joomsubscription/api.php';
		if(!JFile::exists($joomsubscription_file))
		{
			return NULL;
		}

		include_once $joomsubscription_file;

		$session = JFactory::getSession();
		$config  = JFactory::getConfig();
		$plan    = $session->get('joomsubscription_reg_plan', FALSE);

		$cookie_domain = $config->get('cookie_domain', '');
		$cookie_path   = $config->get('cookie_path', '/');

		if($plan)
		{
			$session->clear('joomsubscription_reg_plan');
			JoomsubscriptionApi::send($plan['user_id'], $plan['plan_id'], $plan['gateway'], $plan['coupon'], FALSE);
		}

		if($session->get('joomsubscription_reg_pass') && $session->get('joomsubscription_reg_name'))
		{
			$options             = array();
			$options['remember'] = 1;
			$options['return']   = NULL;
			$options['silent']   = TRUE;
			$options['lifetime'] = 1;

			$credentials             = array();
			$credentials['username'] = $session->get('joomsubscription_reg_name');
			$credentials['password'] = $session->get('joomsubscription_reg_pass');

			$session->clear('joomsubscription_reg_name');
			$session->clear('joomsubscription_reg_pass');

			JFactory::getApplication()->login($credentials, $options);
		}

		$this->_actions();

		$user = JFactory::getUser();

		if(in_array($this->params->get('skip_access'), $user->getAuthorisedViewLevels()))
		{
			return;
		}
		if(in_array($this->params->get('skip_group'), $user->getAuthorisedGroups()))
		{
			return;
		}

		// Check subscription required for any site page.
		$this->_checkSubscr();

		$app    = JFactory::getApplication();
		$input  = $app->input;
		$db     = JFactory::getDbo();
		$option = $input->getCmd('option');

		$plans = $messages = array();
		$count = 0;

		/*
		 * Check Rules restrictions
		 */
		$query = $db->getQuery(TRUE);
		$query->select('*');
		$query->from('#__joomsubscription_plans_rules');
		$query->where('`option` = ' . $db->quote($option));
		$query->where('plan_id IN(SELECT id FROM #__joomsubscription_plans WHERE published = 1)');
		$db->setQuery($query);
		$rules = $db->loadObjectList();

		$hash = md5('joomsubscription_first_free-' . $user->get('id'));

		foreach($rules AS $rule)
		{
			$class = JoomsubscriptionRulesHelper::get_rule_class($rule);
			if($class->isProtected())
			{
				$messages[md5(strtolower(JText::_($class->params->get('message', 'ERR_MSG_RESTRITED'))))] = JText::_($class->params->get('message', 'ERR_MSG_RESTRITED'));

				$count                 = $class->params->get('count_limit_mode') ? $class->params->get('count_limit_mode') : $count;
				$plans[$rule->plan_id] = $rule->plan_id;
			}
		}

		if(empty($plans))
		{
			return;
		}

		/*
		 * Check fields restrictions
		 */
		if($user->get('id'))
		{
			$user_subscriptions = JoomsubscriptionHelper::getUserActiveSubscriptions();
			foreach($user_subscriptions AS $us)
			{
				$fields = json_decode($us->fields, TRUE);

				if(empty($fields))
				{
					continue;
				}

				$plan        = JoomsubscriptionApi::getPlan($us->plan_id);
				$fields_list = MModelBase::getInstance('EmPayment', 'JoomsubscriptionModel')->getAddonFields($plan, $fields);

				foreach($fields_list AS $field)
				{
					if($field->hasAccess() == TRUE)
					{
						if($field->params->get('params.count_limit_mode', 1))
						{
							JoomsubscriptionApi::applyCount($us->id, JUri::getInstance()->toString());
						}

						return;
					}
				}
			}
		}


		$message = count($messages) > 1 ? '<p>' . implode('</p><p>', $messages) . '</p>' : implode('', $messages);
		if(!JoomsubscriptionApi::hasSubscription($plans, $message, $user->get('id'), $count, FALSE))
		{
			if($this->params->get('free_num') > 0)
			{
				$number = max(array(
					$session->get($hash, 0),
					$input->cookie->get($hash, 0)
				));

				if($number < $this->params->get('free_num') && $user->get('id'))
				{
					$session->set($hash, $number + 1);
					$input->cookie->set($hash, $number + 1, time() + 365 * 86400, $cookie_path, $cookie_domain);
					$app->enqueueMessage(str_replace(
						array(
							'{0}',
							'{1}'
						),
						array(
							$number + 1,
							$this->params->get('free_num')
						),
						JText::_($this->params->get('free_num_text'))));

					return;
				}
			}

			if($message)
			{
				$app->enqueueMessage($message, 'warning');
			}
			$session->set('joomsubscription_access_url', JUri::getInstance()->toString());
			$re = JoomsubscriptionApi::getLink('emlist', FALSE, $plans);
			$app->redirect($re);
		}
	}

	function onAfterRender()
	{
		$app = Jfactory::getApplication();

		if($app->isClient('administrator'))
		{
			return;
		}

		$body = \Joomla\CMS\Factory::getApplication()->getBody();

		$body = self::_searchPAID($body);
		$body = self::_searchUNPAID($body);

        \Joomla\CMS\Factory::getApplication()->setBody($body);

	}

	function onAfterSubscriptionActivated($subscription, $plan)
	{
		$app     = Jfactory::getApplication();
		$user_id = JFactory::getUser()->get('id');
		if($user_id
			&& $subscription->user_id == $user_id
			&& JFactory::getSession()->get('joomsubscription_access_url')
		)
		{
			$redirect = JFactory::getSession()->get('joomsubscription_access_url');
			JFactory::getSession()->set('joomsubscription_access_url', NULL);
			$app->redirect($redirect);
		}
	}

	private function _searchUNPAID($body)
	{
		$search_pattern = '/\[UNPAID=([^\]]*)\](.*)\[\/UNPAID\]/isU';

		if(!preg_match_all($search_pattern, $body, $matches, PREG_SET_ORDER))
		{
			return $body;
		}

		$replace = array();
		if(preg_match_all('/(<textarea(.*)<\/textarea>)/isU', $body, $areas, PREG_SET_ORDER))
		{
			foreach($areas As $area)
			{
				if(preg_match_all($search_pattern, $area[0], $m1, PREG_SET_ORDER))
				{
					$replace[] = array(
						'src' => '{{TA_' . md5($area[0]) . '}}',
						'dst' => $area[0]
					);

					$body = str_replace($area[0], '{{TA_' . md5($area[0]) . '}}', $body);
				}
			}
		}
		if(preg_match_all('/(<input([^>]*)>)/isU', $body, $inputs, PREG_SET_ORDER))
		{
			foreach($inputs As $input)
			{
				if(preg_match_all($search_pattern, $input[0], $m2, PREG_SET_ORDER))
				{
					$replace[] = array(
						'src' => '{{IP_' . md5($input[0]) . '}}',
						'dst' => $input[0]
					);

					$body = str_replace($input[0], '{{IP_' . md5($input[0]) . '}}', $body);
				}
			}
		}

		foreach($matches AS $match)
		{
			if(preg_match("/^[0-9, ]*$/", $match[1]))
			{
				$options = new JRegistry();
				$options->set('id', $match[1]);
			}
			else
			{
				$options = new JRegistry($match[1]);
			}

			$options->set('user_id', $options->get('user_id', JFactory::getUser()->get('id')));


			$ids = JoomsubscriptionHelper::getValues($options->get('id'), TRUE);

			if(JoomsubscriptionApi::hasSubscription($ids, '', $options->get('user_id'), 0, FALSE, md5($match[0])))
			{
				$body = str_replace($match[0], '', $body);
			}
			else
			{
				$match[2] = str_replace(array('[NAME]'), array(JFactory::getUser()->get('name')), $match[2]);
				if(strpos($match[2], '[STAT_TOTAL]') !== FALSE || strpos($match[2], '[STAT_COUNTRIES]') !== FALSE )
                {
                    $db = JFactory::getDbo();
					$db->setQuery("SELECT s.id, i.fields AS invoice
                              FROM `#__joomsubscription_subscriptions` AS s
                              LEFT JOIN `#__joomsubscription_invoice_to` AS i ON i.id = s.invoice_id
                            WHERE s.plan_id IN (" . implode(',', $ids) . ")");
					$list = $db->loadObjectList();

					$match[2] = str_replace('[STAT_TOTAL]', count($list), $match[2]);
					if(strpos($match[2], '[STAT_') !== FALSE)
					{
						$country = $city = $state = array();
						foreach($list AS $subscr)
						{
							if(empty($subscr->invoice))
							{
								continue;
							}

							$inv = json_decode($subscr->invoice);

							$country[@$inv->country] = 0;
							$city[@$inv->city]       = 0;
							$state[@$inv->state]     = 0;

						}
						$match[2] = str_replace('[STAT_COUNTRIES]', count($country), $match[2]);
						$match[2] = str_replace('[STAT_CITIES]', count($city), $match[2]);
						$match[2] = str_replace('[STAT_STATES]', count($state), $match[2]);
					}
				}

				if(strpos($match[2], '[NEW]') !== FALSE || strpos($match[2], '[EXPIRED]') !== FALSE)
				{
					$subscr = JoomsubscriptionHelper::userInactiveSubscriptions($options->get('user_id'), $ids);

					if(count($subscr))
					{
						$match[2] = preg_replace("/\[NEW\].*\[\/NEW\]/isU", '', $match[2]);
					}
					else
					{
						$match[2] = preg_replace("/\[EXPIRED\].*\[\/EXPIRED\]/isU", '', $match[2]);
					}
				}

				$match[2] = str_replace(array(
					'[NEW]',
					'[/NEW]',
					'[EXPIRED]',
					'[/EXPIRED]'
				), '', $match[2]);


				$body = str_replace($match[0], $match[2], $body);
			}
		}

		$body = preg_replace($search_pattern, '', $body);

		foreach($replace AS $rep)
		{
			$body = str_replace($rep['src'], $rep['dst'], $body);
		}

		return $body;
	}

	private function _searchPAID($body)
	{

		$search_pattern = '/\[PAID=([^\]]*)\](.*)\[\/PAID\]/isU';

		if(!preg_match_all($search_pattern, $body, $matches, PREG_SET_ORDER))
		{
			return $body;
		}

		$replace = array();
		if(preg_match_all('/(<textarea(.*)<\/textarea>)/isU', $body, $areas, PREG_SET_ORDER))
		{
			foreach($areas As $area)
			{
				if(preg_match_all($search_pattern, $area[0], $m1, PREG_SET_ORDER))
				{
					$replace[] = array(
						'src' => '{{TA_' . md5($area[0]) . '}}',
						'dst' => $area[0]
					);

					$body = str_replace($area[0], '{{TA_' . md5($area[0]) . '}}', $body);
				}
			}
		}
		if(preg_match_all('/(<input([^>]*)>)/isU', $body, $inputs, PREG_SET_ORDER))
		{
			foreach($inputs As $input)
			{
				if(preg_match_all($search_pattern, $input[0], $m2, PREG_SET_ORDER))
				{
					$replace[] = array(
						'src' => '{{IP_' . md5($input[0]) . '}}',
						'dst' => $input[0]
					);

					$body = str_replace($input[0], '{{IP_' . md5($input[0]) . '}}', $body);
				}
			}
		}

		foreach($matches AS $match)
		{

			if(preg_match("/^[0-9, ]*$/", $match[1]))
			{
				$options = new JRegistry();
				$options->set('id', $match[1]);
			}
			else
			{
				$options = new JRegistry($match[1]);
			}

			if(!$options->get('id'))
			{
				continue;
			}

			$ids = JoomsubscriptionHelper::getValues($options->get('id'), TRUE);

			if(empty($ids))
			{
				continue;
			}

			$message = array();
			if(JoomsubscriptionApi::hasSubscription($ids, $options->get('title'), $options->get('user_id'), $options->get('count', 1), $options->get('redirect', 0), md5($match[0])))
			{
				$content = str_replace(array('[NAME]'), array(JFactory::getUser()->get('name')), $match[2]);
				if(strpos($content, '[REMAININGDAYS]') !== FALSE)
				{
					$content = str_replace('[REMAININGDAYS]', JoomsubscriptionApi::getDaysRemain($ids), $content);

				}
				if($options->get('delay') > 0)
				{
					$subscription = JoomsubscriptionHelper::userActiveSubscriptionsByPlans($ids, JFactory::getUser()->get('id'));
					$when         = strtotime($subscription->ctime) + (86400 * $options->get('delay'));
					if($when > time())
					{
						$days = round(($when - time()) / 86400);
						if($days > 0)
						{
							$message[] = JText::sprintf('EM_WILLBEAVAILABLE',
								JHtml::_('date', $when, JText::_('DATE_FORMAT_LC3')),
								JHtml::_('date', $when, 'H:i'),
								$days
							);
						}
						else
						{
							$hours     = ceil(($when - time()) / 3600);
							$message[] = JText::sprintf('EM_WILLBEAVAILABLE_HOUR',
								JHtml::_('date', $when, JText::_('DATE_FORMAT_LC3')),
								JHtml::_('date', $when, 'H:i'),
								$hours
							);
						}
					}
					else
					{
						$body = str_replace($match[0], $content, $body);
						continue;
					}
				}
				else
				{
					$body = str_replace($match[0], $content, $body);
					continue;
				}
			}
			else if($options->get('title'))
			{
				$message[] = str_replace('[NAME]', JFactory::getUser()->get('name'), JText::_($options->get('title')));

				if($options->get('link', 1))
				{
					$message[] = sprintf('<a href="%s">%s</a>',
						JoomsubscriptionApi::getLink('emlist', $ids), JText::_($this->params->get('link', 'Subscribe now')));
				}
			}


			$string = "";
			if($message)
			{
				$pattern = '<div class="alert alert-warning">%s</div>';
				$string  = sprintf($pattern, '<p>' . implode('</p><p>', $message) . '</p>');
			}

			$body = str_replace($match[0], $string, $body);
		}

		// Clean the rest.
		$body = preg_replace($search_pattern, '', $body);

		foreach($replace AS $rep)
		{
			$body = str_replace($rep['src'], $rep['dst'], $body);
		}

		return $body;

	}

	private function _checkSubscr()
	{
		if(!$this->params->get('require'))
		{
			return;
		}

		if(!JFactory::getUser()->get('id'))
		{
			return;
		}

		$app    = JFactory::getApplication();
		$input  = $app->input;
		$option = $input->getCmd('option');

		$allow = array(
			'payment',
			'list',
			'history',
			'empayment',
			'emlist',
			'emhistory',
			'empayment.send'
		);

		if((in_array($input->getCmd('view'), $allow) && $option == 'com_joomsubscription') || substr($input->getCmd('task'), 0, 6) == 'emajax' ||
			$option == 'com_users' || in_array($input->getCmd('task'), $allow))
		{
			return;
		}

		if(!JoomsubscriptionHelper::getUserActiveSubscriptions())
		{
			$app->redirect(JoomsubscriptionApi::getLink('emlist', FALSE));
		}
	}

	private function _actions()
	{
		if(!JFactory::getUser()->get('id'))
		{
			return;
		}

		$db = JFactory::getDbo();

		//$db->setQuery("UPDATE #__users SET `activation` = '' WHERE `block` = 0");
		//$db->execute();

		$query = $db->getQuery(TRUE);
		$query->select("*");
		$query->from("#__joomsubscription_subscriptions");
		$query->where("(extime > NOW() OR extime = '0000-00-00 00:00:00')");
		$query->where("NOW() >= ctime");
		$query->where("track_active = 0");
		$query->where("published = 1");
		$query->where("activated = 1");
		$query->where("user_id = " . JFactory::getUser()->get('id'));

		$db->setQuery($query);
		$list = $db->loadObjectList();
		foreach($list AS $subscription)
		{
			JoomsubscriptionActionsHelper::run('onActive', $subscription);
		}

		$query = $db->getQuery(TRUE);
		$query->select("*");
		$query->from("#__joomsubscription_subscriptions");
		$query->where("((extime < NOW() AND extime != '0000-00-00 00:00:00') OR (access_limit > 0 AND access_count >= access_limit) OR published = 0)");
		$query->where("ctime < NOW()");
		$query->where("track_disactive = 0");
		$query->where("activated = 1");
		$query->where("user_id = " . JFactory::getUser()->get('id'));

		$db->setQuery($query);
		$list = $db->loadObjectList();
		foreach($list AS $subscription)
		{
			JoomsubscriptionActionsHelper::run('onDisactive', $subscription);
			JoomsubscriptionActionsHelper::cleanUrlHistory($subscription->id);
		}
	}
}