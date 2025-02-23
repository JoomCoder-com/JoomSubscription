<?php
/**
 * Cobalt by JoomCoder
 * a component for Joomla! 1.7 - 2.5 CMS (http://www.joomla.org)
 * Author Website: https://www.joomcoder.com/
 * @copyright Copyright (C) 2012 JoomCoder (https://www.joomcoder.com/). All rights reserved.
 * @license   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;

extract($displayData);

?>

<div class="input-group">

	<input
		type="text"
		placeholder="<?php echo JText::_('CFILTER_SEARCH_PLANSDESC'); ?>"
		class="form-control"
		name="filter_<?php echo $filterName ?>"
		id="filter_<?php echo $filterName ?>"
		value="<?php echo $current->state->get('filter.'.$filterName); ?>"
	>

	<button class="btn btn-outline-success" type="submit" data-bs-original-title="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>">
		<i class="fas fa-search"></i>
	</button>
	<button class="btn btn-outline-danger" type="button" onclick="document.getElementById('filter_search').value='';this.form.submit();">
		<i class="fas fa-times"></i>
	</button>
</div>