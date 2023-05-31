<?php
/**
 * JoomSubscription by JoomCoder
 * a component for Joomla! 3.0 CMS (http://www.joomla.org)
 * Author Website: https://www.joomcoder.com/
 *
 * @copyright Copyright (C) 2012 JoomCoder (https://www.joomcoder.com/). All rights reserved.
 * @license   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */
defined('_JEXEC') or die();

class JoomsubscriptionActionProlong extends JoomsubscriptionAction
{
	public function onActive($subscription)
	{
		$id = JFactory::getApplication()->input->cookie->get('i_want_to_prolong');

		if(!$id)
		{
			return;
		}

		//JFactory::getApplication()->input->cookie->set('i_want_to_prolong', 0);

		include_once JPATH_ROOT . '/components/com_cobalt/api.php';

		$record = ItemsStore::getRecord($id);
		$field  = CobaltApi::getField($this->params->get('field_id'), $record);

		if(!method_exists($field, 'prolongRecord'))
		{
			return;
		}

		JFactory::getApplication()->input->set('subscr_id', $subscription->id);

		if($field->prolongRecord(array(), $record, $subscription))
		{
			$url  = JRoute::_(Url::record($record));
			$note = JText::sprintf('P_PROLONG_SUCCESS', JHtml::link($url, $record->title));
			JFactory::getApplication()->enqueueMessage($note);
			JFactory::getApplication()->redirect(JoomsubscriptionApi::getLink('emhistory', FALSE));
		}
	}

	public function getDescription()
	{
		return JText::_('P_EXTENDRECORD');
	}
}
