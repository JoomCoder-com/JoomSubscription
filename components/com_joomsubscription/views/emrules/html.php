<?php
/**
 *
 * @license   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 *
 */
defined('_JEXEC') or die('Restricted access');

class JoomsubscriptionViewsEmRulesHtml extends Joomla\CMS\MVC\View\HtmlView
{
	function render()
	{
		$this->model = new JoomsubscriptionModelsEmRules();
		$this->rules = $this->model->getRules($this->plan->id);

		return parent::render();
	}
	function getName()
	{
		return 'emrules';
	}
}