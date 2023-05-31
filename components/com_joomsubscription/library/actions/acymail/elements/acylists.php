<?php
/**
 * JoomSubscription by JoomCoder
 * a component for Joomla! 3.0 CMS (http://www.joomla.org)
 * Author Website: https://www.joomcoder.com/
 * @copyright Copyright (C) 2012 JoomCoder (https://www.joomcoder.com/). All rights reserved.
 * @license   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die;

JFormHelper::loadFieldClass('list');

class JFormFieldAcylists extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var        string
	 * @since    1.6
	 */
	protected $type = 'Acylists';

	/**
	 * Method to get the field input markup.
	 *
	 * @return    string    The field input markup.
	 * @since    1.6
	 */
	protected function getOptions()
	{
		$api = JPATH_ADMINISTRATOR . '/components/com_acymailing/helpers/helper.php';
		if(!JFile::exists($api))
		{
			return array(JHtml::_('select.option', '', 'Acymailing is not installed'));
		}

		include_once $api;

		$options   = array();
		$listClass = acymailing_get('class.list');
		$allLists  = $listClass->getLists();

		$options[] = JHtml::_('select.option', '', JText::_('X_ACY_SELECT'));

		foreach($allLists AS $list)
		{
			$options[] = JHtml::_('select.option', $list->listid, $list->name);
		}

		return $options;
	}
}