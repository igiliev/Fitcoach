<?php
/**
 * Transform class
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\StyleLibrary\Declarations\Transform;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

/**
 * Transform class.
 *
 * This class contains methods to work with transform CSS style and hover style declarations.
 *
 * @since ??
 */
class Transform {

	use TransformTraits\ValueTrait;
	use TransformTraits\StyleDeclarationTrait;
	use TransformTraits\HoveredStyleDeclarationTrait;

}
