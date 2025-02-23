<?php
/**
 *
 * @license   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 *
 */
defined('_JEXEC') or die('Restricted access');

class JoomsubscriptionViewsEmRulesHtml extends Joomla\CMS\MVC\View\HtmlView
{
	function display($tpl = null)
	{
		$this->model = new JoomsubscriptionModelsEmRules();
		$this->rules = $this->model->getRules($this->plan->id);

		parent::display($tpl);

	}
	function getName()
	{
		return 'emrules';
	}
}