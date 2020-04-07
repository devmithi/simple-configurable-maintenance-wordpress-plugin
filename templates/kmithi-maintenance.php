<?php
/**
 * The template for displaying message
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
 */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();
?>
<style>
div.show-kmithi-maintenance{position: fixed;top: 0;bottom: 0;left: 0;right: 0;text-align: center;width: 100%;background: #888888;color: #FFFF;z-index: 999999; opacity: 1;}
div.show-kmithi-maintenance-body{background: #ffffff;color: #ff9800; margin: 160px 30px; height: 100px;border-radius: 5px;opacity: 1 !important;padding-top: 3%;text-align: center;vertical-align: bottom;position: relative;}
</style>
        <section id="primary" class="content-area">
		<main id="main" class="site-main">
			<div class="show-kmithi-maintenance">
				<div class="show-kmithi-maintenance-body"><?php echo apply_filters( 'km_maintenance_msg', get_option('kmithi-fld-message') ); ?></div>
			</div>
		</main>
	</section>
<?php
get_footer();
