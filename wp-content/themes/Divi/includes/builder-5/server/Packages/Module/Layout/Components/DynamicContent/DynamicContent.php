<?php
/**
 * Module: DynamicContent main class.
 *
 * @package Builder\Packages\Module
 * @since ??
 */

namespace ET\Builder\Packages\Module\Layout\Components\DynamicContent;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Framework\DependencyManagement\DependencyTree;

use ET\Builder\Packages\Module\Layout\Components\DynamicContent\DynamicContentOptionPostTitle;
use ET\Builder\Packages\Module\Layout\Components\DynamicContent\DynamicContentOptionProductTitle;
use ET\Builder\Packages\Module\Layout\Components\DynamicContent\DynamicContentGlobalVariableOptions;
use ET\Builder\Packages\Module\Layout\Components\DynamicContent\DynamicContentOptionPostExcerpt;
use ET\Builder\Packages\Module\Layout\Components\DynamicContent\DynamicContentOptionPostDate;
use ET\Builder\Packages\Module\Layout\Components\DynamicContent\DynamicContentOptionPostCommentCount;
use ET\Builder\Packages\Module\Layout\Components\DynamicContent\DynamicContentOptionPostCategories;
use ET\Builder\Packages\Module\Layout\Components\DynamicContent\DynamicContentOptionPostTags;
use ET\Builder\Packages\Module\Layout\Components\DynamicContent\DynamicContentOptionPostLink;
use ET\Builder\Packages\Module\Layout\Components\DynamicContent\DynamicContentOptionPostAuthor;
use ET\Builder\Packages\Module\Layout\Components\DynamicContent\DynamicContentOptionPostAuthorBio;
use ET\Builder\Packages\Module\Layout\Components\DynamicContent\DynamicContentOptionTermDescription;
use ET\Builder\Packages\Module\Layout\Components\DynamicContent\DynamicContentOptionSiteTitle;
use ET\Builder\Packages\Module\Layout\Components\DynamicContent\DynamicContentOptionSiteTagline;
use ET\Builder\Packages\Module\Layout\Components\DynamicContent\DynamicContentOptionCurrentDate;
use ET\Builder\Packages\Module\Layout\Components\DynamicContent\DynamicContentOptionPostLinkUrl;
use ET\Builder\Packages\Module\Layout\Components\DynamicContent\DynamicContentOptionPostAuthorUrl;
use ET\Builder\Packages\Module\Layout\Components\DynamicContent\DynamicContentOptionAnyPostLinkUrl;
use ET\Builder\Packages\Module\Layout\Components\DynamicContent\DynamicContentOptionHomeUrl;
use ET\Builder\Packages\Module\Layout\Components\DynamicContent\DynamicContentOptionCustomPostLinkUrl;
use ET\Builder\Packages\Module\Layout\Components\DynamicContent\DynamicContentOptionPostFeaturedImage;
use ET\Builder\Packages\Module\Layout\Components\DynamicContent\DynamicContentOptionPostFeaturedImageAltText;
use ET\Builder\Packages\Module\Layout\Components\DynamicContent\DynamicContentOptionPostFeaturedImageTitleText;
use ET\Builder\Packages\Module\Layout\Components\DynamicContent\DynamicContentOptionPostAuthorProfilePicture;
use ET\Builder\Packages\Module\Layout\Components\DynamicContent\DynamicContentOptionSiteLogo;
use ET\Builder\Packages\Module\Layout\Components\DynamicContent\DynamicContentOptionPostMetaKey;
use ET\Builder\Packages\Module\Layout\Components\DynamicContent\DynamicContentOptionProductBreadcrumb;
use ET\Builder\Packages\Module\Layout\Components\DynamicContent\DynamicContentOptionProductPrice;
use ET\Builder\Packages\Module\Layout\Components\DynamicContent\DynamicContentOptionProductDescription;
use ET\Builder\Packages\Module\Layout\Components\DynamicContent\DynamicContentOptionProductShortDescription;
use ET\Builder\Packages\Module\Layout\Components\DynamicContent\DynamicContentOptionProductStockQuantity;
use ET\Builder\Packages\Module\Layout\Components\DynamicContent\DynamicContentOptionProductStockStatus;
use ET\Builder\Packages\Module\Layout\Components\DynamicContent\DynamicContentOptionProductReviewsCount;
use ET\Builder\Packages\Module\Layout\Components\DynamicContent\DynamicContentOptionProductSKU;
use ET\Builder\Packages\Module\Layout\Components\DynamicContent\DynamicContentOptionProductID;
use ET\Builder\Packages\Module\Layout\Components\DynamicContent\DynamicContentOptionProductReviews;
use ET\Builder\Packages\Module\Layout\Components\DynamicContent\DynamicContentOptionProductAdditionalInformation;
use ET\Builder\Packages\Module\Layout\Components\DynamicContent\DynamicContentOptionProductReviewsTab;
use ET\Builder\Packages\Module\Layout\Components\DynamicContent\DynamicContentOptionCustomMeta;

