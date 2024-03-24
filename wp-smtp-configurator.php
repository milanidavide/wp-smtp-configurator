<?php
/**
 * Plugin Name: WP SMTP Configurator
 * Description: Configure SMTP settings for WordPress.
 * Version: 0.1.1
 * Author: Davide Milani
 * Author URI: https://www.milanidavide.com/
 * License: BSD-3-Clause license
 * License URI: https://opensource.org/licenses/BSD-3-Clause
 * Text Domain: wp-smtp-configurator
 * Domain Path: /languages
 * Update URI: false
 *
 * @package WP SMTP Configurator
 */

add_action( 'init', 'wpsmtpc_check_and_configure_smtp' );

/**
 * Check and configure SMTP settings. If constants are missing, display admin notice.
 */
function wpsmtpc_check_and_configure_smtp() {
	$missing_constants = wpsmtpc_get_missing_smtp_constants();
	if ( empty( $missing_constants ) ) {
		add_action( 'phpmailer_init', 'wpsmtpc_configure_smtp' );
	} else {
		add_action( 'admin_notices', 'wpsmtpc_display_missing_constants_notice' );
	}
}

/**
 * Get the missing SMTP constants.
 *
 * @return array The missing constants.
 */
function wpsmtpc_get_missing_smtp_constants() {
	$required_constants = array(
		'WPSMTPC_CONFIG_HOST',
		'WPSMTPC_CONFIG_PORT',
		'WPSMTPC_CONFIG_USERNAME',
		'WPSMTPC_CONFIG_PASSWORD',
		'WPSMTPC_CONFIG_ENCRYPTION',
	);

	return array_filter(
		$required_constants,
		function ( $constant ) {
			return ! defined( $constant );
		}
	);
}

/**
 * Configures the SMTP settings for the given PHPMailer object.
 *
 * @param object $phpmailer The PHPMailer object to configure.
 */
function wpsmtpc_configure_smtp( $phpmailer ) {
	$phpmailer->isSMTP();
	$phpmailer->SMTPSecure = WPSMTPC_CONFIG_ENCRYPTION;
	$phpmailer->Host       = WPSMTPC_CONFIG_HOST;
	$phpmailer->Port       = WPSMTPC_CONFIG_PORT;
	$phpmailer->SMTPAuth   = true;
	$phpmailer->Username   = WPSMTPC_CONFIG_USERNAME;
	$phpmailer->Password   = WPSMTPC_CONFIG_PASSWORD;
}

/**
 * Display a notice for missing SMTP configuration constants.
 */
function wpsmtpc_display_missing_constants_notice() {
	$missing_constants = wpsmtpc_get_missing_smtp_constants();

	$constants_info = array(
		'WPSMTPC_CONFIG_HOST'       => __( 'The hostname of your SMTP server (e.g., smtp.example.com)', 'wp-smtp-configurator' ),
		'WPSMTPC_CONFIG_PORT'       => __( 'The port number your SMTP server uses (e.g., 465 for SSL, 587 for TLS)', 'wp-smtp-configurator' ),
		'WPSMTPC_CONFIG_USERNAME'   => __( 'The username for authenticating with your SMTP server', 'wp-smtp-configurator' ),
		'WPSMTPC_CONFIG_PASSWORD'   => __( 'The password for authenticating with your SMTP server', 'wp-smtp-configurator' ),
		'WPSMTPC_CONFIG_ENCRYPTION' => __( 'The encryption method to use (e.g., ssl or tls)', 'wp-smtp-configurator' ),
	);
	?>

	<div class="notice notice-error">
		<p><?php esc_html_e( 'SMTP configuration constants are missing. Please define the following constants in your wp-config.php file:', 'wp-smtp-configurator' ); ?></p>
		<ul class="ul-disc">
			<?php foreach ( $missing_constants as $constant ) : ?>
				<?php if ( isset( $constants_info[ $constant ] ) ) : ?>
					<li><code><?php echo esc_html( $constant ); ?></code> <em><?php echo esc_html( $constants_info[ $constant ] ); ?></em></li>
				<?php else : ?>
					<li><code><?php echo esc_html( $constant ); ?></code></li>
				<?php endif; ?>
			<?php endforeach; ?>
		</ul>
	</div>

	<?php
}
