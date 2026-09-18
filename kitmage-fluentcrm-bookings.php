<?php
/**
 * Plugin Name:       KitMage FluentCRM Booking-Forms Connector
 * Description:       Adds a FluentBooking merge tag that links a guest to their FluentCRM form submissions.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author: Mike@KitMage
 * Author URI: http://kitmage.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       kitmage-fluentcrm-bookings
 */

defined( 'ABSPATH' ) || exit;

/**
 * Build the FluentCRM subscriber Form Submissions URL.
 *
 * The protocol is deliberately removed because FluentBooking's link editor adds
 * it separately when a merge tag is used as the link destination.
 *
 * @param int $subscriber_id FluentCRM subscriber ID.
 * @return string Protocol-relative admin URL, or an empty string for an invalid ID.
 */
function kitmage_fluentcrm_form_submissions_url( $subscriber_id ) {
	$subscriber_id = absint( $subscriber_id );

	if ( ! $subscriber_id ) {
		return '';
	}

	$url = admin_url( 'admin.php?page=fluentcrm-admin' )
		. '#/subscribers/'
		. $subscriber_id
		. '/form-submissions#fluentcrm_sub_info_body';

	return (string) preg_replace( '#^https?://#i', '', $url );
}

/**
 * Store a subscriber's form-submissions URL in a booking's custom data.
 *
 * @param object $booking       FluentBooking Booking model.
 * @param int    $subscriber_id FluentCRM subscriber ID.
 * @return void
 */
function kitmage_fluentbooking_save_subscriber_url( $booking, $subscriber_id ) {
	if ( ! $booking || empty( $booking->id ) || ! method_exists( $booking, 'getMeta' ) ) {
		return;
	}

	$url = kitmage_fluentcrm_form_submissions_url( $subscriber_id );

	if ( ! $url ) {
		return;
	}

	$custom_data = $booking->getMeta( 'custom_fields_data', array() );

	if ( ! is_array( $custom_data ) ) {
		$custom_data = array();
	}

	// Avoid an unnecessary metadata write when both hooks handle the booking.
	if ( isset( $custom_data['aspen_fluentcrm_form_submissions_url'] )
		&& $url === $custom_data['aspen_fluentcrm_form_submissions_url'] ) {
		return;
	}

	$custom_data['aspen_fluentcrm_form_submissions_url'] = $url;

	\FluentBooking\App\Services\Helper::updateBookingMeta(
		absint( $booking->id ),
		'custom_fields_data',
		$custom_data
	);
}

/**
 * Find the FluentCRM subscriber for a newly created booking.
 *
 * @param object $booking FluentBooking Booking model.
 * @return void
 */
function kitmage_fluentbooking_store_fluentcrm_url( $booking ) {
	if ( ! $booking || empty( $booking->id ) || empty( $booking->email ) ) {
		return;
	}

	if ( ! function_exists( 'FluentCrmApi' ) ) {
		return;
	}

	$email = sanitize_email( $booking->email );

	if ( ! $email ) {
		return;
	}

	$subscriber = FluentCrmApi( 'contacts' )->getContact( $email );

	if ( ! $subscriber || empty( $subscriber->id ) ) {
		return;
	}

	kitmage_fluentbooking_save_subscriber_url( $booking, $subscriber->id );
}

/**
 * Handle FluentBooking after it has persisted a booking's metadata.
 *
 * @param object $booking FluentBooking Booking model.
 * @return void
 */
function kitmage_fluentbooking_after_meta_update( $booking ) {
	kitmage_fluentbooking_store_fluentcrm_url( $booking );
}
add_action( 'fluent_booking/after_booking_meta_update', 'kitmage_fluentbooking_after_meta_update', 10, 4 );

/**
 * Handle the contact returned by FluentBooking's FluentCRM integration.
 *
 * This hook covers contacts created by the integration as well as existing
 * contacts processed by it.
 *
 * @param object $subscriber FluentCRM subscriber model.
 * @param object $booking    FluentBooking Booking model.
 * @return void
 */
function kitmage_fluentcrm_contact_added_by_booking( $subscriber, $booking ) {
	if ( ! $subscriber || empty( $subscriber->id ) ) {
		return;
	}

	kitmage_fluentbooking_save_subscriber_url( $booking, $subscriber->id );
}
add_action( 'fluent_crm/contact_added_by_fluent_booking', 'kitmage_fluentcrm_contact_added_by_booking', 10, 4 );
