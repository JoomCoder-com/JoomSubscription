<?php
/**
 * JoomSubscription by JoomCoder
 * a component for Joomla! 3.0 CMS (http://www.joomla.org)
 * Author Website: https://www.joomcoder.com/
 * @copyright Copyright (C) 2012 JoomCoder (https://www.joomcoder.com/). All rights reserved.
 * @license   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die;

class JFormFieldKunenaRanks extends \Joomla\CMS\Form\Field\ListField
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
		if(!is_dir(JPATH_ROOT.'/components/com_kunena'))
		{
			return array(Joomla\CMS\HTML\HTMLHelper::_('select.option', '', 'Kunena is not installed'));
		}

		$db = \Joomla\CMS\Factory::getDbo();
		$db->setQuery("SELECT rank_id as value, rank_title as text FROM #__kunena_ranks");
		return $db->loadObjectList();
	}
}