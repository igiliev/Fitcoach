<?php
/**
 * Route: RESTRoute class.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Framework\Route;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use WP_REST_Request;
use ET\Builder\Framework\Controllers\RESTController;
use ET\Builder\VisualBuilder\REST\Nonce;

/**
 * REST API Route class.
 *
 * @since ??
 */
class RESTRoute {

	/**
	 * WordPress REST API namespace.
	 *
	 * @var string
	 */
	private $_namespace = 'divi/v1';

	/**
	 * REST route prefix.
	 *
	 * This string is going to be prefixed to the route you want to register.
	 *
	 * @var string
	 */
	public $prefix = '';

	/**
	 * Create an instance of `RestRoute`.
	 *
	 * @param string $namespace WordPress REST API namespace.
	 */
	public function __construct( string $namespace ) {
		$this->_namespace = $namespace;
	}

	/**
	 * Register a REST API route.
	 *
	 * @param string       $method              The method to register e.g. `POST`, `GET`, `PUT`.
	 * @param string       $route               The route name to add e.g. `/route-name`.
	 * @param array        $route_args          Route arguments as used in `register_rest_route()`.
	 * @param string|array $route_callback      Route callback as used in `register_rest_route()`.
	 * @param string|array $permission_callback Route permission callback as used in `register_rest_route()`.
	 *
	 * @return void
	 */
	public function register_rest_route( string $method, string $route, array $route_args, $route_callback, $permission_callback ): void {
		// Create and store nonce data.
		Nonce::add_data( 'wp_rest', 'ALL', wp_create_nonce( 'wp_rest' ) );
		Nonce::add_data( RESTController::get_full_route( $this->_namespace, $route ), $method, RESTController::create_nonce( $this->_namespace, $route, $method ) );

		add_filter(
			'rest_request_before_callbacks',
			function( $response, array $handler, WP_REST_Request $request ) use ( $method, $route ) {
				if ( null !== $response ) {
					// Core starts with a null value.
					// If it is no longer null, another callback has claimed this request.
					return $response;
				}

				$registered_route = rtrim( RESTController::get_full_route( $this->_namespace, $route ), '/' );
				$request_route    = $request->get_route();

				// Remove query string from the request path.
				if ( false !== strpos( $request_route, '?' ) ) {
					$request_route = substr( $request_route, 0, strpos( $request_route, '?' ) );
				}

				$request_route = rtrim( $request_route, '/' );

				// Fixed route.
				$is_route_match = $request_route === $registered_route;

				// Dynamic route.
				// Example: Registered route `/divi/v1/product/(?P<id>\d+)` will match request route `/divi/v1/product/123`.
				if ( ! $is_route_match && false !== strpos( $route, '?P<' ) ) {
					$is_route_match = preg_match( '#^' . $registered_route . '$#', $request_route );
				}

				if ( $is_route_match ) {
					if ( ! wp_verify_nonce( $request->get_header( 'X-ET-Nonce' ), RESTController::get_nonce_name( $this->_namespace, $route, $method ) ) ) {
						return RESTController::response_error_nonce();
					}

					if ( ! is_callable( $handler['permission_callback'] ) ) {
						return new \WP_Error(
							'missing_permission_callback',
							esc_html__( 'The REST API route definition missing the required permission_callback argument.', 'et_builder' ),
							[
								'route'  => $route,
								'method' => $method,
							]
						);
					}
				}

				return $response;
			},
			10,
			3
		);

		add_action(
			'rest_api_init',
			function() use ( $method, $route, $route_args, $route_callback, $permission_callback ) {
				register_rest_route(
					$this->_namespace,
					$this->prefix . $route,
					[
						'methods'             => $method,
						'callback'            => $route_callback,
						'args'                => $route_args,
						'permission_callback' => $permission_callback,
					]
				);
			}
		);
	}

