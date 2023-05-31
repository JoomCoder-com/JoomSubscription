<?php
/**
 * JoomSubscription by JoomCoder
 * a component for Joomla! 3.0 CMS (http://www.joomla.org)
 * Author Website: https://www.joomcoder.com/
 * @copyright Copyright (C) 2012 JoomCoder (https://www.joomcoder.com/). All rights reserved.
 * @license   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die();

include_once JPATH_ROOT.'/components/com_joomsubscription/views/eminvoiceto/html.php';
include_once JPATH_ROOT.'/components/com_joomsubscription/models/eminvoiceto.php';

class JoomsubscriptionInvoiceHelper
{
	public static function form()
	{
		$paths = new SplPriorityQueue;
		$paths->insert(JPATH_ROOT . '/components/com_joomsubscription/views/eminvoiceto/tmpl', 1);

		$template = JFactory::getApplication()->getTemplate();
		$paths->insert(JPATH_ROOT . '/templates/'.$template.'/html/com_joomsubscription/eminvoiceto', 2);

		$view = new JoomsubscriptionViewsEMInvoiceToHtml(new JoomsubscriptionModelsEmInvoiceTo(), $paths);
		$view->setLayout('form');

		return $view->render();
	}

	public static function text()
	{
		$paths = new SplPriorityQueue;
		$paths->insert(JPATH_ROOT . '/components/com_joomsubscription/views/eminvoiceto/tmpl', 1);

		$template = JFactory::getApplication()->getTemplate();
		$paths->insert(JPATH_ROOT . '/templates/'.$template.'/html/com_joomsubscription/eminvoiceto', 2);

		$view = new JoomsubscriptionViewsEMInvoiceToHtml(new JoomsubscriptionModelsEmInvoiceTo(), $paths);
		$view->setLayout('text');

		return $view->render();

	}

	public static function _isEU($country)
	{
		//$eu = array('AT', 'BE', 'BG', 'HR', 'CY', 'CZ', 'DK', 'FR', 'EE', 'FI', 'DE', 'GB', 'UK',
		//	'EL', 'HU', 'IE', 'IT', 'LV', 'LT', 'LU', 'MT', 'NL', 'PL', 'PT', 'SK', 'RO', 'SI', 'ES', 'SE');

		return in_array(strtoupper($country), self::getVIESCountries());
	}

	public  static function getVIESCountries()
	{
		return array('AT', 'BE', 'BG', 'CY', 'CZ', 'DE', 'DK', 'EE', 'EL', 'ES', 'FI', 'FR', 'GB',
			'UK', 'HR', 'HU', 'IE', 'IT', 'LT', 'LU', 'LV', 'MT', 'NL', 'PL', 'PT', 'RO', 'SE', 'SI', 'SK');
	}
}