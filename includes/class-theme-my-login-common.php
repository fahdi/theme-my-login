<?php
/**
 * Holds the Theme My Login Common class
 *
 * @package Theme_My_Login
 * @since 6.3
 */

if ( ! class_exists( 'Theme_My_Login_Common' ) ) :
/*
 * Theme My Login Helper class
 *
 * This class holds methods common to being common.
 *
 * @since 6.3
 */
class Theme_My_Login_Common {
	/**
	 * Returns current URL
	 *
	 * @since 6.3
	 * @access public
	 *
	 * @param string $query Optionally append query to the current URL
	 * @return string URL with optional path appended
	 */
	public static function get_current_url( $query = '' ) {
		$url = remove_query_arg( array( 'instance', 'action', 'checkemail', 'error', 'loggedout', 'registered', 'redirect_to', 'updated', 'key', '_wpnonce', 'reauth', 'login' ) );

		if ( ! empty( $_REQUEST['instance'] ) )
			$url = add_query_arg( 'instance', $_REQUEST['instance'] );

		if ( ! empty( $query ) ) {
			$r = wp_parse_args( $query );
			foreach ( $r as $k => $v ) {
				if ( strpos( $v, ' ' ) !== false )
					$r[$k] = rawurlencode( $v );
			}
			$url = add_query_arg( $r, $url );
		}
		return $url;
	}

	/**
	 * Merges arrays recursively, replacing duplicate string keys
	 *
	 * @since 6.3
	 * @access public
	 */
	public static function array_merge_recursive() {
		$args = func_get_args();

		$result = array_shift( $args );

		foreach ( $args as $arg ) {
			foreach ( $arg as $key => $value ) {
				// Renumber numeric keys as array_merge() does.
				if ( is_numeric( $key ) ) {
					if ( ! in_array( $value, $result ) )
						$result[] = $value;
				}
				// Recurse only when both values are arrays.
				elseif ( array_key_exists( $key, $result ) && is_array( $result[$key] ) && is_array( $value ) ) {
					$result[$key] = self::array_merge_recursive( $result[$key], $value );
				}
				// Otherwise, use the latter value.
				else {
					$result[$key] = $value;
				}
			}
		}
		return $result;
	}
}
endif; // Class exists

