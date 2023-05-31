<?php
/**
 * JoomSubscription by JoomCoder
* a component for Joomla! 3.0 CMS (http://www.joomla.org)
* Author Website: https://www.joomcoder.com/
* @copyright Copyright (C) 2012 JoomCoder (https://www.joomcoder.com/). All rights reserved.
* @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die();

class JoomsubscriptionModelEmState extends MModelAdmin
{

	public function getTable($type = 'EmState', $prefix = 'JoomsubscriptionTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	public function getForm($data = array(), $loadData = true)
	{
		$app = JFactory::getApplication();

		$form = $this->loadForm('com_joomsubscription.state', 'state', array(
			'control' => 'jform',
			'load_data' => $loadData
		));
		if(empty($form))
		{
			return false;
		}
		return $form;
	}

	protected function loadFormData()
	{
		$data = JFactory::getApplication()->getUserState('com_joomsubscription.edit.emstate.data', array());

		if(empty($data))
		{
			$data = $this->getItem();
		}

		return $data;
	}

	protected function canDelete($record)
	{
		return true;
	}

	protected function canEditState($record)
	{
		return true;
	}
}