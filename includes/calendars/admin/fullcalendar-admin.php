<?php
/**
 * FullCalendar - Admin
 *
 * @package    SimpleCalendar/Feeds
 */
namespace SimpleCalendar\Calendars\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * FullCalendar view admin.
 *
 * @since 1.0.0
 */
class FullCalendar_Admin {

	/**
	 * Used to load minified assets
	 *
	 * @since 1.0.0
	 */
	public $min = '.min';

	/**
	 * Hook in tabs.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		$this->min = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG == true ) ? '' : '.min';

		if ( simcal_is_admin_screen() !== false ) {
			add_action( 'simcal_settings_meta_calendar_panel', array(
				$this,
				'add_settings_meta_fullcalendar_panel',
			), 10, 1 );
		}
		add_action( 'simcal_process_settings_meta', array( $this, 'process_meta' ), 10, 1 );

		add_action( 'admin_enqueue_scripts', array( $this, 'load' ), 100 );
	}

	public function load() {

		if ( simcal_is_admin_screen() !== false ) {

			$css_path = SIMPLE_CALENDAR_FULLCALENDAR_ASSETS . 'css/';
			$js_path  = SIMPLE_CALENDAR_FULLCALENDAR_ASSETS . 'js/';

			// TODO Fix this whole class getting loaded & run multiple times on one post edit?

			if ( ! wp_script_is( 'simcal-fullcal-admin' ) ) {

				// Need to load FC vendor libs for admin too.
				wp_register_script( 'simcal-fullcal-moment', $js_path . 'vendor/moment' . $this->min . '.js', array(), SIMPLE_CALENDAR_FULLCALENDAR_VERSION, true );
				wp_register_script( 'simcal-fullcal', $js_path . 'vendor/fullcalendar' . $this->min . '.js', array(
					'jquery',
					'simcal-fullcal-moment',
				), SIMPLE_CALENDAR_FULLCALENDAR_VERSION, true );
				wp_register_script( 'simcal-fullcal-locale', $js_path . 'vendor/lang-all' . $this->min . '.js', array( 'simcal-fullcal' ), SIMPLE_CALENDAR_FULLCALENDAR_VERSION, true );
				wp_register_script( 'simcal-fullcal-admin', $js_path . 'admin' . $this->min . '.js', array(
					'jquery',
					'simcal-fullcal',
					'simcal-fullcal-locale',
				), SIMPLE_CALENDAR_FULLCALENDAR_VERSION, true );

				// Localized PHP to JS global vars for admin.
				$localized_admin_globals = array(

					// Load i18n strings here.
					'displayLanguage' => get_post_meta( get_the_ID(), '_fullcalendar_display_language', true ),

					// Set boolean values to string 'true' or 'false' to avoid localization stringifying to '1'.
					'scriptDebug'     => ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? 'true' : 'false',
				);

				wp_enqueue_script( 'simcal-fullcal-admin' );

				// Localize admin global vars.
				wp_localize_script( 'simcal-fullcal-admin', 'simcalFullCalAdminGlobals', $localized_admin_globals );
			}

			if ( ! wp_style_is( 'simcal-fullcal-admin' ) ) {
				wp_register_style( 'simcal-fullcal-admin', $css_path . 'admin' . $this->min . '.css', false, SIMPLE_CALENDAR_FULLCALENDAR_VERSION );
				wp_enqueue_style( 'simcal-fullcal-admin' );
			}
		}
	}

	/**
	 * Add FullCalendar specific settings to the appearance tab
	 *
	 * @since  1.0.0
	 *
	 * @param int $post_id
	 */
	public function add_settings_meta_fullcalendar_panel( $post_id ) {
		?>
		<table id="fullcalendar-settings">
			<thead>
			<tr>
				<th colspan="2"><?php _e( 'FullCalendar Settings', 'simple-calendar-fullcalendar' ); ?></th>
			</tr>
			</thead>

			<tbody class="simcal-panel-section">

			<tr class="simcal-panel-field simcal-fullcalendar-grid">
				<th>
					<label for="_fullcalendar_height_select"><?php _e( 'Calendar Height', 'simple-calendar-fullcalendar' ); ?></label>
				</th>
				<td>
					<?php
					$fullcalendar_height_select = get_post_meta( $post_id, '_fullcalendar_height_select', true );
					$fullcalendar_height        = get_post_meta( $post_id, '_fullcalendar_height', true );

					if ( false === $fullcalendar_height_select || empty( $fullcalendar_height_select ) ) {
						$fullcalendar_height_select = 'not_set';
					}
					?>

					<select name="_fullcalendar_height_select"
					        id="_fullcalendar_height_select"
					        class="simcal-field simcal-field-select simcal-field-show-other">
						<option value="not_set" <?php selected( 'not_set', $fullcalendar_height_select, true ); ?>><?php _e( 'Default (1.35 aspect ratio)', 'simple-calendar-fullcalendar' ); ?></option>
						<option value="auto" <?php selected( 'auto', $fullcalendar_height_select, true ); ?>><?php _e( 'Auto (no scrollbars)', 'simple-calendar-fullcalendar' ); ?></option>
						<option value="use_custom" data-show-field="_fullcalendar_height_wrap" <?php selected( 'use_custom', $fullcalendar_height_select, true ); ?>><?php _e( 'Custom', 'simple-calendar-fullcalendar' ); ?></option>
					</select>
					<i class="simcal-icon-help simcal-help-tip" data-tip="<?php _e( 'Adjust the height of the calendar. The default behavior is to render a width-to-height aspect ratio of 1.35. Select &quot;Custom&quot; to specify an exact height.', 'simple-calendar-fullcalendar' ) ?>"></i>
					<p id="_fullcalendar_height_wrap" style="<?php echo $fullcalendar_height_select != 'use_custom' ? 'display: none;' : ''; ?>">
						<label for="_fullcalendar_height">
							<input type="text"
							       name="_fullcalendar_height"
							       id="_fullcalendar_height"
							       class="simcal-field simcal-field-text simcal-field-tiny"
							       value="<?php echo esc_attr( $fullcalendar_height ); ?>" />
							<?php _e( 'px', 'simple-calendar-fullcalendar' ); ?>
						</label>
					</p>
				</td>
			</tr>

			<tr class="simcal-panel-field simcal-fullcalendar-grid">
				<th>
					<label for="_fullcalendar_today_button"><?php _e( 'Today Button', 'simple-calendar-fullcalendar' ); ?></label>
				</th>
				<td>
					<?php
					$today_button = get_post_meta( $post_id, '_fullcalendar_today_button', true );
					$today_button = ( ! empty( $today_button ) ? $today_button : 'yes' );

					simcal_print_field( array(
						'type'    => 'checkbox',
						'name'    => '_fullcalendar_today_button',
						'id'      => '_fullcalendar_today_button',
						'class'   => array(
							'',
						),
						'value'   => 'yes' == $today_button ? 'yes' : 'no',
						'text'    => __( 'Show', 'simple-calendar-fullcalendar' ),
						'tooltip' => __( "Display the today button at the top of the calendar to allow visitors to quickly jump to today's date.", 'simple-calendar-fullcalendar' ),
					) );
					?>
				</td>
			</tr>
			<tr class="simcal-panel-field simcal-fullcalendar-grid">
				<th><label><?php _e( 'View Buttons', 'simple-calendar-fullcalendar' ); ?></label></th>
				<td>
					<ul class="simcal-field-checkboxes-inline">
						<li>
							<?php
							$month_button = get_post_meta( $post_id, '_fullcalendar_month_button', true );
							$month_button = ( ! empty( $month_button ) ? $month_button : 'yes' );

							simcal_print_field( array(
								'type'  => 'checkbox',
								'name'  => '_fullcalendar_month_button',
								'id'    => '_fullcalendar_month_button',
								'class' => array(
									'',
								),
								'value' => 'yes' == $month_button ? 'yes' : 'no',
								'text'  => '<label for="_fullcalendar_month_button">' . __( 'Month', 'simple-calendar-fullcalendar' ) . '</label>',
							) );
							?>
						</li>

						<li>
							<?php
							$week_button = get_post_meta( $post_id, '_fullcalendar_week_button', true );
							$week_button = ( ! empty( $week_button ) ? $week_button : 'yes' );

							simcal_print_field( array(
								'type'  => 'checkbox',
								'name'  => '_fullcalendar_week_button',
								'id'    => '_fullcalendar_week_button',
								'class' => array(
									'',
								),
								'value' => 'yes' == $week_button ? 'yes' : 'no',
								'text'  => '<label for="_fullcalendar_week_button">' . __( 'Week', 'simple-calendar-fullcalendar' ) . '</label>',
							) );
							?>
						</li>

						<li>
							<?php

							$day_button = get_post_meta( $post_id, '_fullcalendar_day_button', true );
							$day_button = ( ! empty( $day_button ) ? $day_button : 'yes' );

							simcal_print_field( array(
								'type'    => 'checkbox',
								'name'    => '_fullcalendar_day_button',
								'id'      => '_fullcalendar_day_button',
								'class'   => array(
									'',
								),
								'value'   => 'yes' == $day_button ? 'yes' : 'no',
								'text'    => '<label for="_fullcalendar_day_button">' . __( 'Day', 'simple-calendar-fullcalendar' ) . '</label>',
								'tooltip' => __( 'Display buttons at the top of the calendar that allow the visitor to switch between month, week and day views.', 'simple-calendar-fullcalendar' ),
							) );
							?>
						</li>
					</ul>
				</td>
			</tr>
			<tr class="simcal-panel-field simcal-fullcalendar-grid">
				<th>
					<label for="_fullcalendar_default_view"><?php _e( 'Default View', 'simple-calendar-fullcalendar' ); ?></label>
				</th>
				<td>
					<?php

					$default_view = get_post_meta( $post_id, '_fullcalendar_default_view', true );

					simcal_print_field( array(
						'type'    => 'select',
						'name'    => '_fullcalendar_default_view',
						'id'      => '_fullcalendar_default_view',
						'tooltip' => __( 'The view to show when the calendar is first loaded.', 'simple-calendar-fullcalendar' ),
						'options' => array(
							'month'      => __( 'Month', 'simple-calendar-fullcalendar' ),
							'agendaWeek' => __( 'Week', 'simple-calendar-fullcalendar' ),
							'agendaDay'  => __( 'Day', 'simple-calendar-fullcalendar' ),
						),
						'default' => 'month',
						'value'   => $default_view,
					) );
					?>
				</td>
			</tr>
			<tr class="simcal-panel-field simcal-fullcalendar-grid">
				<th>
					<label for="_fullcalendar_default_start_time"><?php _e( 'Default Start Time', 'simple-calendar-fullcalendar' ); ?></label>
				</th>
				<td>
					<?php

					// TODO Show only for week & day views.

					// TODO Some defaults are set in 2-3 places. Consolidate at some point.
					// Examples: default_start_time, display_min_time, display_max_time, display_language (all added in 1.0.4).

					$default_start_time = get_post_meta( $post_id, '_fullcalendar_default_start_time', true );

					simcal_print_field( array(
						'type'    => 'select',
						'name'    => '_fullcalendar_default_start_time',
						'id'      => '_fullcalendar_default_start_time',
						'tooltip' => __( 'The time of day to scroll to at top of view when the calendar is first loaded. Week and day views only. Default is 6:00 am.', 'simple-calendar-fullcalendar' ),
						'options' => array(
							'0:00' => '12:00 am',
							'1:00' => '1:00 am',
							'2:00' => '2:00 am',
							'3:00' => '3:00 am',
							'4:00' => '4:00 am',
							'5:00' => '5:00 am',
							'6:00' => '6:00 am',
							'7:00' => '7:00 am',
							'8:00' => '8:00 am',
							'9:00' => '9:00 am',
							'10:00' => '10:00 am',
							'11:00' => '11:00 am',
							'12:00' => '12:00 pm',
							'13:00' => '1:00 pm',
							'14:00' => '2:00 pm',
							'15:00' => '3:00 pm',
							'16:00' => '4:00 pm',
							'17:00' => '5:00 pm',
							'18:00' => '6:00 pm',
							'19:00' => '7:00 pm',
							'20:00' => '8:00 pm',
							'21:00' => '9:00 pm',
							'22:00' => '10:00 pm',
							'23:00' => '11:00 pm',
							'24:00' => '12:00 am',
						),
						'default' => '6:00',
						'value'   => $default_start_time,
					) );
					?>
				</td>
			</tr>
			<tr class="simcal-panel-field simcal-fullcalendar-grid">
				<th>
					<label for="_fullcalendar_time_format">
						<?php _e( 'Event Title Time Format', 'simple-calendar-fullcalendar' ); ?>
					</label>
				</th>
				<td>
					<?php
					$time_format = get_post_meta( $post_id, '_fullcalendar_time_format', true );
					$time_format = ! empty( $time_format ) ? $time_format : 'h:mmt';

					simcal_print_field( array(
						'type'    => 'standard',
						'subtype' => 'text',
						'name'    => '_fullcalendar_time_format',
						'id'      => '_fullcalendar_time_format',
						'value'   => $time_format,
						'tooltip' => __( 'The Fullcalendar.js formatDate format for the event title time (ex. "h:mmt") &mdash; see <a style="word-wrap:break-word;color:#FFFFFF">https://fullcalendar.io/docs/v1/formatDate</a> for time formatting options' ),
						'class'   => array(
							'simcal-field-small',
						),
					) );
					?>
					<p>
						<a href="https://fullcalendar.io/docs/v1/formatDate">See formatting options documentation.</a>
					</p>
				</td>
			</tr>
			<tr class="simcal-panel-field simcal-fullcalendar-grid">
				<th>
					<label for="_fullcalendar_default_start_time"><?php _e( 'Limit Display Times', 'simple-calendar-fullcalendar' ); ?></label>
				</th>
				<td>
					<?php

					// TODO Show only for week & day views.

					$display_min_time = get_post_meta( $post_id, '_fullcalendar_display_min_time', true );

					simcal_print_field( array(
						'type'    => 'select',
						'name'    => '_fullcalendar_display_min_time',
						'id'      => '_fullcalendar_display_min_time',
						'options' => array(
							'0:00'  => '12:00 am (start of day)',
							'1:00'  => '1:00 am',
							'2:00'  => '2:00 am',
							'3:00'  => '3:00 am',
							'4:00'  => '4:00 am',
							'5:00'  => '5:00 am',
							'6:00'  => '6:00 am',
							'7:00'  => '7:00 am',
							'8:00'  => '8:00 am',
							'9:00'  => '9:00 am',
							'10:00' => '10:00 am',
							'11:00' => '11:00 am',
							'12:00' => '12:00 pm',
							'13:00' => '1:00 pm',
							'14:00' => '2:00 pm',
							'15:00' => '3:00 pm',
							'16:00' => '4:00 pm',
							'17:00' => '5:00 pm',
							'18:00' => '6:00 pm',
							'19:00' => '7:00 pm',
							'20:00' => '8:00 pm',
							'21:00' => '9:00 pm',
							'22:00' => '10:00 pm',
							'23:00' => '11:00 pm',
						),
						'default' => '0:00',
						'value'   => $display_min_time,
					) );
					?>

					to

					<?php

					$display_max_time = get_post_meta( $post_id, '_fullcalendar_display_max_time', true );

					simcal_print_field( array(
						'type'    => 'select',
						'name'    => '_fullcalendar_display_max_time',
						'id'      => '_fullcalendar_display_max_time',
						'tooltip' => __( 'Part of day to limit times displayed to. Week and day views only. Default is entire day.', 'simple-calendar-fullcalendar' ),
						'options' => array(
							'1:00'  => '1:00 am',
							'2:00'  => '2:00 am',
							'3:00'  => '3:00 am',
							'4:00'  => '4:00 am',
							'5:00'  => '5:00 am',
							'6:00'  => '6:00 am',
							'7:00'  => '7:00 am',
							'8:00'  => '8:00 am',
							'9:00'  => '9:00 am',
							'10:00' => '10:00 am',
							'11:00' => '11:00 am',
							'12:00' => '12:00 pm',
							'13:00' => '1:00 pm',
							'14:00' => '2:00 pm',
							'15:00' => '3:00 pm',
							'16:00' => '4:00 pm',
							'17:00' => '5:00 pm',
							'18:00' => '6:00 pm',
							'19:00' => '7:00 pm',
							'20:00' => '8:00 pm',
							'21:00' => '9:00 pm',
							'22:00' => '10:00 pm',
							'23:00' => '11:00 pm',
							'24:00' => '12:00 am (end of day)',
						),
						'default' => '24:00',
						'value'   => $display_max_time,
					) );
					?>
				</td>
			</tr>
			<tr class="simcal-panel-field simcal-fullcalendar-grid">
				<th>
					<label for="_fullcalendar_display_language"><?php _e( 'Display Language', 'simple-calendar-fullcalendar' ); ?></label>
				</th>
				<td>
					<?php

					$display_language    = get_post_meta( $post_id, '_fullcalendar_display_language', true );
					$installed_languages = get_available_languages( SIMPLE_CALENDAR_FULLCALENDAR_PLUGIN_DIR . 'i18n' );
					$languages           = array();
					if ( ! empty( $installed_languages ) ) {
						foreach ( $installed_languages as $language ) {
							$parts              = explode( '-', $language );
							$last               = array_pop( $parts );
							$key_parts = explode( '_', $last );
							$lang_key = is_array( $key_parts ) ? strtolower( array_pop( $key_parts ) ) : strtolower( $key_parts );
							$languages[ $lang_key ] = $last;
						}
					}

					wp_dropdown_languages( array(
						'id'                          => '_fullcalendar_display_language',
						'name'                        => '_fullcalendar_display_language',
						'languages'                   => $languages,
						'show_available_translations' => false,
						'selected'                    => $display_language,
					));
					?>
				</td>
			</tr>

			</tbody>

			<tbody class="simcal-panel-section">

			<tr class="simcal-panel-field simcal-fullcalendar-grid">
				<th>
					<label for="_fullcalendar_event_bubble_trigger"><?php _e( 'Event Bubbles', 'simple-calendar-fullcalendar' ); ?></label>
				</th>
				<td>
					<?php

					$bubbles = get_post_meta( $post_id, '_fullcalendar_event_bubble_trigger', true );

					simcal_print_field( array(
						'type'    => 'radio',
						'inline'  => 'inline',
						'name'    => '_fullcalendar_event_bubble_trigger',
						'id'      => '_fullcalendar_event_bubble_trigger',
						'tooltip' => __( 'Open event bubbles in calendar grid by clicking or hovering on event titles. On mobile devices it will always default to tapping.', 'simple-calendar-fullcalendar' ),
						'value'   => $bubbles ? $bubbles : 'hover',
						'default' => 'hover',
						'options' => array(
							'click' => __( 'Click', 'simple-calendar-fullcalendar' ),
							'hover' => __( 'Hover', 'simple-calendar-fullcalendar' ),
						),
					) );

					?>
				</td>
			</tr>
			<tr class="simcal-panel-field simcal-fullcalendar-grid">
				<th>
					<label for="_fullcalendar_trim_titles"><?php _e( 'Trim Event Titles', 'simple-calendar-fullcalendar' ); ?></label>
				</th>
				<td>
					<?php

					$trim = get_post_meta( $post_id, '_fullcalendar_trim_titles', true );

					simcal_print_field( array(
						'type'       => 'checkbox',
						'name'       => '_fullcalendar_trim_titles',
						'id'         => '_fullcalendar_trim_titles',
						'class'      => array(
							'simcal-field-show-next',
						),
						'value'      => 'yes' == $trim ? 'yes' : 'no',
						'attributes' => array(
							'data-show-next-if-value' => 'yes',
						),
					) );

					simcal_print_field( array(
						'type'       => 'standard',
						'subtype'    => 'number',
						'name'       => '_fullcalendar_trim_titles_chars',
						'id'         => '_fullcalendar_trim_titles_chars',
						'tooltip'    => __( 'Shorten event titles in calendar grid to a specified length in characters.', 'simple-calendar-fullcalendar' ),
						'class'      => array(
							'simcal-field-tiny',
						),
						'value'      => 'yes' == $trim ? strval( max( absint( get_post_meta( $post_id, '_fullcalendar_trim_titles_chars', true ) ), 1 ) ) : '20',
						'attributes' => array(
							'min' => '1',
						),
					) );

					?>
				</td>
			</tr>
			<tr class="simcal-panel-field simcal-fullcalendar-grid simcal-default-calendar-list">
				<th>
					<label for="_fullcalendar_limit_visible_events"><?php _e( 'Limit Visible Events', 'simple-calendar-fullcalendar' ); ?></label>
				</th>
				<td>
					<?php

					$limit = get_post_meta( $post_id, '_fullcalendar_limit_visible_events', true );

					simcal_print_field( array(
						'type'       => 'checkbox',
						'name'       => '_fullcalendar_limit_visible_events',
						'id'         => '_fullcalendar_limit_visible_events',
						'value'      => 'yes' == $limit ? 'yes' : 'no',
						'class'      => array(
							'simcal-field-show-next',
						),
						'attributes' => array(
							'data-show-next-if-value' => 'yes',
						),
					) );

					$visible_events = absint( get_post_meta( $post_id, '_fullcalendar_visible_events', true ) );
					$visible_events = $visible_events > 0 ? $visible_events : 3;

					simcal_print_field( array(
						'type'       => 'standard',
						'subtype'    => 'number',
						'name'       => '_fullcalendar_visible_events',
						'id'         => '_fullcalendar_visible_events',
						'tooltip'    => __( 'Limit the number of initial visible events on each day to a set maximum.', 'simple-calendar-fullcalendar' ),
						'class'      => array(
							'simcal-field-tiny',
						),
						'value'      => $visible_events,
						'attributes' => array(
							'min' => '1',
						),
					) );

					?>
				</td>
			</tr>
			</tbody>

			<?php
			$default_event_color      = '#1e73be';
			$default_text_color_color = '#ffffff';
			?>

			<tbody class="simcal-panel-section">
			<tr class="simcal-panel-field simcal-fullcalendar-grid">
				<th>
					<label for="_fullcalendar_style_event_color"><?php _e( 'Event Color', 'simple-calendar-fullcalendar' ); ?></label>
				</th>
				<td>
					<?php

					$saved = get_post_meta( $post_id, '_fullcalendar_style_event_color', true );
					$value = ! $saved ? $default_event_color : $saved;

					simcal_print_field( array(
						'type'    => 'standard',
						'subtype' => 'color-picker',
						'name'    => '_fullcalendar_style_event_color',
						'id'      => '_fullcalendar_style_event_color',
						'value'   => $value,
						'tooltip' => __( 'Sets the background color of each event. If using a Google Calendar PRO event source and "Use event colors" is enabled, background colors will instead be set from each Google calendar event.', 'simple-calendar-fullcalendar' ),
					) );

					?>
				</td>
			</tr>

			<tr class="simcal-panel-field simcal-fullcalendar-grid">
				<th>
					<label for="_fullcalendar_style_text_color"><?php _e( 'Event Text Color', 'simple-calendar-fullcalendar' ); ?></label>
				</th>
				<td>
					<?php

					$saved = get_post_meta( $post_id, '_fullcalendar_style_text_color', true );
					$value = ! $saved ? $default_text_color_color : $saved;

					simcal_print_field( array(
						'type'    => 'standard',
						'subtype' => 'color-picker',
						'name'    => '_fullcalendar_style_text_color',
						'id'      => '_fullcalendar_style_text_color',
						'value'   => $value,
						'tooltip' => __( 'Sets the text color of each event.', 'simple-calendar-fullcalendar' ),
					) );

					?>
				</td>
			</tr>
			</tbody>
		</table>
		<?php

	}

