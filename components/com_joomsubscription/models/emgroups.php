<?php
/**
 * JoomSubscription by JoomCoder
 * a component for Joomla! 3.0 CMS (http://www.joomla.org)
 * Author Website: https://www.joomcoder.com/
 * @copyright Copyright (C) 2012 JoomCoder (https://www.joomcoder.com/). All rights reserved.
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die('Restricted access');

class JoomsubscriptionModelEMGroups extends MModelList
{

	public function __construct($config = array())
	{
		if(empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
			'g.id',
			'g.ordering',
			'g.published',
			'g.access',
			'g.name',
			'g.ctime',
			'g.language'
			);
		}

		parent::__construct($config);
	}

	protected function populateState($ordering = null, $direction = null)
	{
		$app = JFactory::getApplication ();

		$search = $app->getUserStateFromRequest ( $this->context . '.filter.search', 'filter_search' );
		$this->setState ( 'filter.search', $search );

		$published = $app->getUserStateFromRequest ( $this->context . '.filter.state', 'filter_published' );
		$this->setState ( 'filter.state', $published );

		$access = $app->getUserStateFromRequest ( $this->context . '.filter.access', 'filter_access' );
		$this->setState ( 'filter.access', $access );

		parent::populateState('g.ordering', 'asc');
	}

	protected function getStoreId($id = '') {
		$id .= ':' . $this->getState ( 'filter.search' );
		$id .= ':' . $this->getState ( 'filter.state' );
		$id .= ':' . $this->getState ( 'filter.access' );

		return parent::getStoreId ( $id );
	}

	protected function getListQuery()
	{
		$db = $this->getDbo();
		$query = $db->getQuery(true);

		$query->select('g.*');
		$query->from('#__joomsubscription_plans_groups AS g');
		$query->select('ag.title AS access_level');
		$query->join('LEFT', '#__viewlevels AS ag ON ag.id = g.access');

		$search = $this->getState ( 'filter.search' );
		if ($search) {
			$search = $db->Quote ( '%' . $db->escape ( $search, true ) . '%' );
			$query->where ( '(g.name LIKE ' . $search . ')' );
		}

		$published = $this->getState ( 'filter.state' );
		if (is_numeric ( $published )) {
			$query->where ( 'g.published = ' . ( int ) $published );
		} else if ($published === '') {
			$query->where ( '(g.published IN (0, 1))' );
		}

		$access = $this->getState ( 'filter.access' );
		if ( $access ) {
			$query->where ( 'g.access = ' . ( int ) $access );
		}

		$orderCol = $this->state->get('list.ordering');
		$orderDirn = $this->state->get('list.direction');
		$query->order($db->escape($orderCol . ' ' . $orderDirn));

		//echo nl2br(str_replace('#__','jos_',$query));
		return $query;
	}
}