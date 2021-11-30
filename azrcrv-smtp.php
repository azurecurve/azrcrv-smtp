<?php
/**
 * ------------------------------------------------------------------------------
 * Plugin Name: SMTP
 * Description: Simple Mail Transport Protocol (SMTP) plugin.
 * Version: 1.4.2
 * Author: azurecurve
 * Author URI: https://development.azurecurve.co.uk/classicpress-plugins/
 * Plugin URI: https://development.azurecurve.co.uk/classicpress-plugins/smtp/
 * Text Domain: azrcrv-smtp
 * Domain Path: /languages
 * ------------------------------------------------------------------------------
 * This is free software released under the terms of the General Public License,
 * version 2, or later. It is distributed WITHOUT ANY WARRANTY; without even the
 * implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. Full
 * text of the license is available at https://www.gnu.org/licenses/gpl-2.0.html.
 * ------------------------------------------------------------------------------
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

// include plugin menu
require_once dirname( __FILE__ ) . '/pluginmenu/menu.php';
add_action( 'admin_init', 'azrcrv_create_plugin_menu_smtp' );

// include update client
require_once dirname( __FILE__ ) . '/libraries/updateclient/UpdateClient.class.php';

// set PHPMailer namespace
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Setup registration activation hook, actions, filters and shortcodes.
 *
 * @since 1.0.0
 */
// add actions
register_activation_hook( __FILE__, 'azrcrv_smtp_activate' );
add_action( 'admin_menu', 'azrcrv_smtp_create_admin_menu' );
add_action( 'admin_enqueue_scripts', 'azrcrv_smtp_load_admin_style' );
add_action( 'admin_post_azrcrv_smtp_save_options', 'azrcrv_smtp_save_options' );
add_action( 'admin_post_azrcrv_smtp_send_test_email', 'azrcrv_smtp_send_test_email' );
add_action( 'admin_action_azrcrv_smtp_import_options', 'azrcrv_smtp_import_options' );
add_action( 'wp_ajax_azrcrv_smtp_import_dismiss', 'azrcrv_smtp_import_dismiss' );

add_action( 'plugins_loaded', 'azrcrv_smtp_load_languages' );
add_action( 'phpmailer_init', 'azrcrv_smtp_send_smtp_email' );

// add filters
add_filter( 'plugin_action_links', 'azrcrv_smtp_add_plugin_action_link', 10, 2 );
add_filter( 'codepotent_update_manager_image_path', 'azrcrv_smtp_custom_image_path' );
add_filter( 'codepotent_update_manager_image_url', 'azrcrv_smtp_custom_image_url' );

/**
 * Function needed because wp_parse_args() is not recursive.
 *
 * @since 1.2.0
 */
function azrcrv_smtp_recursive_merge( &$a, $b ) {
	$a      = (array) $a;
	$b      = (array) $b;
	$result = $b;
	foreach ( $a as $k => &$v ) {
		if ( is_array( $v ) && isset( $result[ $k ] ) ) {
			$result[ $k ] = azrcrv_smtp_recursive_merge( $v, $result[ $k ] );
		} else {
			$result[ $k ] = $v;
		}
	}
	return $result;
}

/**
 * Manage migratation from Easy WP SMTP on activation.
 *
 * @since 1.2.0
 */
