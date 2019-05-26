<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2015-2018
 */

$order = $this->extOrderItem;

$msg = $msg2 = '';
$key = 'pay:' . $order->getPaymentStatus();
$status = $this->translate( 'mshop/code', $key );
$format = $this->translate( 'client', 'Y-m-d' );

switch( $order->getPaymentStatus() )
{
	case 3:
		/// Payment e-mail intro with order ID (%1$s), order date (%2$s) and payment status (%3%s)
		$msg = $this->translate( 'client', 'The payment for your order %1$s from %2$s has been refunded.' );
		break;
	case 4:
		/// Payment e-mail intro with order ID (%1$s), order date (%2$s) and payment status (%3%s)
		$msg = $this->translate( 'client', 'Thank you for your order %1$s from %2$s.' );
		$msg2 = $this->translate( 'client', 'The order is pending until we receive the final payment. If you\'ve chosen to pay in advance, please transfer the money to our bank account with the order ID %1$s as reference.' );
		break;
	case 6:
		/// Payment e-mail intro with order ID (%1$s), order date (%2$s) and payment status (%3%s)
		$msg = $this->translate( 'client', 'Thank you for your order %1$s from %2$s.' );
		$msg2 = $this->translate( 'client', 'We have received your payment, and will take care of your order immediately.' );
		break;
	default:
		/// Payment e-mail intro with order ID (%1$s), order date (%2$s) and payment status (%3%s)
		$msg = $this->translate( 'client', 'Thank you for your order %1$s from %2$s.' );
}

$message = sprintf( $msg, $order->getId(), date_create( $order->getTimeCreated() )->format( $format ), $status );
$message .= "\n" . sprintf( $msg2, $order->getId(), date_create( $order->getTimeCreated() )->format( $format ), $status );


?>
<?php $this->block()->start( 'email/payment/text' ); ?>
<?= wordwrap( strip_tags( $this->get( 'emailIntro' ) ) ); ?>


<?= wordwrap( strip_tags( $message ) ); ?>


<?= $this->block()->get( 'email/common/text/summary' ); ?>


<?= wordwrap( strip_tags( $this->translate( 'client', 'If you have any questions, please reply to this e-mail' ) ) ); ?>


<?= wordwrap( strip_tags( $this->translate( 'client', 'All orders are subject to our terms and conditions.' ) ) ); ?>
<?php $this->block()->stop(); ?>
<?= $this->block()->get( 'email/payment/text' );
