<?php
/**
 * TF_Roles - WordPress role to TOP permission mapping
 *
 * @package THUNDERFIRE
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class TF_Roles
 */
class TF_Roles {

	/**
	 * Permission constants.
	 */
	const PERM_NONE  = 0;
	const PERM_READ  = 1;
	const PERM_WRITE = 2;
	const PERM_ADMIN = 4;

	/**
	 * Get TOP permission level for current user.
	 *
	 * @return int Permission bitmask.
	 */
	public static function get_current_permission() {
		// Test API keys are always read-only.
		$api_key = get_option( 'tf_api_key', '' );
		if ( str_starts_with( $api_key, 'tf_test_' ) ) {
			return is_user_logged_in() ? self::PERM_READ : self::PERM_NONE;
		}

		if ( current_user_can( 'manage_options' ) ) {
			return self::PERM_ADMIN | self::PERM_WRITE | self::PERM_READ;
		}

		if ( current_user_can( 'edit_posts' ) ) {
			return self::PERM_WRITE | self::PERM_READ;
		}

		if ( current_user_can( 'read' ) ) {
			return self::PERM_READ;
		}

		return self::PERM_NONE;
	}

	/**
	 * Check if current user can read.
	 *
	 * @return bool
	 */
	public static function can_read() {
		return ( self::get_current_permission() & self::PERM_READ ) !== 0;
	}

	/**
	 * Check if current user can write.
	 *
	 * @return bool
	 */
	public static function can_write() {
		return ( self::get_current_permission() & self::PERM_WRITE ) !== 0;
	}

	/**
	 * Check if current user is admin.
	 *
	 * @return bool
	 */
	public static function can_admin() {
		return ( self::get_current_permission() & self::PERM_ADMIN ) !== 0;
	}

	/**
	 * Get role label for permission level.
	 *
	 * @param int $permission Permission bitmask.
	 * @return string Role label.
	 */
	public static function get_role_label( $permission ) {
		if ( $permission & self::PERM_ADMIN ) {
			return __( 'Administrator', 'thunderfire' );
		}
		if ( $permission & self::PERM_WRITE ) {
			return __( 'Operator', 'thunderfire' );
		}
		if ( $permission & self::PERM_READ ) {
			return __( 'Observer', 'thunderfire' );
		}
		return __( 'No Access', 'thunderfire' );
	}
}