function azrcrv_smtp_activate() {

	// Exit if the options are already in place
	$my_options = get_option( 'azrcrv-smtp', false );
	if ( $my_options !== false ) {
		return;
	}

	// Exit if swpsmtp_options are missing
	$swpsmtp_options = get_option( 'swpsmtp_options', false );
	if ( $swpsmtp_options === false ) {
		return;
	}

	// Fine... we have settings

	// Check that everything is defined in swpsmtp_options
	$swpsmtp_options_default = array(
		'from_email_field' => '',
		'from_name_field'  => '',
		'smtp_settings'    => array(
			'host'            => '',
			'type_encryption' => 'SSL',
			'port'            => '465',
			'username'        => '',
			'autentication'   => 0,
			'encrypt_pass'    => 0,
		),
	);
	$swpsmtp_options         = azrcrv_smtp_recursive_merge( $swpsmtp_options, $swpsmtp_options_default );

	// Exit if password encrypted and openssl missing (possible?)
	if ( $swpsmtp_options['smtp_settings']['encrypt_pass'] === 1 && ! extension_loaded( 'openssl' ) ) {
		return;
	}
	
	// phpcs:ignore Used to decode password for migration.
	$raw_password = base64_decode( $swpsmtp_options['smtp_settings']['password'], true );

	// Exit on failed Base64 decode
	if ( $raw_password === false ) {
		return;
	}

	// Decrypt password
	if ( $swpsmtp_options['smtp_settings']['encrypt_pass'] === 1 ) {
		// Exit if encryption key is missing
		$key = get_option( 'swpsmtp_enc_key', false );
		if ( $key === false ) {
			return false;
		}
		$iv_num_bytes = openssl_cipher_iv_length( 'aes-256-ctr' );
		$iv           = substr( $raw_password, 0, $iv_num_bytes );
		$data         = substr( $raw_password, $iv_num_bytes );
		$keyhash      = openssl_digest( $key, 'sha256', true );
		$password     = openssl_decrypt( $data, 'aes-256-ctr', $keyhash, OPENSSL_RAW_DATA, $iv );
		// Exit on decrypt error
		if ( $password === false ) {
			return false;
		}
	} else {
		$password = $raw_password;
	}

	// Get test e-mail options
	$smtp_test_mail_defaults = array(
		'swpsmtp_to'      => '',
		'swpsmtp_subject' => '',
		'swpsmtp_message' => '',
	);

	$smtp_test_mail = get_option( 'smtp_test_mail', $smtp_test_mail_defaults );
	$smtp_test_mail = wp_parse_args( $smtp_test_mail, $smtp_test_mail_defaults );

	// Create config and save
	$import = array(
		'smtp-host'               => $swpsmtp_options['smtp_settings']['host'],
		'smtp-encryption-type'    => $swpsmtp_options['smtp_settings']['type_encryption'],
		'smtp-port'               => $swpsmtp_options['smtp_settings']['port'],
		'smtp-username'           => $swpsmtp_options['smtp_settings']['username'],
		'smtp-password'           => $password,
		'allow-no-authentication' => ( $swpsmtp_options['smtp_settings']['autentication'] === 'yes' ) ? 0 : 1,
		'from-email-address'      => $swpsmtp_options['from_email_field'],
		'from-email-name'         => $swpsmtp_options['from_name_field'],
		'test-email-address'      => $smtp_test_mail['swpsmtp_to'],
		'test-email-subject'      => $smtp_test_mail['swpsmtp_subject'],
		'test-email-message'      => $smtp_test_mail['swpsmtp_message'],
	);

	// Save options
	update_option( 'azrcrv-smtp-maybe', $import );
}

/**
 * Load language files.
 *
 * @since 1.0.0
 */
function azrcrv_smtp_load_languages() {
	$plugin_rel_path = basename( dirname( __FILE__ ) ) . '/languages';
	load_plugin_textdomain( 'azrcrv-smtp', false, $plugin_rel_path );
}

/**
 * Check if shortcode on current page and then load css and jqeury.
 *
 * @since 1.0.0
 */
function azrcrv_smtp_check_for_shortcode( $posts ) {
	if ( empty( $posts ) ) {
		return $posts;
	}

	// array of shortcodes to search for
	$shortcodes = array(
		'azrcrv-smtp',
	);

	// loop through posts
	$found = false;
	foreach ( $posts as $post ) {
		// loop through shortcodes
		foreach ( $shortcodes as $shortcode ) {
			// check the post content for the shortcode
			if ( has_shortcode( $post->post_content, $shortcode ) ) {
				$found = true;
				// break loop as shortcode found in page content
				break 2;
			}
		}
	}

	if ( $found ) {
		// as shortcode found call functions to load css and jquery
		azrcrv_smtp_load_css();
	}
	return $posts;
}

/**
 * Load CSS.
 *
 * @since 1.0.0
 */
function azrcrv_smtp_load_css() {
	wp_enqueue_style( 'azrcrv-smtp', plugins_url( 'assets/css/style.css', __FILE__ ), '', '1.0.0' );
}

