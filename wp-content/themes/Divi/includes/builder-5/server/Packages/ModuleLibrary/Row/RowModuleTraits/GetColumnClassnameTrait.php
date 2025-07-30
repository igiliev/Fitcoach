<?php
/**
 * RowModule::get_column_classname()
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\ModuleLibrary\Row\RowModuleTraits;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

trait GetColumnClassnameTrait {

	/**
	 * List of column classnames.
	 *
	 * @since ??
	 *
	 * @var array
	 */
	private static $_column_classnames = [
		'1_4,1_4,1_4,1_4'         => '4col',
		'1_5,1_5,1_5,1_5,1_5'     => '5col',
		'1_6,1_6,1_6,1_6,1_6,1_6' => '6col',
		'1_4,3_4'                 => '1-4_3-4',
		'3_4,1_4'                 => '3-4_1-4',
		'1_4,1_2,1_4'             => '1-4_1-2_1-4',
		'1_4,1_4,1_2'             => '1-4_1-4_1-2',
		'1_2,1_4,1_4'             => '1-2_1-4_1-4',
		'1_5,1_5,3_5'             => '1-5_1-5_3-5',
		'3_5,1_5,1_5'             => '3-5_1-5_1-5',
		'1_6,1_6,1_6,1_2'         => '1-6_1-6_1-6_1-2',
		'1_2,1_6,1_6,1_6'         => '1-2_1-6_1-6_1-6',
	];

	/**
	 * Get the classname of a column based on its structure.
	 *
	 * This function retrieves the classname of a column based on its structure.
	 * The column structure defines the width proportions of each column within a row.
	 *
	 * This function is equivalent of JS function:
	 * {@link /docs/builder-api/js/module-library/getp-column-classname getColumnClassname}
	 * located in `@divi/module-library` package.
	 *
	 * The available column structures are as follows:
	 * - `'1_4,1_4,1_4,1_4'`: 4 equal columns
	 * - `'1_5,1_5,1_5,1_5,1_5'`: 5 equal columns
	 * - `'1_6,1_6,1_6,1_6,1_6,1_6'`: 6 equal columns
	 * - `'1_4,3_4'`: 1/4 and 3/4 columns
	 * - `'3_4,1_4'`: 3/4 and 1/4 columns
	 * - `'1_4,1_2,1_4'`: 1/4, 1/2, and 1/4 columns
	 * - `'1_4,1_4,1_2'`: 1/4, 1/4, and 1/2 columns
	 * - `'1_2,1_4,1_4'`: 1/2, 1/4, and 1/4 columns
	 * - `'1_5,1_5,3_5'`: 1/5, 1/5, and 3/5 columns
	 * - `'3_5,1_5,1_5'`: 3/5, 1/5, and 1/5 columns
	 * - `'1_6,1_6,1_6,1_2'`: 1/6, 1/6, 1/6, and 1/2 columns
	 * - `'1_2,1_6,1_6,1_6'`: 1/2, 1/6, 1/6, and 1/6 columns
	 *
	 * @since ??
	 *
	 * @param string|null $structure The column structure.
	 *
	 * @return string|false The classname of the column, or `false` if the structure does not exist.
	 *
	 * @example:
	 * ```php
	 * If the column structure is '1_4,1_4,1_4,1_4', the function will return 'et_pb_row_4col'.
	 * ```
	 *
	 * @example:
	 * ```php
	 * If the column structure is '1_2,1_4,1_4', the function will return 'et_pb_row_1-2_1-4_1-4'.
	 * ```
	 */
	public static function get_column_classname( ?string $structure ) {
		$structure_suffix = self::$_column_classnames[ $structure ] ?? null;

		return $structure_suffix ? 'et_pb_row_' . $structure_suffix : false;
	}

}
