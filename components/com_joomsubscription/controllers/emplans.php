<?php
/**
 * JoomSubscription by JoomCoder
 * a component for Joomla! 3.0 CMS (http://www.joomla.org)
 * Author Website: https://www.joomcoder.com/
 * @copyright Copyright (C) 2012 JoomCoder (https://www.joomcoder.com/). All rights reserved.
 * @license   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */
defined('_JEXEC') or die();

class JoomsubscriptionControllerEmPlans extends MControllerAdmin
{

	public function &getModel($name = 'EmPlan', $prefix = 'JoomsubscriptionModel', $config = array())
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => TRUE));
		return $model;
	}
	public function delete()
	{
		$cid = JFactory::getApplication()->input->get('cid', array(), 'array');
		$app = JFactory::getApplication();

		if (!is_array($cid) || count($cid) < 1)
		{
			JLog::add(JText::_($this->text_prefix . '_NO_ITEM_SELECTED'), JLog::WARNING, 'jerror');
			$app->redirect(JRoute::_('index.php?option=com_joomsubscription&view=plans', FALSE));
		}
		else
		{
			$db = JFactory::getDbo();
			foreach($cid AS $plan_id)
			{
				$db->setQuery("SELECT count(*) FROM #__joomsubscription_subscriptions WHERE plan_id = $plan_id");
				if($db->loadResult())
				{
					JError::raiseWarning(100, JText::sprintf('EMR_CANNOTDELTEPLAN', $plan_id));
					$app->redirect(JRoute::_('index.php?option=com_joomsubscription&view=plans', FALSE));
				}
			}
			parent::delete();

			$db->setQuery("DELETE FROM #__joomsubscription_plans_actions WHERE plan_id NOT IN (SELECT id FROM #__joomsubscription_plans WHERE id > 0)");
			$db->execute();

			$db->setQuery("DELETE FROM #__joomsubscription_plans_rules WHERE plan_id NOT IN (SELECT id FROM #__joomsubscription_plans WHERE id > 0)");
			$db->execute();
		}
	}


	public function ordersave()
	{
		$pks   = $this->input->post->get('cid', array(), 'array');
		$order = $this->input->post->get('order', array(), 'array');

		// Sanitize the input
		JArrayHelper::toInteger($pks);
		JArrayHelper::toInteger($order);

		// Get the model
		$model = $this->getModel();

		// Save the ordering
		$return = $model->saveorder($pks, $order);

		if($return)
		{
			echo "1";
		}


		JFactory::getApplication()->close();
	}
}