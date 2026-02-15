<?php
/**
 * JoomSubscription by JoomCoder
 * a component for Joomla (http://www.joomla.org)
 * Author Website: https://www.joomcoder.com/
 * @copyright Copyright (C) 2012 JoomCoder (https://www.joomcoder.com/). All rights reserved.
 * @license   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */
defined('_JEXEC') or die();

jimport('mint.mvc.controller.form');
include_once JPATH_ROOT.'/components/com_joomsubscription/helpers/imports.php';

class JoomsubscriptionControllerEmImport extends MControllerForm
{
	public function run()
	{
		$params = $this->input->get('params', array(), 'array');
		$params = new \Joomla\Registry\Registry($params);

		$type = $this->input->get('type');

		$obj = JoomsubscriptionImportsHelper::createImportObject($type);

		$result = $obj->run($params);

		JError::raiseNotice(200, \Joomla\CMS\Language\Text::sprintf('IMPORT_SUCCESS', $result['plans'], $result['subscriptions']));

		$this->setRedirect(\Joomla\CMS\Router\Route::_('index.php?option=com_joomsubscription&view=imports'), \Joomla\CMS\Language\Text::_('IMPORT_COMPLETE'));
	}
}



