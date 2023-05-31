<?php
/**
 * JoomSubscription by JoomCoder
 * a component for Joomla (http://www.joomla.org)
 * Author Website: https://www.joomcoder.com/
 * @copyright Copyright (C) 2012 JoomCoder (https://www.joomcoder.com/). All rights reserved.
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/
defined( '_JEXEC' ) or die( 'Restricted access' );

class JoomsubscriptionTableEmGroup extends JTable
{
    function __construct( &$db ) {
        parent::__construct( '#__joomsubscription_plans_groups', 'id', $db );
    }

	public function check()
	{
		if(empty($this->ordering))
		{
			$this->ordering = 0;
		}
		return true;
	}
}