<?php
/**
 * Admin View: Page - Status Report.
 *
 * @package TrueLayer\Includes\Admin\Views
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<table class="wc_status_table widefat" cellspacing="0">
	<thead>
	<tr>
		<th colspan="6" data-export-label="TrueLayer Request Log">
			<h2><?php esc_html_e( 'TrueLayer', 'truelayer-for-woocommerce' ); ?><?php echo wc_help_tip( esc_html__( 'TrueLayer System Status.', 'truelayer-for-woocommerce' ) ); /* phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped */ ?></h2>
		</th>
	</tr>

	<?php
	$db_logs = get_option( 'krokedil_debuglog_truelayer', array() );

	if ( ! empty( $db_logs ) ) {
		$db_logs = array_reverse( json_decode( $db_logs, true ) );

		?>
			<tr>
				<td ><strong><?php esc_html_e( 'Time', 'truelayer - for - woocommerce' ); ?></strong></td>
				<td class="help"></td>
				<td ><strong><?php esc_html_e( 'Request', 'truelayer - for - woocommerce' ); ?></strong></td>
				<td ><strong><?php esc_html_e( 'Response Code', 'truelayer - for - woocommerce' ); ?></strong></td>
				<td ><strong><?php esc_html_e( 'Response Message', 'truelayer - for - woocommerce' ); ?></strong></td>
			</tr>
		</thead>
		<tbody>
		<?php


		foreach ( $db_logs as $log ) {

			$timestamp      = isset( $log['timestamp'] ) ? $log['timestamp'] : '';
			$log_title      = isset( $log['title'] ) ? $log['title'] : '';
			$code           = isset( $log['response']['code']['response']['code'] ) ? $log['response']['code']['response']['code'] : '';
			$body           = isset( $log['response']['body'] ) ? wp_json_encode( $log['response']['body'] ) : '';
			$error_messages = isset( $log['response']['code']['response']['message'] ) ? 'Error messages: ' . wp_json_encode( $log['response']['code']['response']['message'] ) : '';

			?>
			<tr>
				<td><?php echo esc_html( $timestamp ); ?></td>
				<td class="help"></td>
				<td><?php echo esc_html( $log_title ); ?>
					<span style="display: none;">,
						Response code: <?php echo esc_html( $code ); ?>,
						Response message: <?php echo esc_html( $body ); ?>
					</span</td>
				<td><?php echo esc_html( $code ); ?></td>
				<td><?php echo esc_html( $error_messages ); ?></td>
			</tr>
			<?php
		}
	} else {
		?>
		</thead>
		<tbody>
			<tr>
				<td colspan="6" data-export-label="No TrueLayer errors"><?php esc_html_e( 'No error logs', 'truelayer - for - woocommerce' ); ?></td>
	</tr>
		<?php
	}
	?>
	</tbody>
	</table>
