/**
 * 代购链接前台JavaScript
 */

(function($) {
    'use strict';
    
    // 当文档加载完成时
    $(document).ready(function() {
        // 处理图片加载失败
        handleImageError();
    });
    
    /**
     * 处理图片加载失败
     * 当图片加载失败时，显示平台名称作为替代
     */
    function handleImageError() {
        $('.agent-buy-platform-image').on('error', function() {
            var $img = $(this);
            var platformName = $img.attr('alt');
            
            // 替换图片为文本
            $img.parent().html(platformName);
        });
    }
    
})(jQuery);
