<?php
/**
 * JoomSubscription by JoomCoder
 * a component for Joomla! 3.0 CMS (http://www.joomla.org)
 * Author Website: https://www.joomcoder.com/
 * @copyright Copyright (C) 2012 JoomCoder (https://www.joomcoder.com/). All rights reserved.
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/
defined('_JEXEC') or die('Restricted access');

class JoomsubscriptionTableEmPlan extends JTable
{
	function __construct(& $db) {
		parent::__construct('#__joomsubscription_plans', 'id', $db);
	}

	function check()
	{
		$this->mtime = JDate::getInstance()->toSql();

		return TRUE;
	}

	// Все делается в prepareTable в модели plan
}