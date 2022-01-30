<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Metaways Infosystems GmbH, 2013
 * @copyright Aimeos (aimeos.org), 2015-2022
 */

$enc = $this->encoder();


?>
<?php if( isset( $this->seenProductItem ) ) : $productItem = $this->seenProductItem ?>

	<a href="<?= $enc->attr( $this->link( 'client/html/catalog/detail/url', [
		'd_name' => $productItem->getName( 'url' ),
		'd_prodid' => $productItem->getId(), 'd_pos' => ''
	] ) ) ?>">

		<?php if( ( $mediaItem = $productItem->getRefItems( 'media', 'default', 'default' )->first() ) !== null ) : ?>
			<div class="media-item" style="background-image: url('<?= $enc->attr( $this->content( $mediaItem->getPreview(), $mediaItem->getFileSystem() ) ) ?>')"></div>
		<?php else : ?>
			<div class="media-item"></div>
		<?php endif ?>

		<h2 class="name"><?= $enc->html( $productItem->getName(), $enc::TRUST ) ?></h2>

		<div class="price-list">
			<?= $this->partial(
				$this->config( 'client/html/common/partials/price', 'common/partials/price' ),
				array( 'prices' => $productItem->getRefItems( 'price', null, 'default' ) )
			) ?>
		</div>

	</a>
<?php endif ?>