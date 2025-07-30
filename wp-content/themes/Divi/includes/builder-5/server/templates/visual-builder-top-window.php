<?php
/**
 * Visual Builder's Top Window page template.
 *
 * This template is intentionally made to be as light as it needs to be because top window page is used
 * to render app window's iframe. For performance reason, top page needs to be as light as possible without
 * unnecessary element being rendered so the VB can be loaded and has its spinner gone as fast as possible.
 *
 * @package Divi
 * @since ??
 */

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
<?php
	elegant_description();
	elegant_keywords();
	elegant_canonical();

	/**
	 * Fires in the head, before {@see wp_head()} is called. This action can be used to
	 * insert elements into the beginning of the head before any styles or scripts.
	 *
	 * @since 1.0
	 */
	do_action( 'et_head_meta' );
?>

	<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />

	<script type="text/javascript">
		document.documentElement.className = 'js';
	</script>

	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
	<div id="page-container">
		<div id="et-main-area">
			<div id="main-content">
				<?php
				while ( have_posts() ) :
					the_post();
					?>
					<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
						<div class="entry-content">
							<?php the_content(); ?>
						</div>
					</article>
				<?php endwhile; ?>
			</div>
		</div>
	</div>
<?php wp_footer(); ?>
</body>
</html>
