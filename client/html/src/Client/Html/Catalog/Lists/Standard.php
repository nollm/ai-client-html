<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Metaways Infosystems GmbH, 2012
 * @copyright Aimeos (aimeos.org), 2015-2021
 * @package Client
 * @subpackage Html
 */


namespace Aimeos\Client\Html\Catalog\Lists;


/**
 * Default implementation of catalog list section HTML clients.
 *
 * @package Client
 * @subpackage Html
 */
class Standard
	extends \Aimeos\Client\Html\Catalog\Base
	implements \Aimeos\Client\Html\Common\Client\Factory\Iface
{
	/** client/html/catalog/lists/subparts
	 * List of HTML sub-clients rendered within the catalog list section
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
	private $subPartPath = 'client/html/catalog/lists/subparts';

	/** client/html/catalog/lists/promo/name
	 * Name of the promotion part used by the catalog list client implementation
	 *
	 * Use "Myname" if your class is named "\Aimeos\Client\Html\Catalog\Lists\Promo\Myname".
	 * The name is case-sensitive and you should avoid camel case names like "MyName".
	 *
	 * @param string Last part of the client class name
	 * @since 2014.03
	 * @category Developer
	 */

	/** client/html/catalog/lists/items/name
	 * Name of the items part used by the catalog list client implementation
	 *
	 * Use "Myname" if your class is named "\Aimeos\Client\Html\Catalog\Lists\Items\Myname".
	 * The name is case-sensitive and you should avoid camel case names like "MyName".
	 *
	 * @param string Last part of the client class name
	 * @since 2014.03
	 * @category Developer
	 */
	private $subPartNames = array( 'items' );

	private $tags = [];
	private $expire;
	private $view;


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
		$prefixes = ['f_catid', 'f_supid', 'f_sort', 'l_page', 'l_type'];

		/** client/html/catalog/lists/cache
		 * Enables or disables caching only for the catalog lists component
		 *
		 * Disable caching for components can be useful if you would have too much
		 * entries to cache or if the component contains non-cacheable parts that
		 * can't be replaced using the modifyBody() and modifyHeader() methods.
		 *
		 * @param boolean True to enable caching, false to disable
		 * @category Developer
		 * @category User
		 * @see client/html/catalog/detail/cache
		 * @see client/html/catalog/filter/cache
		 * @see client/html/catalog/stage/cache
		 */

		/** client/html/catalog/lists
		 * All parameters defined for the catalog list component and its subparts
		 *
		 * This returns all settings related to the filter component.
		 * Please refer to the single settings for details.
		 *
		 * @param array Associative list of name/value settings
		 * @category Developer
		 * @see client/html/catalog#list
		 */
		$confkey = 'client/html/catalog/lists';

		$args = map( $view->param() )->except( $prefixes )->filter( function( $val, $key ) {
			return !strncmp( $key, 'f_', 2 ) || !strncmp( $key, 'l_', 2 );
		} );

		if( !$args->isEmpty() || ( $html = $this->getCached( 'body', $uid, $prefixes, $confkey ) ) === null )
		{
			/** client/html/catalog/lists/template-body
			 * Relative path to the HTML body template of the catalog list client.
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
			 * It's also possible to create a specific template for each type, e.g.
			 * for the grid, list or whatever view you want to offer your users. In
			 * that case, you can configure the template by adding "-<type>" to the
			 * configuration key. To configure an alternative list view template for
			 * example, use the key
			 *
			 * client/html/catalog/lists/template-body-list = catalog/lists/body-list.php
			 *
			 * The argument is the relative path to the new template file. The type of
			 * the view is determined by the "l_type" parameter (allowed characters for
			 * the types are a-z and 0-9), which is also stored in the session so users
			 * will keep the view during their visit. The catalog list type subpart
			 * contains the template for switching between list types.
			 *
			 * @param string Relative path to the template creating code for the HTML page body
			 * @since 2014.03
			 * @category Developer
			 * @see client/html/catalog/lists/template-header
			 * @see client/html/catalog/lists/type/template-body
			 */
			$tplconf = 'client/html/catalog/lists/template-body';
			$default = 'catalog/lists/body-standard';

			try
			{
				$html = '';

				if( !isset( $this->view ) ) {
					$view = $this->view = $this->object()->data( $view, $this->tags, $this->expire );
				}

				foreach( $this->getSubClients() as $subclient ) {
					$html .= $subclient->setView( $view )->body( $uid );
				}
				$view->listBody = $html;

				$html = $view->render( $this->getTemplatePath( $tplconf, $default ) );

				if( $args->isEmpty() ) {
					$this->setCached( 'body', $uid, $prefixes, $confkey, $html, $this->tags, $this->expire );
				}

				return $html;
			}
			catch( \Aimeos\Client\Html\Exception $e )
			{
				$error = array( $context->translate( 'client', $e->getMessage() ) );
				$view->listErrorList = array_merge( $view->get( 'listErrorList', [] ), $error );
			}
			catch( \Aimeos\Controller\Frontend\Exception $e )
			{
				$error = array( $context->translate( 'controller/frontend', $e->getMessage() ) );
				$view->listErrorList = array_merge( $view->get( 'listErrorList', [] ), $error );
			}
			catch( \Aimeos\MShop\Exception $e )
			{
				$error = array( $context->translate( 'mshop', $e->getMessage() ) );
				$view->listErrorList = array_merge( $view->get( 'listErrorList', [] ), $error );
			}
			catch( \Exception $e )
			{
				$error = array( $context->translate( 'client', 'A non-recoverable error occured' ) );
				$view->listErrorList = array_merge( $view->get( 'listErrorList', [] ), $error );
				$this->logException( $e );
			}

			$html = $view->render( $this->getTemplatePath( $tplconf, $default ) );
		}
		else
		{
			$html = $this->modifyBody( $html, $uid );
		}

		return $html;
	}


	/**
	 * Returns the HTML string for insertion into the header.
	 *
	 * @param string $uid Unique identifier for the output if the content is placed more than once on the same page
	 * @return string|null String including HTML tags for the header on error
	 */
	public function header( string $uid = '' ) : ?string
	{
		$view = $this->view();
		$confkey = 'client/html/catalog/lists';
		$prefixes = ['f_catid', 'f_supid', 'f_sort', 'l_page', 'l_type'];

		$args = map( $view->param() )->except( $prefixes )->filter( function( $val, $key ) {
			return !strncmp( $key, 'f_', 2 ) || !strncmp( $key, 'l_', 2 );
		} );

		if( !$args->isEmpty() || ( $html = $this->getCached( 'header', $uid, $prefixes, $confkey ) ) === null )
		{
			/** client/html/catalog/lists/template-header
			 * Relative path to the HTML header template of the catalog list client.
			 *
			 * The template file contains the HTML code and processing instructions
			 * to generate the HTML code that is inserted into the HTML page header
			 * of the rendered page in the frontend. The configuration string is the
			 * path to the template file relative to the templates directory (usually
			 * in client/html/templates).
			 *
			 * You can overwrite the template file configuration in extensions and
			 * provide alternative templates. These alternative templates should be
			 * named like the default one but with the string "standard" replaced by
			 * an unique name. You may use the name of your project for this. If
			 * you've implemented an alternative client class as well, "standard"
			 * should be replaced by the name of the new class.
			 *
			 * It's also possible to create a specific template for each type, e.g.
			 * for the grid, list or whatever view you want to offer your users. In
			 * that case, you can configure the template by adding "-<type>" to the
			 * configuration key. To configure an alternative list view template for
			 * example, use the key
			 *
			 * client/html/catalog/lists/template-header-list = catalog/lists/header-list.php
			 *
			 * The argument is the relative path to the new template file. The type of
			 * the view is determined by the "l_type" parameter (allowed characters for
			 * the types are a-z and 0-9), which is also stored in the session so users
			 * will keep the view during their visit. The catalog list type subpart
			 * contains the template for switching between list types.
			 *
			 * @param string Relative path to the template creating code for the HTML page head
			 * @since 2014.03
			 * @category Developer
			 * @see client/html/catalog/lists/template-body
			 * @see client/html/catalog/lists/type/template-body
			 */
			$tplconf = 'client/html/catalog/lists/template-header';
			$default = 'catalog/lists/header-standard';

			try
			{
				$html = '';

				if( !isset( $this->view ) ) {
					$view = $this->view = $this->object()->data( $view, $this->tags, $this->expire );
				}

				foreach( $this->getSubClients() as $subclient ) {
					$html .= $subclient->setView( $view )->header( $uid );
				}
				$view->listHeader = $html;

				$html = $view->render( $this->getTemplatePath( $tplconf, $default ) );

				if( $args->isEmpty() ) {
					$this->setCached( 'header', $uid, $prefixes, $confkey, $html, $this->tags, $this->expire );
				}

				return $html;
			}
			catch( \Exception $e )
			{
				$this->logException( $e );
			}
		}
		else
		{
			$html = $this->modifyHeader( $html, $uid );
		}

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
		/** client/html/catalog/lists/decorators/excludes
		 * Excludes decorators added by the "common" option from the catalog list html client
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
		 *  client/html/catalog/lists/decorators/excludes = array( 'decorator1' )
		 *
		 * This would remove the decorator named "decorator1" from the list of
		 * common decorators ("\Aimeos\Client\Html\Common\Decorator\*") added via
		 * "client/html/common/decorators/default" to the html client.
		 *
		 * @param array List of decorator names
		 * @since 2014.05
		 * @category Developer
		 * @see client/html/common/decorators/default
		 * @see client/html/catalog/lists/decorators/global
		 * @see client/html/catalog/lists/decorators/local
		 */

		/** client/html/catalog/lists/decorators/global
		 * Adds a list of globally available decorators only to the catalog list html client
		 *
		 * Decorators extend the functionality of a class by adding new aspects
		 * (e.g. log what is currently done), executing the methods of the underlying
		 * class only in certain conditions (e.g. only for logged in users) or
		 * modify what is returned to the caller.
		 *
		 * This option allows you to wrap global decorators
		 * ("\Aimeos\Client\Html\Common\Decorator\*") around the html client.
		 *
		 *  client/html/catalog/lists/decorators/global = array( 'decorator1' )
		 *
		 * This would add the decorator named "decorator1" defined by
		 * "\Aimeos\Client\Html\Common\Decorator\Decorator1" only to the html client.
		 *
		 * @param array List of decorator names
		 * @since 2014.05
		 * @category Developer
		 * @see client/html/common/decorators/default
		 * @see client/html/catalog/lists/decorators/excludes
		 * @see client/html/catalog/lists/decorators/local
		 */

		/** client/html/catalog/lists/decorators/local
		 * Adds a list of local decorators only to the catalog list html client
		 *
		 * Decorators extend the functionality of a class by adding new aspects
		 * (e.g. log what is currently done), executing the methods of the underlying
		 * class only in certain conditions (e.g. only for logged in users) or
		 * modify what is returned to the caller.
		 *
		 * This option allows you to wrap local decorators
		 * ("\Aimeos\Client\Html\Catalog\Decorator\*") around the html client.
		 *
		 *  client/html/catalog/lists/decorators/local = array( 'decorator2' )
		 *
		 * This would add the decorator named "decorator2" defined by
		 * "\Aimeos\Client\Html\Catalog\Decorator\Decorator2" only to the html client.
		 *
		 * @param array List of decorator names
		 * @since 2014.05
		 * @category Developer
		 * @see client/html/common/decorators/default
		 * @see client/html/catalog/lists/decorators/excludes
		 * @see client/html/catalog/lists/decorators/global
		 */

		return $this->createSubClient( 'catalog/lists/' . $type, $name );
	}


	/**
	 * Processes the input, e.g. store given values.
	 *
	 * A view must be available and this method doesn't generate any output
	 * besides setting view variables if necessary.
	 */
	public function init()
	{
		$context = $this->context();
		$view = $this->view();

		try
		{
			$site = $context->getLocale()->getSiteItem()->getCode();
			$params = $this->getClientParams( $view->param() );

			$catId = $context->config()->get( 'client/html/catalog/lists/catid-default' );

			if( ( $catId = $view->param( 'f_catid', $catId ) ) )
			{
				$params['f_name'] = $view->param( 'f_name' );
				$params['f_catid'] = $catId;
			}

			$context->getSession()->set( 'aimeos/catalog/lists/params/last/' . $site, $params );

			parent::init();
		}
		catch( \Aimeos\Client\Html\Exception $e )
		{
			$error = array( $context->translate( 'client', $e->getMessage() ) );
			$view->listErrorList = array_merge( $view->get( 'listErrorList', [] ), $error );
		}
		catch( \Aimeos\Controller\Frontend\Exception $e )
		{
			$error = array( $context->translate( 'controller/frontend', $e->getMessage() ) );
			$view->listErrorList = array_merge( $view->get( 'listErrorList', [] ), $error );
		}
		catch( \Aimeos\MShop\Exception $e )
		{
			$error = array( $context->translate( 'mshop', $e->getMessage() ) );
			$view->listErrorList = array_merge( $view->get( 'listErrorList', [] ), $error );
		}
		catch( \Exception $e )
		{
			$error = array( $context->translate( 'client', 'A non-recoverable error occured' ) );
			$view->listErrorList = array_merge( $view->get( 'listErrorList', [] ), $error );
			$this->logException( $e );
		}
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
		$total = 0;
		$context = $this->context();
		$config = $context->config();


		/** client/html/catalog/domains
		 * A list of domain names whose items should be available in the catalog view templates
		 *
		 * The templates rendering catalog related data usually add the images and
		 * texts associated to each item. If you want to display additional
		 * content like the attributes, you can configure your own list of
		 * domains (attribute, media, price, product, text, etc. are domains)
		 * whose items are fetched from the storage. Please keep in mind that
		 * the more domains you add to the configuration, the more time is required
		 * for fetching the content!
		 *
		 * This configuration option can be overwritten by the "client/html/catalog/lists/domains"
		 * configuration option that allows to configure the domain names of the
		 * items fetched specifically for all types of product listings.
		 *
		 * @param array List of domain names
		 * @since 2014.03
		 * @category Developer
		 * @see client/html/catalog/lists/domains
		 * @see client/html/catalog/lists/size
		 * @see client/html/catalog/lists/levels
		 * @see client/html/catalog/lists/sort
		 * @see client/html/catalog/lists/pages
		 */
		$domains = $config->get( 'client/html/catalog/domains', ['media', 'media/property', 'price', 'text'] );

		/** client/html/catalog/lists/domains
		 * A list of domain names whose items should be available in the product list view template
		 *
		 * The templates rendering product lists usually add the images, prices
		 * and texts associated to each product item. If you want to display additional
		 * content like the product attributes, you can configure your own list of
		 * domains (attribute, media, price, product, text, etc. are domains)
		 * whose items are fetched from the storage. Please keep in mind that
		 * the more domains you add to the configuration, the more time is required
		 * for fetching the content!
		 *
		 * This configuration option overwrites the "client/html/catalog/domains"
		 * option that allows to configure the domain names of the items fetched
		 * for all catalog related data.
		 *
		 * @param array List of domain names
		 * @since 2014.03
		 * @category Developer
		 * @see client/html/catalog/domains
		 * @see client/html/catalog/detail/domains
		 * @see client/html/catalog/stage/domains
		 * @see client/html/catalog/lists/attrid-default
		 * @see client/html/catalog/lists/catid-default
		 * @see client/html/catalog/lists/supid-default
		 * @see client/html/catalog/lists/size
		 * @see client/html/catalog/lists/levels
		 * @see client/html/catalog/lists/sort
		 * @see client/html/catalog/lists/pages
		 * @see client/html/catalog/lists/instock
		 */
		$domains = $config->get( 'client/html/catalog/lists/domains', $domains );

		/** client/html/catalog/lists/instock
		 * Show only products which are in stock
		 *
		 * This configuration option overwrites the "client/html/catalog/domains"
		 * option that allows to configure the domain names of the items fetched
		 * for all catalog related data.
		 *
		 * @param int Zero to show all products, "1" to show only products with stock
		 * @since 2021.10
		 * @see client/html/catalog/domains
		 * @see client/html/catalog/lists/domains
		 * @see client/html/catalog/detail/domains
		 * @see client/html/catalog/stage/domains
		 * @see client/html/catalog/lists/attrid-default
		 * @see client/html/catalog/lists/catid-default
		 * @see client/html/catalog/lists/supid-default
		 * @see client/html/catalog/lists/size
		 * @see client/html/catalog/lists/levels
		 * @see client/html/catalog/lists/sort
		 * @see client/html/catalog/lists/pages
		 */
		$inStock = $config->get( 'client/html/catalog/lists/instock', 0 );

		/** client/html/catalog/lists/pages
		 * Maximum number of product pages shown in pagination
		 *
		 * Limits the number of product pages that are shown in the navigation.
		 * The user is able to move to the next page (or previous one if it's not
		 * the first) to display the next (or previous) products.
		 *
		 * The value must be a positive integer number. Negative values are not
		 * allowed. The value can't be overwritten per request.
		 *
		 * @param integer Number of pages
		 * @since 2019.04
		 * @category User
		 * @category Developer
		 * @see client/html/catalog/lists/attrid-default
		 * @see client/html/catalog/lists/catid-default
		 * @see client/html/catalog/lists/supid-default
		 * @see client/html/catalog/lists/domains
		 * @see client/html/catalog/lists/levels
		 * @see client/html/catalog/lists/sort
		 * @see client/html/catalog/lists/size
		 * @see client/html/catalog/lists/instock
		 */
		$pages = $config->get( 'client/html/catalog/lists/pages', 100 );

		/** client/html/catalog/lists/size
		 * The number of products shown in a list page
		 *
		 * Limits the number of products that are shown in the list pages to the
		 * given value. If more products are available, the products are split
		 * into bunches which will be shown on their own list page. The user is
		 * able to move to the next page (or previous one if it's not the first)
		 * to display the next (or previous) products.
		 *
		 * The value must be an integer number from 1 to 100. Negative values as
		 * well as values above 100 are not allowed. The value can be overwritten
		 * per request if the "l_size" parameter is part of the URL.
		 *
		 * @param integer Number of products
		 * @since 2014.03
		 * @category User
		 * @category Developer
		 * @see client/html/catalog/lists/attrid-default
		 * @see client/html/catalog/lists/catid-default
		 * @see client/html/catalog/lists/supid-default
		 * @see client/html/catalog/lists/domains
		 * @see client/html/catalog/lists/levels
		 * @see client/html/catalog/lists/sort
		 * @see client/html/catalog/lists/pages
		 * @see client/html/catalog/lists/instock
		 */
		$size = $config->get( 'client/html/catalog/lists/size', 48 );

		/** client/html/catalog/lists/levels
		 * Include products of sub-categories in the product list of the current category
		 *
		 * Sometimes it may be useful to show products of sub-categories in the
		 * current category product list, e.g. if the current category contains
		 * no products at all or if there are only a few products in all categories.
		 *
		 * Possible constant values for this setting are:
		 *
		 * * 1 : Only products from the current category
		 * * 2 : Products from the current category and the direct child categories
		 * * 3 : Products from the current category and the whole category sub-tree
		 *
		 * Caution: Please keep in mind that displaying products of sub-categories
		 * can slow down your shop, especially if it contains more than a few
		 * products! You have no real control over the positions of the products
		 * in the result list too because all products from different categories
		 * with the same position value are placed randomly.
		 *
		 * Usually, a better way is to associate products to all categories they
		 * should be listed in. This can be done manually if there are only a few
		 * ones or during the product import automatically.
		 *
		 * @param integer Tree level constant
		 * @since 2015.11
		 * @category Developer
		 * @see client/html/catalog/lists/attrid-default
		 * @see client/html/catalog/lists/catid-default
		 * @see client/html/catalog/lists/supid-default
		 * @see client/html/catalog/lists/domains
		 * @see client/html/catalog/lists/size
		 * @see client/html/catalog/lists/sort
		 * @see client/html/catalog/lists/pages
		 * @see client/html/catalog/lists/instock
		 */
		$level = $config->get( 'client/html/catalog/lists/levels', \Aimeos\MW\Tree\Manager\Base::LEVEL_ONE );


		/** client/html/catalog/lists/attrid-default
		 * Additional attribute IDs used to limit search results
		 *
		 * Using this setting, products result lists can be limited by additional
		 * attributes. Then, only products which have associated the configured
		 * attribute IDs will be returned and shown in the frontend. The value
		 * can be either a single attribute ID or a list of attribute IDs.
		 *
		 * @param array|string Attribute ID or IDs
		 * @since 2021.10
		 * @category User
		 * @category Developer
		 * @see client/html/catalog/lists/sort
		 * @see client/html/catalog/lists/size
		 * @see client/html/catalog/lists/domains
		 * @see client/html/catalog/lists/levels
		 * @see client/html/catalog/lists/instock
		 * @see client/html/catalog/lists/catid-default
		 * @see client/html/catalog/lists/supid-default
		 * @see client/html/catalog/detail/prodid-default
		 */
		$attrids = $config->get( 'client/html/catalog/lists/attrid-default' );
		$attrids = $attrids != null && is_scalar( $attrids ) ? explode( ',', $attrids ) : $attrids; // workaround for TYPO3

		/** client/html/catalog/lists/catid-default
		 * The default category ID used if none is given as parameter
		 *
		 * If users view a product list page without a category ID in the
		 * parameter list, the first found products are displayed with a
		 * random order. You can circumvent this by configuring a default
		 * category ID that should be used in this case (the ID of the root
		 * category is best for this). In most cases you can set this value
		 * via the administration interface of the shop application.
		 *
		 * @param array|string Category ID or IDs
		 * @since 2014.03
		 * @category User
		 * @category Developer
		 * @see client/html/catalog/lists/sort
		 * @see client/html/catalog/lists/size
		 * @see client/html/catalog/lists/domains
		 * @see client/html/catalog/lists/levels
		 * @see client/html/catalog/lists/attrid-default
		 * @see client/html/catalog/detail/prodid-default
		 * @see client/html/catalog/lists/supid-default
		 * @see client/html/catalog/lists/instock
		 */
		$catids = $view->param( 'f_catid', $config->get( 'client/html/catalog/lists/catid-default' ) );
		$catids = $catids != null && is_scalar( $catids ) ? explode( ',', $catids ) : $catids; // workaround for TYPO3

		/** client/html/catalog/lists/supid-default
		 * The default supplier ID used if none is given as parameter
		 *
		 * Products in the list page can be limited to one or more suppliers.
		 * By default, the products are not limited to any supplier until one or
		 * more supplier IDs are passed in the URL using the f_supid parameter.
		 * You can also configure the default supplier IDs for limiting the
		 * products if no IDs are passed in the URL using this configuration.
		 *
		 * @param array|string Supplier ID or IDs
		 * @since 2021.01
		 * @category User
		 * @category Developer
		 * @see client/html/catalog/lists/sort
		 * @see client/html/catalog/lists/size
		 * @see client/html/catalog/lists/domains
		 * @see client/html/catalog/lists/levels
		 * @see client/html/catalog/lists/attrid-default
		 * @see client/html/catalog/lists/catid-default
		 * @see client/html/catalog/detail/prodid-default
		 * @see client/html/catalog/lists/instock
		 */
		$supids = $view->param( 'f_supid', $config->get( 'client/html/catalog/lists/supid-default' ) );
		$supids = $supids != null && is_scalar( $supids ) ? explode( ',', $supids ) : $supids; // workaround for TYPO3

		/** client/html/catalog/lists/sort
		 * Default sorting of product list if no other sorting is given by parameter
		 *
		 * Configures the standard sorting of products in list views. This sorting is used
		 * as long as it's not overwritten by an URL parameter. Except "relevance", all
		 * other sort codes can be prefixed by a "-" (minus) sign to sort the products in
		 * a descending order. By default, the sorting is ascending.
		 *
		 * @param string Sort code "relevance", "name", "-name", "price", "-price", "ctime" or "-ctime"
		 * @since 2018.07
		 * @category User
		 * @category Developer
		 * @see client/html/catalog/lists/attrid-default
		 * @see client/html/catalog/lists/catid-default
		 * @see client/html/catalog/lists/supid-default
		 * @see client/html/catalog/lists/domains
		 * @see client/html/catalog/lists/levels
		 * @see client/html/catalog/lists/size
		 * @see client/html/catalog/lists/instock
		 */
		$sort = $view->param( 'f_sort', $config->get( 'client/html/catalog/lists/sort', 'relevance' ) );
		$size = min( max( $view->param( 'l_size', $size ), 1 ), 100 );
		$page = min( max( $view->param( 'l_page', 1 ), 1 ), $pages );

		if( $catids != null )
		{
			$controller = \Aimeos\Controller\Frontend::create( $context, 'catalog' )->uses( $domains );
			$listCatPath = $controller->getPath( is_array( $catids ) ? reset( $catids ) : $catids );

			if( ( $categoryItem = $listCatPath->last() ) !== null ) {
				$view->listCurrentCatItem = $categoryItem;
			}

			$view->listCatPath = $listCatPath;
			$this->addMetaItems( $listCatPath, $expire, $tags );
		}

		if( $view->config( 'client/html/catalog/lists/basket-add', false ) ) {
			$domains = array_merge_recursive( $domains, ['product' => ['default'], 'attribute' => ['variant', 'custom', 'config']] );
		}

		$cntl = \Aimeos\Controller\Frontend::create( $context, 'product' )
			->sort( $sort ) // prioritize user sorting over the sorting through relevance and category
			->text( $view->param( 'f_search' ) )
			->price( $view->param( 'f_price' ) )
			->category( $catids, 'default', $level )
			->supplier( $supids )
			->allOf( $attrids )
			->allOf( $view->param( 'f_attrid', [] ) )
			->oneOf( $view->param( 'f_optid', [] ) )
			->oneOf( $view->param( 'f_oneid', [] ) )
			->slice( ( $page - 1 ) * $size, $size )
			->uses( $domains );

		if( $inStock ) {
			$cntl->compare( '>', 'product.instock', 0 );
		}

		$products = $cntl->search( $total );


		// Delete cache when products are added or deleted even when in "tag-all" mode
		$this->addMetaItems( $products, $expire, $tags, ['product'] );


		$view->listProductItems = $products;
		$view->listProductSort = $sort;
		$view->listProductTotal = $total;

		$view->listPageSize = $size;
		$view->listPageCurr = $page;
		$view->listPagePrev = ( $page > 1 ? $page - 1 : 1 );
		$view->listPageLast = ( $total != 0 ? min( ceil( $total / $size ), $pages ) : 1 );
		$view->listPageNext = ( $page < $view->listPageLast ? $page + 1 : $view->listPageLast );

		$view->listParams = $this->getClientParams( map( $view->param() )->toArray() );

		return parent::data( $view, $tags, $expire );
	}
}
