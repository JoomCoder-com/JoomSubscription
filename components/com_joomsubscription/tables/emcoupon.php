<?php
/**
 * JoomSubscription by JoomCoder
 * a component for Joomla (http://www.joomla.org)
 * Author Website: https://www.joomcoder.com/
 * @copyright Copyright (C) 2012 JoomCoder (https://www.joomcoder.com/). All rights reserved.
 * @license   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */
defined('_JEXEC') or die('Restricted access');

class JoomsubscriptionTableEmCoupon extends JTable
{

	function __construct(&$db)
	{
		parent::__construct('#__joomsubscription_coupons', 'id', $db);
	}

	function check()
	{
		if(!$this->id)
		{
			$db  = JFactory::getDBO();
			$sql = "SELECT id FROM #__joomsubscription_coupons WHERE `value` = '{$this->value}'";
			$db->setQuery($sql);
			if($db->loadResult())
			{
				$this->setError(JText::_('E_COUPON_EXIST'));

				return FALSE;
			}
		}

		if(!empty($this->plan_ids))
		{
			$this->plan_ids = json_encode($this->plan_ids);
		}

		return parent::check();
	}

}