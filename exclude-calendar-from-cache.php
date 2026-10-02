<?php
/*
  Plugin Name: Exclude calendar from cache
  Plugin URI: https://github.com/devcollaborative/exclude-calendar-from-cache
  Description: Exclude calendar event listing page from Pantheon Cache
  Author URI: https://devcollaborative.com
*/

/**
 * Only do this on Pantheon env
 * local env will be "lando".
 * Pantheon env could be 'development','test','live' or any multidev name
 * */
if ( isset( $_ENV['PANTHEON_ENVIRONMENT'] ) && ( 'lando' != $_ENV['PANTHEON_ENVIRONMENT'] ) ):

  /**
  * Set $regex_path_patterns for pages to be excluded
  *
  * For example, to exclude pages in the /news/ and /about/ path from cache, set:
  *
  *   $regex_path_patterns = array(
  *     '#^/news/?#',
  *     '#^/about/?#',
  *   );
  * @link https://docs.pantheon.io/cache-control#exclude-specific-pages-from-caching
  */

  $regex_path_patterns = array(
    '#^/events/?#',
    '#^/learning-opportunities/?#',
  );

  // Loop through the patterns.
  foreach ($regex_path_patterns as $regex_path_pattern) {
    if (preg_match($regex_path_pattern, $_SERVER['REQUEST_URI'])) {
      add_action( 'send_headers', 'add_header_nocache', 15 );

      // No need to continue the loop once there's a match.
      break;
    }
  }

  function add_header_nocache() {
      header( 'Cache-Control: no-cache, must-revalidate, max-age=0' );
  }

  /* For WP REST API specific paths, we use a different approach by using the rest_post_dispatch filter */

  // wp-json paths or any custom endpoints
  $regex_json_path_patterns = array(
    '#^/wp-json/wp/v2?#',
    '#^/wp-json/?#'
  );

  foreach ($regex_json_path_patterns as $regex_json_path_pattern) {
    if (preg_match($regex_json_path_pattern, $_SERVER['REQUEST_URI'])) {
        // re-use the rest_post_dispatch filter in the Pantheon page cache plugin
        add_filter( 'rest_post_dispatch', 'filter_rest_post_dispatch_send_cache_control', 12 );
        break;
    }
  }

  // Re-define the send_header value with any custom Cache-Control header
  function filter_rest_post_dispatch_send_cache_control( $response ) {
      $response->header( 'Cache-Control', 'no-cache, must-revalidate, max-age=0' );
      return $response;
  }

endif;//pantheon env is set
