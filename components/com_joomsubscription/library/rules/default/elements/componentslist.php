<?php
/**
 * @package          HikaShop for Joomla!
 * @version          2.2.2
 * @author           hikashop.com
 * @copyright    (C) 2010-2013 HIKARI SOFTWARE. All rights reserved.
 * @license          GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');

JFormHelper::loadFieldClass('list');

class JFormFieldComponentslist extends JFormFieldList
{
	protected $options = array();
	protected $type = 'Componentslist';

	public function getOptions()
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
				//$c->text .= ' - (' . $trans .')';
			}
		}

		array_unshift($coms, JHtml::_('select.option', '', JText::_('ESELECT_COMPONENT')));

		return $coms;
	}

}
