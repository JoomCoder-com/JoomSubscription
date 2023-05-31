<?php
/**
 * JoomSubscription by JoomCoder
 * a component for Joomla (http://www.joomla.org)
 * Author Website: https://www.joomcoder.com/
 * @copyright Copyright (C) 2012 JoomCoder (https://www.joomcoder.com/). All rights reserved.
 * @license   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */
defined('_JEXEC') or die();

jimport('joomla.application.component.controller');
class JoomsubscriptionController extends JControllerLegacy
{
	function __construct()
	{
		$app = JFactory::getApplication();

		if(!$app->input->get('view'))
		{
			$app->input->set('view', 'plans');
		}

		parent::__construct();
	}

	function display($cachable = FALSE, $urlparams = FALSE)
	{
		$this->_path['view'] = array(JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_joomsubscription' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR);

		$this->input->set('view', 'about');
		$view = $this->getView('about', 'html', '', array('base_path' => JPATH_ADMINISTRATOR . '/components/com_joomsubscription'));
		$view->setLayout('default');
		$view->display();
	}
}