<tr id="post-<?php the_ID() ?>" <?php ghc_product_post_class(); ?>>
    <td class="thumb">
        <?php
        if ( has_post_thumbnail() ) {
            the_post_thumbnail( array( 50, 50 ) );
        }
        ?>
    </td>
    <td class="title">
        <?php echo $product->get_title(); ?>
        <?php
        if ( get_field( 'subtitle' ) ) {
            echo '<div class="entry-meta">' . get_field( 'subtitle' ) . '</div>';
        }
        ?>
        <?php if ( strpos( $product->get_title(), 'teen track' ) !== false ) { echo '<div class="small">Parents are welcome to attend without tickets</div>'; } ?>
    </td>
    <td class="price"><?php echo $product->get_price_html(); ?></td>
    <td class="actions">
        <?php
        $product_terms = get_the_terms( get_the_ID(), 'product_cat' );
        $registration_product = false;

        foreach ( $product_terms as $term ) {
            if ( 'registration' == $term->slug ) {
                $registration_product = true;
            }
        }

        if ( $registration_product ) {
            echo '<input name="qty-' . get_the_ID()  . '" type="hidden" value="1" min="1" max="1" />
            <label for="family-members"><input name="family-members-display" type="number" value="1" disabled /> family members</label>';
        } else {
            echo '<label class="qty" for="qty-' . $variation_array['variation_id'] . '"><input class="qty" name="qty-' . $variation_array['variation_id']  . '" type="number" value="0" min="0" max="6" /> <span class="tickets-qty">tickets</span></label>';
        }
        ?>
        <p class="product woocommerce add_to_cart_inline">
            <a rel="nofollow" href="<?php echo $variation->add_to_cart_url(); ?>" data-quantity="1" data-product_id="<?php echo get_the_ID(); ?>" class="button product_type_variation add_to_cart_button ajax_add_to_cart">Add to my order</a>
            <span class="spinner hidden"></span>
        </p>
    </td>
</tr>
