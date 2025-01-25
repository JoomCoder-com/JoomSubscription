<?php
/**
 * Cobalt by MintJoomla
 * a component for Joomla! 1.7 - 2.5 CMS (http://www.joomla.org)
 * Author Website: http://www.mintjoomla.com/
 * @copyright Copyright (C) 2012 MintJoomla (http://www.mintjoomla.com). All rights reserved.
 * @license   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */
defined('JPATH_PLATFORM') or die();

jimport('joomla.form.formfield');

class JFormFieldMEMultiRatings extends JFormField
{
	protected $type = 'MultiRatings';

	protected function getInput()
	{
		// Initialize some field attributes.
		$class    = $this->element['class'] ? ' class="' . (string)$this->element['class'] . '"' : '';
		$disabled = ((string)$this->element['disabled'] == 'true') ? ' disabled="disabled"' : '';
		$columns  = $this->element['cols'] ? ' cols="' . (int)$this->element['cols'] . '"' : '';
		$rows     = $this->element['rows'] ? ' rows="' . (int)$this->element['rows'] . '"' : '';

		// Initialize JavaScript field attributes.
		$onchange = $this->element['onchange'] ? ' onchange="' . (string)$this->element['onchange'] . '"' : '';

		$v = htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8');

		$templates = $this->getTmplObjectList('rating');

		$text = "Available templates: " . implode(', ', $templates) . '.<br/>Input options devide by new line. Pattern "option_name::rating_tamplate"';

		return '<textarea name="' . $this->name . '" id="' . $this->id . '"' .
		$columns . $rows . $class . $disabled . $onchange . '>' .
		str_replace('\\n', "\n", $v) .
		'</textarea><br/>' . $text;
	}

	function getTmplObjectList($type)
	{
		$result = array();

		$layouts_path = CobaltTmplHelper::getTmplPath($type);
		$tmpl_mask    = CobaltTmplHelper::getTmplMask($type);

		$files = JFolder::files($layouts_path, $tmpl_mask['index_file']);

		foreach($files as $key => $file)
		{
			$tmplnames[] = '<b>' . preg_replace($tmpl_mask['ident'], '', $file) . '</b>';
		}

		return $tmplnames;
	}
}
