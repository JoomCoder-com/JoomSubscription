<?php
/**
 * JoomSubscription by JoomCoder
 * a component for Joomla! 3.0 CMS (http://www.joomla.org)
 * Author Website: https://www.joomcoder.com/
 * @copyright Copyright (C) 2012 JoomCoder (https://www.joomcoder.com/). All rights reserved.
 * @license   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die('Restricted access');

class JoomsubscriptionModelsEmInvoiceTo extends Joomla\CMS\MVC\Model\BaseModel
{
	public function getList($user_id = null)
	{
		$user = JFactory::getUser($user_id);
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('fields, id as value');
		$query->from('#__joomsubscription_invoice_to');
		$query->where('user_id='.$user->get('id'));
		$db->setQuery($query);

		$result = $db->loadObjectList();

		foreach ($result as $r)
		{
			$line = array();
			$r->fields = json_decode($r->fields);

			if($r->fields->country)
			{
				$db->setQuery("SELECT name FROM #__joomsubscription_country WHERE id = '".$r->fields->country."'");
				$line[] = $db->loadResult();
			}

			if(!empty($r->fields->state))
			{
				$db->setQuery("SELECT label FROM #__joomsubscription_states WHERE id = '".$r->fields->state."'");
				$line[] = $db->loadResult();
			}

			if($r->fields->zip)
			{
				$line[] = $r->fields->zip;
			}

			if($r->fields->address)
			{
				$line[] = $r->fields->address;
			}

			if($r->fields->tax_id)
			{
				$line[] = JText::_('E_INVOICE_TAX_ID').': '. $r->fields->tax_id;
			}

			$r->text = $r->fields->billto.', '. implode(', ', $line);
		}

		array_unshift($result, JHtml::_('select.option', '', JText::_('E_SELECT_BILL_TO')));
		$result[] = JHtml::_('select.option', -1, JText::_('E_ADD_BILL_TO'));

		return $result;
	}

	public function getTable($type = 'EmInvoiceTo', $prefix = 'JoomsubscriptionTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	public function getText($id)
	{
		$db = JFactory::getDbo();
		$line = array();
		$db->setQuery("SELECT * FROM #__joomsubscription_invoice_to WHERE id = ".$id);

		$out = $db->loadObject();
		@$out->fields = new JRegistry(@$out->fields);

		if($out->fields->get('country'))
		{
			$out->fields->set('country_id', $out->fields->get('country'));
			$db->setQuery("SELECT name FROM #__joomsubscription_country WHERE id = '".$out->fields->get('country')."'");
			$out->fields->set('country', $db->loadResult());
			$line[] = $out->fields->get('country');
		}

		if($out->fields->get('state'))
		{
			$out->fields->set('state_id', $out->fields->get('state'));
			$db->setQuery("SELECT label FROM #__joomsubscription_states WHERE id = '".$out->fields->get('state')."'");
			$out->fields->set('state', $db->loadResult());
			$line[] = $out->fields->get('state');
		}

		if($out->fields->get('zip'))
		{
			$line[] = $out->fields->get('zip');
		}

		$out->fields->set('line1', implode(', ', $line));

		return $out;
	}
	public function getForm()
	{
		$form = new JForm('comjoomsubscription.invoiceto', array('control' => 'invoiceto'));
		$form->loadFile(JPATH_COMPONENT.'/models/forms/invoiceto.xml');

		$data = JFactory::getApplication()->getUserState('com_joomsubscription.invoiceto.data', array());

		if($data)
		{
			$form->bind($data);
		}

		return $form;
	}
}