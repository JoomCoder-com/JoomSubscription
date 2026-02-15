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
		$id = \Joomla\CMS\Factory::getApplication()->input->cookie->get('i_want_to_prolong');

		if(!$id)
		{
			return;
		}

		//\Joomla\CMS\Factory::getApplication()->input->cookie->set('i_want_to_prolong', 0);

		include_once JPATH_ROOT . '/components/com_cobalt/api.php';

		$record = ItemsStore::getRecord($id);
		$field  = CobaltApi::getField($this->params->get('field_id'), $record);

		if(!method_exists($field, 'prolongRecord'))
		{
			return;
		}

		\Joomla\CMS\Factory::getApplication()->input->set('subscr_id', $subscription->id);

		if($field->prolongRecord(array(), $record, $subscription))
		{
			$url  = \Joomla\CMS\Router\Route::_(Url::record($record));
			$note = \Joomla\CMS\Language\Text::sprintf('P_PROLONG_SUCCESS', Joomla\CMS\HTML\HTMLHelper::link($url, $record->title));
			\Joomla\CMS\Factory::getApplication()->enqueueMessage($note);
			\Joomla\CMS\Factory::getApplication()->redirect(JoomsubscriptionApi::getLink('emhistory', FALSE));
		}
	}

	public function getDescription()
	{
		return \Joomla\CMS\Language\Text::_('P_EXTENDRECORD');
	}
}
