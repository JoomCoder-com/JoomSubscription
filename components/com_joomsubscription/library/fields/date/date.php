<?php
/**
 * Created by PhpStorm.
 * User: Sergey
 * Date: 1/27/15
 * Time: 14:21
 */

defined('_JEXEC') or die('Restricted access');

class JoomsubscriptionFieldDate extends JoomsubscriptionField
{
	public function affectPrice($plan = NULL)
	{
		$app = JFactory::getApplication();
		if(empty($this->default['cal']))
		{
			$this->setError(JText::_('EMR_NODATE'));
			return;
		}

		if(strtotime($this->default['cal']) < strtotime(date('Y-m-d')))
		{
			$this->setError(JText::_('EMR_BEFORE'));
			return;
		}

		return ($this->default['day'] * $plan->price) - $plan->price;
	}

	public function affectDates($subscription)
	{
		$out = array();
		$db = JFactory::getDbo();

		$out['ctime'] = $this->default['cal'] . ' 00:00:00';

		$db->setQuery("SELECT FROM_UNIXTIME(".strtotime($this->default['cal']).") + INTERVAL {$this->default['day']} DAY");
		$out['extime'] = $db->loadResult();

		return $out;
	}
}