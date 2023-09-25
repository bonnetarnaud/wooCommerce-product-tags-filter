<?php
class WooCommerce_Product_Tags_Filter_Widget extends WP_Widget
{
    // Constructor
    public function __construct()
    {
        parent::__construct(
            'woocommerce_product_tag_filter_widget',
            __('WooCommerce Product Tag Filter', 'woocommerce_product_tag_filter_widget_domain'),
            array(
                'description' => __('A widget to filter products by tags.', 'woocommerce_product_tag_filter_widget_domain')
            )
        );
    }

    // Widget output
    public function widget($args, $instance)
    {
        extract($args);

        $title = apply_filters('wooCommerce_product_tags_filter_title', $instance['title']);
        echo $before_widget;
        if (!empty($title)) {
            echo $before_title . $title . $after_title;
        }
        $render = $this->display_product_tags_with_checkboxes();
        echo $render;
        echo $after_widget;
    }

    // Widget settings form
    public function form($instance)
    {
        // Widget settings form fields
        // Implement the widget configuration form here
        if (isset($instance['title'])) {
            $title = $instance['title'];
        } else {
            $title = __('New title', 'text_domain');
        }
?>
        <p>
            <label for="<?php echo $this->get_field_name('title'); ?>"><?php _e('Title:'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
        </p>
        <?php

    }

    // Update widget settings
    public function update($new_instance, $old_instance)
    {
        // Save widget settings
        // Implement the update logic here
        $instance          = array();
        $instance['title'] = (!empty($new_instance['title'])) ? strip_tags($new_instance['title']) : '';
        return $instance;
    }

    public function get_product_tags_enabled()
    {

        $args = array(
            'post_type' => 'product',
            'posts_per_page' => -1, // Pour obtenir tous les produits
            'product_cat' => get_queried_object()->slug // Filtrer par catégorie
        );

        $query = new WP_Query($args);

        if ($query->have_posts()) {
            $product_tags = array();
            while ($query->have_posts()) {
                $query->the_post();
                $product_tags = array_merge($product_tags, wp_get_post_terms(get_the_ID(), 'product_tag', array('fields' => 'ids')));
            }
            wp_reset_postdata();
            return array_unique($product_tags);
        } else {
            return array();
        }
    }

    public function display_product_tags_with_checkboxes()
    {
        $render = 'No product tags found.';
        // Get all product tags
        $product_tags = get_terms(array(
            'taxonomy' => 'product_tag',
            'hide_empty' => true,
        ));

        $product_tags_enabled = array();
        $product_tags_enabled = $this->get_product_tags_enabled();


        if ($product_tags && !is_wp_error($product_tags)) {
            ob_start();
        ?>
            <div class="tags_filter_box">

                <ul>
                    <?php
                    foreach ($product_tags as $tag) : ?>
                        <?php
                        global $wp;

                        $min_price = get_query_var('min_price');
                        $max_price = get_query_var('max_price');

                        $arr_params = array();
                        if (isset($min_price) && !empty($min_price)) {
                            $arr_params['min_price'] = $min_price;
                        }
                        if (isset($max_price) && !empty($max_price)) {
                            $arr_params['max_price'] =  $max_price;
                        }
                        $arr_params['product_tag'] = $tag->slug;
                        $tag_link = esc_url(add_query_arg($arr_params, home_url($wp->request)));
                        $link = (get_query_var('product_tag') == $tag->slug) ? get_permalink(wc_get_page_id('shop')) : $tag_link;
                        $class = '';
                        if (!in_array($tag->term_id, $product_tags_enabled)) {
                            $class = 'disabled ';
                            $link = '#';
                        }
                        if (get_query_var('product_tag') == $tag->slug) {
                            $class = 'active ';
                        }

                        ?>
                        <li class="<?php echo $class ?>">
                            <a href="<?php echo  $link ?>">
                                <?php echo esc_html($tag->name) ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
<?php

            $render = ob_get_clean();
        }
        return $render;
    }
}

// Register the widget
function register_woocommerce_product_tags_filter_widget()
{
    register_widget('WooCommerce_Product_Tags_Filter_Widget');
}
add_action('widgets_init', 'register_woocommerce_product_tags_filter_widget');
