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

<div class="border rounded p-3">
	<div class="row">
		<div class="col-md-8">
			<?php echo \Joomla\CMS\Layout\LayoutHelper::render('core.list.actionButtons',[]); ?>
		</div>
		<div class="col-md-4">
			<?php echo \Joomla\CMS\Layout\LayoutHelper::render('core.list.search',['filterName' => 'search','current' => $current]); ?>
		</div>

	</div>
</div>