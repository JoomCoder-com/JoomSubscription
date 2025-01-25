<?php
/**
 * Cobalt by MintJoomla
 * a component for Joomla! 1.7 - 2.5 CMS (http://www.joomla.org)
 * Author Website: http://www.mintjoomla.com/
 * @copyright Copyright (C) 2012 MintJoomla (http://www.mintjoomla.com). All rights reserved.
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

defined('JPATH_PLATFORM') or die;

jimport('joomla.html.html');
jimport('joomla.form.formfield');

class JFormFieldMetags extends JFormField
{
	public $type = 'Metags';

	public function getInput()
	{
		$app = JFactory::getApplication();
		$model = JModelLegacy::getInstance('Form', 'CobaltModel');
		$type = $model->getRecordType($app->input->getInt('type_id'));

		if(!$this->value)
		{
			$value = array();
		}
		else
		{
			$value = json_decode($this->value, 1);
			if(empty($value))
			{
				$value = $this->value;
			}
		}

		if (! is_array($value) && ! empty($value))
		{
			$value = explode(',', $this->value);
		}
		ArrayHelper::clean_r($value);
		$default = array();
		foreach ($value AS $tag)
		{
			$default[$tag] = $tag;
		}

		/*$query = $db->getQuery(true);
		$query->select('tag');
		$query->from('#__js_res_tags');
		$query->order('tag ASC');
		$db->setQuery($query);
		$list = $db->loadColumn();
		*/

		$this->params = new JRegistry();

		$options['coma_separate'] = 0;
		$options['only_values'] = 0;
		$options['min_length'] = 1;
		$options['max_result'] = 10;
		$options['case_sensitive'] = 0;
		$options['unique'] = 1;
		$options['highlight'] = 1;

		$options['max_width'] = $this->params->get('params.max_width', 500);
		$options['min_width'] = $this->params->get('params.min_width', 400);
		$options['max_items'] = $type->params->get('general.item_tags_max', 25);

		$options['ajax_url'] = 'index.php?option=com_cobalt&task=ajax.tags_list&tmpl=component';
		$options['ajax_data'] = '';

		return JHtml::_('mrelements.listautocomplete', "jform[tags]", "tags", $default, NULL, $options);

	}
}
