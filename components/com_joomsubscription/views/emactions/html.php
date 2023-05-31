<?php
/**
 *
 * @license   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 *
 */
defined('_JEXEC') or die('Restricted access');

class JoomsubscriptionViewsEmActionsHtml extends JViewHtml
{
	function render()
	{
		$this->model = new JoomsubscriptionModelsEmActions();
		$this->actions = $this->model->getActions($this->plan->id);

		return parent::render();
	}
	function getName()
	{
		return 'emactions';
	}
}