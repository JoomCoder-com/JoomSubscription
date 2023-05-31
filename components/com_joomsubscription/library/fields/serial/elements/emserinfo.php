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

class JFormFieldEmserinfo extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var        string
	 * @since    1.6
	 */
	protected $type = 'Emserinfo';

	/**
	 * Method to get the field input markup.
	 *
	 * @return    string    The field input markup.
	 * @since    1.6
	 */
	protected function getInput()
	{
		$db = JFactory::getDbo();

		$db->setQuery(sprintf("SELECT * FROM #__joomsubscription_serial WHERE field_id = %d AND active = %d",
			JFactory::getApplication()->input->get('field_id'), 0));

		echo $db->getQuery();

		$list = $db->loadObjectList();

		ob_start();
		include 'tmpl.php';
		$html = ob_get_contents();
		ob_end_clean();

		return $html;
	}
}