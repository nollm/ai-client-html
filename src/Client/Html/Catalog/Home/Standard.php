<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2020-2022
 * @package Client
 * @subpackage Html
 */


namespace Aimeos\Client\Html\Catalog\Home;

/**
 * Implementation of catalog home section HTML clients for a configurable list of homes.
 *
 * @package Client
 * @subpackage Html
 */
class Standard
	extends \Aimeos\Client\Html\Catalog\Base
	implements \Aimeos\Client\Html\Common\Client\Factory\Iface
{
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
		/** client/html/catalog/home/cache
		 * Enables or disables caching only for the catalog home component
		 *
		 * Disable caching for components can be useful if you would have too much
		 * entries to cache or if the component contains non-cacheable parts that
		 * can't be replaced using the modify() method.
		 *
		 * @param boolean True to enable caching, false to disable
		 * @see client/html/catalog/detail/cache
		 * @see client/html/catalog/filter/cache
		 * @see client/html/catalog/stage/cache
		 * @see client/html/catalog/list/cache
		 */

		/** client/html/catalog/home
		 * All parameters defined for the catalog home component and its subparts
		 *
		 * Please refer to the single settings for details.
		 *
		 * @param array Associative list of name/value settings
		 * @see client/html/catalog#home
		 */
		$confkey = 'client/html/catalog/home';
		$config = $this->context()->config();

		if( $html = $this->cached( 'body', $uid, [], $confkey ) ) {
			return $this->modify( $html, $uid );
		}

		$template = $config->get( 'client/html/catalog/home/template-body', 'catalog/home/body' );
		$view = $this->view = $this->view ?? $this->object()->data( $this->view(), $this->tags, $this->expire );
		$html = $view->render( $template );

		return $this->cache( 'body', $uid, [], $confkey, $html, $this->tags, $this->expire );
	}


	/**
	 * Returns the HTML string for insertion into the header.
	 *
	 * @param string $uid Unique identifier for the output if the content is placed more than once on the same page
	 * @return string|null String including HTML tags for the header on error
	 */
	public function header( string $uid = '' ) : ?string
	{
		$confkey = 'client/html/catalog/home';
		$config = $this->context()->config();

		if( $html = $this->cached( 'header', $uid, [], $confkey ) ) {
			return $this->modify( $html, $uid );
		}

		$template = $config->get( 'client/html/catalog/home/template-header', 'catalog/home/header' );
		$view = $this->view = $this->view ?? $this->object()->data( $this->view(), $this->tags, $this->expire );
		$html = $view->render( $template );

		return $this->cache( 'header', $uid, [], $confkey, $html, $this->tags, $this->expire );
	}


	/**
	 * Sets the necessary parameter values in the view.
	 *
	 * @param \Aimeos\Base\View\Iface $view The view object which generates the HTML output
	 * @param array &$tags Result array for the list of tags that are associated to the output
	 * @param string|null &$expire Result variable for the expiration date of the output (null for no expiry)
	 * @return \Aimeos\Base\View\Iface Modified view object
	 */
	public function data( \Aimeos\Base\View\Iface $view, array &$tags = [], string &$expire = null ) : \Aimeos\Base\View\Iface
	{
		$context = $this->context();
		$config = $context->config();

		$tree = \Aimeos\Controller\Frontend::create( $context, 'catalog' )->uses( $this->domains() )
			->getTree( \Aimeos\Controller\Frontend\Catalog\Iface::LIST );


		$articles = map();
		$products = $tree->getRefItems( 'product', null, 'promotion' );

		foreach( $tree->getChildren() as $child ) {
			$products->union( $child->getRefItems( 'product', null, 'promotion' ) );
		}

		if( $config->get( 'client/html/catalog/home/basket-add', false ) )
		{
			foreach( $products as $product )
			{
				if( $product->getType() === 'select' ) {
					$articles->union( $product->getRefItems( 'product', 'default', 'default' ) );
				}
			}
		}

		/** client/html/catalog/home/stock/enable
		 * Enables or disables displaying product stock levels in product list views
		 *
		 * This configuration option allows shop owners to display product
		 * stock levels for each product in list views or to disable
		 * fetching product stock information.
		 *
		 * The stock information is fetched via AJAX and inserted via Javascript.
		 * This allows to cache product items by leaving out such highly
		 * dynamic content like stock levels which changes with each order.
		 *
		 * @param boolean Value of "1" to display stock levels, "0" to disable displaying them
		 * @since 2020.10
		 * @see client/html/catalog/detail/stock/enable
		 * @see client/html/catalog/stock/url/target
		 * @see client/html/catalog/stock/url/controller
		 * @see client/html/catalog/stock/url/action
		 * @see client/html/catalog/stock/url/config
		 */
		if( !$products->isEmpty() && (bool) $config->get( 'client/html/catalog/home/stock/enable', true ) === true ) {
			$view->homeStockUrl = $this->getStockUrl( $view, $products->union( $articles ) );
		}

		// Delete cache when products are added or deleted even when in "tag-all" mode
		$this->addMetaItems( $tree, $expire, $tags, ['catalog', 'product'] );

		$view->homeTree = $tree;

		return parent::data( $view, $tags, $expire );
	}


	/**
	 * Returns the data domains fetched along with the products
	 *
	 * @return array List of domain names
	 */
	protected function domains() : array
	{
		$context = $this->context();
		$config = $context->config();

		/** client/html/catalog/home/domains
		 * A list of domain names whose items should be available in the catalog home view template
		 *
		 * The templates rendering home lists usually add the images, prices
		 * and texts associated to each home item. If you want to display additional
		 * content like the home attributes, you can configure your own list of
		 * domains (attribute, media, price, home, text, etc. are domains)
		 * whose items are fetched from the storage. Please keep in mind that
		 * the more domains you add to the configuration, the more time is required
		 * for fetching the content!
		 *
		 * This configuration option overwrites the "client/html/catalog/domains"
		 * option that allows to configure the domain names of the items fetched
		 * for all catalog related data.
		 *
		 * @param array List of domain names
		 * @since 2020.10
		 * @see client/html/catalog/domains
		 * @see client/html/catalog/detail/domains
		 * @see client/html/catalog/stage/domains
		 * @see client/html/catalog/lists/domains
		 */
		$domains = ['catalog', 'media', 'media/property', 'price', 'supplier', 'text', 'product' => ['promotion']];
		$domains = $config->get( 'client/html/catalog/domains', $domains );
		$domains = $config->get( 'client/html/catalog/home/domains', $domains );

		/** client/html/catalog/home/basket-add
		 * Display the "add to basket" button for each product item in the catalog home component
		 *
		 * Enables the button for adding products to the basket for the listed products.
		 * This works for all type of products, even for selection products with product
		 * variants and product bundles. By default, also optional attributes are
		 * displayed if they have been associated to a product.
		 *
		 * @param boolean True to display the button, false to hide it
		 * @since 2020.10
		 * @see client/html/catalog/lists/basket-add
		 * @see client/html/catalog/detail/basket-add
		 * @see client/html/basket/related/basket-add
		 * @see client/html/catalog/product/basket-add
		 */
		if( $config->get( 'client/html/catalog/home/basket-add', false ) ) {
			$domains = array_merge_recursive( $domains, ['attribute' => ['variant', 'custom', 'config']] );
		}

		return $domains;
	}


	/** client/html/catalog/home/template-body
	 * Relative path to the HTML body template of the catalog home client.
	 *
	 * The template file contains the HTML code and processing instructions
	 * to generate the result shown in the body of the frontend. The
	 * configuration string is the path to the template file relative
	 * to the templates directory (usually in client/html/templates).
	 *
	 * You can overwrite the template file configuration in extensions and
	 * provide alternative templates. These alternative templates should be
	 * named like the default one but suffixed by
	 * an unique name. You may use the name of your project for this. If
	 * you've implemented an alternative client class as well, it
	 * should be suffixed by the name of the new class.
	 *
	 * @param string Relative path to the template creating code for the HTML page body
	 * @since 2020.10
	 * @see client/html/catalog/home/template-header
	 */

	/** client/html/catalog/home/template-header
	 * Relative path to the HTML header template of the catalog home client.
	 *
	 * The template file contains the HTML code and processing instructions
	 * to generate the HTML code that is inserted into the HTML page header
	 * of the rendered page in the frontend. The configuration string is the
	 * path to the template file relative to the templates directory (usually
	 * in client/html/templates).
	 *
	 * You can overwrite the template file configuration in extensions and
	 * provide alternative templates. These alternative templates should be
	 * named like the default one but suffixed by
	 * an unique name. You may use the name of your project for this. If
	 * you've implemented an alternative client class as well, it
	 * should be suffixed by the name of the new class.
	 *
	 * @param string Relative path to the template creating code for the HTML page head
	 * @since 2020.10
	 * @see client/html/catalog/home/template-body
	 */
}
