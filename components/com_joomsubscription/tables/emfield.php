<?php
/**
 * JoomSubscription by JoomCoder
 * a component for Joomla (http://www.joomla.org)
 * Author Website: https://www.joomcoder.com/
 * @copyright Copyright (C) 2012 JoomCoder (https://www.joomcoder.com/). All rights reserved.
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/
defined( '_JEXEC' ) or die( 'Restricted access' );

class JoomsubscriptionTableEmField extends JTable
{
    function __construct( &$db ) {
        parent::__construct( '#__joomsubscription_fields', 'id', $db );
    }

	public function check()
	{
		if(empty($this->ordering))
		{
			$this->ordering = 0;
		}

		if(empty($this->params))
		{
			$this->params = [];
		}

		if(is_array($this->params))
		{
			$this->params = json_encode($this->params);
		}

		if(empty($this->ctime) || $this->ctime == '0000-00-00 00:00:00')
		{
			$this->ctime = JDate::getInstance()->toSql();
		}

		return true;
	}
}