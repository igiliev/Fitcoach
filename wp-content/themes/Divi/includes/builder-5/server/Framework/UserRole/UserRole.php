<?php
/**
 * UserRole class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Framework\UserRole;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

/**
 * UserRole class.
 *
 * This class contains functionality to determine a user's permissions, role and capabilities.
 *
 * @since ??
 */
class UserRole {

	/**
	 * Determine whether the current user can use the Visual Builder.
	 *
	 * This function checks if the current user has permission to use the visual builder based on role settings.
	 *
	 *
	 * Note: By default, all roles have access to the Visual Builder, when you start using RoleEditor then only the
	 * selected users will have access. This is admittedly not an ideal situation that might be changed in the future
	 * and is kept in D5 for backwards compatibility.
	 *
	 * @since ??
	 *
	 * @return bool Returns `true` if the current user can use the visual builder, `false` otherwise.
	 */
	public static function can_current_user_use_visual_builder(): bool {
		$role_settings = self::_get_role_settings();
		$user_role     = self::_get_current_user_role();

		/**
		 * Default value for the role is 'on' because when after a fresh install "Dashboard > Divi > RoleEditor" is not
		 * used, it will return an empty array which indicates all roles have access to VB. Once you start using
		 * RoleEditor then it will always return a filled array. This is a weird behavior but we need to keep it for
		 * backward compatibility.
		 */
		if ( $user_role ) {
			$use_visual_builder_role = $role_settings[ $user_role ]['use_visual_builder'] ?? 'on';
			$can_use_visual_builder  = 'on' === $use_visual_builder_role;
		} else {
			$can_use_visual_builder = false;
		}

		return $can_use_visual_builder;
	}

	/**
	 * Get role settings.
	 *
	 * This method retrieves the role settings from the `'et_pb_role_settings'` option,
	 * selected in "Dashboard > Divi > RoleEditor".
	 * If no settings are found, an empty array is returned.
	 *
	 * @since ??
	 *
	 * @return array The role settings.
	 */
	private static function _get_role_settings(): array {
		$role_settings = get_option( 'et_pb_role_settings', [] );

		return $role_settings;
	}

	/**
	 * Get the current user role.
	 *
	 * Get the current user role `wp_get_current_user()` and call `self::_determine_current_user_role()`
	 * when the result of `wp_get_current_user()` is empty.
	 *
	 * @since ??
	 *
	 * @return string|null The current user role.
	 *
	 * @example:
	 * ```php
	 * $role = UserRole::_get_current_user_role();
	 *
	 * if ($role !== null) {
	 *     echo "Current user role: " . $role;
	 * } else {
	 *     echo "User role not found.";
	 * }
	 * ```
	 */
	private static function _get_current_user_role(): ?string {
		$current_user = wp_get_current_user();
		$user_roles   = $current_user->roles;

		// retrieve the role from array if exists or determine it using custom mechanism
		// $user_roles array may start not from 0 index. Use reset() to retrieve the first value from array regardless its index.
		$role = ! empty( $user_roles ) ? reset( $user_roles ) : self::_determine_current_user_role();

		return $role;
	}

	/**
	 * Get the current user role.
	 *
	 * This function checks the capabilities of the current user and returns the corresponding role.
	 * The roles are retrieved via `UserRole::_get_all_roles_list()`.
	 *
	 * @since ??
	 *
	 * @return string|null The user role if found, `null` otherwise.
	 *
	 * @example:
	 * ```php
	 * $role = UserRole::_determine_current_user_role();
	 *
	 * if ($role !== null) {
	 *     echo "Current user role: " . $role;
	 * } else {
	 *     echo "User role not found.";
	 * }
	 * ```
	 */
	private static function _determine_current_user_role(): ?string {
		$all_roles = self::_get_all_roles_list();

		// go through all the registered roles and return the one current user have.
		foreach ( $all_roles as $role => $role_data ) {
			if ( current_user_can( $role ) ) {
				return $role;
			}
		}

		return null;
	}

	/**
	 * Get the list of all roles (with editing permissions) registered in the WordPress.
	 *
	 * This function generates an array of roles that have editing permissions and are registered in WordPress.
	 * It excludes the 'Support' and 'Support - Elevated' roles.
	 * The list of roles is obtained using the `get_editable_roles` function.
	 *
	 * @since ??
	 *
	 * @return array An array containing all the roles with editing permissions.
	 *
	 * @example:
	 * ```php
	 * // Usage example
	 * $rolesList = MyClass::_get_all_roles_list();
	 * ```
	 *
	 * @output:
	 * ```php
	 *  [
	 *    'administrator' => 'Administrator',
	 *    'editor' => 'Editor',
	 *    'author' => 'Author',
	 *    'contributor' => 'Contributor',
	 *  ]
	 * ```
	 */
	private static function _get_all_roles_list(): array {
		// get all roles registered in current WP.
		if ( ! function_exists( 'get_editable_roles' ) ) {
			require_once ABSPATH . 'wp-admin/includes/user.php';
		}

		$all_roles           = get_editable_roles();
		$builder_roles_array = [];

		if ( ! empty( $all_roles ) ) {
			foreach ( $all_roles as $role => $role_data ) {
				// add roles with edit_posts capability into $builder_roles_array (but not Support).
				if (
					! empty( $role_data['capabilities']['edit_posts'] )
					&&
					1 === (int) $role_data['capabilities']['edit_posts']
					&&
					! in_array( $role_data['name'], array( 'ET Support', 'ET Support - Elevated' ), true )
				) {
					$builder_roles_array[ $role ] = $role_data['name'];
				}
			}
		}

		// fill the builder roles array with default roles if it's empty.
		if ( empty( $builder_roles_array ) ) {
			$builder_roles_array = array(
				'administrator' => esc_html__( 'Administrator', 'et_builder' ),
				'editor'        => esc_html__( 'Editor', 'et_builder' ),
				'author'        => esc_html__( 'Author', 'et_builder' ),
				'contributor'   => esc_html__( 'Contributor', 'et_builder' ),
			);
		}

		return $builder_roles_array;
	}


}