/**
 * Custom plugin image path.
 *
 * @since 2.1.0
 */
function azrcrv_smtp_custom_image_path( $path ) {
	if ( strpos( $path, 'azrcrv-smtp' ) !== false ) {
		$path = plugin_dir_path( __FILE__ ) . 'assets/pluginimages';
	}
	return $path;
}

/**
 * Custom plugin image url.
 *
 * @since 2.1.0
 */
function azrcrv_smtp_custom_image_url( $url ) {
	if ( strpos( $url, 'azrcrv-smtp' ) !== false ) {
		$url = plugin_dir_url( __FILE__ ) . 'assets/pluginimages';
	}
	return $url;
}

/**
 * Get options including defaults.
 *
 * @since 1.2.0
 */
function azrcrv_smtp_get_option( $option_name ) {

	$defaults = array(
		'smtp-host'               => '',
		'smtp-encryption-type'    => 'ssl',
		'smtp-port'               => 465,
		'smtp-username'           => '',
		'smtp-password'           => '',
		'allow-no-authentication' => 0,
		'from-email-address'      => '',
		'from-email-name'         => '',
		'test-email-address'      => '',
		'test-email-subject'      => '',
		'test-email-message'      => '',
	);

	$options = get_option( $option_name, $defaults );

	$options = wp_parse_args( $options, $defaults );

	return $options;

}

/**
 * Add action link on plugins page.
 *
 * @since 1.0.0
 */
function azrcrv_smtp_add_plugin_action_link( $links, $file ) {
	static $this_plugin;

	if ( ! $this_plugin ) {
		$this_plugin = plugin_basename( __FILE__ );
	}

	if ( $file == $this_plugin ) {
		$settings_link = '<a href="' . admin_url( 'admin.php?page=azrcrv-smtp' ) . '"><img src="' . plugins_url( '/pluginmenu/images/logo.svg', __FILE__ ) . '" style="padding-top: 2px; margin-right: -5px; height: 16px; width: 16px;" alt="azurecurve" />' . esc_html__( 'Settings', 'azrcrv-smtp' ) . '</a>';
		array_unshift( $links, $settings_link );
	}

	return $links;
}

/**
 * Add to menu.
 *
 * @since 1.0.0
 */
function azrcrv_smtp_create_admin_menu() {
	// global $admin_page_hooks;

	add_submenu_page(
		'azrcrv-plugin-menu',
		esc_html__( 'SMTP Settings', 'azrcrv-smtp' ),
		esc_html__( 'SMTP', 'azrcrv-smtp' ),
		'manage_options',
		'azrcrv-smtp',
		'azrcrv_smtp_display_options'
	);
}

/**
 * Load css and jquery for flags.
 *
 * @since 2.3.0
 */
function azrcrv_smtp_load_admin_style() {
	wp_register_style( 'smtp-css', plugins_url( 'assets/css/admin.css', __FILE__ ), false, '1.0.0' );
	wp_enqueue_style( 'smtp-css' );

	wp_enqueue_script( 'smtp-admin-js', plugins_url( 'assets/jquery/jquery.js', __FILE__ ), array( 'jquery', 'jquery-ui-core', 'jquery-ui-tabs' ), '1.0.0', true );
}

/**
 * Check if function active (included due to standard function failing due to order of load).
 *
 * @since 1.0.0
 */
function azrcrv_smtp_is_plugin_active( $plugin ) {
	return in_array( $plugin, (array) get_option( 'active_plugins', array() ) );
}

/**
 * Display Settings page.
 *
 * @since 1.0.0
 */
