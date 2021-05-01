<?php
/**
 * Simple Calendar - FullCalendar add-on
 *
 */
namespace SimpleCalendar;

use SimpleCalendar\Calendars\FullCalendar;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * A FullCalendar add on for Simple Calendar.
 */
class Add_On_FullCalendar {

	/**
	 * Plugin add-on name.
	 *
	 * @access public
	 * @var string
	 */
	public $name = 'FullCalendar';

	/**
	 * Plugin add-on internal slug.
	 *
	 * @access public
	 * @var string
	 */
	public $slug = 'simcal-fullcal';

	/**
	 * Plugin add-on internal unique id.
	 *
	 * @access public
	 * @var string
	 */
	public $id = '';

	/**
	 * Plugin add-on version.
	 *
	 * @access public
	 * @var string
	 */
	public $version = SIMPLE_CALENDAR_FULLCALENDAR_VERSION;

	/**
	 * Load plugin.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		// TODO: Real ID needs to go here.
		$this->id = defined( 'SIMPLE_CALENDAR_FULLCALENDAR_ID' ) ? SIMPLE_CALENDAR_FULLCALENDAR_ID : '4381';

		add_action( 'init', function () {
			load_plugin_textdomain( 'simple-calendar-fullcalendar', false, 'simple-calendar-fullcalendar' . '/languages' );
		} );

		add_action( 'plugins_loaded', array( $this, 'init' ), 0 );
	}

	public function license_notification() {

		if ( simcal_is_admin_screen() !== false ) {

			$license = $this->check_license();

			if ( $license === 'expired' ) {
				// TODO: Add link to admin message to purchase new license?
				?>
				<div class="notice notice-error is-dismissible">
					<p><?php printf( __( 'Your Simple Calendar %1$s add-on license key has expired.', 'simple-calendar-fullcalendar' ), $this->name ); ?></p>
				</div>
				<?php
			} elseif ( $license !== 'valid' && ! empty( $license ) ) {
				?>
				<div class="notice notice-error is-dismissible">
					<p><?php printf( __( 'Your Simple Calendar %1$s add-on license key is invalid.', 'simple-calendar-fullcalendar' ), $this->name ); ?></p>
				</div>
				<?php
			}
		}
	}

	public function pre_wp_update_check( $array ) {

		$addon        = 'simcal_' . $this->id;
		$licenses     = get_option( 'simple-calendar_licenses_status', array() );
		$license_data = $this->check_license();

		if ( $license_data !== 'valid' ) {
			$licenses[ $addon ] = $license_data;
			update_option( 'simple-calendar_licenses_status', $licenses );
		} else {
			$licenses[ $addon ] = $license_data;
			update_option( 'simple-calendar_licenses_status', $licenses );
		}

		// This is a parameter from the hook itself so we want to make sure we return it
		return $array;
	}

	public function check_license() {

		$addon        = 'simcal_' . $this->id;
		$keys         = get_option( 'simple-calendar_settings_licenses', array() );
		$key          = isset( $keys['keys'][ $addon ] ) ? $keys['keys'][ $addon ] : '';
		$status       = get_option( 'simple-calendar_licenses_status' );
		$license_data = '';

		if ( ! empty( $status[ $addon ] ) ) {
			if ( ! empty( $key ) ) {

				$api_params = array(
					'edd_action' => 'check_license',
					'license'    => $key,
					'item_id'    => intval( $this->id ),
					'url'        => home_url(),
				);

				// Call the API.
				$response = wp_remote_post( defined( 'SIMPLE_CALENDAR_STORE_URL' ) ? SIMPLE_CALENDAR_STORE_URL : simcal_get_url( 'home' ), array(
					'timeout'   => 15,
					'sslverify' => false,
					'body'      => $api_params,
				) );

				$license_data = json_decode( wp_remote_retrieve_body( $response ) );
			}

			if ( ! empty( $license_data->license ) ) {
				$status[ $addon ] = $license_data->license;
				update_option( 'simple-calendar_licenses_status', $status );

				return $license_data->license;
			}
		}

		return false;
	}

	/**
	 * Init.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function init() {

		$name = $this->name;

		if ( class_exists( 'SimpleCalendar\Plugin' ) ) {

			// Show a message if SC is not 3.1.0 or above
			if ( defined( 'SIMPLE_CALENDAR_VERSION' ) && version_compare( SIMPLE_CALENDAR_VERSION, '3.1.0' ) == -1 ) {
				add_action( 'admin_notices', function () use ( $name ) {
					?>
					<div class="notice notice-error is-dismissible">
						<p><?php printf( __( 'The Simple Calendar %s add-on requires the Simple Calendar core plugin to be version 3.1.0 or higher.', 'simple-calendar-fullcalendar' ), $name ); ?></p>
					</div>
					<?php
				} );
			} else {

				// Load files we need
				include_once 'calendars/fullcalendar.php';
				include_once 'fullcalendar-json.php';
				include_once 'calendars/views/fullcalendar-grid.php';
				include_once 'calendars/admin/fullcalendar-admin.php';

				add_filter( 'simcal_get_calendar_types', function ( $calendar_types ) {
					return array_merge( $calendar_types, array(
						'fullcalendar' => array(
							'grid',
						),
					) );
				}, 10, 1 );

				// Load objects
				add_action( 'simcal_load_objects ', function () {
					new FullCalendar();
				} );

				// License management and updates.
				if ( is_admin() ) {
					$this->admin_init();
				}
			}

		} else {

			add_action( 'admin_notices', function () use ( $name ) {
				echo '<div class="error"><p>' . sprintf( __( 'The Simple Calendar %s add-on requires the <a href="%s" target="_blank">Simple Calendar core plugin</a> to be installed and activated.', 'simple-calendar-fullcalendar' ), $name, 'https://wordpress.org/plugins/google-calendar-events/' ) . '</p></div>';
			} );

		}

	}

	/**
	 * Hook in tabs.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function admin_init() {

		$id   = $this->id;
		$name = $this->name;

		// Add license key field.
		add_filter( 'simcal_installed_addons', function ( $addons ) use ( $id, $name ) {
			if ( ! isset( $addons['fullcalendar'] ) ) {
				$addons = array_merge_recursive( (array) $addons, array( strval( 'simcal_' . $id ) => $name ) );
			}

			return $addons;
		}, 20, 1 );

		// Enable license settings page.
		add_filter( 'simcal_get_admin_pages', function ( $pages ) {
			if ( isset( $pages['settings'] ) && ! isset( $pages['settings']['licenses'] ) ) {
				$pages = array_merge_recursive( (array) $pages, array( 'settings' => array( 'licenses' ) ), $pages );
			}

			return $pages;
		}, 20, 1 );

		// Init plugin updater.
		add_action( 'admin_init', array( $this, 'updater' ), 0 );

		// License stuff
		add_action( 'admin_notices', array( $this, 'license_notification' ) );
		add_filter( 'simcal_addon_status_simcal_' . $this->id, array( $this, 'check_license' ) );
		add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'pre_wp_update_check' ), 0 );

	}

	/**
	 * Plugin updater.
	 *
	 * @since 1.0.0
	 * @internal
	 *
	 * @return void
	 */
	public function updater() {

		$license    = simcal_get_license_key( strval( 'simcal_' . $this->id ) );
		$activation = simcal_get_license_status( strval( 'simcal_' . $this->id ) );

		if ( ! empty( $license ) && 'valid' == $activation ) {

			simcal_addon_updater( defined( 'SIMPLE_CALENDAR_STORE_URL' ) ? SIMPLE_CALENDAR_STORE_URL : simcal_get_url( 'home' ), SIMPLE_CALENDAR_FULLCALENDAR_MAIN_FILE, array(
				'version' => $this->version,
				'license' => $license,
				'item_id' => intval( $this->id ),
				'author'  => 'SureSwift Capital',
			) );

		}
	}
}

new Add_On_FullCalendar();
