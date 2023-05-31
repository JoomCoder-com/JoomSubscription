<?php
/**
 * JoomSubscription by JoomCoder
 * a component for Joomla (http://www.joomla.org)
 * Author Website: https://www.joomcoder.com/
 * @copyright Copyright (C) 2012 JoomCoder (https://www.joomcoder.com/). All rights reserved.
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */
defined('_JEXEC') or die();

jimport('mint.mvc.controller.base');

class JoomsubscriptionControllerEmApi extends MControllerForm
{

	public function plans()
	{
		$db = JFactory::getDbo();
		$db->setQuery("SELECT id, name FROM #__jcs_plans");

		echo json_encode($db->loadObjectList());

		JFactory::getApplication()->close();
	}

	public function action()
	{
		$app = JFactory::getApplication();
		$action = $app->input->get('action');
		$method = $app->input->get('method');

		$file = JPATH_ROOT."/components/com_joomsubscription/library/actions/{$action}/{$action}.php";

		if(JFile::exists($file))
		{
			include_once $file;
			$class = 'JoomsubscriptionAction'.ucfirst($action);

			$data = new stdClass();
			$data->action = array();
			$data->plan_id = 0;

			$action = new $class($data);

			if(method_exists($action, $method))
			{
				try {
					echo json_encode(array('success' => 1, 'result' => $action->$method()));
				}
				catch(Exception $e)
				{
					echo json_encode(array('success' => 0, 'error' => $e->getMessage()));
				}
			}
		}

		JFactory::getApplication()->close();
	}
}