function azrcrv_smtp_display_options() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'azrcrv-smtp' ) );
	}

	global $wpdb;

	// Retrieve plugin configuration options from database
	$options = azrcrv_smtp_get_option( 'azrcrv-smtp' );

	$types = get_option( 'azrcrv-smtp-types' );
	if ( is_array( $types ) ) {
		ksort( $types ); }

	?>
	<div id="azrcrv-smtp-general" class="wrap">
		<fieldset>
			<h1>
				<?php
					echo '<a href="https://development.azurecurve.co.uk/classicpress-plugins/"><img src="' . esc_attr( plugins_url( '/pluginmenu/images/logo.svg', __FILE__ ) ) . '" style="padding-right: 6px; height: 20px; width: 20px;" alt="azurecurve" /></a>';
					echo esc_html( get_admin_page_title() );
				?>
			</h1>
			<?php if ( $options['smtp-host'] === '' && get_option( 'azrcrv-smtp-maybe', false ) !== false ) { ?>
				<div class="notice notice-info is-dismissible azrcrv-smtp-import-dismiss" data-nonce="<?php echo wp_create_nonce( 'azrcrv_smtp_import_dismiss_nonce' ); ?>">
					<p><strong>
					<?php
					// Display notice about imported settings
					$url = remove_query_arg( 'page' );
					$url = add_query_arg(
						array(
							'action'                   => 'azrcrv_smtp_import_options',
							'azrcrv_smtp_import_nonce' => wp_create_nonce( 'azrcrv_smtp_import_nonce' ),
						),
						$url
					);
							   esc_html_e( 'Found Easy WP SMTP settings that can be imported.', 'azrcrv-smtp' );
							   echo '<br>';
							   echo '<a href="' . esc_url_raw( $url ) . '">';
							   esc_html_e( 'Import settings', 'azrcrv-smtp' );
							   echo '</a>';
					?>
					</strong></p>
				</div>
				<?php
			}
			if ( isset( $_GET['settings-updated'] ) ) {
				?>
				<div class="notice notice-success is-dismissible">
					<p><strong>
					<?php
						// phpcs:ignore WordPress.Security.NonceVerification.Recommended
						esc_html_e( 'Settings have been saved.', 'azrcrv-smtp' );
					?>
					</strong></p>
				</div>
			<?php } elseif ( isset( $_GET['test-email'] ) and $_GET['status'] == 'sent' ) { ?>
				<div class="notice notice-info is-dismissible">
					<p><strong>
					<?php
						// phpcs:ignore WordPress.Security.NonceVerification.Recommended
						esc_html_e( 'Test email has been sent successfully.', 'azrcrv-smtp' );
					?>
					</strong></p>
				</div>
			<?php } elseif ( isset( $_GET['test-email'] ) and $_GET['status'] == 'failed' ) { ?>
				<div class="notice notice-error is-dismissible">
					<p>
						<strong>
						<?php
							// phpcs:ignore WordPress.Security.NonceVerification.Recommended
							esc_html_e( 'Error sending test email:', 'azrcrv-smtp' );
						?>
						</strong>
						<?php
						$test_result = get_option( 'azrcrv-smtp-test' );
						foreach ( $test_result as $error ) {
							echo '<br />' . esc_html( $error );
						}
						?>
					</p>
				</div>
			<?php } ?>
				
			<?php
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			if ( isset( $_GET['test-email'] ) ) {
				$tab1active     = '';
				$tab2active     = 'nav-tab-active';
				$tab1visibility = 'invisible';
				$tab2visibility = '';
			} else {
				$tab1active     = 'nav-tab-active';
				$tab2active     = '';
				$tab1visibility = '';
				$tab2visibility = 'invisible';
			}
			?>
		
			<h2 class="nav-tab-wrapper nav-tab-wrapper-azrcrv-smtp">
				<a class="nav-tab <?php echo esc_attr( $tab1active ); ?>" data-item=".tabs-1" href="#tabs-1"><?php esc_html_e( 'SMTP Settings', 'azrcrv-smtp' ); ?></a>
				<a class="nav-tab <?php echo esc_attr( $tab2active ); ?>" data-item=".tabs-2" href="#tabs-2"><?php esc_html_e( 'Test Email', 'azrcrv-smtp' ); ?></a>
			</h2>
			
			<div>
				<div class="azrcrv_smtp_tabs <?php echo esc_attr( $tab1visibility ); ?> tabs-1">
		
					<form method="post" action="admin-post.php">
					
						<input type="hidden" name="action" value="azrcrv_smtp_save_options" />
						<input name="page_options" type="hidden" value="smtp-host,smtp-encryption-type,smtp-port,smtp-username,smtp-password,from-email-address,from-email-name,reply-to-email-address,bcc-email-address" />
						
						<!-- Adding security through hidden referrer field -->
						<?php wp_nonce_field( 'azrcrv-smtp', 'azrcrv-smtp-nonce' ); ?>
						
						<table class="form-table">
							
							<tr>
								<th scope="row">
									<label for="smtp-host">
										<?php esc_html_e( 'SMTP Host', 'azrcrv-smtp' ); ?>
									</label>
								</th>
								<td>
									<input name="smtp-host" type="text" id="smtp-host" value="<?php echo esc_attr( $options['smtp-host'] ); ?>" class="regular-text" />
									<p class="description"><?php esc_html_e( 'Your mail server address.', 'azrcrv-smtp' ); ?></p>
								</td>
							</tr>
							
							<tr>
								<th scope="row">
									<label for="smtp-encryption-type">
										<?php esc_html_e( 'SMTP EncryptionType', 'azrcrv-smtp' ); ?>
									</label>
								</th>
								<td>
									<select name="smtp-encryption-type">
										<option value="none" 
										<?php
										if ( $options['smtp-encryption-type'] == 'none' ) {
											echo ' selected'; }
										?>
										>None</option>
										<option value="ssl" 
										<?php
										if ( $options['smtp-encryption-type'] == 'ssl' ) {
											echo ' selected'; }
										?>
										>SSL/TLS</option>
										<option value="tls" 
										<?php
										if ( $options['smtp-encryption-type'] == 'tls' ) {
											echo ' selected'; }
										?>
										>StartTLS</option>
									</select>
									<p class="description"><?php esc_html_e( 'For most servers SSL/TLS is the recommended encryption type.', 'azrcrv-smtp' ); ?></p>
								</td>
							</tr>
						
							<tr>
								<th scope="row">
									<label for="smtp-port">
										<?php esc_html_e( 'SMTP Port', 'azrcrv-smtp' ); ?>
									</label>
								</th>
								<td>
									<input name="smtp-port" type="number" step="1" min="1" id="smtp-port" value="<?php echo esc_attr( $options['smtp-port'] ); ?>" class="small-text" />
									<p class="description"><?php esc_html_e( 'The port to your mail server (Standards are 25 for no encryption, 465 is standard for SSL/TLS and 587 is standard for StartTLS.', 'azrcrv-smtp' ); ?></p>
								</td>
							</tr>
							
							<tr>
								<th scope="row">
									<label for="smtp-username">
										<?php esc_html_e( 'SMTP Username', 'azrcrv-smtp' ); ?>
									</label>
								</th>
								<td>
									<input name="smtp-username" type="text" id="smtp-username" value="<?php echo esc_attr( $options['smtp-username'] ); ?>" class="regular-text" />
									<p class="description"><?php esc_html_e( 'The username to login to your mail server.', 'azrcrv-smtp' ); ?></p>
								</td>
							</tr>
							
							<tr>
								<th scope="row">
									<label for="smtp-password">
										<?php esc_html_e( 'SMTP Password', 'azrcrv-smtp' ); ?>
									</label>
								</th>
								<td>
									<input name="smtp-password" type="password" id="smtp-password" value="#ProtectedPassword#" class="regular-text" />
									<p class="description"><?php esc_html_e( 'The password to login to your mail server. NB. The password is stored in plain text in the database.', 'azrcrv-smtp' ); ?></p>
								</td>
							</tr>
							
							<tr>
								<th scope="row">
									<label for="allow-no-authentication">
										<?php esc_html_e( 'Allow No Authentication', 'azrcrv-smtp' ); ?>
									</label>
								</th>
								<td>
									<input name="allow-no-authentication" type="checkbox" id="allow-no-authentication" value="1" '.checked('1', $options['allow-no-authentication'], false).' />
									<label for="allow-no-authentication"><span class="description">
										<?php esc_html_e( 'Allow no authentication when username not set.', 'azrcrv-smtp' ); ?>
									</span></label
								</td>
							</tr>
							
							<tr>
								<th scope="row">
									<label for="from-email-address">
										<?php esc_html_e( 'From Email Address', 'azrcrv-smtp' ); ?>
									</label>
								</th>
								<td>
									<input name="from-email-address" type="email" id="from-email-address" value="<?php echo esc_attr( $options['from-email-address'] ); ?>" class="regular-text" />
									<p class="description"><?php esc_html_e( 'This will be used as the "From" email address; leave blank to use the admin email.', 'azrcrv-smtp' ); ?></p>
								</td>
							</tr>
							
							<tr>
								<th scope="row">
									<label for="from-email-name">
										<?php esc_html_e( 'From Email Name', 'azrcrv-smtp' ); ?>
									</label>
								</th>
								<td>
									<input name="from-email-name" type="text" id="from-email-name" value="<?php echo esc_attr( $options['from-email-name'] ); ?>" class="regular-text" />
									<p class="description"><?php printf( esc_html__( 'This will be used as the name for the "From" email address; leave blank to use %s.', 'azrcrv-smtp' ), 'ClassicPress' ); ?></p>
								</td>
							</tr>
							
						</table>
						<input type="submit" value="Save Changes" class="button-primary"/>
					</form>
				</div>
				
				<div class="azrcrv_smtp_tabs <?php echo esc_attr( $tab2visibility ); ?> tabs-2">
		
					<form method="post" action="admin-post.php">
					
						<input type="hidden" name="action" value="azrcrv_smtp_send_test_email" />
						<input name="page_options" type="hidden" value="test-email-address,test-email-subject,test-email-message" />
						
						<!-- Adding security through hidden referrer field -->
						<?php wp_nonce_field( 'azrcrv-smtp-send-test-email', 'azrcrv-smtp-send-test-email-nonce' ); ?>
					
						<table class="form-table">
							
							<tr>
								<th scope="row" colspan=2>
										<?php esc_html_e( 'Test your email configuration by sending a test email.', 'azrcrv-smtp' ); ?>
								</th>
							</tr>
							
							<tr>
								<th scope="row">
									<label for="test-email-address">
										<?php esc_html_e( 'Email Address', 'azrcrv-smtp' ); ?>
									</label>
								</th>
								<td>
									<input name="test-email-address" type="email" id="test-email-address" value="<?php echo esc_attr( $options['test-email-address'] ); ?>" class="regular-text" />
								</td>
							</tr>
							
							<tr>
								<th scope="row">
									<label for="test-email-subject">
										<?php esc_html_e( 'Email Subject', 'azrcrv-smtp' ); ?>
									</label>
								</th>
								<td>
									<input name="test-email-subject" type="text" id="test-email-subject" value="<?php echo esc_attr( $options['test-email-subject'] ); ?>" class="regular-text" />
								</td>
							</tr>
							
							<tr>
								<th scope="row">
									<label for="test-email-message">
										<?php esc_html_e( 'Email Message', 'azrcrv-smtp' ); ?>
									</label>
								</th>
								<td>
									<input name="test-email-message" type="text" id="test-email-message" value="<?php echo esc_attr( $options['test-email-message'] ); ?>" class="regular-text" />
								</td>
							</tr>
							
						</table>
						<input type="submit" value="Send Test Email" class="button-primary"/>
					</form>
				</div>
			</div>
		</fieldset>
	</div>
	
	<?php
}

