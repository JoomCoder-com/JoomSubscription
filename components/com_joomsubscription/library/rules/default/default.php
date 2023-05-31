<?php
/**
 * JoomSubscription by JoomCoder
 * a component for Joomla! 3.0 CMS (http://www.joomla.org)
 * Author Website: https://www.joomcoder.com/
 *
 * @copyright Copyright (C) 2012 JoomCoder (https://www.joomcoder.com/). All rights reserved.
 * @license   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */
defined('_JEXEC') or die();

class JoomsubscriptionRuleDefault extends JoomsubscriptionRule
{

	public function getDescription()
	{
		$out = JText::_('EMR_UNIVERSAL') . ' ';

		if($this->params->get('var1'))
		{
			$out .= JText::sprintf('EMR_PARAMS', $this->params->get('var1'), $this->params->get('cond1'), $this->params->get('val1', JText::_('ENR_ANYTHING')));
		}

		if($this->params->get('var2'))
		{
			$out .= ' ' . JText::_('AND') . ' ' . JText::sprintf('EMR_PARAMS', $this->params->get('var2'), $this->params->get('cond2'), $this->params->get('val2', JText::_('ENR_ANYTHING')));
		}

		return $out;

	}

	public function isProtected()
	{
		$input = JFactory::getApplication()->input;

		if($this->params->get('var1'))
		{
			if($this->params->get('val1'))
			{
				if($this->_compare($this->params->get('val1'), $input->getString($this->params->get('var1')), $this->params->get('cond1')))
				{
					if($this->params->get('var2'))
					{
						if($this->params->get('val2'))
						{
							if($this->_compare($this->params->get('val2'), $input->getString($this->params->get('var2')), $this->params->get('cond2')))
							{
								return TRUE;
							}
						}
						else
						{
							return TRUE;
						}
					}
					else
					{
						return TRUE;
					}
				}
			}
			else
			{
				return TRUE;
			}
		}
		else
		{
			return TRUE;
		}

		return FALSE;
	}

	private function _compare($val1, $val2, $cond)
	{
		if(empty($val2))
		{
			return FALSE;
		}

		$vals = JoomsubscriptionHelper::getValues($val1);
		foreach($vals AS $val)
		{
			$eq = FALSE;

			$to = preg_match('/^[0-9]*$/', trim($val)) ? (int)$val2 : $val2;

			switch(str_replace(array('&lt;', '&gt;'), array('<', '>'), $cond))
			{
				case '=':
					$eq = (boolean)($val == $to);
					break;
				case '!=':
					$eq = (boolean)($val != $to);
					break;
				case '>':
					$eq = (boolean)((int)$to > (int)$val);
					break;
				case '<':
					$eq = (boolean)((int)$to < (int)$val);
					break;
				case '>=':
					$eq = (boolean)((int)$to >= (int)$val);
					break;
				case '<=':
					$eq = (boolean)((int)$to <= (int)$val);
					break;
				case "like '(str)%'":
					$eq = preg_match("/^" . preg_quote($val) . "/", $to);
					break;
				case "like '%(str)'":
					$eq = preg_match("/" . preg_quote($val) . "$/", $to);
					break;
				case "like '%(str)%'":
					$eq = preg_match("/" . preg_quote($val) . "/", $to);
					break;
				case "not like '(str)%'":
					$eq = !preg_match("/^" . preg_quote($val) . "/", $to);
					break;
				case "not like '%(str)'":
					$eq = !preg_match("/" . preg_quote($val) . "$/", $to);
					break;
				case "not like '%(str)%'":
					$eq = !preg_match("/" . preg_quote($val) . "/", $to);
					break;
			}

			if($eq)
			{
				return TRUE;
			}
		}

		return FALSE;
	}
}
