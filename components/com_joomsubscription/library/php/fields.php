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

class JoomsubscriptionField extends JObject
{
	/**
	 * @var JRegistry
	 */
	public $params;
	public $_errors = array();

	public function __construct($data)
	{
		$this->data   = $data;
		$this->params = new JRegistry($data->params);
	}

	public function onActive($subscription)
	{

	}

	public function onHistory($subscription)
	{

	}

	public function onDisactive($subscription)
	{

	}


	public function onSuccess($subscription)
	{

	}

	public function onCreate($subscription)
	{

	}

	public function affectPrice($plan = NULL)
	{
		return 0;
	}
	public function affectDates($subscription)
	{
		return NULL;
	}

	/**
	 * @return bool Check if current page is allowed and return true.
	 */
	public function hasAccess()
	{
		return FALSE;
	}

	public function getDescription()
	{
		return Mint::markdown(Mint::_($this->data->description));
	}

	public function getLabel()
	{
		return Mint::_($this->data->name);
	}

	public function getPaymentLabel()
	{
		return $this->getLabel();
	}


	public function getField()
	{
		return $this->_load_template('input');
	}

	public function getValue()
	{
		return $this->_load_template('output');
	}

	public function isReady()
	{
		return TRUE;
	}

	public function _load_template($what)
	{
		$template = JFactory::getApplication()->getTemplate();
		$override = JPATH_ROOT . '/templates/' . $template . '/html/com_joomsubscription/fields/' . $this->type . '/';

		ob_start();

		if(JFile::exists($override . $this->id . '-' . $what . '.php'))
		{
			include $override . $this->id . '-' . $what . '.php';

			return;
		}

		if(JFile::exists($override . $what . '.php'))
		{
			include $override . $what . '.php';

			return;
		}

		if(JFile::exists($this->root . '/tmpl/' . $this->id . '-' . $what . '.php'))
		{
			include $this->root . '/tmpl/' . $this->id . '-' . $what . '.php';

			return;
		}

		include $this->root . '/tmpl/' . $what . '.php';

		$out = ob_get_contents();
		ob_end_clean();

		return $out;

	}

	public function __get($name)
	{
		if(property_exists($this->data, $name))
		{
			return $this->data->$name;
		}

		return NULL;
	}
	public function setError($error)
	{
		$this->_errors[] = $error;
	}
	public function getError($i = null, $toString = true)
	{
		// Find the error
		if ($i === null)
		{
			// Default, return the last message
			$error = end($this->_errors);
		}
		elseif (!array_key_exists($i, $this->_errors))
		{
			// If $i has been specified but does not exist, return false
			return false;
		}
		else
		{
			$error = $this->_errors[$i];
		}

		// Check if only the string is requested
		if ($error instanceof \Exception && $toString)
		{
			return $error->getMessage();
		}

		return $error;
	}
}