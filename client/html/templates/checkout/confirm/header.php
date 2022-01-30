<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2015-2022
 */

$enc = $this->encoder();


?>
<title><?= $this->translate( 'client', 'Confirmation' ) ?> | <?= $enc->html( $this->get( 'contextSiteLabel', 'Aimeos' ) ) ?></title>

<link rel="stylesheet" href="<?= $enc->attr( $this->content( $this->get( 'contextSite', 'default' ) . '/summary.css', 'fs-theme', true ) ) ?>">
<link rel="stylesheet" href="<?= $enc->attr( $this->content( $this->get( 'contextSite', 'default' ) . '/checkout-confirm.css', 'fs-theme', true ) ) ?>">
<script defer src="<?= $enc->attr( $this->content( $this->get( 'contextSite', 'default' ) . '/checkout-confirm.js', 'fs-theme', true ) ) ?>"></script>

<?= $this->get( 'confirmHeader' ) ?>