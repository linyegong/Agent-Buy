<?php
/**
 * 产品元框模板
 */

// 如果直接访问此文件，则中止执行
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="agent-buy-product-meta-box">
    <?php wp_nonce_field('agent_buy_product_meta_box', 'agent_buy_product_meta_box_nonce'); ?>
    
    <table class="form-table">
        <?php foreach ($shops as $shop) : ?>
            <tr>
                <th>
                    <label for="agent_buy_<?php echo esc_attr($shop['id']); ?>">
                        <?php echo esc_html($shop['name']); ?><?php _e('产品ID', 'agent-buy'); ?>
                    </label>
                </th>
                <td>
                    <input 
                        type="text" 
                        id="agent_buy_<?php echo esc_attr($shop['id']); ?>" 
                        name="agent_buy_<?php echo esc_attr($shop['id']); ?>" 
                        value="<?php echo esc_attr($product_shop_ids[$shop['id']] ?? ''); ?>" 
                        class="regular-text"
                    >
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>