/**
 * Module: DynamicContent main class.
 *
 * Dynamic content is sub type of dynamic data. It's a dynamic data that specifically
 * handles `content` related value such as `site_title`, `post_title`, etc. The dynamic
 * content is identified and wrapped in `$variables()` format with `type="content"` and
 * `value="{JSON_VALUE}"` attributes. This class is responsible to resolve the dynamic
 * content value and registering the options. This includes:
 * - Add hook callbacks to filter dynamic content resolved value for built-in and custom
 *   meta options value.
 * - Add hook callbacks to filter dynamic content options registration for built-in and
 *   custom meta.
 *
 * @see ET\Builder\Packages\Module\Layout\Components\DynamicData
 *
 * @since ??
 */
class DynamicContent {

	/**
	 * Stores dependencies that was passed to constructor.
	 *
	 * @var DependencyTree
	 *
	 * @since ??
	 */
	private $_dependency_tree;

	/**
	 * Create an instance of DynamicContent class.
	 * Initializes the object with a DependencyTree instance.
	 *
	 * @since ??
	 *
	 * @param DependencyTree $dependency_tree The dependencies to be loaded by the VisualBuilder.
	 */
	public function __construct( DependencyTree $dependency_tree ) {
		$this->_dependency_tree = $dependency_tree;
	}


	/**
	 * Initialize the object and load dependencies.
	 *
	 * This function initializes the object by loading its dependencies.
	 * It should be called before using any other methods in the class.
	 *
	 * @since ??
	 *
	 * @return void
	 *
	 * @example
	 * ```php
	 *  ET_Core_Logger::initialize();
	 * ```
	 */
	public function initialize(): void {
		$this->_dependency_tree->load_dependencies();
	}
}

$dependency_tree = new DependencyTree();

$dependency_tree->add_dependency( new DynamicContentOptionProductTitle() );
$dependency_tree->add_dependency( new DynamicContentOptionPostTitle() );
$dependency_tree->add_dependency( new DynamicContentGlobalVariableOptions() );
$dependency_tree->add_dependency( new DynamicContentOptionPostExcerpt() );
$dependency_tree->add_dependency( new DynamicContentOptionPostDate() );
$dependency_tree->add_dependency( new DynamicContentOptionPostCommentCount() );
$dependency_tree->add_dependency( new DynamicContentOptionPostCategories() );
$dependency_tree->add_dependency( new DynamicContentOptionPostTags() );
$dependency_tree->add_dependency( new DynamicContentOptionPostLink() );
$dependency_tree->add_dependency( new DynamicContentOptionPostAuthor() );
$dependency_tree->add_dependency( new DynamicContentOptionPostAuthorBio() );
$dependency_tree->add_dependency( new DynamicContentOptionTermDescription() );
$dependency_tree->add_dependency( new DynamicContentOptionSiteTitle() );
$dependency_tree->add_dependency( new DynamicContentOptionSiteTagline() );
$dependency_tree->add_dependency( new DynamicContentOptionCurrentDate() );
$dependency_tree->add_dependency( new DynamicContentOptionPostLinkUrl() );
$dependency_tree->add_dependency( new DynamicContentOptionPostAuthorUrl() );
$dependency_tree->add_dependency( new DynamicContentOptionAnyPostLinkUrl() );
$dependency_tree->add_dependency( new DynamicContentOptionHomeUrl() );
$dependency_tree->add_dependency( new DynamicContentOptionCustomPostLinkUrl() );
$dependency_tree->add_dependency( new DynamicContentOptionPostFeaturedImage() );
$dependency_tree->add_dependency( new DynamicContentOptionPostFeaturedImageAltText() );
$dependency_tree->add_dependency( new DynamicContentOptionPostFeaturedImageTitleText() );
$dependency_tree->add_dependency( new DynamicContentOptionPostAuthorProfilePicture() );
$dependency_tree->add_dependency( new DynamicContentOptionSiteLogo() );
$dependency_tree->add_dependency( new DynamicContentOptionPostMetaKey() );
$dependency_tree->add_dependency( new DynamicContentOptionProductBreadcrumb() );
$dependency_tree->add_dependency( new DynamicContentOptionProductPrice() );
$dependency_tree->add_dependency( new DynamicContentOptionProductDescription() );
$dependency_tree->add_dependency( new DynamicContentOptionProductShortDescription() );
$dependency_tree->add_dependency( new DynamicContentOptionProductStockQuantity() );
$dependency_tree->add_dependency( new DynamicContentOptionProductStockStatus() );
$dependency_tree->add_dependency( new DynamicContentOptionProductReviewsCount() );
$dependency_tree->add_dependency( new DynamicContentOptionProductSKU() );
$dependency_tree->add_dependency( new DynamicContentOptionProductID() );
$dependency_tree->add_dependency( new DynamicContentOptionProductReviews() );
$dependency_tree->add_dependency( new DynamicContentOptionProductAdditionalInformation() );
$dependency_tree->add_dependency( new DynamicContentOptionProductReviewsTab() );
$dependency_tree->add_dependency( new DynamicContentOptionCustomMeta() );

$dynamic_content = new DynamicContent( $dependency_tree );
$dynamic_content->initialize();
