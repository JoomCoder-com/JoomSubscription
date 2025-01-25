<?php
/**
 * JoomSubscription by JoomCoder
 * a component for Joomla (http://www.joomla.org)
 * Author Website: https://www.joomcoder.com/
 *
 * @copyright Copyright (C) 2012 JoomCoder (https://www.joomcoder.com/). All rights reserved.
 * @license   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */
defined('_JEXEC') or die();

jimport('mint.mvc.controller.base');

class JoomsubscriptionController extends MControllerBase
{

	public function display($cachable = FALSE, $urlparams = array())
	{
		$app            = JFactory::getApplication();
		$user           = JFactory::getUser();
		$joomsubscription_config = JComponentHelper::getParams('com_joomsubscription');
		$is_moder       = in_array($joomsubscription_config->get('moderate'), $user->getAuthorisedViewLevels());
		$redirect       = FALSE;
		$sid            = $this->input->getCmd('sid');

		if(!$this->input->getCmd('view'))
		{
			$this->input->set('view', 'emlist');
		}

		/*** Legacy support ***/
		if(substr($this->input->getCmd('view'), 0, 2) != 'em')
		{
			$this->input->set('view', 'em' . $this->input->getCmd('view'));
		}
		/***    ****/

		if(!$user->get('id') && $this->input->getCmd('view', '') != 'emlist')
		{
			if(!($this->input->getCmd('view') == 'empayment' && $sid &&
				JoomsubscriptionApi::getPreparedPlan($sid) &&
				JoomsubscriptionApi::getPreparedPlan($sid)->params->get('properties.rds', 0)))
			{
				$session = JFactory::getSession();
				$session->set('try_this_plan', $sid);
				\Joomla\CMS\Factory::getApplication()->enqueueMessage(JText::_('EMR_REDIRECT'),'warning');
				$redirect = JRoute::_(JComponentHelper::getParams('com_joomsubscription')->get('general_login_url','index.php?option=com_users&view=login') .
					'&return=' . urlencode(base64_encode(JUri::getInstance()->toString())), FALSE);
			}
		}

		if($user->get('id') && !$is_moder && !in_array(
				$this->input->getCmd('view', ''),
				array(
					'emlist', 'emhistory',
					'empayment', 'emmua', 'eminvoice', 'embill'
				)
			)
		)
		{
			$app->enqueueMessage(JText::_('EMR_REDIRECT'), 'warning');
			$redirect = \Joomla\CMS\Uri\Uri::base();
		}

		if($redirect)
		{
			JFactory::getApplication()->redirect($redirect, FALSE);

			return;
		}

		parent::display($cachable = FALSE, $urlparams = array());
	}
}

?>
