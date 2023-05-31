<?php
/**
 * JoomSubscription by JoomCoder
 * a component for Joomla! 3.0 CMS (http://www.joomla.org)
 * Author Website: https://www.joomcoder.com/
 * @copyright Copyright (C) 2012 JoomCoder (https://www.joomcoder.com/). All rights reserved.
 * @license   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */
defined('_JEXEC') or die();

class JoomsubscriptionControllerPlans extends MControllerAdmin
{

	public function &getModel($name = 'EmPlan', $prefix = 'JoomsubscriptionModel', $config = array())
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => TRUE));
		return $model;
	}

	public function create()
	{
		$table     = JTable::getInstance('EmSubscription', 'JoomsubscriptionTable');
		$processor = $this->input->get('processor');

		// TASK for legacy parse for pay prefix in processor.
		//$processor = preg_replace('/^pay/');

		if(!$processor)
		{
			JError::raiseError(505, JText::_('EMR_NOPROCESSOR'));
			JFactory::getApplication()->close();
		}

		$file = JPATH_ROOT . '/components/com_joomsubscription/library/gateways/' . $processor . '/' . $processor . '.php';
		if(!JFile::exists($file))
		{
			JError::raiseError(500, JText::sprintf('EMR_PROCNOTFOUND', $processor));
			JFactory::getApplication()->close();
		}

		include_once $file;

		$class = 'JoomsubscriptionGateway' . ucfirst($processor);

		if(!class_exists($class))
		{
			JError::raiseWarning(404, JText::_('EMR_GATEWAY_CLASS_NOTFOUND'));
			JFactory::getApplication()->close();
		}

		$class = new $class($processor, array());

		$subscription = $class->get_subscrption_id('NOTIFY_URL');

		if(!$subscription)
		{
			// This is legacy code. To ensure old IPNs are working.
			$plan_id = $class->get_plan_id();
			if(!$plan_id)
			{
				JError::raiseError(500, JText::_('EMR_PLAN_NOT_FOUND'));
				$class->log('Cannot find subscription ID');
				JFactory::getApplication()->close();
			}

			$table->create($processor, $class->get_gateway_id(), $class->get_user_id(), $plan_id, $class->get_amount());
		}
		else
		{
			$table->load($subscription);
		}

		if(empty($table->id))
		{
			JError::raiseWarning(404, JText::_('EMR_MUA_PLAN_NOTFOUND'));
			JFactory::getApplication()->close();
		}

		$plan = JoomsubscriptionApi::getPreparedPlan($table->plan_id);

		if(empty($plan->id))
		{
			JError::raiseError(500, JText::_('EMR_PLAN_NOT_FOUND') );
			JFactory::getApplication()->close();
		}

		$class->init_params($plan->params->get('gateways.' . $processor));

		if(!$class->params->get('enable'))
		{
			JError::raiseError(500, JText::sprintf('EMR_PROCDISABLE', $table->gateway));
			JFactory::getApplication()->close();
		}

		$class->log('Accept started ---', $table->getProperties());
		$class->log('Accept started Request ---', $_REQUEST);
		if(!$class->accept($table, $plan))
		{
			$class->log('Accept method fail ---', JFactory::getApplication()->input->post);
			JError::raiseError(500, $class->getError());
			JFactory::getApplication()->close();
		}

		JoomsubscriptionHelper::activateSubscription($table, $plan);

		$table->store();

		$class->log('Accept finished ---', $table->getProperties());

		$user = JFactory::getUser($table->user_id);

		if($table->published == 1 && $table->activated == 1 && ($user->get('block') == 1 || $user->get('activation')))
		{
			$user->set('block', 0);
			$user->set('activation', '');
			$user->save();
		}

		JFactory::getApplication()->close();
	}
}