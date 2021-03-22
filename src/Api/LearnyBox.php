<?php

namespace LearnyboxMap\Api;

/**
 * Functionalities to retrieve some info/data from the LearnyBox API:
 * -> we DO NOT use the LearnyBox API Client for PHP (https://packagist.org/packages/learnybox/learnybox-client-php)
 * -> but the WordPress mechanisms for API calls and caching.
 *
 * @since      1.0.0
 * @package    LearnyboxMap
 * @subpackage Api
 * @author     freepius
 */
class LearnyBox {
	protected const API_ENTRYPOINT = 'api/v2/';

	/**
	 * WordPress Transients API is used to cache:
	 * -> the access and refresh tokens (for the authentication process)
	 * -> and some data to avoid requesting them again to early
	 */
	protected const TRANSIENT = 'learnyboxmap_api';

	protected string $url;
	protected string $key;

	/**
	 * Initialize the LearnyBox API class.
	 *
	 * @param string $api_url  A LearnyBox API url to discuss with.
	 * @param string $api_key  A LearnyBox API key to authenticate.
	 */
	public function __construct( string $api_url, string $api_key ) {
		$this->url = $api_url . self::API_ENTRYPOINT;
		$this->key = $api_key;
	}

	/**
	 * Retrieve all members of a given training.
	 *
	 * @param integer $training_id The training ID.
	 */
	public function get_all_members_by_training_id( int $training_id ) {
		$response = $this->request( 'get', "formations/$training_id/membres/" );
	}

	/**
	 * Send a request to a LearnyBox API route and return the response.
	 *
	 * @param string $method  Request method. Accepts 'GET', 'POST', 'DELETE', or 'PATCH'.
	 * @param string $route   LearnyBox API route to request (not suffixed by API URL).
	 * @param array  $args    Array to pass to wp_remote_request(): ie headers, body, etc.
	 * @param bool   $with_authorization  Is request need an authorization? Default true.
	 *
	 * @throws \LearnyboxMap\Api\ApiException If the request failed for any reasons.
	 */
	protected function request( string $method, string $route, array $args = array(), bool $with_authorization = true ) {
		$args['method']                   = strtoupper( $method );
		$args['headers']['Accept']        = 'application/json';
		$args['headers']['Content-Type']  = 'application/x-www-form-urlencoded';
		$args['headers']['Authorization'] = $with_authorization ? 'Bearer ' . $this->get_access_token() : null;

		$response = $this->handle_response(
			wp_remote_request( $this->url . $route, $args )
		);

		if ( ! is_wp_error( $response ) ) {
			return $response;
		}

		// Case of an invalid or expired access token: try to refresh it, then send again the request.
		// FIXME: loop if access token invalid!!!
		if ( 498 === $response->get_error_code() ) {
			$this->authenticate( false );
			return $this->request( $method, $route, $args );
		}

		// Other API errors: unable to authenticate, etc.
		throw new ApiException( $response->get_error_message(), $response->get_error_code() );
	}

	/**
	 * From the return of a wp_remote_request() call,
	 * retrieve the data of a LearnyBox API response
	 * or return a \WP_Error (if any errors occur).
	 *
	 * @param array|\WP_Error $response Value returned by wp_remote_request().
	 */
	protected function handle_response( $response ) {
		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$code    = wp_remote_retrieve_response_code( $response );
		$body    = json_decode( wp_remote_retrieve_body( $response ) );
		$message = $body->message ?? wp_remote_retrieve_response_message( $response );

		return ( $code >= 200 && $code < 300 )
		? $body->data
		: new \WP_Error( $code, $message );
	}

	/**
	 * Return an access token to the LearnyBox API:
	 * -> either from the cache (ie from a WP transient)
	 * -> or sending an authentication request.
	 */
	protected function get_access_token(): string {
		return get_transient( self::TRANSIENT )->access_token ?? $this->authenticate();
	}

	/**
	 * Try to authenticate with the LearnyBox API, ie try to get or refresh an access token.
	 * If it works, cache the access and refresh tokens in a WP transient.
	 * Otherwise, throw an exception.
	 *
	 * @param boolean $get_access_token  Either get an access token (true) or refresh it (false).
	 * @return string The access token.
	 */
	protected function authenticate( bool $get_access_token = true ): string {
		$args = $get_access_token
		// Get an access token.
		? array(
			'headers' => array( 'X-API-Key' => $this->key ),
			'body'    => array( 'grant_type' => 'access_token' ),
		)
		// Refresh an access token.
		: array(
			'body' => array(
				'grant_type'    => 'refresh_token',
				'refresh_token' => get_transient( self::TRANSIENT )->refresh_token ?? null,
			),
		);

		$response = $this->request( 'post', 'oauth/token/', $args, false );

		set_transient( self::TRANSIENT, $response, $response->expires_in );

		return $response->access_token;
	}
}
