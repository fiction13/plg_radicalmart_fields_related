<?php
/*
 * @package   plg_radicalmart_fields_related
 * @version   1.0.1
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
<div class="radicalmart-related__item product-block uk-transition-toggle uk-card-body uk-card-small">
	<div class="uk-child-width-expand@s uk-grid-divider" uk-grid="">
		<div>
			<div class="uk-child-width-expand@m" uk-grid="">
				<div class="uk-width-1-3@s uk-flex uk-flex-top">
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
						<a href="<?php echo $product->link; ?>"
						   class="uk-link-reset"><?php echo $product->title; ?></a>
					</div>
					<?php if (!empty($product->introtext)): ?>
						<div class="uk-text-small uk-visible@s">
							<?php echo $product->introtext; ?>
						</div>
					<?php endif; ?>
					<?php if (!empty($product->fields)): ?>
						<?php foreach ($product->fields as $field):
							if (empty($field->value)) continue; ?>
							<div class="uk-form-horizontal uk-margin-remove uk-clearfix">
								<div class="uk-form-label"><?php echo $field->title; ?></div>
								<div class="uk-form-controls uk-form-controls-text">
									<?php echo $field->value; ?>
								</div>
							</div>
						<?php endforeach; ?>
					<?php endif; ?>
					<?php if (!empty($product->manufacturers)): ?>
						<div class="uk-margin">
							<ul class="uk-thumbnav">
								<?php foreach ($product->manufacturers as $manufacturer): ?>
									<li>
										<a href="<?php echo $manufacturer->link; ?>" uk-tooltip
										   title="<?php echo Text::sprintf('COM_RADICALMART_PRODUCT_MANUFACTURER_LINK', $manufacturer->title); ?>">
											<?php if ($src = $manufacturer->media->get('icon'))
											{
												$src = RadicalMartHelperMedia::findThumb($src);
												echo HTMLHelper::image($src, htmlspecialchars($manufacturer->title),
													array('class' => 'uk-width-small'));
											}
											else echo $manufacturer->title; ?>
										</a>
									</li>
								<?php endforeach; ?>
							</ul>
						</div>
					<?php endif; ?>
				</div>
				<div class="uk-flex uk-flex-middle uk-flex-between uk-margin-small uk-hidden@s">
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
					<div class="uk-hidden@s">
						<div>
							<?php if (!$hidePrice && $mode === 'shop'): ?>
								<div radicalmart-cart="product" data-id="<?php echo $product->id; ?>">
									<span radicalmart-cart="add"
										  class="uk-icon-button uk-link uk-background-primary uk-light" uk-icon="cart"
										  uk-tooltip=""
										  title="<?php echo Text::_('COM_RADICALMART_CART_ADD'); ?>"></span>
								</div>
							<?php elseif ($hidePrice || $mode === 'catalog'): ?>
								<a href="<?php echo $product->link; ?>"
								   class="uk-icon-button uk-background-primary uk-light" uk-icon="chevron-right"
								   uk-tooltip="" title="<?php echo Text::_('COM_RADICALMART_READMORE'); ?>"></a>
							<?php endif; ?>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="uk-width-1-4@s uk-visible@s">
			<?php if (!$hidePrice): ?>
			<div>
				<?php if ($product->price['discount_enable']): ?>
					<div class="uk-text-small uk-text-muted">
						<s><?php echo $product->price['base_string']; ?></s>
					</div>
				<?php endif; ?>
				<div class="">
					<strong><?php echo $product->price['final_string']; ?></strong>
				</div>
			</div>
			<?php endif;?>
			<div class="uk-margin">
				<?php if (!$hidePrice && $mode === 'shop' && (int) $product->state === 1): ?>
					<div radicalmart-cart="product" data-id="<?php echo $product->id; ?>">
						<span radicalmart-cart="add" class="uk-button uk-button-primary">
							<?php echo Text::_('COM_RADICALMART_CART_ADD'); ?>
						</span>
					</div>
				<?php elseif ($hidePrice || $mode === 'catalog'): ?>
					<a href="<?php echo $product->link; ?>"
					   class="uk-button uk-button-primary">
						<?php echo Text::_('COM_RADICALMART_READMORE'); ?>
					</a>
				<?php endif; ?>
			</div>
		</div>
	</div>
</div>