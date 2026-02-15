<?php
/**
 * JoomSubscription by JoomCoder
 * a component for Joomla! 3.0 CMS (http://www.joomla.org)
 * Author Website: https://www.joomcoder.com/
 * @copyright Copyright (C) 2012 JoomCoder (https://www.joomcoder.com/). All rights reserved.
 * @license   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */
defined('_JEXEC') or die();

class JoomsubscriptionControllerEmPayment extends MControllerAdmin
{

	public function &getModel($name = 'EmPayment', $prefix = 'JoomsubscriptionModel', $config = array())
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => TRUE), $config = array());

		return $model;
	}

	public function getinvoicetext()
	{
		echo JoomsubscriptionInvoiceHelper::text();
		\Joomla\CMS\Factory::getApplication()->close();
	}

	public function getinvoiceform()
	{
		echo JoomsubscriptionInvoiceHelper::form();
		\Joomla\CMS\Factory::getApplication()->close();
	}

	public function coupon()
	{
		$app = \Joomla\CMS\Factory::getApplication();
		$app->setUserState('last-joomsubscription-coupon', $app->input->getString('coupon'));
		$app->redirect(\Joomla\CMS\Uri\Uri::getInstance()->toString());
	}

	public function send()
	{
		$app        = \Joomla\CMS\Factory::getApplication();
		$this->plan = JoomsubscriptionApi::getPreparedPlan($this->input->get('sid'));
		$user_id    = \Joomla\CMS\Factory::getUser()->get('id');

		if(!$user_id && $this->plan->params->get('properties.rds', 0))
		{
			$user_id = $this->_rds();
		}

		JoomsubscriptionApi::send($user_id, $this->input->get('sid'), $this->input->get('processor'), $this->input->get('coupon'));
	}

	private function _rds()
	{
		$email   = $this->input->getString('email');
		$element = new SimpleXMLElement('<xml required="true"></xml>');
		$rule    = new JFormRuleEmail($element, $email);

		if(!$rule->test($element, $email))
		{
			$msg = \Joomla\CMS\Language\Text::_('EMAILISNOTCORRECT');

			$this->setRedirect(\Joomla\CMS\Router\Route::_('index.php?option=com_joomsubscription&view=empayment&sid=' . $this->plan->id), $msg);
			$this->redirect();
		}

		$db = \Joomla\CMS\Factory::getDbo();

		$db->setQuery("SELECT id FROM #__users WHERE email = '{$email}'");
		$result = $db->loadObject();

		if($result)
		{
			return $result->id;
		}

		$username = $email;

		if(!$this->plan->params->get('properties.rds_email_login', 0))
		{
			$parts    = explode('@', $email);
			$username = $parts[0];

			do
			{
				$db->setQuery("SELECT id FROM #__users WHERE username = '" . $db->escape($username) . "'");
				$result = $db->loadResult();
				if($result)
				{
					$username .= '_';
				}
			}
			while($result);

		}

		$data['name']      = $username;
		$data['username']  = $username;
		$data['email1']    = $email;
		$data['email2']    = $email;
		$pass              = JUserHelper::genRandomPassword();
		$data['password1'] = $pass;

		$lang = \Joomla\CMS\Factory::getLanguage();
		$lang->load('com_users');

		MModelBase::addIncludePath(JPATH_ROOT . '/components/com_users/models');
		$model = MModelBase::getInstance('Registration', 'UsersModel');

		//В модели Registration этого нет, модель вызывается не из родного компонента.
		//Формы не находит, ошибки не выдает, см. строка 128.
		JForm::addFormPath(JPATH_ROOT . '/components/com_users/models/forms');

		$return = $model->register($data);

		if(!$return)
		{
			$this->setError($model->getError());
			$this->setRedirect(\Joomla\CMS\Router\Route::_('index.php?option=com_joomsubscription&view=empayment&sid=' . $this->plan->id), $model->getError());
			$this->redirect();
		}

		$com_user = \Joomla\CMS\Component\ComponentHelper::getParams('com_users');

		if($com_user->get('useractivation') == 0)
		{
			$options             = array();
			$options['remember'] = 1;
			$options['return']   = NULL;
			$options['silent']   = TRUE;
			$options['lifetime'] = 1;

			$credentials             = array();
			$credentials['username'] = $username;
			$credentials['password'] = $pass;

			$url = \Joomla\CMS\Factory::getSession()->get('joomsubscription_access_url', NULL);
			$result = \Joomla\CMS\Factory::getApplication()->login($credentials, $options);
			\Joomla\CMS\Factory::getSession()->set('joomsubscription_access_url', $url);
		}

		if(!is_int($return))
		{
			$query = $db->getQuery(TRUE);
			$query->select('id');
			$query->from('#__users');
			$query->where('username = ' . $db->Quote($username));
			$db->setQuery($query);

			return $db->loadResult();
		}

		return $return;
	}
}