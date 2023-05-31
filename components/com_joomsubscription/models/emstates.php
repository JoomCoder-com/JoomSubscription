<?php
/**
 * JoomSubscription by JoomCoder
 * a component for Joomla! 3.0 CMS (http://www.joomla.org)
 * Author Website: https://www.joomcoder.com/
 * @copyright Copyright (C) 2012 JoomCoder (https://www.joomcoder.com/). All rights reserved.
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die('Restricted access');

class JoomsubscriptionModelEmStates extends MModelList
{

	public function __construct($config = array())
	{
		if(empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
			'st.label',
			'st.state',
			'st.id'
			);
		}

		parent::__construct($config);
	}

	protected function populateState($ordering = 'st.label', $direction = 'asc')
	{
		$app = JFactory::getApplication ();

		$country = $app->getUserStateFromRequest ( $this->context . '.filter.country', 'filter_country' );
		$this->setState ( 'filter.country', $country );

		parent::populateState($ordering, $direction);
	}

	protected function getStoreId($id = '') {
		$id .= ':' . $this->getState ( 'filter.country' );

		return parent::getStoreId ( $id );
	}

	public function getCountries()
	{
		$db = $this->getDbo();
		$query = $db->getQuery(true);

		$query->select('*');
		$query->from('#__joomsubscription_country AS c');
		$query->where('id IN (SELECT DISTINCT(country) FROM #__joomsubscription_states)');
		$db->setQuery($query);
		$result = $db->loadAssocList('id');
		array_unshift($result, array('id'=>'', 'name' => JText::_('E_SELECT_COUNTRY')));
		return $result;
	}

	protected function getListQuery()
	{
		$db = $this->getDbo();
		$query = $db->getQuery(true);

		$query->select('st.*');
		$query->from('#__joomsubscription_states AS st');

		$country = $this->getState ( 'filter.country' );
		if ($country) {
			$query->where ( 'st.country = "' . $country . '"' );
		}

		$orderCol = $this->state->get('list.ordering');
		$orderDirn = $this->state->get('list.direction');
		$query->order('st.country, '.$db->escape($orderCol . ' ' . $orderDirn));

		//echo nl2br(str_replace('#__','jos_',$query));
		return $query;
	}
}