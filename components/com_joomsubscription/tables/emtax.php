<?php
/**
 * JoomSubscription by JoomCoder
 * a component for Joomla (http://www.joomla.org)
 * Author Website: https://www.joomcoder.com/
 * @copyright Copyright (C) 2012 JoomCoder (https://www.joomcoder.com/). All rights reserved.
 * @license   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */
defined('_JEXEC') or die('Restricted access');

class JoomsubscriptionTableEmTax extends JTable
{
	function __construct(&$db)
	{
		parent::__construct('#__joomsubscription_taxes', 'id', $db);
	}

	public function check()
	{
		if(empty($this->id))
		{
			$query = 'SELECT id FROM #__joomsubscription_taxes WHERE country_id = "' . $this->country_id . '"';
			if($this->state_id)
			{
				$query .= ' AND state_id="' . $this->state_id . '"';
			}
			$this->_db->setQuery($query);
			$result = $this->_db->loadResult();
			if($result)
			{
				$this->setError(JText::_('E_TAX_EXISTS'));

				return FALSE;
			}
		}

		return parent::check();
	}
}