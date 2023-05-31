<?php
/**
 * JoomSubscription by JoomCoder
 * a component for Joomla! 3.0 CMS (http://www.joomla.org)
 * Author Website: https://www.joomcoder.com/
 * @copyright Copyright (C) 2012 JoomCoder (https://www.joomcoder.com/). All rights reserved.
 * @license   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die();

class JoomsubscriptionControllerEmField extends MControllerForm
{
	public function allowEdit($data = array(), $key = 'id')
	{
		return TRUE;
	}

	public function allowAdd($data = array())
	{
		return TRUE;
	}
}