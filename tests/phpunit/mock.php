<?php

/**
 * Mock for add_filter
 *
 * @param string $filter
 * @param mixed $callback
 *
 * @return void
 */
function add_filter( $filter, $callback ) {
	// Do nothing.
}

/**
 * Mock for get_option
 *
 * @param string $option
 * @param mixed $default
 *
 * @return mixed
 */
function get_option( $option, $default = null ) {
	global $mock_settings;

	if ( isset( $mock_settings[ $option ] ) ) {
		return $mock_settings[ $option ];
	}

	return $default;
}

/**
 * Mock delete_option
 */
function delete_option( $key ) {
	// Do nothing.
}

/**
 * Mock update_option
 */
function update_option( $key, $value ) {
	// Do nothing.
}
