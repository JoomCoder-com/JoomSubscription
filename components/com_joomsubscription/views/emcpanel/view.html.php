<?php
/**
 * Cobalt by JoomCoder
 * a component for Joomla! 1.7 - 2.5 CMS (http://www.joomla.org)
 * Author Website: https://www.joomcoder.com/
 * @copyright Copyright (C) 2012 JoomCoder (https://www.joomcoder.com/). All rights reserved.
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */
defined('_JEXEC') or die();

jimport('mint.mvc.view.base');
class JoomsubscriptionViewEmCpanel extends MViewBase
{
	function display($tpl = null)
	{
		JFactory::getDocument()->addScript(JUri::root(TRUE).'/components/com_joomsubscription/library/js/hightcharts.js');

		$this->latest = $this->get('Latest');
		$this->activate = $this->get('Active');
		$this->data = $this->get('Analytics');

		parent::display($tpl);
	}

}