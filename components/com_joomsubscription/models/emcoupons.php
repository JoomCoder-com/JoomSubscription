<?php
/**
 * JoomSubscription by JoomCoder
 * a component for Joomla! 3.0 CMS (http://www.joomla.org)
 * Author Website: https://www.joomcoder.com/
 * @copyright Copyright (C) 2012 JoomCoder (https://www.joomcoder.com/). All rights reserved.
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die('Restricted access');

class JoomsubscriptionModelEmCoupons extends MModelList
{

	public function __construct($config = array())
	{
		if(empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
			'c.id',
			'c.published',
			'c.value',
			'c.ctime',
			'c.extime',
			'c.use_num',
			'c.used_num',
			'c.discount'
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

		parent::populateState('c.value', 'asc');
	}

	protected function getStoreId($id = '') {
		$id .= ':' . $this->getState ( 'filter.search' );
		$id .= ':' . $this->getState ( 'filter.state' );

		return parent::getStoreId ( $id );
	}

	protected function getListQuery()
	{
		$db = $this->getDbo();
		$query = $db->getQuery(true);

		$query->select('c.*, IF(c.extime < NOW(),1,0) AS expire');
		$query->from('#__joomsubscription_coupons AS c');

		$search = $this->getState ( 'filter.search' );
		if ($search) {
			$search = $db->Quote ( '%' . $db->escape ( $search, true ) . '%' );
			$query->where ( '(c.value LIKE ' . $search . ')' );
		}

		$published = $this->getState ( 'filter.state' );
		if (is_numeric ( $published )) {
			$query->where ( 'c.published = ' . ( int ) $published );
		} else if ($published === '') {
			$query->where ( '(c.published IN (0, 1))' );
		}

		$orderCol = $this->state->get('list.ordering');
		$orderDirn = $this->state->get('list.direction');
		$query->order($db->escape($orderCol . ' ' . $orderDirn));

		//echo nl2br(str_replace('#__','jos_',$query));
		return $query;
	}
}