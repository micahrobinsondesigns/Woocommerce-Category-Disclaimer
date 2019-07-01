<?php

/**
 * @wordpress-plugin
 * Plugin Name:       Woocommerce Category Disclaimer
 * Description:       Add disclaimer field to Add/Edit Category page in wp-admin
 * Version:           1.0
 * Author:            Micah Robinson
 * License:           GPL-2.0
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       woocommerce-category-disclaimer
 WC tested up to: 5.1.1
 */

/** Die if accessed directly
*/

defined( 'ABSPATH' ) || exit;

if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
		// ONLY RUN IF WOOCOMMERCE IS ACTIVE....

	if ( is_admin() ) {
		$taxonomy= 'product_cat';

		add_action( "{$taxonomy}_add_form_fields", 'add_cat_disc_form', 10);
		add_action( "{$taxonomy}_edit_form_fields", 'edit_cat_disc_form', 10, 2 );
		if (!function_exists('add_cat_disc_form')) {
			function add_cat_disc_form( $taxonomy ) {
				?>
					<div class="form-field term-disclaimer-wrap">
						<label for="disclaimer">Disclaimer</label>
						<input name="disclaimer" id="disclaimer" type="text" size="40" value="<?php echo $cat_disc; ?>" placeholder="">
						<p class="description">Adds emphasized text in Short Description as a disclaimer for all products in this category.</p>
					</div>
				<?php
			};
		}
		if (!function_exists('edit_cat_disc_form')) {
			function edit_cat_disc_form( $tag, $taxonomy ) {
				$termid = $tag->term_id;
				$cat_disc = get_option( "disc_$termid" );
				?>
			    <tr class="form-field term-disclaimer-wrap">
			        <th scope="row">
			            <label for="disclaimer">Disclaimer</label>
			        </th>
			        <td>
			            <input name="disclaimer" id="disclaimer" type="text" size="40" value="<?php echo $cat_disc; ?>" placeholder="">
			            <p class="description">Adds emphasized text in Short Description as a disclaimer for all products in this category.</p>
			        </td>
			    </tr>
				<?php
			};
		}

		add_action ( "edited_{$taxonomy}", 'save_cat_disc_form' );
		add_action ( "created_{$taxonomy}",'save_cat_disc_form' );
		if (!function_exists('save_cat_disc_form')) {
			function save_cat_disc_form( $term_id ) {
				if ( isset( $_POST['disclaimer'] ) ) {
					$termid= $term_id;
					$prev_cat_disc = get_option( "disc_$termid" );
					if ( $prev_cat_disc !== false ) {
						update_option( "disc_$termid", $_POST['disclaimer'] );
					} else {
						add_option( "disc_$termid", $_POST['disclaimer'], '', 'yes' );
					}
				}
			}
		}
	}

	add_filter('woocommerce_short_description', 'add_cat_disc', 10);
	if (!function_exists('add_cat_disc')) {
		function add_cat_disc($post_post_excerpt){
			if ( is_product() ) {
				global $product;
			    $discount = 0;
				if ($product !== null) {
					$catIds= $product->get_category_ids();
					$disclaimers= '';
					foreach( $catIds as $catId ){
						$discStr= get_option( "disc_$catId");
						if($discStr){
							$disclaimers.= $discStr . "\n";
						}
					}
					if($disclaimers !== '') {
						return $post_post_excerpt . nl2br("<p><em>{$disclaimers}</em></p>");
					} else {
						return $post_post_excerpt;
					}
				} else {
					return $post_post_excerpt;
				}
			} else {
				return $post_post_excerpt;
			}
		}
	}
}
