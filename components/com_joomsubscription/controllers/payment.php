<?php
/**
 * JoomSubscription by JoomCoder
 * a component for Joomla! 3.0 CMS (http://www.joomla.org)
 * Author Website: https://www.joomcoder.com/
 * @copyright Copyright (C) 2012 JoomCoder (https://www.joomcoder.com/). All rights reserved.
 * @license   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */
defined('_JEXEC') or die();

class JoomsubscriptionControllerPayment extends MControllerAdmin
{
	public function back()
	{
		$table     = JTable::getInstance('EmSubscription', 'JoomsubscriptionTable');
		$processor = $this->input->get('processor');

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

		$subscription = $class->get_subscrption_id('RETURN_URL');

		if(!$subscription)
		{
			JError::raiseError(500, JText::_('EMR_MUA_PLAN_NOTFOUND'));
			JFactory::getApplication()->close();
		}
		$table->load($subscription);

		$plan = NULL;

		if(!empty($table->id))
		{
			$plan = JoomsubscriptionApi::getPlans($table->plan_id);
			$plan = $plan[0];
		}

		if($table->published == 1)
		{
			JFactory::getApplication()->enqueueMessage(JText::_('EMR_ACTIVATED_SUCCESS'));
		}
		else
		{
			JError::raiseNotice(100, JText::_('EMR_ACTIVATED_NOTSUCCESS'));
		}

		JoomsubscriptionHelper::redirect($plan, $table->published);
	}
}