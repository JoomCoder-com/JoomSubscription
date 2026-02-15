<?php
/**
 *
 * @license   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 *
 */
defined('_JEXEC') or die('Restricted access');

class JoomsubscriptionViewEmAnalytics extends MViewBase
{
	function  display($tpl = null)
	{
		\Joomla\CMS\Factory::getDocument()->addScript(\Joomla\CMS\Uri\Uri::root(TRUE).'/components/com_joomsubscription/library/js/hightcharts.js');
		\Joomla\CMS\Factory::getDocument()->addScript(\Joomla\CMS\Uri\Uri::root(TRUE).'/components/com_joomsubscription/library/js/options.js');

		$this->menu = Mint::loadLayout('links', JPATH_COMPONENT .'/layouts');

		return parent::display($tpl);
	}
}