/**
 * Save settings.
 *
 * @since 1.0.0
 */
function azrcrv_smtp_save_options() {
	// Check that user has proper security level
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have permissions to perform this action', 'azrcrv-smtp' ) );
	}
	// Check that nonce field created in configuration form is present
	if ( ! empty( $_POST ) && check_admin_referer( 'azrcrv-smtp', 'azrcrv-smtp-nonce' ) ) {

		// Retrieve original plugin options array
		$options = get_option( 'azrcrv-smtp' );

		$option_name = 'smtp-host';
		if ( isset( $_POST[ $option_name ] ) ) {
			$options[ $option_name ] = sanitize_text_field( wp_unslash( $_POST[ $option_name ] ) );
		}

		$option_name = 'smtp-encryption-type';
		if ( isset( $_POST[ $option_name ] ) ) {
			$options[ $option_name ] = sanitize_text_field( wp_unslash( $_POST[ $option_name ] )) ;
		}

		$option_name = 'smtp-port';
		if ( isset( $_POST[ $option_name ] ) ) {
			$options[ $option_name ] = sanitize_text_field( intval( $_POST[ $option_name ] ) );
		}

		$option_name = 'smtp-username';
		if ( isset( $_POST[ $option_name ] ) ) {
			$options[ $option_name ] = sanitize_text_field( wp_unslash( $_POST[ $option_name ] ) );
		}

		$option_name = 'smtp-password';
		if ( isset( $_POST[ $option_name ] ) ) {
			if ( $_POST[ $option_name ] != '#ProtectedPassword#' ) {
				$options[ $option_name ] = sanitize_text_field( wp_unslash( $_POST[ $option_name ] ) );
			}
		}

		$option_name = 'allow-no-authentication';
		if ( isset( $_POST[ $option_name ] ) ) {
			$options[ $option_name ] = 1;
		} else {
			$options[ $option_name ] = 0;
		}

		$option_name = 'from-email-address';
		if ( isset( $_POST[ $option_name ] ) ) {
			$options[ $option_name ] = sanitize_email( wp_unslash( $_POST[ $option_name ] ) );
		}

		$option_name = 'from-email-name';
		if ( isset( $_POST[ $option_name ] ) ) {
			$options[ $option_name ] = sanitize_text_field( wp_unslash( $_POST[ $option_name ] ) );
		}

		// Store updated options array to database
		update_option( 'azrcrv-smtp', $options );

		// Redirect the page to the configuration form that was processed
		wp_safe_redirect( add_query_arg( 'page', 'azrcrv-smtp&settings-updated', admin_url( 'admin.php' ) ) );
		exit;
	}
}

