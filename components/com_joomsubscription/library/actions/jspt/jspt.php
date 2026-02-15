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

class JoomsubscriptionActionJspt extends JoomsubscriptionAction
{
	private static $_groups = array();

	public function onActive($subscription)
	{
		if(!$this->params->get('profile_active'))
		{
			return;
		}

		$db = \Joomla\CMS\Factory::getDbo();

		$db->setQuery("DELETE FROM `#__xipt_users` WHERE userid = " . $subscription->user_id);
		$db->execute();

		$db->setQuery(sprintf("INSERT INTO `#__xipt_users` (`userid`, `profiletype`, `template`) VALUES (%d, '%s', '%s'",
			$subscription->user_id, $this->params->get('profile_active'), $this->params->get('profile_template')));
		$db->execute();
	}

	public function onDisactive($subscription)
	{
		$db = \Joomla\CMS\Factory::getDbo();

		if($this->params->get('profile_remove') || $this->params->get('profile_deactive'))
		{
			$db->setQuery("DELETE FROM `#__xipt_users` WHERE userid = " . $subscription->user_id);
			$db->execute();
		}

		if(!$this->params->get('profile_deactive'))
		{
			return;
		}

		$db->setQuery(sprintf("INSERT INTO `#__xipt_users` (`userid`, `profiletype`, `template`) VALUES (%d, '%s', '%s'",
			$subscription->user_id, $this->params->get('profile_deactive'), $this->params->get('profile_template')));
		$db->execute();
	}

	public function getDescription()
	{
		$out = '';
		if($this->params->get('profile_active'))
		{
			$out .= '<b>' . \Joomla\CMS\Language\Text::_('ACT_JSPT_DESC_ACTIVE') . '</b><br />';
			$out .= $this->_getProfileType($this->params->get('group_active')) . '<br/>';
		}

		if($this->params->get('profile_remove'))
		{
			$out .= '<b>' . \Joomla\CMS\Language\Text::_('ACT_JSPT_DESC_REMOVE') . '</b><br />';
			$out .= $this->params->get('profile_remove') ? \Joomla\CMS\Language\Text::_('X_YES') : \Joomla\CMS\Language\Text::_('X_NO');
		}

		if($this->params->get('profile_deactive'))
		{
			$out .= '<b>' . \Joomla\CMS\Language\Text::_('ACT_JSPT_DESC_DEACTIVE') . '</b><br />';
			$out .= $this->_getProfileType($this->params->get('profile_deactive')) . '<br/>';
		}

		return $out;
	}

	private function _getProfileType($id)
	{
		$db = \Joomla\CMS\Factory::getDbo();

		$db->setQuery("SELECT `name` FROM `#__xipt_profiletypes` WHERE id = " . $id);

		return $db->loadResult();

	}
}
