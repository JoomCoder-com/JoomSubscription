<?php
/**
 * JoomSubscription by JoomCoder
 * a component for Joomla (http://www.joomla.org)
 * Author Website: https://www.joomcoder.com/
 * @copyright Copyright (C) 2012 JoomCoder (https://www.joomcoder.com/). All rights reserved.
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */
defined('_JEXEC') or die();
$user = JFactory::getUser();

echo $this->menu->render(null);
?>
<style>
	.alert p:last-child {
		margin-bottom: 0;
	}
</style>
<?php //if($this->mparams->get('page_heading', false)): ?>
<div class="page-header <?php echo $this->mparams->get('pageclass_sfx'); ?>">
    <?php if(count($this->usersubs)): ?>
    <div class="input-append pull-right">
    	<a class="btn" href="<?php echo JoomsubscriptionApi::getLink('emhistory'); ?>">
    		<img src="<?php echo JUri::root(TRUE); ?>/components/com_joomsubscription/images/cpanel/history-small.png" />
    		<?php echo JText::_('EMP_CHECKHISTORY'); ?>
    	</a>
    </div>
    <?php endif; ?>  
	<h1>
        <img src="<?php echo JUri::root(TRUE); ?>/components/com_joomsubscription/images/cpanel/purchase.png" />
		<?php echo JText::_('EM_NEWPURCHASE') ?>
        <?php //echo $this->mparams->get('page_heading'); ?>
    </h1>
</div>
<?php //endif; ?>

<?php foreach($this->items AS $key => $plans): $this->plans = $plans;?>

	<?php if(count($this->items) > 1): ?>
		<div class="page-header">
			<h1><?php echo $this->cats[$key]['name']; ?></h1>
			<?php if (!empty($this->cats[$key]['image']) ):?>
				<div class="group-image"><?php echo JHtml::image($this->cats[$key]['image'], $this->cats[$key]['name']) ; ?></div>
			<?php endif;?>
		</div>
	<?php endif; ?>

	<?php echo $this->cats[$key]['description']; ?>

	<?php echo $this->loadTemplate('list_' . $this->cats[$key]['template']); ?>
	<br><br>

<?php endforeach; ?>
