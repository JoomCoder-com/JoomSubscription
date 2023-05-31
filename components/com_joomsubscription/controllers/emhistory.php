<?php
/**
 * JoomSubscription by JoomCoder
 * a component for Joomla! 3.0 CMS (http://www.joomla.org)
 * Author Website: https://www.joomcoder.com/
 *
 * @copyright Copyright (C) 2012 JoomCoder (https://www.joomcoder.com/). All rights reserved.
 * @license   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die;

class JoomsubscriptionControllerEmHistory extends MControllerAdmin
{
	public function cancels()
	{
		$cid   = JFactory::getApplication()->input->get('id');
		$app   = JFactory::getApplication();
		$table = JTable::getInstance('EmSubscription', 'JoomsubscriptionTable');

		$table->load($cid);

		if(!$table->id)
		{
			$app->enqueueMessage(JText::_('EM_SUBSCR_NOTFOUND'), 'warning');
			$app->redirect(JoomsubscriptionApi::getLink('emhistory'));
		}

		if($table->activated == 1)
		{
			$app->enqueueMessage(JText::_('EM_SUBSCR_IS_ACTIVE'), 'warning');
			$app->redirect(JoomsubscriptionApi::getLink('emhistory'));
		}

		if($table->delete($cid))
		{
			$app->enqueueMessage(JText::_('EM_CANCEL_CUBSCRIPTION'));
			$app->redirect(JoomsubscriptionApi::getLink('emhistory'));
		}


		$app->redirect(JoomsubscriptionApi::getLink('emhistory'));
	}
}
