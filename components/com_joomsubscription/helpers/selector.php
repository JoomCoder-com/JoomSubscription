<?php
/**
 * JoomSubscription by JoomCoder
 * a component for Joomla! 3.0 CMS (http://www.joomla.org)
 * Author Website: https://www.joomcoder.com/
 * @copyright Copyright (C) 2012 JoomCoder (https://www.joomcoder.com/). All rights reserved.
 * @license   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die();

include_once JPATH_ROOT.'/components/com_joomsubscription/views/emselector/html.php';
include_once JPATH_ROOT.'/components/com_joomsubscription/models/emselector.php';

class JoomsubscriptionSelectorHelper
{
	public static function render($name, $plan_ids = 0, $group_ids = 0, $default = array(), $required = false, $layout = 'default')
	{
		$lang = JFactory::getLanguage();
		$lang->load('com_joomsubscription', JPATH_BASE);

		$session = JFactory::getSession();
		if(empty($default['plan_id']) && $session->get('try_this_plan')) {
			$default['plan_id'] = $session->get('try_this_plan');
		}

		$paths = new SplPriorityQueue;
		$paths->insert(JPATH_ROOT . '/components/com_joomsubscription/views/emselector/tmpl', 1);

		$template = JFactory::getApplication()->getTemplate();
		$paths->insert(JPATH_ROOT . '/templates/'.$template.'/html/com_joomsubscription/emselector', 2);

		$view = new JoomsubscriptionViewsEmSelectorHtml(new JoomsubscriptionModelsEmSelector(), $paths);
		$view->setLayout($layout);
		$view->name = $name;
		$view->required = $required;
		$view->plans = $plan_ids;
		$view->groups = $group_ids;
		$view->default = $default;

		return $view->render();
	}
}
