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
	 * -> and some 'heavy' data to avoid requesting them again to early
	 */
	protected const TRANSIENT_AUTH = 'learnyboxmap_api_auth';

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
	 * Get one member by his unique user ID.
	 *
	 * @param integer $user_id The user ID.
	 * @return \stdClass All the member data.
	 * @api
	 */
	public function get_one_member_by_id( int $user_id ): \stdClass {
		return (object) $this->request( 'get', "users/$user_id/" )->data;
	}

	/**
	 * Get all members of a given training.
	 *
	 * @param integer $training_id The training ID.
	 * @return \Generator<\stdClass> Return a generator of LearnyBox members.
	 * @api
	 */
	public function get_all_members_by_training_id( int $training_id ): \Generator {
		return $this->get_all( "formations/$training_id/membres/" );
	}

	/**
	 * Get all data of a given GET route of LearnyBox API: first from cache, then through API requests.
	 *
	 * For performance, API responses are cached (in a WP transient, for one day max).
	 * So if API responses are already/still in the cache, data are retrieved from it first.
	 * Otherwise, data are retrieved from several API requests (of 500 items each).
	 * In both cases (cache or requests), last request is always replayed in order to retrieve any new data.
	 *
	 * @param string $route The LearnyBox API GET route to request.
	 *
	 * @return \Generator<\stdClass> Return a generator of LearnyBox API response data.
	 */
	protected function get_all( string $route ): \Generator {
		$offset           = 0;
		$limit            = 500;
		$transient_id     = __CLASS__ . __FUNCTION__ . $route;
		$cached_responses = (array) get_transient( $transient_id );

		// Do not keep the last API response.
		array_pop( $cached_responses );

		// Consume first the cached API responses if there are.
		foreach ( $cached_responses as $response ) {
			$offset += $limit;
			yield from $response->data;
		}

		while (
			// phpcs:ignore WordPress.CodeAnalysis.AssignmentInCondition
			null !== ( $response = $this->request( 'get', "$route?limit=$limit&offset=$offset" ) )
			&&
			array() !== $response->data
		) {
			$offset += $limit;

			// Store API response in the cache (for one day max).
			$cached_responses[] = $response;
			set_transient( $transient_id, $cached_responses, 60 * 60 * 24 );

			yield from $response->data;
		}
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
	 * retrieve the content of a LearnyBox API response
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
		? $body
		: new \WP_Error( $code, $message );
	}

	/**
	 * Return an access token to the LearnyBox API:
	 * -> either from the cache (ie from a WP transient)
	 * -> or sending an authentication request.
	 */
	protected function get_access_token(): string {
		return get_transient( self::TRANSIENT_AUTH )->access_token ?? $this->authenticate();
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
				'refresh_token' => get_transient( self::TRANSIENT_AUTH )->refresh_token ?? null,
			),
		);

		$response = $this->request( 'post', 'oauth/token/', $args, false );

		set_transient( self::TRANSIENT_AUTH, $response->data, $response->data->expires_in );

		return $response->data->access_token;
	}
}