	/**
	 * Register a REST resource route with WordPress.
	 *
	 * This function registers a REST resource route with WordPress.
	 * The route should be a string representing the URL endpoint for the resource.
	 * The controller should be an instance of a class that contains the action methods for the resource.
	 * The options parameter allows customization of the resource registration.
	 *
	 * A resource route is useful if you are to perform the same sets of actions against each resource. So by using
	 * `resource()` you can assign the typical create, read, update, and delete ("CRUD") routes to a controller with a
	 * single method call, following RESTful convention.
	 *
	 * @see https://developer.wordpress.org/rest-api/extending-the-rest-api/routes-and-endpoints/#resource-paths
	 *
	 * @since ??
	 *
	 * @param string $route      The route string for the resource.
	 * @param mixed  $controller A controller containing the action methods for the resource.
	 * @param array  $options {
	 *     An array of options to customize the resource registration.
	 *
	 *     @type array  $actions        An array of action names allowed for the resource.
	 *     @type string $route_variable A regex string representing the route variable for the resource.
	 * }
	 *
	 * @return void
	 *
	 * @example:
	 * ```php
	 * $route = '/my-resource';
	 * $controller = new MyController();
	 * $options = [
	 *     'actions' => ['index', 'store', 'show', 'update', 'destroy'],
	 *     'route_variable' => '(?P<id>[\d]+)',
	 * ];
	 * resource($route, $controller, $options);
	 * ```
	 */
	public function resource( string $route, $controller, array $options ): void {
		$default_options     = [
			'actions'        => [ 'index', 'store', 'show', 'update', 'destroy' ],
			'route_variable' => '(?P<id>[\d]+)',
		];
		$route_options       = array_merge( $default_options, $options );
		$route_with_variable = $route . '/' . $route_options['route_variable'];

		$routes = [
			[
				'route'  => $route,
				'method' => 'GET',
				'action' => 'index',
			],
			[
				'route'  => $route,
				'method' => 'POST',
				'action' => 'store',
			],
			[
				'route'  => $route_with_variable,
				'method' => 'GET',
				'action' => 'show',
			],
			[
				'route'  => $route_with_variable,
				'method' => 'PUT,PATCH',
				'action' => 'update',
			],
			[
				'route'  => $route_with_variable,
				'method' => 'DELETE',
				'action' => 'destroy',
			],
		];

		foreach ( $routes as $route ) {
			if ( array_search( $route['action'], $route_options['actions'], true ) === false ) {
				continue;
			}
			$args = call_user_func( [ $controller, $route['action'] . '_args' ] );
			$this->register_rest_route(
				$route['method'],
				$route['route'],
				$args,
				[ $controller, $route['action'] ],
				[ $controller, $route['action'] . '_permission' ]
			);
		}

	}

	/**
	 * Register a `GET` REST route with the specified route and controller.
	 *
	 * If the controller is a string and a class, retrieve the index arguments, callback, and permission callback from the controller.
	 * If the controller is an array, retrieve the args, callback, and permission callback from the array.
	 *
	 * @since ??
	 *
	 * @param string|array $route      The route to register.
	 * @param mixed        $controller The controller containing the endpoint logic, either a class-string or array.
	 *                                 This should define `args`, `callback` and `permission_callback` as used in `RESTRoute::register_rest_route()`.
	 *
	 * @example:
	 * ```php
	 *      $restRouter = new RestRoute();
	 *      $controller = new MyController();
	 *      $restRouter->get('/my-route', $controller);
	 * ```
	 *
	 * @example:
	 * ```php
	 *      // Register a REST route with an array controller.
	 *      $restRouter = new RestRoute();
	 *      $restRouter->get('/my-route', [
	 *          'args'                => 'my_callback_args',
	 *          'callback'            => 'my_callback',
	 *          'permission_callback' => 'my_permission_callback',
	 *      ]);
	 * ```
	 */
	public function get( $route, $controller ): void {
		if ( is_string( $controller ) && class_exists( $controller ) ) {
			$args                = call_user_func( [ $controller, 'index_args' ] );
			$callback            = [ $controller, 'index' ];
			$permission_callback = [ $controller, 'index_permission' ];
		} else {
			$args                = is_callable( $controller['args'] ) ? call_user_func( $controller['args'] ) : [];
			$callback            = $controller['callback'] ?? null;
			$permission_callback = $controller['permission_callback'] ?? null;
		}

		$this->register_rest_route( 'GET', $route, $args, $callback, $permission_callback );
	}

	/**
	 * Register a `POST` REST route with the specified route and controller.
	 *
	 * If the controller is a string and a class, retrieve the index arguments, callback, and permission callback from the controller.
	 * If the controller is an array, retrieve the args, callback, and permission callback from the array.
	 *
	 * @since ??
	 *
	 * @param string $route      The route to register.
	 * @param mixed  $controller The controller containing the endpoint logic, either a class-string or array.
	 *                           This should define `args`, `callback` and `permission_callback` as used in `RESTRoute::register_rest_route()`.
	 *
	 * @example:
	 * ```php
	 *      $restRouter = new RestRoute();
	 *      $controller = new MyController();
	 *      $restRouter->post('/my-route', $controller);
	 * ```
	 *
	 * @example:
	 * ```php
	 *      // Register a REST route with an array controller.
	 *      $restRouter = new RestRoute();
	 *      $restRouter->post('/my-route', [
	 *          'args'                => 'my_callback_args',
	 *          'callback'            => 'my_callback',
	 *          'permission_callback' => 'my_permission_callback',
	 *      ]);
	 * ```
	 */
	public function post( string $route, $controller ): void {
		if ( is_string( $controller ) && class_exists( $controller ) ) {
			$args                = call_user_func( [ $controller, 'store_args' ] );
			$callback            = [ $controller, 'store' ];
			$permission_callback = [ $controller, 'store_permission' ];
		} else {
			$args                = is_callable( $controller['args'] ) ? call_user_func( $controller['args'] ) : [];
			$callback            = $controller['callback'] ?? null;
			$permission_callback = $controller['permission_callback'] ?? null;
		}

		$this->register_rest_route( 'POST', $route, $args, $callback, $permission_callback );
	}

