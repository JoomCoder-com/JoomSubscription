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

class JFormFieldKunenaRanks extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var        string
	 * @since    1.6
	 */
	protected $type = 'KunenaRanks';

	/**
	 * Method to get the field input markup.
	 *
	 * @return    string    The field input markup.
	 * @since    1.6
	 */
	protected function getOptions()
	{
		if(!JFolder::exists(JPATH_ROOT.'/components/com_kunena'))
		{
			return array(JHtml::_('select.option', '', 'Kunena is not installed'));
		}

		$db = JFactory::getDbo();
		$db->setQuery("SELECT rank_id as value, rank_title as text FROM #__kunena_ranks");
		return $db->loadObjectList();
	}
}