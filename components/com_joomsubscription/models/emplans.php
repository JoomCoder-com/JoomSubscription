<?php
/**
 * JoomSubscription by JoomCoder
 * a component for Joomla (http://www.joomla.org)
 * Author Website: https://www.joomcoder.com/
 * @copyright Copyright (C) 2012 JoomCoder (https://www.joomcoder.com/). All rights reserved.
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */
defined('_JEXEC') or die();

class JoomsubscriptionModelEmPlans extends MModelList
{
	public function __construct($config = array())
	{
		if(empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
			'p.id',
			'p.ordering',
			'p.published',
			'p.access',
			'p.name',
			'p.ctime',
			'group_name'
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

		$group = $app->getUserStateFromRequest ( $this->context . '.filter.group', 'filter_group' );
		$this->setState ( 'filter.group', $group );

		parent::populateState('p.ordering', 'asc');
	}

	protected function getStoreId($id = '') {

		$id .= ':emplans';
		$id .= ':' . $this->getState ( 'filter.search' );
		$id .= ':' . $this->getState ( 'filter.state' );
		$id .= ':' . $this->getState ( 'filter.access' );
		$id .= ':' . $this->getState ( 'filter.group' );

		return parent::getStoreId ( $id );
	}

	protected function getReorderConditions($table)
	{
		$condition = array();
		$condition[] = 'group_id = '.(int) $table->group_id;
		return $condition;
	}

	protected function getListQuery()
	{
		$db = $this->getDbo();
		$query = $db->getQuery(true);

		$query->select('p.*');
		$query->select('(SELECT COUNT(id) FROM #__joomsubscription_subscriptions WHERE plan_id = p.id) AS subscr');
		$query->from('#__joomsubscription_plans AS p');
		$query->select('g.name AS group_name');
		$query->join('LEFT', '#__joomsubscription_plans_groups AS g ON g.id = p.group_id');
		$query->select('ag.title AS access_level');
		$query->join('LEFT', '#__viewlevels AS ag ON ag.id = p.access');


		$search = $this->getState ( 'filter.search' );
		if ($search) {
			$search = $db->Quote ( '%' . $db->escape ( $search, true ) . '%' );
			$query->where ( '(p.name LIKE ' . $search . ')' );
		}

		$published = $this->getState ( 'filter.state' );
		if (is_numeric ( $published )) {
			$query->where ( 'p.published = ' . ( int ) $published );
		} else if ($published === '') {
			$query->where ( '(p.published IN (0, 1))' );
		}

		$access = $this->getState ( 'filter.access' );
		if ( $access ) {
			$query->where ( 'p.access = ' . ( int ) $access );
		}

		$group = $this->getState ( 'filter.group' );
		if ( $group ) {
			$query->where ( 'p.group_id = ' . ( int ) $group );
		}

		$orderCol = $this->state->get('list.ordering');
		$orderDirn = $this->state->get('list.direction');

		if ($orderCol == 'p.ordering' || $orderCol == 'group_name')
		{
			$orderCol = 'g.ordering '.$orderDirn.', p.ordering';
		}

		$query->order($db->escape($orderCol . ' ' . $orderDirn));

		//echo nl2br(str_replace('#__','jos_',$query));
		return $query;
	}
}
?>
