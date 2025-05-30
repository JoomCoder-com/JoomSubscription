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

class JFormFieldEsprofiles extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var        string
	 * @since    1.6
	 */
	protected $type = 'Esprofiles';

	/**
	 * Method to get the field input markup.
	 *
	 * @return    string    The field input markup.
	 * @since    1.6
	 */
	protected function getOptions()
	{

		if(!JFile::exists(JPATH_ADMINISTRATOR . '/components/com_easysocial/includes/foundry.php'))
			return false;

		require_once(JPATH_ADMINISTRATOR . '/components/com_easysocial/includes/foundry.php');

		if(!class_exists('Foundry'))
		{
			return;
		}

		$model    = Foundry::model('Profiles');
		$profiles = $model->getProfiles();

		$options[] = JHtml::_('select.option', '', \Joomla\CMS\Language\Text::_('ES_SELECTPROFILE'));
		foreach($profiles AS $profile)
		{
			$options[] = JHtml::_('select.option', $profile->id, $profile->title);
		}

		return $options;
	}
}