<?php
/**
 * Cobalt by JoomCoder
 * a component for Joomla! 1.7 - 2.5 CMS (http://www.joomla.org)
 * Author Website: https://www.joomcoder.com/
 * @copyright Copyright (C) 2012 JoomCoder (https://www.joomcoder.com/). All rights reserved.
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die();

if(!JoomsubscriptionHelper::isModer()) return;
$view = JFactory::getApplication()->input->getCmd('view');
$img_url =  JUri::root(TRUE).'/components/com_joomsubscription/images/cpanel/';
?>
<style>
.joomsubscription a.brand,
.joomsubscription li {line-height: 27px;}
.joomsubscription a.brand img{width: 30px;}
</style>
<div class="navbar joomsubscription">
	<div class="navbar-inner">
		<a class="brand" href="<?php echo JRoute::_('index.php?option=com_joomsubscription&view=emcpanel')?>"><img src="<?php echo $img_url; ?>dashboard-small.png" align="absmiddle"/> <strong>Joomsubscription <sup><small>10</small></sup></strong></a>
		<ul class="nav">
		<li class="<?php echo($view == 'emsales' ? 'active' : NULL); ?>">
			<a href="<?php echo JRoute::_('index.php?option=com_joomsubscription&view=emsales') ?>">
				<img src="<?php echo $img_url; ?>sales-small.png" align="absmiddle"/> <?php echo JText::_('ESUBSCRIPTIONS') ?></a></li>
		<li class="<?php echo($view == 'emplans' ? 'active' : NULL); ?>">
			<a href="<?php echo JRoute::_('index.php?option=com_joomsubscription&view=emplans') ?>">
				<img src="<?php echo $img_url; ?>plans-small.png" align="absmiddle"/> <?php echo JText::_('EPLANS') ?></a></li>
		<li class="<?php echo($view == 'emgroups' ? 'active' : NULL); ?>">
			<a href="<?php echo JRoute::_('index.php?option=com_joomsubscription&view=emgroups') ?>">
				<img src="<?php echo $img_url; ?>groups-small.png" align="absmiddle"/> <?php echo JText::_('EGROUPS') ?></a></li>
		<li class="<?php echo($view == 'emfields' ? 'active' : NULL); ?>">
			<a href="<?php echo JRoute::_('index.php?option=com_joomsubscription&view=emfields') ?>">
				<img src="<?php echo $img_url; ?>fields-small.png" align="absmiddle"/> <?php echo JText::_('EFIELDS') ?></a></li>

			<li class="dropdown <?php echo(in_array($view, array('emcoupons', 'emanalytics', 'emstates', 'emtaxes', 'emimports', 'emlist', 'emhistory')) ? 'active' : NULL); ?>">
				<a href="#" class="dropdown-toggle" data-toggle="dropdown">
                    <img src="<?php echo $img_url; ?>more-small.png" align="absmiddle"/> <?php echo JText::_('EOTHER')?> <i class="caret"></i>
				</a>
				<ul class="dropdown-menu">
					<li class="<?php echo($view == 'emcoupons' ? 'uk-active' : NULL); ?>">
						<a href="<?php echo JRoute::_('index.php?option=com_joomsubscription&view=emcoupons') ?>">
							<img src="<?php echo $img_url; ?>coupons-small.png" align="absmiddle"/> <?php echo JText::_('ECOUPONS') ?></a></li>
					<li class="<?php echo($view == 'emanalytics' ? 'uk-active' : NULL); ?>">
						<a href="<?php echo JRoute::_('index.php?option=com_joomsubscription&view=emanalytics') ?>">
							<img src="<?php echo $img_url; ?>analytics-small.png" align="absmiddle"/> <?php echo JText::_('EANALYTICS') ?></a></li>
					<li class="<?php echo($view == 'emstates' ? 'uk-active' : NULL); ?>">
						<a href="<?php echo JRoute::_('index.php?option=com_joomsubscription&view=emstates') ?>">
							<img src="<?php echo $img_url; ?>states-small.png" align="absmiddle"/> <?php echo JText::_('ESTATES') ?></a></li>
					<li class="<?php echo($view == 'emtaxes' ? 'uk-active' : NULL); ?>">
						<a href="<?php echo JRoute::_('index.php?option=com_joomsubscription&view=emtaxes') ?>">
							<img src="<?php echo $img_url; ?>taxes-small.png" align="absmiddle"/> <?php echo JText::_('ETAXES') ?></a></li>
					<li class="<?php echo($view == 'emimports' ? 'uk-active' : NULL); ?>">
						<a href="<?php echo JRoute::_('index.php?option=com_joomsubscription&view=emimports') ?>">
							<img src="<?php echo $img_url; ?>import-small.png" align="absmiddle"/> <?php echo JText::_('EIMPORT') ?></a></li>
					<li class="divider"></li>
					<li class="<?php echo($view == 'emlist' ? 'uk-active' : NULL); ?>">
						<a href="<?php echo JRoute::_('index.php?option=com_joomsubscription&view=emlist') ?>">
							<img src="<?php echo $img_url; ?>purchase-small.png" align="absmiddle"/> <?php echo JText::_('EPLUNLIST') ?></a></li>
					<li class="<?php echo($view == 'emhistory' ? 'uk-active' : NULL); ?>">
						<a href="<?php echo JRoute::_('index.php?option=com_joomsubscription&view=emhistory') ?>">
							<img src="<?php echo $img_url; ?>history-small.png" align="absmiddle"/> <?php echo JText::_('EPLUNHISTORY') ?></a></li>
				</ul>

			</li>
		</ul>
	</div>
</div>
