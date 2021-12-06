<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2018-2021
 * @package Client
 * @subpackage Html
 */


namespace Aimeos\Client\Html\Email\Voucher\Html;


/**
 * Default implementation of voucher e-mail html HTML client.
 *
 * @package Client
 * @subpackage Html
 */
class Standard
	extends \Aimeos\Client\Html\Common\Client\Factory\Base
	implements \Aimeos\Client\Html\Common\Client\Factory\Iface
{
	/** client/html/email/voucher/html/subparts
	 * List of HTML sub-clients rendered within the voucher e-mail html section
	 *
	 * The output of the frontend is composed of the code generated by the HTML
	 * clients. Each HTML client can consist of serveral (or none) sub-clients
	 * that are responsible for rendering certain sub-parts of the output. The
	 * sub-clients can contain HTML clients themselves and therefore a
	 * hierarchical tree of HTML clients is composed. Each HTML client creates
	 * the output that is placed inside the container of its parent.
	 *
	 * At first, always the HTML code generated by the parent is printed, then
	 * the HTML code of its sub-clients. The order of the HTML sub-clients
	 * determines the order of the output of these sub-clients inside the parent
	 * container. If the configured list of clients is
	 *
	 *  array( "subclient1", "subclient2" )
	 *
	 * you can easily change the order of the output by reordering the subparts:
	 *
	 *  client/html/<clients>/subparts = array( "subclient1", "subclient2" )
	 *
	 * You can also remove one or more parts if they shouldn't be rendered:
	 *
	 *  client/html/<clients>/subparts = array( "subclient1" )
	 *
	 * As the clients only generates structural HTML, the layout defined via CSS
	 * should support adding, removing or reordering content by a fluid like
	 * design.
	 *
	 * @param array List of sub-client names
	 * @since 2018.07
	 * @category Developer
	 */
	private $subPartPath = 'client/html/email/voucher/html/subparts';
	private $subPartNames = [];


	/**
	 * Returns the HTML code for insertion into the body.
	 *
	 * @param string $uid Unique identifier for the output if the content is placed more than once on the same page
	 * @return string HTML code
	 */
	public function body( string $uid = '' ) : string
	{
		$view = $this->view();

		$content = '';
		foreach( $this->getSubClients() as $subclient ) {
			$content .= $subclient->setView( $view )->body( $uid );
		}
		$view->htmlBody = $content;

		/** client/html/email/voucher/html/template-body
		 * Relative path to the HTML body template of the voucher e-mail html client.
		 *
		 * The template file contains the HTML code and processing instructions
		 * to generate the result shown in the body of the e-mail. The
		 * configuration string is the path to the template file relative
		 * to the templates directory (usually in client/html/templates).
		 *
		 * You can overwrite the template file configuration in extensions and
		 * provide alternative templates. These alternative templates should be
		 * named like the default one but with the string "standard" replaced by
		 * an unique name. You may use the name of your project for this. If
		 * you've implemented an alternative client class as well, "standard"
		 * should be replaced by the name of the new class.
		 *
		 * The voucher e-mail html client allows to use a different template for
		 * each voucher status value. You can create a template for each voucher
		 * status and store it in the "email/voucher/<status number>/" directory
		 * below the "templates" directory (usually in client/html/templates). If no
		 * specific layout template is found, the common template in the
		 * "email/voucher/" directory is used.
		 *
		 * @param string Relative path to the template creating code for the HTML e-mail body
		 * @since 2018.07
		 * @category Developer
		 * @see client/html/email/voucher/html/template-header
		 */
		$tplconf = 'client/html/email/voucher/html/template-body';

		$html = $view->render( $view->config( $tplconf, 'email/voucher/html-body-standard' ) );
		$view->mail()->html( $html );
		return $html;
	}


	/**
	 * Returns the sub-client given by its name.
	 *
	 * @param string $type Name of the client type
	 * @param string|null $name Name of the sub-client (Default if null)
	 * @return \Aimeos\Client\Html\Iface Sub-client object
	 */
	public function getSubClient( string $type, string $name = null ) : \Aimeos\Client\Html\Iface
	{
		/** client/html/email/voucher/html/decorators/excludes
		 * Excludes decorators added by the "common" option from the "email voucher html" html client
		 *
		 * Decorators extend the functionality of a class by adding new aspects
		 * (e.g. log what is currently done), executing the methods of the underlying
		 * class only in certain conditions (e.g. only for logged in users) or
		 * modify what is returned to the caller.
		 *
		 * This option allows you to remove a decorator added via
		 * "client/html/common/decorators/default" before they are wrapped
		 * around the html client.
		 *
		 *  client/html/email/voucher/html/decorators/excludes = array( 'decorator1' )
		 *
		 * This would remove the decorator named "decorator1" from the list of
		 * common decorators ("\Aimeos\Client\Html\Common\Decorator\*") added via
		 * "client/html/common/decorators/default" to the html client.
		 *
		 * @param array List of decorator names
		 * @since 2018.07
		 * @category Developer
		 * @see client/html/common/decorators/default
		 * @see client/html/email/voucher/html/decorators/global
		 * @see client/html/email/voucher/html/decorators/local
		 */

		/** client/html/email/voucher/html/decorators/global
		 * Adds a list of globally available decorators only to the "email voucher html" html client
		 *
		 * Decorators extend the functionality of a class by adding new aspects
		 * (e.g. log what is currently done), executing the methods of the underlying
		 * class only in certain conditions (e.g. only for logged in users) or
		 * modify what is returned to the caller.
		 *
		 * This option allows you to wrap global decorators
		 * ("\Aimeos\Client\Html\Common\Decorator\*") around the html client.
		 *
		 *  client/html/email/voucher/html/decorators/global = array( 'decorator1' )
		 *
		 * This would add the decorator named "decorator1" defined by
		 * "\Aimeos\Client\Html\Common\Decorator\Decorator1" only to the html client.
		 *
		 * @param array List of decorator names
		 * @since 2018.07
		 * @category Developer
		 * @see client/html/common/decorators/default
		 * @see client/html/email/voucher/html/decorators/excludes
		 * @see client/html/email/voucher/html/decorators/local
		 */

		/** client/html/email/voucher/html/decorators/local
		 * Adds a list of local decorators only to the "email voucher html" html client
		 *
		 * Decorators extend the functionality of a class by adding new aspects
		 * (e.g. log what is currently done), executing the methods of the underlying
		 * class only in certain conditions (e.g. only for logged in users) or
		 * modify what is returned to the caller.
		 *
		 * This option allows you to wrap local decorators
		 * ("\Aimeos\Client\Html\Checkout\Decorator\*") around the html client.
		 *
		 *  client/html/email/voucher/html/decorators/local = array( 'decorator2' )
		 *
		 * This would add the decorator named "decorator2" defined by
		 * "\Aimeos\Client\Html\Checkout\Decorator\Decorator2" only to the html client.
		 *
		 * @param array List of decorator names
		 * @since 2018.07
		 * @category Developer
		 * @see client/html/common/decorators/default
		 * @see client/html/email/voucher/html/decorators/excludes
		 * @see client/html/email/voucher/html/decorators/global
		 */

		return $this->createSubClient( 'email/voucher/html/' . $type, $name );
	}


	/**
	 * Returns the list of sub-client names configured for the client.
	 *
	 * @return array List of HTML client names
	 */
	protected function getSubClientNames() : array
	{
		return $this->context()->config()->get( $this->subPartPath, $this->subPartNames );
	}


	/**
	 * Sets the necessary parameter values in the view.
	 *
	 * @param \Aimeos\MW\View\Iface $view The view object which generates the HTML output
	 * @param array &$tags Result array for the list of tags that are associated to the output
	 * @param string|null &$expire Result variable for the expiration date of the output (null for no expiry)
	 * @return \Aimeos\MW\View\Iface Modified view object
	 */
	public function data( \Aimeos\MW\View\Iface $view, array &$tags = [], string &$expire = null ) : \Aimeos\MW\View\Iface
	{
		/** client/html/email/logo
		 * Path to the logo image displayed in HTML e-mails
		 *
		 * The path can either be an absolute local path or an URL to a file on a
		 * remote server. If the file is stored on a remote server, "allow_url_fopen"
		 * must be enabled. See {@link http://php.net/manual/en/filesystem.configuration.php#ini.allow-url-fopen php.ini allow_url_fopen}
		 * documentation for details.
		 *
		 * @param string Absolute file system path or remote URL to the logo image
		 * @since 2018.07
		 * @category User
		 * @see client/html/email/from-email
		 */
		$file = $view->config( 'client/html/email/logo', 'client/html/themes/default/media/aimeos.png' );

		if( file_exists( $file ) && ( $content = file_get_contents( $file ) ) !== false )
		{
			$finfo = new \finfo( FILEINFO_MIME_TYPE );
			$mimetype = $finfo->file( $file );

			$view->htmlLogo = $view->mail()->embed( $content, $mimetype, basename( $file ) );
		}


		$path = $view->config( 'client/html/common/template/baseurl', 'client/html/themes/default' );
		$filepath = $path . DIRECTORY_SEPARATOR . 'email.css';

		if( file_exists( $filepath ) && ( $css = file_get_contents( $filepath ) ) !== false ) {
			$view->htmlCss = $css;
		}

		return parent::data( $view, $tags, $expire );
	}
}
