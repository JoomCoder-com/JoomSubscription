<?php
/**
 * JoomSubscription by JoomCoder
 * a component for Joomla! 3.0 CMS (http://www.joomla.org)
 * Author Website: https://www.joomcoder.com/
 * @copyright Copyright (C) 2012 JoomCoder (https://www.joomcoder.com/). All rights reserved.
 * @license   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die();

class JoomsubscriptionModelEmSale extends MModelAdmin
{

	public function getTable($type = 'EmSubscription', $prefix = 'JoomsubscriptionTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	public function getForm($data = array(), $loadData = TRUE)
	{
		$app = JFactory::getApplication();

		$form = $this->loadForm('com_joomsubscription.subscription', 'sale', array(
																		 'control'   => 'jform',
																		 'load_data' => $loadData
																	));
		if(empty($form))
		{
			return FALSE;
		}

		return $form;
	}

	protected function loadFormData()
	{
		$data = JFactory::getApplication()->getUserState('com_joomsubscription.edit.emsale.data', array());

		if(empty($data))
		{
			$data = $this->getItem();
		}

		return $data;
	}

	public function publish(&$pks, $value = 1)
	{
		$app   = JFactory::getApplication();
		$table = $this->getTable();
		$pks   = (array)$pks;

		foreach($pks as $i => $pk)
		{
			$table->reset();

			if(!$table->load($pk))
			{
				continue;
			}

			if(!$this->canEditState($table))
			{
				continue;
			}

			if($value == 1 && $table->parent > 0)
			{
				$parent = $this->getTable();
				$parent->load($table->parent);

				if($parent->published = 0)
				{
					$app->enqueueMessage(JText::sprintf('EM_CANNOT_PUBLISH_MUA', $table->id, $parent->id), 'warning');
					continue;
				}

			}

			if(empty($table->gateway_id))
			{
				$table->gateway_id = substr(strtoupper(md5(time() . '-' . $pk)), 0, 8);
			}

			$table->published = $value;

			if($table->published)
			{
				JoomsubscriptionHelper::activateSubscription($table);
			}
			elseif($table->published == 0 && JoomsubscriptionHelper::isActiveSubscription($pk))
			{
				JoomsubscriptionHelper::sendAlert('cancel', $pk);
			}

			$table->store();

		}
		$this->_db->setQuery("UPDATE #__joomsubscription_subscriptions SET published = {$value} WHERE parent IN (" . implode(',', $pks) . ")");
		$this->_db->execute();

		$this->cleanCache();

		if($app->input->get('return') == 'cpanel')
		{
			$app->redirect(JRoute::_('index.php?option=com_joomsubscription&view=cpanel'));
		}

		return TRUE;
	}

	protected function canDelete($record)
	{
		return TRUE;
	}

	protected function canEditState($record)
	{
		return TRUE;
	}
}