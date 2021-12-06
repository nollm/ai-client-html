<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Metaways Infosystems GmbH, 2014
 * @copyright Aimeos (aimeos.org), 2015-2021
 * @package Client
 * @subpackage Html
 */


namespace Aimeos\Client\Html\Catalog\Session\Pinned;


/**
 * Default implementation of catalog session pinned section for HTML clients.
 *
 * @package Client
 * @subpackage Html
 */
class Standard
	extends \Aimeos\Client\Html\Common\Client\Factory\Base
	implements \Aimeos\Client\Html\Common\Client\Factory\Iface
{
	/** client/html/catalog/session/pinned/subparts
	 * List of HTML sub-clients rendered within the catalog session pinned section
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
	 * @since 2014.03
	 * @category Developer
	 */
	private $subPartPath = 'client/html/catalog/session/pinned/subparts';
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
		$context = $this->context();
		$session = $context->getSession();

		/** client/html/catalog/session/pinned
		 * All parameters defined for the catalog session pinned subpart
		 *
		 * This returns all settings related to the catalog session pinned subpart.
		 * Please refer to the single settings for details.
		 *
		 * @param array Associative list of name/value settings
		 * @category Developer
		 * @see client/html/catalog/session#pinned
		 */
		$config = $context->config()->get( 'client/html/catalog/session/pinned', [] );
		$key = $this->getParamHash( [], $uid . ':catalog:session-pinned-body', $config );

		if( ( $html = $session->get( $key ) ) === null )
		{
			$output = '';
			foreach( $this->getSubClients() as $subclient ) {
				$output .= $subclient->setView( $view )->body( $uid );
			}
			$view->pinnedBody = $output;

			/** client/html/catalog/session/pinned/template-body
			 * Relative path to the HTML body template of the catalog session pinned client.
			 *
			 * The template file contains the HTML code and processing instructions
			 * to generate the result shown in the body of the frontend. The
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
			 * @param string Relative path to the template creating code for the HTML page body
			 * @since 2014.03
			 * @category Developer
			 * @see client/html/catalog/session/pinned/template-header
			 */
			$tplconf = 'client/html/catalog/session/pinned/template-body';
			$default = 'catalog/session/pinned-body-standard';

			$html = $view->render( $view->config( $tplconf, $default ) );

			$cached = $session->get( 'aimeos/catalog/session/pinned/cache', [] ) + array( $key => true );
			$session->set( 'aimeos/catalog/session/pinned/cache', $cached );
			$session->set( $key, $html );
		}

		$view->block()->set( 'catalog/session/pinned', $html );

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
		/** client/html/catalog/session/pinned/decorators/excludes
		 * Excludes decorators added by the "common" option from the catalog session pinned html client
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
		 *  client/html/catalog/session/pinned/decorators/excludes = array( 'decorator1' )
		 *
		 * This would remove the decorator named "decorator1" from the list of
		 * common decorators ("\Aimeos\Client\Html\Common\Decorator\*") added via
		 * "client/html/common/decorators/default" to the html client.
		 *
		 * @param array List of decorator names
		 * @since 2015.08
		 * @category Developer
		 * @see client/html/common/decorators/default
		 * @see client/html/catalog/session/pinned/decorators/global
		 * @see client/html/catalog/session/pinned/decorators/local
		 */

		/** client/html/catalog/session/pinned/decorators/global
		 * Adds a list of globally available decorators only to the catalog session pinned html client
		 *
		 * Decorators extend the functionality of a class by adding new aspects
		 * (e.g. log what is currently done), executing the methods of the underlying
		 * class only in certain conditions (e.g. only for logged in users) or
		 * modify what is returned to the caller.
		 *
		 * This option allows you to wrap global decorators
		 * ("\Aimeos\Client\Html\Common\Decorator\*") around the html client.
		 *
		 *  client/html/catalog/session/pinned/decorators/global = array( 'decorator1' )
		 *
		 * This would add the decorator named "decorator1" defined by
		 * "\Aimeos\Client\Html\Common\Decorator\Decorator1" only to the html client.
		 *
		 * @param array List of decorator names
		 * @since 2015.08
		 * @category Developer
		 * @see client/html/common/decorators/default
		 * @see client/html/catalog/session/pinned/decorators/excludes
		 * @see client/html/catalog/session/pinned/decorators/local
		 */

		/** client/html/catalog/session/pinned/decorators/local
		 * Adds a list of local decorators only to the catalog session pinned html client
		 *
		 * Decorators extend the functionality of a class by adding new aspects
		 * (e.g. log what is currently done), executing the methods of the underlying
		 * class only in certain conditions (e.g. only for logged in users) or
		 * modify what is returned to the caller.
		 *
		 * This option allows you to wrap local decorators
		 * ("\Aimeos\Client\Html\Catalog\Decorator\*") around the html client.
		 *
		 *  client/html/catalog/session/pinned/decorators/local = array( 'decorator2' )
		 *
		 * This would add the decorator named "decorator2" defined by
		 * "\Aimeos\Client\Html\Catalog\Decorator\Decorator2" only to the html client.
		 *
		 * @param array List of decorator names
		 * @since 2015.08
		 * @category Developer
		 * @see client/html/common/decorators/default
		 * @see client/html/catalog/session/pinned/decorators/excludes
		 * @see client/html/catalog/session/pinned/decorators/global
		 */

		return $this->createSubClient( 'catalog/session/pinned/' . $type, $name );
	}


	/**
	 * Processes the input, e.g. store given values.
	 *
	 * A view must be available and this method doesn't generate any output
	 * besides setting view variables if necessary.
	 */
	public function init()
	{
		$refresh = false;
		$view = $this->view();
		$context = $this->context();
		$session = $context->getSession();
		$pinned = $session->get( 'aimeos/catalog/session/pinned/list', [] );

		if( $view->request()->getMethod() === 'POST' )
		{
			switch( $view->param( 'pin_action' ) )
			{
				case 'add':

					foreach( (array) $view->param( 'pin_id', [] ) as $id ) {
						$pinned[$id] = $id;
					}

					/** client/html/catalog/session/pinned/maxitems
					 * Maximum number of products displayed in the "pinned" section
					 *
					 * This option limits the number of products that are shown in the
					 * "pinned" section after the users added the product to their list
					 * of pinned products. It must be a positive integer value greater
					 * than 0.
					 *
					 * Note: The higher the value is the more data has to be transfered
					 * to the client each time the user loads a page with the list of
					 * pinned products.
					 *
					 * @param integer Number of products
					 * @since 2014.09
					 * @category User
					 * @category Developer
					 */
					$max = $context->config()->get( 'client/html/catalog/session/pinned/maxitems', 50 );

					$pinned = array_slice( $pinned, -$max, $max, true );
					$refresh = true;
					break;

				case 'delete':

					foreach( (array) $view->param( 'pin_id', [] ) as $id ) {
						unset( $pinned[$id] );
					}

					$refresh = true;
					break;
			}
		}


		if( $refresh )
		{
			$session->set( 'aimeos/catalog/session/pinned/list', $pinned );

			foreach( $session->get( 'aimeos/catalog/session/pinned/cache', [] ) as $key => $value ) {
				$session->set( $key, null );
			}
		}

		parent::init();
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
		$items = [];
		$context = $this->context();
		$config = $context->config();
		$session = $context->getSession();

		$domains = $config->get( 'client/html/catalog/domains', ['media', 'price', 'text'] );

		/** client/html/catalog/session/pinned/domains
		 * A list of domain names whose items should be available in the pinned view template for the product
		 *
		 * The templates rendering product details usually add the images,
		 * prices and texts, etc. associated to the product
		 * item. If you want to display additional or less content, you can
		 * configure your own list of domains (attribute, media, price, product,
		 * text, etc. are domains) whose items are fetched from the storage.
		 * Please keep in mind that the more domains you add to the configuration,
		 * the more time is required for fetching the content!
		 *
		 * @param array List of domain names
		 * @since 2015.04
		 * @category Developer
		 * @see client/html/catalog/domains
		 * @see client/html/catalog/lists/domains
		 * @see client/html/catalog/detail/domains
		 */
		$domains = $config->get( 'client/html/catalog/session/pinned/domains', $domains );

		if( ( $pinned = $session->get( 'aimeos/catalog/session/pinned/list', [] ) ) !== [] )
		{
			$result = \Aimeos\Controller\Frontend::create( $context, 'product' )
				->uses( $domains )->product( $pinned )->slice( 0, count( $pinned ) )->search();

			foreach( array_reverse( $pinned ) as $id )
			{
				if( isset( $result[$id] ) ) {
					$items[$id] = $result[$id];
				}
			}
		}

		$view->pinnedProductItems = $items;
		$view->pinnedParams = $this->getClientParams( $view->param() );

		return parent::data( $view, $tags, $expire );
	}
}