/**
 * Send test email.
 *
 * @since 1.0.0
 */
function azrcrv_smtp_send_test_email() {
	// Check that user has proper security level
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have permissions to perform this action', 'azrcrv-smtp' ) );
	}
	// Check that nonce field created in configuration form is present
	if ( ! empty( $_POST ) && check_admin_referer( 'azrcrv-smtp-send-test-email', 'azrcrv-smtp-send-test-email-nonce' ) ) {

		// Retrieve original plugin options array
		$options = get_option( 'azrcrv-smtp' );

		$option_name = 'test-email-address';
		if ( isset( $_POST[ $option_name ] ) ) {
			$options[ $option_name ] = sanitize_email( wp_unslash( $_POST[ $option_name ] ) );
		}

		$option_name = 'test-email-subject';
		if ( isset( $_POST[ $option_name ] ) ) {
			$options[ $option_name ] = sanitize_text_field( wp_unslash( $_POST[ $option_name ] ) );
		}

		$option_name = 'test-email-message';
		if ( isset( $_POST[ $option_name ] ) ) {
			$options[ $option_name ] = sanitize_text_field( wp_unslash( $_POST[ $option_name ] ) );
		}

		// Store updated options array to database
		update_option( 'azrcrv-smtp', $options );

		$result = 'test-email&status=sent';

		require_once ABSPATH . WPINC . '/class-phpmailer.php';

		$test_result = array();
		$error       = '';
		$result      = 'test-email&status=sent';
		$phpmailer   = new \PHPMailer();

		$phpmailer->isSMTP();

		$charset            = get_bloginfo( 'charset' );
		$phpmailer->CharSet = $charset;

		$phpmailer->addCustomHeader( 'MIME-Version', '1.0' );
		$phpmailer->addCustomHeader( 'Content-type', 'text/html' );

		$phpmailer->Host = $options['smtp-host'];
		$phpmailer->Port = $options['smtp-port'];

		if ( $options['smtp-encryption-type'] !== 'none' ) {
			$phpmailer->SMTPSecure = $options['smtp-encryption-type'];
		}
		$phpmailer->Username = $options['smtp-username'];
		$phpmailer->Password = $options['smtp-password'];

		// Don't authenticate if explicitly set to allow no authentication when username not set and username is not set
		if ( $options['allow-no-authentication'] == 1 and $options['smtp-username'] == '' ) {
			$phpmailer->SMTPAuth = false;
		} else {
			$phpmailer->SMTPAuth = true;
		}

		if ( strlen( $options['from-email-address'] ) > 0 ) {
			$phpmailer->From = $options['from-email-address'];
		}
		if ( strlen( $options['from-email-name'] ) > 0 ) {
			$phpmailer->FromName = $options['from-email-name'];
		}

		$phpmailer->addAddress( $options['test-email-address'] );
		$phpmailer->Subject = $options['test-email-subject'];
		$phpmailer->Body    = $options['test-email-message'];

		$level                  = 2;
		$phpmailer->SMTPDebug   = 1;
		$phpmailer->Debugoutput = function( $str, $level ) use ( $error ) {
			$error .= $level . ': ' . $str . '\n';
		};

		// Don't fail if the server is advertising TLS with an invalid certificate
		$phpmailer->SMTPAutoTLS = false;

		if ( $phpmailer->send() ) {
			$result = 'test-email&status=sent';
		} else {
			$test_result[] = $phpmailer->ErrorInfo;
			$result        = 'test-email&status=failed';
		}

		if ( strlen( $error ) > 0 ) {
			$test_result[] = $error;
		}

		update_option( 'azrcrv-smtp-test', $test_result );

		// Redirect the page to the configuration form that was processed
		wp_safe_redirect( add_query_arg( 'page', 'azrcrv-smtp&' . $result, admin_url( 'admin.php' ) ) );
		exit;
	}

}

