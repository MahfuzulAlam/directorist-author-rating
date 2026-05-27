<?php
/**
 * Plugin Name:       Directorist Author Rating
 * Plugin URI:        https://wpxplore.com/
 * Description:       Maintains Directorist listing author rating metadata from listing reviews.
 * Version:           2.0.0
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Requires Plugins:  directorist
 * Author:            wpXplore
 * Author URI:        https://wpxplore.com/
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       directorist-author-rating
 * Domain Path:       /languages
 *
 * @package DirectoristAuthorRating
 */

defined( 'ABSPATH' ) || exit;

define( 'DIRECTORIST_AUTHOR_RATING_VERSION', '2.0.0' );
define( 'DIRECTORIST_AUTHOR_RATING_FILE', __FILE__ );
define( 'DIRECTORIST_AUTHOR_RATING_DIR', plugin_dir_path( __FILE__ ) );
define( 'DIRECTORIST_AUTHOR_RATING_URL', plugin_dir_url( __FILE__ ) );

require_once DIRECTORIST_AUTHOR_RATING_DIR . 'includes/class-author-rating-sync.php';
require_once DIRECTORIST_AUTHOR_RATING_DIR . 'includes/class-dashboard-reviews.php';
require_once DIRECTORIST_AUTHOR_RATING_DIR . 'includes/class-activator.php';
require_once DIRECTORIST_AUTHOR_RATING_DIR . 'includes/class-plugin.php';
require_once DIRECTORIST_AUTHOR_RATING_DIR . 'includes/class-template-loader.php';

register_activation_hook(
	__FILE__,
	static function () {
		\DirectoristAuthorRating\Activator::activate();
	}
);

add_action(
	'plugins_loaded',
	static function () {
		\DirectoristAuthorRating\Plugin::instance()->init();
	}
);
