<?php
/**
 * JoomSubscription by JoomCoder
 * a component for Joomla! 3.0 CMS (http://www.joomla.org)
 * Author Website: https://www.joomcoder.com/
 * @copyright Copyright (C) 2012 JoomCoder (https://www.joomcoder.com/). All rights reserved.
 * @license   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die();

class JoomsubscriptionControllerEmPlan extends MControllerForm
{
	public function getModel($name = 'EmPlan', $prefix = 'JoomsubscriptionModel', $config = array())
	{
		return MModelBase::getInstance($name, $prefix, $config);
	}

	public function allowEdit($data = array(), $key = 'id')
	{
		return TRUE;
	}

	public function allowAdd($data = array())
	{
		return TRUE;
	}

	public function postSaveHook(MModelBase $model, $validData = array())
	{
		$app  = JFactory::getApplication();
		$db   = JFactory::getDbo();
		$item = $model->getItem();

		$old_id = $app->input->get('id');
		$new_id = $item->get('id');

		if($this->getTask() == 'save2copy')
		{
			$query = "INSERT INTO #__joomsubscription_plans_actions (action, plan_id, type, hits) SELECT action, {$new_id}, type, hits FROM #__joomsubscription_plans_actions WHERE plan_id={$old_id}";

			$db->setQuery($query);
			$db->execute();

			$query = "INSERT INTO #__joomsubscription_plans_rules (rule, plan_id, `option`, `name`, hits) SELECT rule, {$new_id}, `option`, `name`, hits FROM #__joomsubscription_plans_rules WHERE plan_id={$old_id}";

			$db->setQuery($query);
			$db->execute();
		}
	}
}