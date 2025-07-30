<?php
/**
 * REST: RESTRegistration class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\VisualBuilder\REST;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Framework\Route\RESTRoute;
use ET\Builder\Packages\GlobalData\GlobalDataController;
use ET\Builder\Packages\GlobalData\GlobalPresetController;
use ET\Builder\Packages\Module\Layout\Components\DynamicContent\DynamicContentOptionsController;
use ET\Builder\Packages\Module\Layout\Components\DynamicData\DynamicDataController;
use ET\Builder\Packages\Module\Options\Conditions\RESTControllers\AuthorConditionRESTController;
use ET\Builder\Packages\Module\Options\Conditions\RESTControllers\ConditionsStatusRESTController;
use ET\Builder\Packages\Module\Options\Conditions\RESTControllers\PostMetaFieldsRESTController;
use ET\Builder\Packages\Module\Options\Conditions\RESTControllers\PostsRESTController;
use ET\Builder\Packages\Module\Options\Conditions\RESTControllers\CategoriesRESTController;
use ET\Builder\Packages\Module\Options\Conditions\RESTControllers\TagsRESTController;
use ET\Builder\Packages\Module\Options\Conditions\RESTControllers\UserRoleConditionRESTController;
use ET\Builder\Packages\Module\Options\Conditions\RESTControllers\PostTypeConditionRESTController;
use ET\Builder\Packages\Module\Options\Loop\QueryType\QueryTypeController;
use ET\Builder\Packages\Module\Options\Loop\QueryResults\QueryResultsController;
use ET\Builder\Packages\Module\Options\Loop\QueryOrderBy\QueryOrderByController;
use ET\Builder\Packages\Module\Options\WooCommerceSelectProduct\WooCommerceSelectProductController;
use ET\Builder\Packages\ModuleLibrary\Audio\AudioController;
use ET\Builder\Packages\ModuleLibrary\Blog\BlogController;
use ET\Builder\Packages\ModuleLibrary\Blog\PostTypeController;
use ET\Builder\Packages\ModuleLibrary\FilterablePortfolio\FilterablePortfolioController;
use ET\Builder\Packages\ModuleLibrary\FullwidthPortfolio\FullwidthPortfolioController;
use ET\Builder\Packages\ModuleLibrary\FullwidthMenu\FullwidthMenuHTMLController;
use ET\Builder\Packages\ModuleLibrary\FullwidthMenu\FullwidthMenuTermsController;
use ET\Builder\Packages\ModuleLibrary\Gallery\GalleryController;
use ET\Builder\Packages\ModuleLibrary\Menu\MenuHTMLController;
use ET\Builder\Packages\ModuleLibrary\Menu\MenuTermsController;
use ET\Builder\Packages\ModuleLibrary\Portfolio\PortfolioController;
use ET\Builder\Packages\ModuleLibrary\PostNavigation\PostNavigationController;
use ET\Builder\Packages\ModuleLibrary\Sidebar\SidebarController;
use ET\Builder\Packages\ModuleLibrary\Video\VideoCoverController;
use ET\Builder\Packages\ModuleLibrary\Video\VideoHTMLController;
use ET\Builder\Packages\ModuleLibrary\Video\VideoThumbnailController;
use ET\Builder\Packages\ModuleLibrary\VideoSlider\VideoSlideThumbnailController;
use ET\Builder\Packages\ShortcodeModule\Module\ShortcodeModuleBatchController;
use ET\Builder\Packages\ShortcodeModule\Module\ShortcodeModuleController;
use ET\Builder\VisualBuilder\REST\AILayoutSaveDefault\AILayoutSaveDefaultController;
use ET\Builder\VisualBuilder\REST\Breakpoint\BreakpointController;
use ET\Builder\VisualBuilder\REST\CloudApp\CloudAppController;
use ET\Builder\VisualBuilder\REST\CustomFont\CustomFontController;
use ET\Builder\VisualBuilder\REST\DiviLibrary\DiviLibraryController;
use ET\Builder\VisualBuilder\REST\Portability\PortabilityController;
use ET\Builder\VisualBuilder\REST\SyncToServer\SyncToServerController;
use ET\Builder\VisualBuilder\REST\UpdateDefaultColors\UpdateDefaultColorsController;
use ET\Builder\VisualBuilder\REST\SpamProtectionService\SpamProtectionServiceController;
use ET\Builder\VisualBuilder\REST\EmailService\EmailServiceController;
use ET\Builder\VisualBuilder\SettingsData\SettingsDataController;
use ET\Builder\VisualBuilder\REST\ModuleRender\ModuleRenderController;
use ET\Builder\Packages\ModuleLibrary\WooCommerce\Breadcrumb\WooCommerceBreadcrumbController;
use ET\Builder\Packages\ModuleLibrary\WooCommerce\CartNotice\WooCommerceCartNoticeController;
use ET\Builder\Packages\ModuleLibrary\WooCommerce\ProductAddToCart\WooCommerceProductAddToCartController;
use ET\Builder\Packages\ModuleLibrary\WooCommerce\ProductAdditionalInfo\WooCommerceProductAdditionalInfoController;
use ET\Builder\Packages\ModuleLibrary\WooCommerce\ProductDescription\WooCommerceProductDescriptionController;
use ET\Builder\Packages\ModuleLibrary\WooCommerce\ProductGallery\WooCommerceProductGalleryController;
use ET\Builder\Packages\ModuleLibrary\WooCommerce\ProductImages\WooCommerceProductImagesController;
use ET\Builder\Packages\ModuleLibrary\WooCommerce\ProductMeta\WooCommerceProductMetaController;
use ET\Builder\Packages\ModuleLibrary\WooCommerce\ProductPrice\WooCommerceProductPriceController;
use ET\Builder\Packages\ModuleLibrary\WooCommerce\ProductRating\WooCommerceProductRatingController;
use ET\Builder\Packages\ModuleLibrary\WooCommerce\ProductReviews\WooCommerceProductReviewsController;
use ET\Builder\Packages\ModuleLibrary\WooCommerce\ProductStock\WooCommerceProductStockController;
use ET\Builder\Packages\ModuleLibrary\WooCommerce\ProductTabs\WooCommerceProductTabsController;
use ET\Builder\Packages\ModuleLibrary\WooCommerce\ProductTitle\WooCommerceProductTitleController;
use ET\Builder\Packages\ModuleLibrary\WooCommerce\ProductUpsell\WooCommerceProductUpsellController;
use ET\Builder\Packages\ModuleLibrary\WooCommerce\RelatedProducts\WooCommerceRelatedProductsController;


/**
 * `RESTRegistration` class registers REST API endpoints upon calling `load()`, These endpoints are used in different parts of Divi.
 *
 * This is a dependency class and can be used as a dependency for `DependencyTree`.
 *
 * @since ??
 */
