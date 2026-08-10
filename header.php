<?php
/**
 * Header WordPress — header.php
 *
 * Correspondance de layout/theme.liquid (partie haute) :
 *   <head> + ouverture <body> + render du header.
 *
 * Le composant header (conversion de sections/header.liquid) est inclus
 * via get_template_part( 'template-parts/header' ).
 *
 * @package Panstellar
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<!doctype html>
<html class="js" <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="theme-color" content="">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<?php wp_head(); ?>
</head>

<body class="gradient<?php echo is_admin_bar_showing() ? ' admin-bar' : ''; ?>">
<?php wp_body_open(); ?>
<a class="skip-to-content-link button visually-hidden" href="#MainContent">
	<?php esc_html_e( 'Skip to content', 'panstellar' ); ?>
</a>

<?php get_template_part( 'template-parts/header' ); ?>

<main id="MainContent" class="content-for-layout focus-none" role="main" tabindex="-1">