// Handle click on import
function azrcrv_smtp_import_options() {
	
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( ! current_user_can( 'manage_options' ) || ! isset( $_REQUEST['azrcrv_smtp_import_nonce'] ) || ! wp_verify_nonce( $_REQUEST['azrcrv_smtp_import_nonce'], 'azrcrv_smtp_import_nonce' ) ) {
		wp_die( esc_html__( 'You do not have permissions to perform this action', 'azrcrv-smtp' ) );
	}

	update_option( 'azrcrv-smtp', get_option( 'azrcrv-smtp-maybe' ) );
	delete_option( 'azrcrv-smtp-maybe' );

	wp_safe_redirect( add_query_arg( 'page', 'azrcrv-smtp&settings-updated', admin_url( 'admin.php' ) ) );
	exit;

}

// Handle AJAX notice dismiss
function azrcrv_smtp_import_dismiss() {
	
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( ! wp_doing_ajax() || ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'azrcrv_smtp_import_dismiss_nonce' ) ) {
		wp_die( esc_html__( 'You do not have permissions to perform this action', 'azrcrv-smtp' ) );
	}
	delete_option( 'azrcrv-smtp-maybe' );
	wp_send_json_success( array( 'Dismissed' => 'Yes' ) );
}

/**
 * Intercept phpmailer and update SMTP details and send email.
 *
 * @since 1.0.0
 * @since 1.4.0 replace from address with SMTP plugin settings only if set to admin email
 */
