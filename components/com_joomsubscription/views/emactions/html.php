<?php
/**
 *
 * @license   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 *
 */
defined('_JEXEC') or die('Restricted access');

class JoomsubscriptionViewsEmActionsHtml extends Joomla\CMS\MVC\View\HtmlView
{
	function display($tpl = null)
	{
		$this->model = new JoomsubscriptionModelsEmActions();
		$this->actions = $this->model->getActions($this->plan->id);

		parent::display();
	}
	function getName()
	{
		return 'emactions';
	}
}