<?php
/**
 * JoomSubscription by JoomCoder
 * a component for Joomla! 3.0 CMS (http://www.joomla.org)
 * Author Website: https://www.joomcoder.com/
 * @copyright Copyright (C) 2012 JoomCoder (https://www.joomcoder.com/). All rights reserved.
 * @license   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */
defined('_JEXEC') or die('dead');


class JoomsubscriptionModelEmCoupon extends MModelAdmin
{

	public function getTable($type = 'EmCoupon', $prefix = 'JoomsubscriptionTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	public function getForm($data = array(), $loadData = TRUE)
	{
		$app = JFactory::getApplication();

		$form = $this->loadForm('com_joomsubscription.coupon', 'coupon', array(
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
		$data = JFactory::getApplication()->getUserState('com_joomsubscription.edit.coupon.data', array());

		if(empty($data))
		{
			$data = $this->getItem();
			$data->discount_type = strtolower($data->discount_type);
		}

		return $data;
	}

	protected function prepareTable($table)
	{
		if($table->ctime == '' || $table->ctime == '0000-00-00 00:00:00')
		{
			$table->ctime = JDate::getInstance()->toSql();
		}
		if($table->user_ids == 0)
		{
			$table->user_ids = '';
		}
		if($table->plan_ids == 0)
		{
			$table->plan_ids = '';
		}
	}

	public function save($data)
	{
		if(isset($data['amount']) && $data['amount'] > 1)
		{
			$value = $data['value'];
			for($i = 1; $i <= $data['amount']; $i++)
			{
				$data['value'] = $value . '_' . strtoupper(substr(JApplication::getHash($data['value']), 0, 5));
				if(parent::save($data))
				{
					$this->setState($this->getName() . '.id', NULL);
				}
			}

			return TRUE;
		}

		return parent::save($data);
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

?>