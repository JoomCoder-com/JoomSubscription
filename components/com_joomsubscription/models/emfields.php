<?php
/**
 * JoomSubscription by JoomCoder
 * a component for Joomla! 3.0 CMS (http://www.joomla.org)
 * Author Website: https://www.joomcoder.com/
 *
 * @copyright Copyright (C) 2012 JoomCoder (https://www.joomcoder.com/). All rights reserved.
 * @license   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die('Restricted access');

class JoomsubscriptionModelEMFields extends MModelList
{

	public function __construct($config = array())
	{
		if(empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'f.id',
				'f.ordering',
				'f.published',
				'f.access',
				'f.name',
				'f.ctime'
			);
		}

		parent::__construct($config);
	}

	protected function populateState($ordering = NULL, $direction = NULL)
	{
		$app = JFactory::getApplication();

		$search = $app->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$published = $app->getUserStateFromRequest($this->context . '.filter.state', 'filter_published');
		$this->setState('filter.state', $published);

		$access = $app->getUserStateFromRequest($this->context . '.filter.access', 'filter_access');
		$this->setState('filter.access', $access);

		parent::populateState('f.ordering', 'asc');
	}

	protected function getStoreId($id = '')
	{
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.state');
		$id .= ':' . $this->getState('filter.access');

		return parent::getStoreId($id);
	}

	protected function getListQuery()
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(TRUE);

		$query->select('f.*');
		$query->from('#__joomsubscription_fields AS f');
		$query->select('ag.title AS access_level');
		$query->join('LEFT', '#__viewlevels AS ag ON ag.id = f.access');

		$search = $this->getState('filter.search');
		if($search)
		{
			$search = $db->Quote('%' . $db->escape($search, TRUE) . '%');
			$query->where('(f.name LIKE ' . $search . ')');
		}

		$published = $this->getState('filter.state');
		if(is_numeric($published))
		{
			$query->where('f.published = ' . ( int )$published);
		}
		else if($published === '')
		{
			$query->where('(f.published IN (0, 1))');
		}

		$access = $this->getState('filter.access');
		if($access)
		{
			$query->where('f.access = ' . ( int )$access);
		}

		$orderCol  = $this->state->get('list.ordering');
		$orderDirn = $this->state->get('list.direction');
		$query->order($db->escape($orderCol . ' ' . $orderDirn));

		return $query;
	}
}