	/**
	 * Process meta fields.
	 *
	 * @since 1.0.0
	 *
	 * @param int $post_id
	 */
	public function process_meta( $post_id ) {

		// Height
		$height_select = isset( $_POST['_fullcalendar_height_select'] ) ? esc_attr( $_POST['_fullcalendar_height_select'] ) : 'not_set';
		update_post_meta( $post_id, '_fullcalendar_height_select', $height_select );
		$height = isset( $_POST['_fullcalendar_height'] ) ? intval( sanitize_text_field( $_POST['_fullcalendar_height'] ) ) : '';
		update_post_meta( $post_id, '_fullcalendar_height', $height );

		// Today button
		$today_button = isset( $_POST['_fullcalendar_today_button'] ) ? 'yes' : 'no';
		update_post_meta( $post_id, '_fullcalendar_today_button', $today_button );

		// Month button
		$month_button = isset( $_POST['_fullcalendar_month_button'] ) ? 'yes' : 'no';
		update_post_meta( $post_id, '_fullcalendar_month_button', $month_button );

		// Week button
		$week_button = isset( $_POST['_fullcalendar_week_button'] ) ? 'yes' : 'no';
		update_post_meta( $post_id, '_fullcalendar_week_button', $week_button );

		// Day button
		$day_button = isset( $_POST['_fullcalendar_day_button'] ) ? 'yes' : 'no';
		update_post_meta( $post_id, '_fullcalendar_day_button', $day_button );

		// Default view
		$default_view = isset( $_POST['_fullcalendar_default_view'] ) ? esc_attr( $_POST['_fullcalendar_default_view'] ) : 'month';
		update_post_meta( $post_id, '_fullcalendar_default_view', $default_view );

		// TODO Some defaults are set in 2-3 places. Consolidate at some point.
		// Examples: default_start_time, display_min_time, display_max_time, display_language (all added in 1.0.4).

		// Default start time
		$default_start_time = isset( $_POST['_fullcalendar_default_start_time'] ) ? esc_attr( $_POST['_fullcalendar_default_start_time'] ) : '6:00';
		update_post_meta( $post_id, '_fullcalendar_default_start_time', $default_start_time );

		// Limit display times
		$display_min_time = isset( $_POST['_fullcalendar_display_min_time'] ) ? esc_attr( $_POST['_fullcalendar_display_min_time'] ) : '0:00';
		update_post_meta( $post_id, '_fullcalendar_display_min_time', $display_min_time );
		$display_max_time = isset( $_POST['_fullcalendar_display_max_time'] ) ? esc_attr( $_POST['_fullcalendar_display_max_time'] ) : '24:00';
		update_post_meta( $post_id, '_fullcalendar_display_max_time', $display_max_time );

		// FC display language
		$display_language = isset( $_POST['_fullcalendar_display_language'] ) ? esc_attr( $_POST['_fullcalendar_display_language'] ) : 'en';
		update_post_meta( $post_id, '_fullcalendar_display_language', $display_language );

		// Event color.
		$event_color = isset( $_POST['_fullcalendar_style_event_color'] ) ? sanitize_text_field( $_POST['_fullcalendar_style_event_color'] ) : '#1e73be';
		update_post_meta( $post_id, '_fullcalendar_style_event_color', $event_color );

		// Event text color.
		$text_color = isset( $_POST['_fullcalendar_style_text_color'] ) ? sanitize_text_field( $_POST['_fullcalendar_style_text_color'] ) : '#ffffff';
		update_post_meta( $post_id, '_fullcalendar_style_text_color', $text_color );

		// Limit number of initially visible daily events.
		$limit = isset( $_POST['_fullcalendar_limit_visible_events'] ) ? 'yes' : 'no';
		update_post_meta( $post_id, '_fullcalendar_limit_visible_events', $limit );
		$number = isset( $_POST['_fullcalendar_visible_events'] ) ? absint( $_POST['_fullcalendar_visible_events'] ) : 3;
		update_post_meta( $post_id, '_fullcalendar_visible_events', $number );

		// Grid event bubbles action.
		$bubbles = isset( $_POST['_fullcalendar_event_bubble_trigger'] ) ? esc_attr( $_POST['_fullcalendar_event_bubble_trigger'] ) : 'hover';
		update_post_meta( $post_id, '_fullcalendar_event_bubble_trigger', $bubbles );

		// Trim event titles characters length.
		$trim = isset( $_POST['_fullcalendar_trim_titles'] ) ? 'yes' : 'no';
		update_post_meta( $post_id, '_fullcalendar_trim_titles', $trim );
		$chars = isset( $_POST['_fullcalendar_trim_titles_chars'] ) ? max( absint( $_POST['_fullcalendar_trim_titles_chars'] ), 1 ) : 20;
		update_post_meta( $post_id, '_fullcalendar_trim_titles_chars', $chars );

		// Event title time format.
		$time_format = isset( $_POST['_fullcalendar_time_format'] ) ? sanitize_text_field( $_POST['_fullcalendar_time_format'] ) : 'h:mmt';
		update_post_meta( $post_id, '_fullcalendar_time_format', $time_format );
	}
}
