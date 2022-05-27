<?php
/*
 * @package   plg_radicalmart_fields_related
 * @version   1.0.0
 * @author    Dmitriy Vasyukov - https://fictionlabs.ru
 * @copyright Copyright (c) 2022 Fictionlabs. All rights reserved.
 * @license   GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link      https://fictionlabs.ru/
 */

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

extract($displayData);

/**
 * Layout variables
 * -----------------
 *
 * @var  object $product Product object.
 * @var  string $mode    RadicalMart mode.
 * @var  object $params  Module params
 *
 */
$hidePrice = (ComponentHelper::getParams('com_radicalmart')->get('hide_prices', 0) || !empty($product->price['hide']));
?>
<div class="radicalmart-related__item product-block uk-transition-toggle">
	<div class="uk-overflow-hidden">
		<div class="uk-position-relative">
			<a href="<?php echo $product->link; ?>"
			   class="uk-height-medium uk-width-1-1 uk-flex uk-flex-center uk-flex-middle uk-transition-scale-up uk-transition-opaque ">
				<?php if ($image = $product->image)
				{
					$image = RadicalMartHelperMedia::findThumb($image);
					echo HTMLHelper::image($image, htmlspecialchars($product->title),
						array('class' => 'uk-height-max-medium'));
				}
				else
				{
					echo HTMLHelper::image('com_radicalmart/no-image.svg', htmlspecialchars($product->title),
						array('class' => 'uk-height-max-medium'), true);
				} ?>
			</a>
			<?php if (!empty($product->badges)): ?>
				<ul class="uk-thumbnav uk-position-top-right uk-margin-small-top">
					<?php foreach ($product->badges as $badge): ?>
						<li>
							<a href="<?php echo $badge->link; ?>" uk-tooltip
							   title="<?php echo Text::sprintf('COM_RADICALMART_PRODUCT_BADGE_LINK', $badge->title); ?>">
								<?php if ($src = $badge->media->get('icon'))
								{
									$src = RadicalMartHelperMedia::findThumb($src);
									echo HTMLHelper::image($src, htmlspecialchars($badge->title));
								}
								else echo '<span class="uk-label">' . $badge->title . '</span>'; ?>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>
	</div>
	<div class="middle uk-margin-small">
		<?php if ($product->category && $params->get('show_category', 1)): ?>
			<div>
				<a href="<?php echo $product->category->link; ?>"
				   class="uk-text-nowrap uk-text-small uk-link-muted">
					<?php echo $product->category->title; ?>
				</a>
			</div>
		<?php endif; ?>
		<div>
			<a href="<?php echo $product->link; ?>" class="uk-link-reset"><?php echo $product->title; ?></a>
		</div>
	</div>
	<div class="uk-flex uk-flex-bottom uk-flex-between uk-margin-small">
		<div>
			<?php if (!$hidePrice): ?>
				<?php if ($product->price['discount_enable']): ?>
					<div class="uk-text-small uk-text-muted">
						<s><?php echo $product->price['base_string']; ?></s>
					</div>
				<?php endif; ?>
				<div class="">
					<strong><?php echo $product->price['final_string']; ?></strong>
				</div>
			<?php endif; ?>
		</div>
		<div>
			<?php if (!$hidePrice && $mode === 'shop' && (int) $product->state === 1): ?>
				<div radicalmart-cart="product" data-id="<?php echo $product->id; ?>">
					<span radicalmart-cart="add"
						  class="uk-icon-button uk-link uk-background-primary uk-light" uk-icon="cart"
						  uk-tooltip="" title="<?php echo Text::_('COM_RADICALMART_CART_ADD'); ?>"></span>
				</div>
			<?php elseif ($hidePrice || $mode === 'catalog'): ?>
				<a href="<?php echo $product->link; ?>"
				   class="uk-icon-button uk-background-primary uk-light" uk-icon="chevron-right"
				   uk-tooltip="" title="<?php echo Text::_('COM_RADICALMART_READMORE'); ?>"></a>
			<?php endif; ?>
		</div>
	</div>
</div>