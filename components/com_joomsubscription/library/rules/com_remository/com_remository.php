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

class JoomsubscriptionRuleCom_remository extends JoomsubscriptionRule
{
	public function isProtected()
	{
		$db = JFactory::getDbo();
		$id = $this->input->getInt('id');

		if(in_array($this->input->getCmd('func'), array('startdown', 'download')) && $id)
		{
			if($this->params->get('files') && in_array($id, explode(',', str_replace(array("\r", "\n", " "), '', $this->params->get('files')))))
			{
				return TRUE;
			}

			$db->setQuery("SELECT containerid FROM #__downloads_files WHERE id = " . $id);
			$catid = $db->loadResult();
			$cats  = $this->params->get('cats');
			if(is_array($cats) && in_array($catid, $cats))
			{
				if($this->params->get('files_exc') && in_array($id, explode(',', str_replace(array("\r", "\n", " "), '', $this->params->get('files_exc')))))
				{
					return FALSE;
				}

				return TRUE;
			}
		}

		return FALSE;
	}

	public function getDescription()
	{
		if($this->params->get('cats'))
		{
			return JText::_('REM_READ_RESTRICT') . ': ' . implode(', ', $this->_getContainerNames($this->params->get('cats')));
		}
	}

	private function _getContainerNames($ids)
	{
		$db = JFactory::getDbo();
		$db->setQuery("SELECT name FROM #__downloads_containers WHERE id IN(" . implode(',', $ids) . ")");
		$names = $db->loadColumn();

		return $names;
	}
}
