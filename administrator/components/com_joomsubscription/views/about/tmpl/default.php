<?php
/**
 * Cobalt by JoomCoder
 * a component for Joomla! 1.7 - 2.5 CMS (http://www.joomla.org)
 * Author Website: https://www.joomcoder.com/
 * @copyright Copyright (C) 2012 JoomCoder (https://www.joomcoder.com/). All rights reserved.
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die('Restricted access'); ?>

<div class="row-fliud">
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
		<div class="hero-unit" style="text-align: left;">
		<h1>Joomsubscription 10 <small>JoomCoder</small></h1>
		<p><?php echo JText::_('E_LEAD'); ?></p>

		<a class="btn btn-primary" target="_blank" href="https://www.joomcoder.com/joomla-components/joomsubscription.html"><?php echo JText::_('E_HOMEPAGE'); ?></a>
		<a class="btn" target="_blank" href="https://www.joomcoder.com/support/community-forum/category-items/6-community-forum/52-joomsubscription-9.html"><?php echo JText::_('E_IQP_'); ?></a>
		<a class="btn" target="_blank" href="https://www.joomcoder.com/blog.html"><?php echo JText::_('E_UPDATELOG'); ?></a>
		<a class="btn" target="_blank" href="https://www.joomcoder.com/community/depot.html"><?php echo JText::_('E_3DP'); ?></a>
		</div>

		<p><?php echo JText::_('E_DESCR'); ?></p>


		<div class="row-fluid">
			<div class="span4">
				<legend><?php echo JText::_('E_DETAILS'); ?></legend>
				<table class="table table-bordered table-striped">
				    <tbody>
					     <tr>
					        <td class="key"><?php echo JText::_('E_VERSION'); ?></td>
					        <td><?php echo $this->data['version'];?></td>
					    </tr>

					    <tr>
					        <td class="key"><?php echo JText::_('E_LICENSE'); ?></td>
					        <td>GNU GPL</td>
					    </tr>
					    <tr>
					        <td class="key"><?php echo JText::_('E_EMAIL'); ?></td>
					        <td><a href="mailto:<?php echo $this->data['authorEmail'];?>"><?php echo $this->data['authorEmail'];?></a></td>
					    </tr>
					    <tr>
					        <td class="key"><?php echo JText::_('E_HOMEPAGE'); ?></td>
					        <td><a href="<?php echo $this->data['authorUrl'];?>" target="_blank"><?php echo $this->data['author'];?></a></td>
					    </tr>
					    <tr>
					        <td class="key"><?php echo JText::_('E_COPYRIGHT'); ?></td>
					        <td><?php echo $this->data['copyright'];?></td>
					    </tr>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>
