<?php
/**
 * 代购链接前台显示模板
 */

// 如果直接访问此文件，则中止执行
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="agent-buy-links">
    <h4 class="agent-buy-title">Agent Buy</h4>
    <div class="agent-buy-platforms">
        <?php foreach ($links as $link) : ?>
            <a href="<?php echo esc_url($link['url']); ?>" target="_blank" class="agent-buy-platform" title="<?php echo esc_attr($link['shop_name'] . ' - ' . $link['platform_name']); ?>">
                <?php 
                if (!empty($link['image_id'])) {
                    $image = wp_get_attachment_image_src($link['image_id'], 'thumbnail');
                    if ($image) {
                        echo '<img src="' . esc_url($image[0]) . '" alt="' . esc_attr($link['platform_name']) . '" class="agent-buy-platform-image">';
                    } else {
                        echo esc_html($link['platform_name']);
                    }
                } else {
                    echo esc_html($link['platform_name']);
                }
                ?>
            </a>
        <?php endforeach; ?>
    </div>
</div>