class RESTRegistration implements DependencyInterface {

	/**
	 * Loads and registers all REST routes.
	 *
	 * @since ??
	 *
	 * @return void
	 */
	public function load(): void {

		$route = new RESTRoute( 'divi/v1' );

		/**
		 * `/settings-data` REST routes for getting `divi/settings`' state.
		 */
		$route
			->prefix( '/settings-data' )
			->group(
				function ( $router ) {
					$router->get( '/after-app-load', SettingsDataController::class );
				}
			);

		/**
		 * `/breakpoints` REST routes for managing breakpoints.
		 */
		$route
			->prefix( '/breakpoints' )
			->group(
				function ( $router ) {

					/**
					 * Update breakpoints
					 */
					$router->post(
						'/update',
						[
							'args'                => [ BreakpointController::class, 'update_args' ],
							'callback'            => [ BreakpointController::class, 'update' ],
							'permission_callback' => [ BreakpointController::class, 'update_permission' ],
						]
					);
				}
			);

		/**
		 * `/module-data` REST routes.
		 */
		$route
			->prefix( '/module-data' )
			->group(
				function ( $router ) {

					if ( et_is_woocommerce_plugin_active() && et_get_experiment_flag( 'wooProductPageModules' ) ) {
						/**
						 * WooCommerce Breadcrumbs Module.
						 */
						$router->post(
							'/woocommerce/breadcrumb/html',
							[
								'args'                => [ WooCommerceBreadcrumbController::class, 'index_args' ],
								'callback'            => [ WooCommerceBreadcrumbController::class, 'index' ],
								'permission_callback' => [ WooCommerceBreadcrumbController::class, 'index_permission' ],
							]
						);

						/**
						 * WooCommerce Cart Notice Module.
						 */
						$router->post(
							'/woocommerce/cart-notice/html',
							[
								'args'                => [ WooCommerceCartNoticeController::class, 'index_args' ],
								'callback'            => [ WooCommerceCartNoticeController::class, 'index' ],
								'permission_callback' => [ WooCommerceCartNoticeController::class, 'index_permission' ],
							]
						);

						/**
						 * WooCommerce Product Add to Cart Module.
						 */
						$router->post(
							'/woocommerce/product-add-to-cart/html',
							[
								'args'                => [ WooCommerceProductAddToCartController::class, 'index_args' ],
								'callback'            => [ WooCommerceProductAddToCartController::class, 'index' ],
								'permission_callback' => [ WooCommerceProductAddToCartController::class, 'index_permission' ],
							]
						);

						/**
						 * WooCommerce Product Additional Info Module.
						 */
						$router->post(
							'/woocommerce/product-additional-info/html',
							[
								'args'                => [ WooCommerceProductAdditionalInfoController::class, 'index_args' ],
								'callback'            => [ WooCommerceProductAdditionalInfoController::class, 'index' ],
								'permission_callback' => [ WooCommerceProductAdditionalInfoController::class, 'index_permission' ],
							]
						);

						/**
						 * Woo Product Description Module
						 */
						$router->post(
							'/woocommerce/product-description/html',
							[
								'args'                => [ WooCommerceProductDescriptionController::class, 'index_args' ],
								'callback'            => [ WooCommerceProductDescriptionController::class, 'index' ],
								'permission_callback' => [ WooCommerceProductDescriptionController::class, 'index_permission' ],
							]
						);

						/**
						 * WooCommerce Product Gallery Module.
						 */
						$router->post(
							'/woocommerce/product-gallery/html',
							[
								'args'                => [ WooCommerceProductGalleryController::class, 'index_args' ],
								'callback'            => [ WooCommerceProductGalleryController::class, 'index' ],
								'permission_callback' => [ WooCommerceProductGalleryController::class, 'index_permission' ],
							]
						);

						/**
						 * WooCommerce Product Images Module.
						 */
						$router->post(
							'/woocommerce/product-images/html',
							[
								'args'                => [ WooCommerceProductImagesController::class, 'index_args' ],
								'callback'            => [ WooCommerceProductImagesController::class, 'index' ],
								'permission_callback' => [ WooCommerceProductImagesController::class, 'index_permission' ],
							]
						);

						/**
						 * WooCommerce Product Meta Module.
						 */
						$router->post(
							'/woocommerce/product-meta/html',
							[
								'args'                => [ WooCommerceProductMetaController::class, 'index_args' ],
								'callback'            => [ WooCommerceProductMetaController::class, 'index' ],
								'permission_callback' => [ WooCommerceProductMetaController::class, 'index_permission' ],
							]
						);

						/**
						 * WooCommerce Product Price Module.
						 */
						$router->post(
							'/woocommerce/product-price/html',
							[
								'args'                => [ WooCommerceProductPriceController::class, 'index_args' ],
								'callback'            => [ WooCommerceProductPriceController::class, 'index' ],
								'permission_callback' => [ WooCommerceProductPriceController::class, 'index_permission' ],
							]
						);

						/**
						 * WooCommerce Product Rating Module.
						 */
						$router->post(
							'/woocommerce/product-rating/html',
							[
								'args'                => [ WooCommerceProductRatingController::class, 'index_args' ],
								'callback'            => [ WooCommerceProductRatingController::class, 'index' ],
								'permission_callback' => [ WooCommerceProductRatingController::class, 'index_permission' ],
							]
						);

						/**
						 * WooCommerce Product Reviews Module.
						 */
						$router->post(
							'/woocommerce/product-reviews/html',
							[
								'args'                => [ WooCommerceProductReviewsController::class, 'index_args' ],
								'callback'            => [ WooCommerceProductReviewsController::class, 'index' ],
								'permission_callback' => [ WooCommerceProductReviewsController::class, 'index_permission' ],
							]
						);

						/**
						 * WooCommerce Product Stock Module.
						 */
						$router->post(
							'/woocommerce/product-stock/html',
							[
								'args'                => [ WooCommerceProductStockController::class, 'index_args' ],
								'callback'            => [ WooCommerceProductStockController::class, 'index' ],
								'permission_callback' => [ WooCommerceProductStockController::class, 'index_permission' ],
							]
						);

						/**
						 * WooCommerce Product Tabs Module.
						 */
						$router->post(
							'/woocommerce/product-tabs/html',
							[
								'args'                => [ WooCommerceProductTabsController::class, 'index_args' ],
								'callback'            => [ WooCommerceProductTabsController::class, 'index' ],
								'permission_callback' => [ WooCommerceProductTabsController::class, 'index_permission' ],
							]
						);

						/**
						 * WooCommerce Product Title Module.
						 */
						$router->post(
							'/woocommerce/product-title/html',
							[
								'args'                => [ WooCommerceProductTitleController::class, 'index_args' ],
								'callback'            => [ WooCommerceProductTitleController::class, 'index' ],
								'permission_callback' => [ WooCommerceProductTitleController::class, 'index_permission' ],
							]
						);

						/**
						 * WooCommerce Product Upsell Module.
						 */
						$router->post(
							'/woocommerce/product-upsell/html',
							[
								'args'                => [ WooCommerceProductUpsellController::class, 'index_args' ],
								'callback'            => [ WooCommerceProductUpsellController::class, 'index' ],
								'permission_callback' => [ WooCommerceProductUpsellController::class, 'index_permission' ],
							]
						);

						/**
						 * WooCommerce Related Products Module.
						 */
						$router->post(
							'/woocommerce/related-products/html',
							[
								'args'                => [ WooCommerceRelatedProductsController::class, 'index_args' ],
								'callback'            => [ WooCommerceRelatedProductsController::class, 'index' ],
								'permission_callback' => [ WooCommerceRelatedProductsController::class, 'index_permission' ],
							]
						);
					}

					/**
					 * Gallery Module
					 */
					$router->get( '/gallery/attachments', GalleryController::class );

					/**
					 * Video module.
					 */
					$router->get( '/video/html', VideoHTMLController::class );
					$router->get( '/video/thumbnail', VideoThumbnailController::class );
					$router->get( '/video/cover', VideoCoverController::class );

					/**
					 * Video Slider module.
					 */
					$router->get( '/video-slide/thumbnail', VideoSlideThumbnailController::class );

					/**
					 * Audio module.
					 */
					$router->get( '/audio/html', AudioController::class );

					/**
					 * Menu module.
					 */
					$router->get( '/menu/html', MenuHTMLController::class );
					$router->get( '/menu/terms', MenuTermsController::class );

					/**
					 * Fullwidth Menu module.
					 */
					$router->get( '/fullwidth-menu/html', FullwidthMenuHTMLController::class );
					$router->get( '/fullwidth-menu/terms', FullwidthMenuTermsController::class );

					/**
					 * Portfolio module.
					 */
					$router->get( '/portfolio/posts', PortfolioController::class );

					/**
					 * Filterable Portfolio module.
					 */
					$router->get( '/filterable-portfolio/posts', FilterablePortfolioController::class );

					/**
					 * Post Navigation module.
					 */
					$router->get( '/post-navigation/navigation', PostNavigationController::class );

					/**
					 * Fullwidth Portfolio module.
					 */
					$router->get( '/fullwidth-portfolio/posts', FullwidthPortfolioController::class );

					/**
					 * Shortcode module.
					 *
					 * TODO feat(D5, Shortcode Module): Move Shortcode module REST API route registration
					 * to ShortcodeModule package.
					 *
					 * @see https://github.com/elegantthemes/Divi/issues/32183
					 */
					$router->get( '/shortcode-module/html', ShortcodeModuleController::class );
					$router->post(
						'/shortcode-module/html/batch',
						[
							'args'                => [ ShortcodeModuleBatchController::class, 'index_args' ],
							'callback'            => [ ShortcodeModuleBatchController::class, 'index' ],
							'permission_callback' => [ ShortcodeModuleBatchController::class, 'index_permission' ],
						]
					);

					/**
					 * Sidebar module.
					 */
					$router->get( '/sidebar/html', SidebarController::class );

					/**
					 * Blog module.
					 */
					$router->get( '/blog/posts', BlogController::class );

					/**
					 * Blog module.
					 */
					$router->get( '/blog/types', PostTypeController::class );
				}
			);

		/**
		 * `/dynamic-content` REST routes.
		 */
		$route
			->prefix( '/dynamic-content' )
			->group(
				function ( $router ) {

					/**
					 * Dynamic Content Options.
					 */
					$router->get( '/options', DynamicContentOptionsController::class );
				}
			);

		/**
		 * `/option-data` REST routes.
		 */
		$route
		->prefix( '/option-data' )
		->group(
			function ( $router ) {

				/**
				 * Conditions option.
				 */
				$router->post( '/conditions/status', ConditionsStatusRESTController::class );
				$router->get( '/conditions/posts', PostsRESTController::class );
				$router->get( '/conditions/post-meta-fields', PostMetaFieldsRESTController::class );
				$router->get( '/conditions/user-role', UserRoleConditionRESTController::class );
				$router->get( '/conditions/author', AuthorConditionRESTController::class );
				$router->get( '/conditions/post-type', PostTypeConditionRESTController::class );
				$router->get( '/conditions/categories', CategoriesRESTController::class );
				$router->get( '/conditions/tags', TagsRESTController::class );

			}
		);

		/**
		 * `/loop/query-types` REST routes.
		 */
		$route->prefix( '/loop' )
			->group(
				function ( $router ) {

					/**
					 * Query Type Options.
					 */
					$router->get(
						'/query-types',
						[
							'args'                => [ QueryTypeController::class, 'index_args' ],
							'callback'            => [ QueryTypeController::class, 'index' ],
							'permission_callback' => [ QueryTypeController::class, 'index_permission' ],
						]
					);

					/**
					 * Query Result Options.
					 */
					$router->get(
						'/query-results',
						[
							'args'                => [ QueryResultsController::class, 'index_args' ],
							'callback'            => [ QueryResultsController::class, 'index' ],
							'permission_callback' => [ QueryResultsController::class, 'index_permission' ],
						]
					);
					/**
					 * Query Order By.
					 */
					$router->get(
						'/query-order-by',
						[
							'args'                => [ QueryOrderByController::class, 'index_args' ],
							'callback'            => [ QueryOrderByController::class, 'index' ],
							'permission_callback' => [ QueryOrderByController::class, 'index_permission' ],
						]
					);
				}
			);

		/**
		 * `/divi-library` REST routes.
		 */
		$route->post(
			'/divi-library',
			[
				'args'                => [ DiviLibraryController::class, 'index_args' ],
				'callback'            => [ DiviLibraryController::class, 'index' ],
				'permission_callback' => [ DiviLibraryController::class, 'index_permission' ],
			]
		);

		$route->post(
			'/divi-library/cloud-token',
			[
				'args'                => [ DiviLibraryController::class, 'get_token_args' ],
				'callback'            => [ DiviLibraryController::class, 'get_token' ],
				'permission_callback' => [ DiviLibraryController::class, 'get_token_permission' ],
			]
		);

		$route->post(
			'/divi-library/item',
			[
				'args'                => [ DiviLibraryController::class, 'show_args' ],
				'callback'            => [ DiviLibraryController::class, 'show' ],
				'permission_callback' => [ DiviLibraryController::class, 'show_permission' ],
			]
		);

		$route->post(
			'/divi-library/update-terms',
			[
				'args'                => [ DiviLibraryController::class, 'update_args' ],
				'callback'            => [ DiviLibraryController::class, 'update' ],
				'permission_callback' => [ DiviLibraryController::class, 'update_permission' ],
			]
		);

		$route->post(
			'/divi-library/update-item',
			[
				'args'                => [ DiviLibraryController::class, 'update_item_args' ],
				'callback'            => [ DiviLibraryController::class, 'update_item' ],
				'permission_callback' => [ DiviLibraryController::class, 'update_item_permission' ],
			]
		);

		$route->post(
			'/divi-library/convert-item',
			[
				'args'                => [ DiviLibraryController::class, 'convert_item_args' ],
				'callback'            => [ DiviLibraryController::class, 'convert_item' ],
				'permission_callback' => [ DiviLibraryController::class, 'convert_item_permission' ],
			]
		);

		$route->post(
			'/divi-library/split-item',
			[
				'args'                => [ DiviLibraryController::class, 'split_item_args' ],
				'callback'            => [ DiviLibraryController::class, 'split_item' ],
				'permission_callback' => [ DiviLibraryController::class, 'split_item_permission' ],
			]
		);

		$route->post(
			'/divi-library/load',
			[
				'args'                => [ DiviLibraryController::class, 'load_args' ],
				'callback'            => [ DiviLibraryController::class, 'load' ],
				'permission_callback' => [ DiviLibraryController::class, 'load_permission' ],
			]
		);
		$route->post(
			'/divi-library/create-item',
			[
				'args'                => [ DiviLibraryController::class, 'create_item_args' ],
				'callback'            => [ DiviLibraryController::class, 'create_item' ],
				'permission_callback' => [ DiviLibraryController::class, 'create_item_permission' ],
			]
		);
		$route->post(
			'/divi-library/save',
			[
				'args'                => [ DiviLibraryController::class, 'save_args' ],
				'callback'            => [ DiviLibraryController::class, 'save' ],
				'permission_callback' => [ DiviLibraryController::class, 'save_permission' ],
			]
		);
		$route->post(
			'/divi-library/upload-image',
			[
				'args'                => [ DiviLibraryController::class, 'upload_image_args' ],
				'callback'            => [ DiviLibraryController::class, 'upload_image' ],
				'permission_callback' => [ DiviLibraryController::class, 'upload_image_permission' ],
			]
		);
		$route->post(
			'/divi-library/item-location',
			[
				'args'                => [ DiviLibraryController::class, 'item_location_args' ],
				'callback'            => [ DiviLibraryController::class, 'item_location' ],
				'permission_callback' => [ DiviLibraryController::class, 'item_location_permission' ],
			]
		);

		/**
		 * `/custom-font` REST routes.
		 */
		$route->post(
			'/custom-font/add',
			[
				'args'                => [ CustomFontController::class, 'store_args' ],
				'callback'            => [ CustomFontController::class, 'store' ],
				'permission_callback' => [ CustomFontController::class, 'store_permission' ],
			]
		);
		$route->post(
			'/custom-font/remove',
			[
				'args'                => [ CustomFontController::class, 'destroy_args' ],
				'callback'            => [ CustomFontController::class, 'destroy' ],
				'permission_callback' => [ CustomFontController::class, 'destroy_permission' ],
			]
		);

		/**
		 * `/portability` REST routes.
		 */
		$route->post(
			'/portability/export',
			[
				'args'                => [ PortabilityController::class, 'show_args' ],
				'callback'            => [ PortabilityController::class, 'show' ],
				'permission_callback' => [ PortabilityController::class, 'show_permission' ],
			]
		);

		$route->post(
			'/portability/import',
			[
				'args'                => [ PortabilityController::class, 'store_args' ],
				'callback'            => [ PortabilityController::class, 'store' ],
				'permission_callback' => [ PortabilityController::class, 'store_permission' ],
			]
		);

		/**
		 * `/sync-to-server` REST routes.
		 */
		$route->post(
			'/sync-to-server',
			[
				'args'                => [ SyncToServerController::class, 'update_args' ],
				'callback'            => [ SyncToServerController::class, 'update' ],
				'permission_callback' => [ SyncToServerController::class, 'update_permission' ],
			]
		);

		/**
		 * `/ai_layout_save_defaults` REST routes.
		 */
		$route->post(
			'/ai_layout_save_defaults',
			[
				'args'                => [ AILayoutSaveDefaultController::class, 'update_args' ],
				'callback'            => [ AILayoutSaveDefaultController::class, 'update' ],
				'permission_callback' => [ AILayoutSaveDefaultController::class, 'update_permission' ],
			]
		);

		/**
		 * `/update-default-colors` REST routes.
		 */
		$route->post(
			'/update-default-colors',
			[
				'args'                => [ UpdateDefaultColorsController::class, 'update_args' ],
				'callback'            => [ UpdateDefaultColorsController::class, 'update' ],
				'permission_callback' => [ UpdateDefaultColorsController::class, 'update_permission' ],
			]
		);

		/**
		 * `/dynamic-data` REST routes.
		 */
		$route->post(
			'/dynamic-data',
			[
				'args'                => [ DynamicDataController::class, 'index_args' ],
				'callback'            => [ DynamicDataController::class, 'index' ],
				'permission_callback' => [ DynamicDataController::class, 'index_permission' ],
			]
		);

		/**
		 * `/update-account` REST routes.
		 */
		$route->post(
			'/update-account',
			[
				'args'                => [ CloudAppController::class, 'update_account_args' ],
				'callback'            => [ CloudAppController::class, 'update_account' ],
				'permission_callback' => [ CloudAppController::class, 'update_account_permission' ],
			]
		);

		/**
		 * `/spam-protection-provider` REST routes.
		 */
		$route
			->prefix( '/spam-protection-service' )
			->group(
				function ( $router ) {
					$router->post(
						'/create',
						[
							'args'                => [ SpamProtectionServiceController::class, 'create_args' ],
							'callback'            => [ SpamProtectionServiceController::class, 'create' ],
							'permission_callback' => [ SpamProtectionServiceController::class, 'create_permission' ],
						]
					);
					$router->post(
						'/delete',
						[
							'args'                => [ SpamProtectionServiceController::class, 'delete_args' ],
							'callback'            => [ SpamProtectionServiceController::class, 'delete' ],
							'permission_callback' => [ SpamProtectionServiceController::class, 'delete_permission' ],
						]
					);
				}
			);

		/**
		 * `/email-service` REST routes.
		 */
		$route
			->prefix( '/email-service' )
			->group(
				function ( $router ) {
					$router->post(
						'/create',
						[
							'args'                => [ EmailServiceController::class, 'create_args' ],
							'callback'            => [ EmailServiceController::class, 'create' ],
							'permission_callback' => [ EmailServiceController::class, 'create_permission' ],
						]
					);
					$router->post(
						'/read',
						[
							'args'                => [ EmailServiceController::class, 'read_args' ],
							'callback'            => [ EmailServiceController::class, 'read' ],
							'permission_callback' => [ EmailServiceController::class, 'read_permission' ],
						]
					);
					$router->post(
						'/delete',
						[
							'args'                => [ EmailServiceController::class, 'delete_args' ],
							'callback'            => [ EmailServiceController::class, 'delete' ],
							'permission_callback' => [ EmailServiceController::class, 'delete_permission' ],
						]
					);
				}
			);

		/**
		 * `/spam-protection-service` REST routes.
		 */
		$route
		->prefix( '/spam-protection-service' )
		->group(
			function ( $router ) {
				$router->post(
					'/create',
					[
						'args'                => [ SpamProtectionServiceController::class, 'create_args' ],
						'callback'            => [ SpamProtectionServiceController::class, 'create' ],
						'permission_callback' => [ SpamProtectionServiceController::class, 'create_permission' ],
					]
				);
				$router->post(
					'/delete',
					[
						'args'                => [ SpamProtectionServiceController::class, 'delete_args' ],
						'callback'            => [ SpamProtectionServiceController::class, 'delete' ],
						'permission_callback' => [ SpamProtectionServiceController::class, 'delete_permission' ],
					]
				);
			}
		);

		/**
		 * `/global-data/global-colors` REST routes.
		 */
		$route->post(
			'/global-data/global-colors',
			[
				'args'                => [ GlobalDataController::class, 'update_global_colors_args' ],
				'callback'            => [ GlobalDataController::class, 'update_global_colors' ],
				'permission_callback' => [ GlobalDataController::class, 'update_global_colors_permission' ],
			]
		);

		/**
		 * `/global-data/global-fonts` REST routes.
		 */
		$route->post(
			'/global-data/global-fonts',
			[
				'args'                => [ GlobalDataController::class, 'update_global_fonts_args' ],
				'callback'            => [ GlobalDataController::class, 'update_global_fonts' ],
				'permission_callback' => [ GlobalDataController::class, 'update_global_fonts_permission' ],
			]
		);

		/**
		 * `/global-data/global-variables` REST routes.
		 */
		$route->post(
			'/global-data/global-variables',
			[
				'args'                => [ GlobalDataController::class, 'update_global_variables_args' ],
				'callback'            => [ GlobalDataController::class, 'update_global_variables' ],
				'permission_callback' => [ GlobalDataController::class, 'update_global_variables_permission' ],
			]
		);

		/**
		 * Register route `/global-data/global-preset/sync`.
		 */
		$route->post(
			'/global-data/global-preset/sync',
			[
				'args'                => [ GlobalPresetController::class, 'sync_args' ],
				'callback'            => [ GlobalPresetController::class, 'sync' ],
				'permission_callback' => [ GlobalPresetController::class, 'sync_permission' ],
			]
		);

		/**
		 * `/module-render` REST routes.
		 */
		$route->post(
			'/module-render',
			[
				'args'                => [ ModuleRenderController::class, 'module_render_args' ],
				'callback'            => [ ModuleRenderController::class, 'module_render' ],
				'permission_callback' => [ ModuleRenderController::class, 'module_render_permission' ],
			]
		);

		/**
		 * WooCommerce Select Product option.
		 */
		$route->get(
			'/woocommerce/search-products',
			[
				'args'                => [ WooCommerceSelectProductController::class, 'index_args' ],
				'callback'            => [ WooCommerceSelectProductController::class, 'index' ],
				'permission_callback' => [ WooCommerceSelectProductController::class, 'index_permission' ],
			]
		);

	}

}
