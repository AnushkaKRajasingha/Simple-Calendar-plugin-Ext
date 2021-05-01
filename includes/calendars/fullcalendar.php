<?php
/**
 * FullCalendar
 *
 * @package SimpleCalendar\Calendars
 */
namespace SimpleCalendar\Calendars;

use SimpleCalendar\Abstracts\Calendar;
use SimpleCalendar\Abstracts\Calendar_View;
use SimpleCalendar\Calendars\Admin\FullCalendar_Admin;
use SimpleCalendar\Calendars\Views;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * FullCalendar.
 *
 * The default calendar view bundled with the FullCalendar add-on.
 *
 * @since 1.0.0
 */
class FullCalendar extends Calendar {

	/**
	 * Limit visibility of daily events.
	 *
	 * @access public
	 * @var int
	 */
	public $events_limit = -1;

	/**
	 * Trim characters event titles in grid.
	 *
	 * @access public
	 * @var int
	 */
	public $trim_titles = -1;

	/**
	 * Event bubbles action trigger.
	 *
	 * @access public
	 * @var string
	 */
	public $event_bubble_trigger = 'click';

	/**
	 * Today color.
	 *
	 * @access public
	 * @var string
	 */
	public $event_color = '#1e73be';

	/**
	 * Days with events color.
	 *
	 * @access public
	 * @var string
	 */
	public $text_color = '#ffffff';

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param int|object|\WP_Post|Calendar $calendar
	 */
	public function __construct( $calendar ) {

		$this->type  = 'fullcalendar';
		$this->name  = __( 'FullCalendar', 'simple-calendar-fullcalendar' );
		$this->views = array(
			'grid' => __( 'Grid', 'simple-calendar-fullcalendar' ),
		);

		parent::__construct( $calendar );

		if ( ! is_null( $this->post ) ) {

			$this->set_properties( $this->view->get_type() );

			$id    = $this->id;
			$theme = $this->theme;

			add_filter( 'simcal_calendar_class', function ( $class, $post_id ) use ( $theme, $id ) {
				if ( in_array( 'default-calendar', $class ) && $post_id === $id ) {
					array_push( $class, 'fullcalendar-' . $theme );
				}

				return $class;
			}, 10, 2 );

		}

		// Calendar settings handling.
		if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
			$admin = new FullCalendar_Admin();
		}
	}

	/**
	 * Set properties.
	 *
	 * @since  1.0.0
	 * @access private
	 *
	 * @param  $view
	 */
	private function set_properties( $view ) {

		// Set styles.
		if ( $event_color = get_post_meta( $this->id, '_fullcalendar_style_event_color', true ) ) {
			$this->event_color = esc_attr( $event_color );
		}
		if ( $text_color = get_post_meta( $this->id, '_fullcalendar_style_text_color', true ) ) {
			$this->text_color = esc_attr( $text_color );
		}

		// Hide too many events.
		if ( 'yes' == get_post_meta( $this->id, '_fullcalendar_limit_visible_events', true ) ) {
			$this->events_limit = absint( get_post_meta( $this->id, '_fullcalendar_visible_events', true ) );
		}

		// Use hover to open event bubbles.
		if ( 'hover' == get_post_meta( $this->id, '_fullcalendar_event_bubble_trigger', true ) ) {
			$this->event_bubble_trigger = 'hover';
		}

		// Trim long event titles.
		if ( 'yes' == get_post_meta( $this->id, '_fullcalendar_trim_titles', true ) ) {
			$this->trim_titles = max( absint( get_post_meta( $this->id, '_fullcalendar_trim_titles_chars', true ) ), 1 );
		}
	}

	/**
	 * Get a view.
	 *
	 * Returns one of this calendar's views.
	 *
	 * @since  1.0.0
	 *
	 * @param  string $view
	 *
	 * @return null|Calendar_View
	 */
	public function get_view( $view = '' ) {

		$view = ! empty( $view ) ? $view : 'grid';

		do_action( 'simcal_calendar_get_view', $this->type, $view );

		if ( 'grid' == $view || empty( $view ) ) {
			return new Views\FullCalendar_Grid( $this );
		}

		return null;
	}

}