function azrcrv_smtp_send_smtp_email( $phpmailer ) {

	$options = azrcrv_smtp_get_option( 'azrcrv-smtp' );

	$phpmailer->isSMTP();
	$phpmailer->Host = $options['smtp-host'];
	$phpmailer->Port = $options['smtp-port'];
	if ( $options['smtp-encryption-type'] !== 'none' ) {
		$phpmailer->SMTPSecure = $options['smtp-encryption-type'];
	}
	$phpmailer->Username = $options['smtp-username'];
	$phpmailer->Password = $options['smtp-password'];

	// Don't authenticate if username is left empty
	$phpmailer->SMTPAuth = $options['smtp-encryption-type'] !== '';
	// Don't fail if the server is advertising TLS with an invalid certificate
	$phpmailer->SMTPAutoTLS = false;

	// replace from address only if currently set to admin email
	if ( get_option( 'admin_email' ) == $phpmailer->From ) {
		if ( strlen( $options['from-email-address'] ) > 0 ) {
			$phpmailer->From = $options['from-email-address'];
		}
		if ( strlen( $options['from-email-name'] ) > 0 ) {
			$phpmailer->FromName = $options['from-email-name'];
		}
	}

	$charset            = get_bloginfo( 'charset' );
	$phpmailer->CharSet = $charset;

	$phpmailer->addCustomHeader( 'MIME-Version', '1.0' );
	$phpmailer->addCustomHeader( 'Content-type', 'text/html' );

}
