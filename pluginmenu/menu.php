<?php
/**
Menu Version 3.0
 */

add_action( 'admin_menu', 'azrcrv_add_plugin_menu' );
add_action( 'admin_head', 'azrcrv_plugin_menu_load_css' );


/**
 * Add azurecurve menu.
 *
 * @since 1.0.0
 */
if ( ! function_exists( 'azrcrv_add_plugin_menu' ) ) {
	function azrcrv_add_plugin_menu() {
		global $admin_page_hooks;

		if ( empty( $admin_page_hooks['azrcrv-menu-test'] ) ) {
			add_menu_page(
				'azurecurve Plugins',
				'azurecurve',
				'manage_options',
				'azrcrv-plugin-menu',
				'azrcrv_display_plugin_menu',
				plugins_url( '/pluginmenu/images/logo.svg', __DIR__ )
			);
			add_submenu_page(
				'azrcrv-plugin-menu',
				'Plugins',
				'Plugins',
				'manage_options',
				'azrcrv-plugin-menu',
				'azrcrv_display_plugin_menu'
			);
		}
	}
}

/**
 * Load plugin menu css.
 *
 * @since 1.0.0
 */
if ( ! function_exists( 'azrcrv_plugin_menu_load_css' ) ) {
	function azrcrv_plugin_menu_load_css() {
		wp_enqueue_style( 'azrcrv-plugin-index', plugins_url( 'css/style.css', __FILE__ ) );
	}
}

/**
 * Display plugin menu.
 *
 * @since 1.0.0
 */
if ( ! function_exists( 'azrcrv_display_plugin_menu' ) ) {
	function azrcrv_display_plugin_menu() {

		echo '<h3>azurecurve ClassicPress Plugins</h3>';

		echo "<div style='display: block;'><h3>Active Plugins</h3>";
		echo "<span class='azrcrv-plugin-index'>";

		$plugin_array = get_option( 'azrcrv-plugin-menu' );

		foreach ( $plugin_array as $plugin_name => $plugin_details ) {
			if ( $plugin_details['retired'] == 0 ) {
				if ( is_plugin_active( $plugin_details['plugin_link'] ) ) {
					echo '<a href="' . esc_url_raw( $plugin_details['admin_URL'] ) . '" class="azrcrv-plugin-index">' . esc_html( $plugin_name ) . '</a>';
				}
			}
		}

		echo '</span></div>';
		echo "<p style='clear: both' />";

		echo "<div style='display: block;'><h3>Other Available Plugins</h3>";
		echo "<span class='azrcrv-plugin-index'>";

		$countofplugins = 0;

		foreach ( $plugin_array as $plugin_name => $plugin_details ) {
			if ( $plugin_details['retired'] == 0 ) {
				if ( ! is_plugin_active( $plugin_details['plugin_link'] ) ) {
					echo '<a href="' . esc_url_raw( $plugin_details['dev_URL'] ) . '" class="azrcrv-plugin-index">' . esc_html( $plugin_name ) . '</a>';
					$countofplugins += 1;
				}
			}
		}

		if ( $countofplugins == 0 ) {
			echo "Congratulations! You're using all of the azurecurve plugins.";
		}

		echo '</span></div>';

		echo "<p style='clear: both' />";

		echo "<div style='display: block;'><h3>Support azurecurve</h3>";
		echo "<p>azurecurve plugins are free, open source tools for increasing the potential of ClassicPress as a CMS.</p>
				<p>Plugins are a result of many many hours spent to deliver best product possible, reading comments from you and trying to support every ClassicPress release as soon as possible.</p>
				<p>You can help support the development by donating a small amount of money.</p>
				<div style='width:165px; margin: auto; '>
				<form action='https://www.paypal.com/cgi-bin/webscr' method='post' target='_top'>
				<input type='hidden' name='cmd' value='_s-xclick'>
				<input type='hidden' name='hosted_button_id' value='MCJQN9SJZYLWJ'>
				<input type='image' src='https://www.paypalobjects.com/en_US/GB/i/btn/btn_donateCC_LG.gif' border='0' name='submit' alt='PayPal – The safer, easier way to pay online.'>
				<img alt='' border='0' src='https://www.paypalobjects.com/en_GB/i/scr/pixel.gif' width='1' height='1'>
				</form>
				</div>
				</div>";
	}
}