	/**
	 * Register a `PUT` REST route with the specified route and controller.
	 *
	 * If the controller is a string and a class, retrieve the index arguments, callback, and permission callback from the controller.
	 * If the controller is an array, retrieve the args, callback, and permission callback from the array.
	 *
	 * @since ??
	 *
	 * @param string $route      The route to register.
	 * @param mixed  $controller The controller containing the endpoint logic, either a class-string or array.
	 *                           This should define `args`, `callback` and `permission_callback` as used in `RESTRoute::register_rest_route()`.
	 *
	 * @example:
	 * ```php
	 *      $restRouter = new RestRoute();
	 *      $controller = new MyController();
	 *      $restRouter->put('/my-route', $controller);
	 * ```
	 *
	 * @example:
	 * ```php
	 *      // Register a REST route with an array controller.
	 *      $restRouter = new RestRoute();
	 *      $restRouter->put('/my-route', [
	 *          'args'                => 'my_callback_args',
	 *          'callback'            => 'my_callback',
	 *          'permission_callback' => 'my_permission_callback',
	 *      ]);
	 * ```
	 */
	public function put( string $route, $controller ): void {
		if ( is_string( $controller ) && class_exists( $controller ) ) {
			$args                = call_user_func( [ $controller, 'update_args' ] );
			$callback            = [ $controller, 'update' ];
			$permission_callback = [ $controller, 'update_permission' ];
		} else {
			$args                = is_callable( $controller['args'] ) ? call_user_func( $controller['args'] ) : [];
			$callback            = $controller['callback'] ?? null;
			$permission_callback = $controller['permission_callback'] ?? null;
		}

		$this->register_rest_route( 'PUT', $route, $args, $callback, $permission_callback );
	}

	/**
	 * Register a `DELETE` REST route with the specified route and controller.
	 *
	 * If the controller is a string and a class, retrieve the index arguments, callback, and permission callback from the controller.
	 * If the controller is an array, retrieve the args, callback, and permission callback from the array.
	 *
	 * @since ??
	 *
	 * @param string $route      The route to register.
	 * @param mixed  $controller The controller containing the endpoint logic, either a class-string or array.
	 *                           This should define `args`, `callback` and `permission_callback` as used in `RESTRoute::register_rest_route()`.
	 *
	 * @example:
	 * ```php
	 *      $restRouter = new RestRoute();
	 *      $controller = new MyController();
	 *      $restRouter->delete('/my-route', $controller);
	 * ```
	 *
	 * @example:
	 * ```php
	 *      // Register a REST route with an array controller.
	 *      $restRouter = new RestRoute();
	 *      $restRouter->delete('/my-route', [
	 *          'args'                => 'my_callback_args',
	 *          'callback'            => 'my_callback',
	 *          'permission_callback' => 'my_permission_callback',
	 *      ]);
	 * ```
	 */
	public function delete( string $route, $controller ): void {
		if ( is_string( $controller ) && class_exists( $controller ) ) {
			$args                = call_user_func( [ $controller, 'store_args' ] );
			$callback            = [ $controller, 'store' ];
			$permission_callback = [ $controller, 'store_permission' ];
		} else {
			$args                = is_callable( $controller['args'] ) ? call_user_func( $controller['args'] ) : [];
			$callback            = $controller['callback'] ?? null;
			$permission_callback = $controller['permission_callback'] ?? null;
		}

		$this->register_rest_route( 'DELETE', $route, $args, $callback, $permission_callback );
	}

	/**
	 * Set a new prefix for the current RESTRoute instance.
	 *
	 * Create a new instance of `RESTRoute` and set the given prefix which
	 * will be applied to all the routes instance.
	 *
	 * @since ??
	 *
	 * @param string $prefix REST route prefix.
	 *
	 * @return RESTRoute
	 */
	public function prefix( string $prefix ): RESTRoute {
		$new_instance         = new RESTRoute( $this->_namespace );
		$new_instance->prefix = $prefix;
		return $new_instance;
	}

	/**
	 * Group a set of REST routes using a callback function.
	 *
	 * This method allows for grouping a set of routes by executing a given callback function,
	 * which can modify and add routes to the current route collection represented by this class instance.
	 *
	 * This function passes the current instance of `RESTRoute` to the executed callback.
	 *
	 * @since ??
	 *
	 * @param callable $callback The callback function to be executed for grouping routes.
	 *                           It should accept a single parameter representing the current instance
	 *                           of the RESTRoute class.
	 *
	 * @return RESTRoute The current instance of the RESTRoute class.
	 *
	 * @example:
	 * ```php
	 * // Grouping routes using a callback function
	 * $route = new RESTRoute();
	 * $route->group( function( $router ) {
	 *     $router->get( '/posts', 'MyApp\Controllers\PostController@index' );
	 *     $router->post( '/posts', 'MyApp\Controllers\PostController@store' );
	 * });
	 * ```
	 */
	public function group( callable $callback ): RESTRoute {
		call_user_func( $callback, $this );
		return $this;
	}


}
