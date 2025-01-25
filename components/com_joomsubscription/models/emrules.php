<?php
/**
 * JoomSubscription by JoomCoder
 * a component for Joomla! 3.0 CMS (http://www.joomla.org)
 * Author Website: https://www.joomcoder.com/
 * @copyright Copyright (C) 2012 JoomCoder (https://www.joomcoder.com/). All rights reserved.
 * @license   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die('Restricted access');

class JoomsubscriptionModelsEmRules extends Joomla\CMS\MVC\Model\BaseModel
{
	public function getAdapters()
	{
		$path    = JPATH_COMPONENT . DIRECTORY_SEPARATOR . 'library' . DIRECTORY_SEPARATOR . 'rules';
		$acts    = JFolder::folders($path);
		$options = array();

		foreach($acts AS $a)
		{
			if($a == 'default')
			{
				$def = JHtml::_('select.option', $a, JoomsubscriptionRulesHelper::get_rule_name($a));
				continue;
			}
			$options[] = JHtml::_('select.option', $a, JoomsubscriptionRulesHelper::get_rule_name($a));
		}

		array_unshift($options, $def);
		array_unshift($options, JHtml::_('select.option', '', JText::_('ESELECT_COMPONENT')));

		return $options;
	}
	/*public function getComponents()
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(TRUE);

		$query->select('e.element as value');
		$query->from('#__extensions AS e');
		$query->where('e.enabled = 1');
		$query->where('e.type = "component"');
		$query->order('e.name asc');

		$skip = array(
			'com_banners', 'com_admin', 'com_cache', 'com_categories', 'com_checkin', 'com_config', 'com_cpanel',
			'com_users', 'com_joomlaupdate', 'com_languages', 'com_login', 'com_menus', 'com_modules', 'com_plugins',
			'com_templates', 'com_extplorer', 'com_jce', 'com_search', 'com_jedchecker', 'com_komento', 'com_redirect',
			'com_gantry', 'com_installer', 'com_media', 'com_language', 'com_menu', 'com_jaextmanager', 'com_joomsubscription', 'com_jslang',
			'com_widgetkit', 'com_cmsupdate', 'com_ajax'
		);
		$query->where("element NOT IN ('" . implode("','", $skip) . "')");
		$query->order('e.element ASC');

		$db->setQuery($query);
		$coms = $db->loadObjectList();

		foreach($coms AS &$c)
		{
			JFactory::getLanguage()->load($c->value . '.sys', JPATH_ADMINISTRATOR);
			$c->text = $c->value;

			$trans = strip_tags(JText::_($c->value));
			if($trans != $c->value)
			{
				$c->text .= ' - (' . $trans .')';
			}
		}

		array_unshift($coms, JHtml::_('select.option', '', JText::_('ESELECT_COMPONENT')));

		return $coms;
	}*/

	public function getRules($plan_id)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(TRUE);

		$query->select('*');
		$query->from('#__joomsubscription_plans_rules');
		$query->where('plan_id = ' . (int)$plan_id);
		$db->setQuery($query);

		return $db->loadObjectList();
	}
}