/**
 * Create plugin menu on installation.
 *
 * @since 1.0.0
 */
if ( ! function_exists( 'azrcrv_create_plugin_menu_r' ) ) {
	function azrcrv_create_plugin_menu_r() {
		azrcrv_populate_plugin_menu_r();
	}
}

/**
 * Populate table on installation.
 *
 * @since 1.0.0
 */
if ( ! function_exists( 'azrcrv_populate_plugin_menu_r' ) ) {
	function azrcrv_populate_plugin_menu_r() {

		$plugins = array(
			'Add Open Graph Tags'        => array(
				'plugin_link' => 'azrcrv-add-open-graph-tags/azrcrv-add-open-graph-tags.php',
				'admin_URL'   => 'admin.php?page=azrcrv-aogt',
				'dev_URL'     => 'https://development.azurecurve.co.uk/classicpress-plugins/add-open-graph-tags/',
				'retired'     => 0,
				'updated'     => '2020-04-04',
			),
			'Add Twitter Cards'          => array(
				'plugin_link' => 'azrcrv-add-twitter-cards/azrcrv-add-twitter-cards.php',
				'admin_URL'   => 'admin.php?page=azrcrv-atc',
				'dev_URL'     => 'https://development.azurecurve.co.uk/classicpress-plugins/add-twitter-cards/',
				'retired'     => 0,
				'updated'     => '2020-04-04',
			),
			'Avatars'                    => array(
				'plugin_link' => 'azrcrv-avatars/azrcrv-avatars.php',
				'admin_URL'   => 'admin.php?page=azrcrv-a',
				'dev_URL'     => 'https://development.azurecurve.co.uk/classicpress-plugins/avatars/',
				'retired'     => 0,
				'updated'     => '2020-04-04',
			),
			'BBCode'                     => array(
				'plugin_link' => 'azrcrv-bbcode/azrcrv-bbcode.php',
				'admin_URL'   => 'admin.php?page=azrcrv-bbc',
				'dev_URL'     => 'https://development.azurecurve.co.uk/classicpress-plugins/bbcode/',
				'retired'     => 0,
				'updated'     => '2020-04-04',
			),
			'Breadcrumbs'                => array(
				'plugin_link' => 'azrcrv-breadcrumbs/azrcrv-breadcrumbs.php',
				'admin_URL'   => 'admin.php?page=azrcrv-b',
				'dev_URL'     => 'https://development.azurecurve.co.uk/classicpress-plugins/breadcrumbs/',
				'retired'     => 0,
				'updated'     => '2020-04-04',
			),
			'Call-out Boxes'             => array(
				'plugin_link' => 'azrcrv-call-out-boxes/azrcrv-call-out-boxes.php',
				'admin_URL'   => 'admin.php?page=azrcrv-cob',
				'dev_URL'     => 'https://development.azurecurve.co.uk/classicpress-plugins/call-out-boxes/',
				'retired'     => 0,
				'updated'     => '2020-04-04',
			),
			'Check Plugin Status'        => array(
				'plugin_link' => 'azrcrv-check-plugin-status/azrcrv-check-plugin-status.php',
				'admin_URL'   => 'admin.php?page=azrcrv-check-plugin-status',
				'dev_URL'     => 'https://development.azurecurve.co.uk/classicpress-plugins/check-plugin-status/',
				'retired'     => 0,
				'updated'     => '2020-02-23',
			),
			'Code'                       => array(
				'plugin_link' => 'azrcrv-code/azrcrv-code.php',
				'admin_URL'   => 'admin.php?page=azrcrv-c',
				'dev_URL'     => 'https://development.azurecurve.co.uk/classicpress-plugins/code/',
				'retired'     => 0,
				'updated'     => '2020-04-04',
			),
			'Check Plugin Status'        => array(
				'plugin_link' => 'azrcrv-check-plugin-status/azrcrv-check-plugin-status.php',
				'admin_URL'   => 'admin.php?page=azrcrv-cps',
				'dev_URL'     => 'https://development.azurecurve.co.uk/classicpress-plugins/check-plugin-status/',
				'retired'     => 0,
				'updated'     => '2021-01-11',
			),
			'Comment Validator'          => array(
				'plugin_link' => 'azrcrv-comment-validator/azrcrv-comment-validator.php',
				'admin_URL'   => 'admin.php?page=azrcrv-cv',
				'dev_URL'     => 'https://development.azurecurve.co.uk/classicpress-plugins/comment-validator/',
				'retired'     => 0,
				'updated'     => '2020-04-04',
			),
			'Conditional Links'          => array(
				'plugin_link' => 'azrcrv-conditional-links/azrcrv-conditional-links.php',
				'admin_URL'   => 'admin.php?page=azrcrv-cl',
				'dev_URL'     => 'https://development.azurecurve.co.uk/classicpress-plugins/conditional-links/',
				'retired'     => 0,
				'updated'     => '2020-04-04',
			),
			'Contact Forms'              => array(
				'plugin_link' => 'azrcrv-contact-forms/azrcrv-contact-forms.php',
				'admin_URL'   => 'admin.php?page=azrcrv-cf',
				'dev_URL'     => 'https://development.azurecurve.co.uk/classicpress-plugins/contact-forms/',
				'retired'     => 0,
				'updated'     => '2021-12-01',
			),
			'Display After Post Content' => array(
				'plugin_link' => 'azrcrv-display-after-post-content/azrcrv-display-after-post-content.php',
				'admin_URL'   => 'admin.php?page=azrcrv-dapc',
				'dev_URL'     => 'https://development.azurecurve.co.uk/classicpress-plugins/display-after-post-content/',
				'retired'     => 0,
				'updated'     => '2020-04-04',
			),
			'Disabe FLoC'                => array(
				'plugin_link' => 'azrcrv-disable-floc/azrcrv-disable-floc.php',
				'admin_URL'   => 'https://development.azurecurve.co.uk/classicpress-plugins/disable-floc/',
				'dev_URL'     => 'https://development.azurecurve.co.uk/classicpress-plugins/disable-floc/',
				'retired'     => 0,
				'updated'     => '2021-06-12',
			),
			'Estimated Read Time'        => array(
				'plugin_link' => 'codepotent-estimated-read-time/codepotent-estimated-read-time.php',
				'admin_URL'   => 'admin.php?page=azrcrv-ert',
				'dev_URL'     => 'https://development.azurecurve.co.uk/classicpress-plugins/estimated-read-time/',
				'retired'     => 0,
				'updated'     => '2021-06-10',
			),
			'Events'                     => array(
				'plugin_link' => 'azrcrv-events/azrcrv-events.php',
				'admin_URL'   => 'admin.php?page=azrcrv-e',
				'dev_URL'     => 'https://development.azurecurve.co.uk/classicpress-plugins/events/',
				'retired'     => 0,
				'updated'     => '2020-11-30',
			),
			'Filtered Categories'        => array(
				'plugin_link' => 'azrcrv-filtered-categories/azrcrv-filtered-categories.php',
				'admin_URL'   => 'admin.php?page=azrcrv-fc',
				'dev_URL'     => 'https://development.azurecurve.co.uk/classicpress-plugins/filtered-categories/',
				'retired'     => 0,
				'updated'     => '2020-06-02',
			),
			'Flags'                      => array(
				'plugin_link' => 'azrcrv-flags/azrcrv-flags.php',
				'admin_URL'   => 'admin.php?page=azrcrv-f',
				'dev_URL'     => 'https://development.azurecurve.co.uk/classicpress-plugins/flags/',
				'retired'     => 0,
				'updated'     => '2020-04-04',
			),
			'Floating Featured Image'    => array(
				'plugin_link' => 'azrcrv-floating-featured-image/azrcrv-floating-featured-image.php',
				'admin_URL'   => 'admin.php?page=azrcrv-ffi',
				'dev_URL'     => 'https://development.azurecurve.co.uk/classicpress-plugins/floating-featured-image/',
				'retired'     => 0,
				'updated'     => '2020-04-04',
			),
			'From Twitter'               => array(
				'plugin_link' => 'azrcrv-from-twitter/azrcrv-from-twitter.php',
				'admin_URL'   => 'admin.php?page=azrcrv-ft',
				'dev_URL'     => 'https://development.azurecurve.co.uk/classicpress-plugins/from-twitter/',
				'retired'     => 0,
				'updated'     => '2020-12-26',
			),
			'Gallery From Folder'        => array(
				'plugin_link' => 'azrcrv-gallery-from-folder/azrcrv-gallery-from-folder.php',
				'admin_URL'   => 'admin.php?page=azrcrv-gff',
				'dev_URL'     => 'https://development.azurecurve.co.uk/classicpress-plugins/gallery-from-folder/',
				'retired'     => 0,
				'updated'     => '2020-10-26',
			),
			'Get GitHub File'            => array(
				'plugin_link' => 'azrcrv-get-github-file/azrcrv-get-github-file.php',
				'admin_URL'   => 'admin.php?page=azrcrv-gghf',
				'dev_URL'     => 'https://development.azurecurve.co.uk/classicpress-plugins/get-github-file/',
				'retired'     => 0,
				'updated'     => '2020-11-20',
			),
			'Icons'                      => array(
				'plugin_link' => 'azrcrv-icons/azrcrv-icons.php',
				'admin_URL'   => 'admin.php?page=azrcrv-i',
				'dev_URL'     => 'https://development.azurecurve.co.uk/classicpress-plugins/icons/',
				'retired'     => 0,
				'updated'     => '2020-04-04',
			),
			'Images'                     => array(
				'plugin_link' => 'azrcrv-images/azrcrv-images.php',
				'admin_URL'   => 'admin.php?page=azrcrv-im',
				'dev_URL'     => 'https://development.azurecurve.co.uk/classicpress-plugins/images/',
				'retired'     => 0,
				'updated'     => '2020-04-04',
			),
			'Insult Generator'           => array(
				'plugin_link' => 'azrcrv-insult-generator/azrcrv-insult-generator.php',
				'admin_URL'   => 'admin.php?page=azrcrv-ig',
				'dev_URL'     => 'https://development.azurecurve.co.uk/classicpress-plugins/insult-generator/',
				'retired'     => 0,
				'updated'     => '2020-04-04',
			),
			'Loop Injection'             => array(
				'plugin_link' => 'azrcrv-loop-injection/azrcrv-loop-injection.php',
				'admin_URL'   => 'admin.php?page=azrcrv-li',
				'dev_URL'     => 'https://development.azurecurve.co.uk/classicpress-plugins/loop-injection/',
				'text'        => 'Loop Injection',
				'retired'     => 0,
				'updated'     => '2020-04-04',
			),
			'Maintenance Mode'           => array(
				'plugin_link' => 'azrcrv-maintenance-mode/azrcrv-maintenance-mode.php',
				'admin_URL'   => 'admin.php?page=azrcrv-mm',
				'dev_URL'     => 'https://development.azurecurve.co.uk/classicpress-plugins/maintenance-mode/',
				'retired'     => 0,
				'updated'     => '2020-04-04',
			),
			'Markdown'                   => array(
				'plugin_link' => 'azrcrv-markdown/azrcrv-markdown.php',
				'admin_URL'   => 'admin.php?page=azrcrv-m',
				'dev_URL'     => 'https://development.azurecurve.co.uk/classicpress-plugins/markdown/',
				'retired'     => 0,
				'updated'     => '2020-04-04',
			),
			'Mobile Detection'           => array(
				'plugin_link' => 'azrcrv-mobile-detection/azrcrv-mobile-detection.php',
				'admin_URL'   => 'admin.php?page=azrcrv-md',
				'dev_URL'     => 'https://development.azurecurve.co.uk/classicpress-plugins/mobile-detection/',
				'text'        => 'Mobile Detection',
				'retired'     => 0,
				'updated'     => '2020-04-04',
			),
			'Multisite Favicon'          => array(
				'plugin_link' => 'azrcrv-multisite-favicon/azrcrv-multisite-favicon.php',
				'admin_URL'   => 'admin.php?page=azrcrv-msf',
				'dev_URL'     => 'https://development.azurecurve.co.uk/classicpress-plugins/multisite-favicon/',
				'text'        => 'Multisite Favicon',
				'retired'     => 0,
				'updated'     => '2020-04-04',
			),
			'Nearby'                     => array(
				'plugin_link' => 'azrcrv-nearby/azrcrv-nearby.php',
				'admin_URL'   => 'admin.php?page=azrcrv-n',
				'dev_URL'     => 'https://development.azurecurve.co.uk/classicpress-plugins/nearby/',
				'retired'     => 0,
				'updated'     => '2020-08-05',
			),
			'Page Index'                 => array(
				'plugin_link' => 'azrcrv-page-index/azrcrv-page-index.php',
				'admin_URL'   => 'admin.php?page=azrcrv-pi',
				'dev_URL'     => 'https://development.azurecurve.co.uk/classicpress-plugins/page-index/',
				'retired'     => 0,
				'updated'     => '2020-04-04',
			),
			'Post Archive'               => array(
				'plugin_link' => 'azrcrv-post-archive/azrcrv-post-archive.php',
				'admin_URL'   => 'admin.php?page=azrcrv-pa',
				'dev_URL'     => 'https://development.azurecurve.co.uk/classicpress-plugins/post-archive/',
				'retired'     => 0,
				'updated'     => '2020-04-04',
			),
			'Read GitHub File'           => array(
				'plugin_link' => 'azrcrv-read-github-file/azrcrv-read-github-file.php',
				'admin_URL'   => 'admin.php?page=azrcrv-rghf',
				'dev_URL'     => 'https://development.azurecurve.co.uk/classicpress-plugins/read-github-file/',
				'retired'     => 1,
				'updated'     => '2020-10-30',
			),
			'Redirect'                   => array(
				'plugin_link' => 'azrcrv-redirect/azrcrv-redirect.php',
				'admin_URL'   => 'admin.php?page=azrcrv-r',
				'dev_URL'     => 'https://development.azurecurve.co.uk/classicpress-plugins/redirect/',
				'retired'     => 0,
				'updated'     => '2021-11-10',
			),
			'Remove Revisions'           => array(
				'plugin_link' => 'azrcrv-remove-revisions/azrcrv-remove-revisions.php',
				'admin_URL'   => 'admin.php?page=azrcrv-rr',
				'dev_URL'     => 'https://development.azurecurve.co.uk/classicpress-plugins/remove-revisions/',
				'retired'     => 0,
				'updated'     => '2021-09-14',
			),
			'RSS Feed'                   => array(
				'plugin_link' => 'azrcrv-rss-feed/azrcrv-rss-feed.php',
				'admin_URL'   => 'admin.php?page=azrcrv-rssf',
				'dev_URL'     => 'https://development.azurecurve.co.uk/classicpress-plugins/rss-feed/',
				'retired'     => 0,
				'updated'     => '2020-04-04',
			),
			'RSS Suffix'                 => array(
				'plugin_link' => 'azrcrv-rss-suffix/azrcrv-rss-suffix.php',
				'admin_URL'   => 'admin.php?page=azrcrv-rsss',
				'dev_URL'     => 'https://development.azurecurve.co.uk/classicpress-plugins/rss-suffix/',
				'retired'     => 0,
				'updated'     => '2020-04-04',
			),
			'Series Index'               => array(
				'plugin_link' => 'azrcrv-series-index/azrcrv-series-index.php',
				'admin_URL'   => 'admin.php?page=azrcrv-si',
				'dev_URL'     => 'https://development.azurecurve.co.uk/classicpress-plugins/series-index/',
				'retired'     => 0,
				'updated'     => '2020-04-04',
			),
			'Shortcodes in Comments'     => array(
				'plugin_link' => 'azrcrv-shortcodes-in-comments/azrcrv-shortcodes-in-comments.php',
				'admin_URL'   => 'admin.php?page=azrcrv-sic',
				'dev_URL'     => 'https://development.azurecurve.co.uk/classicpress-plugins/shortcodes-in-comments/',
				'retired'     => 0,
				'updated'     => '2020-04-04',
			),
			'Shortcodes in Widgets'      => array(
				'plugin_link' => 'azrcrv-shortcodes-in-widgets/azrcrv-shortcodes-in-widgets.php',
				'admin_URL'   => 'admin.php?page=azrcrv-siw',
				'dev_URL'     => 'https://development.azurecurve.co.uk/classicpress-plugins/shortcodes-in-widgets/',
				'retired'     => 0,
				'updated'     => '2020-04-04',
			),
			'Sidebar Login'              => array(
				'plugin_link' => 'azrcrv-sidebar-login/azrcrv-sidebar-login.php',
				'admin_URL'   => 'admin.php?page=azrcrv-sl',
				'dev_URL'     => 'https://development.azurecurve.co.uk/classicpress-plugins/sidebar-login/',
				'retired'     => 0,
				'updated'     => '2020-04-04',
			),
			'SMTP'                       => array(
				'plugin_link' => 'azrcrv-smtp/azrcrv-smtp.php',
				'admin_URL'   => 'admin.php?page=azrcrv-smtp',
				'dev_URL'     => 'https://development.azurecurve.co.uk/classicpress-plugins/smtp/',
				'retired'     => 0,
				'updated'     => '2020-01-28',
			),
			'Snippets'                   => array(
				'plugin_link' => 'azrcrv-snippets/azrcrv-snippets.php',
				'admin_URL'   => 'admin.php?page=azrcrv-s',
				'dev_URL'     => 'https://development.azurecurve.co.uk/classicpress-plugins/snippets/',
				'retired'     => 0,
				'updated'     => '2020-10-28',
			),
			'Tag Cloud'                  => array(
				'plugin_link' => 'azrcrv-tag-cloud/azrcrv-tag-cloud.php',
				'admin_URL'   => 'admin.php?page=azrcrv-tc',
				'dev_URL'     => 'https://development.azurecurve.co.uk/classicpress-plugins/tag-cloud/',
				'retired'     => 0,
				'updated'     => '2020-04-04',
			),
			'Taxonomy Index'             => array(
				'plugin_link' => 'azrcrv-taxonomy-index/azrcrv-taxonomy-index.php',
				'admin_URL'   => 'admin.php?page=azrcrv-ti',
				'dev_URL'     => 'https://development.azurecurve.co.uk/classicpress-plugins/taxonomy-index/',
				'retired'     => 0,
				'updated'     => '2020-04-04',
			),
			'Taxonomy Order'             => array(
				'plugin_link' => 'azrcrv-taxonomy-order/azrcrv-taxonomy-order.php',
				'admin_URL'   => 'admin.php?page=azrcrv-to',
				'dev_URL'     => 'https://development.azurecurve.co.uk/classicpress-plugins/taxonomy-order/',
				'retired'     => 0,
				'updated'     => '2020-10-28',
			),
			'Theme Switcher'             => array(
				'plugin_link' => 'azrcrv-theme-switcher/azrcrv-theme-switcher.php',
				'admin_URL'   => 'admin.php?page=azrcrv-ts',
				'dev_URL'     => 'https://development.azurecurve.co.uk/classicpress-plugins/theme-switcher/',
				'retired'     => 0,
				'updated'     => '2020-04-04',
			),
			'Timelines'                  => array(
				'plugin_link' => 'azrcrv-timelines/azrcrv-timelines.php',
				'admin_URL'   => 'admin.php?page=azrcrv-t',
				'dev_URL'     => 'https://development.azurecurve.co.uk/classicpress-plugins/timelines/',
				'retired'     => 0,
				'updated'     => '2020-04-04',
			),
			'To Twitter'                 => array(
				'plugin_link' => 'azrcrv-to-twitter/azrcrv-to-twitter.php',
				'admin_URL'   => 'admin.php?page=azrcrv-tt',
				'dev_URL'     => 'https://development.azurecurve.co.uk/classicpress-plugins/to-twitter/',
				'retired'     => 0,
				'updated'     => '2020-04-04',
			),
			'Toggle Show/Hide'           => array(
				'plugin_link' => 'azrcrv-toggle-showhide/azrcrv-toggle-showhide.php',
				'admin_URL'   => 'admin.php?page=azrcrv-tsh',
				'dev_URL'     => 'https://development.azurecurve.co.uk/classicpress-plugins/toggle-showhide/',
				'retired'     => 0,
				'updated'     => '2020-04-04',
			),
			'Update Admin Menu'          => array(
				'plugin_link' => 'azrcrv-update-admin-menu/azrcrv-update-admin-menu.php',
				'admin_URL'   => 'admin.php?page=azrcrv-uam',
				'dev_URL'     => 'https://development.azurecurve.co.uk/classicpress-plugins/update-admin-menu/',
				'retired'     => 0,
				'updated'     => '2020-12-26',
			),
			'URL Shortener'              => array(
				'plugin_link' => 'azrcrv-url-shortener/azrcrv-url-shortener.php',
				'admin_URL'   => 'admin.php?page=azrcrv-urls',
				'dev_URL'     => 'https://development.azurecurve.co.uk/classicpress-plugins/url-shortener/',
				'retired'     => 0,
				'updated'     => '2020-04-04',
			),
			'Username Protection'        => array(
				'plugin_link' => 'codepotent-username-protection/codepotent-username-protection.php',
				'admin_URL'   => 'https://development.azurecurve.co.uk/classicpress-plugins/username-protection/',
				'dev_URL'     => 'https://development.azurecurve.co.uk/classicpress-plugins/username-protection/',
				'retired'     => 0,
				'updated'     => '2021-06-12',
			),
			'Widget Announcements'       => array(
				'plugin_link' => 'azrcrv-widget-announcements/azrcrv-widget-announcements.php',
				'admin_URL'   => 'admin.php?page=azrcrv-wa',
				'dev_URL'     => 'https://development.azurecurve.co.uk/classicpress-plugins/widget-announcements/',
				'retired'     => 0,
				'updated'     => '2020-11-13',
			),
		);

		$plugin_menu = get_option( 'azrcrv-plugin-menu' );

		foreach ( $plugins as $plugin_name => $plugin_details ) {
			if ( isset( $plugin_menu[ $plugin_name ] ) ) {
				if ( strtotime( $plugin_menu[ $plugin_name ]['updated'] ) <= strtotime( $plugin_details['updated'] ) ) {
					$plugin_menu[ $plugin_name ] = $plugin_details;
				}
			} else {
				$plugin_menu[ $plugin_name ] = $plugin_details;
			}
		}

		ksort( $plugin_menu );

		update_option( 'azrcrv-plugin-menu', $plugin_menu );
	}
}
