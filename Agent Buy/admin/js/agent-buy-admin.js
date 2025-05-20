(function($) {
    'use strict';

    $(document).ready(function() {
        // 快速编辑点击事件
        $('.agent-buy-edit').on('click', function(e) {
            e.preventDefault();
            var $this = $(this);
            var id = $this.data('id');
            var type = $this.data('type');
            var $row = $this.closest('tr');
            
            // 获取当前行的数据
            var name = $row.find('.name strong').text();
            var description = $row.find('.description').text();
            var status = $row.find('.status').data('status');
            var imageId = $row.find('.thumbnail img').data('image-id') || 0;
            
            // 构建编辑表单
            var $form = $('<div class="quick-edit-form">');
            $form.append('<div><label>名称：</label><input type="text" name="name" value="' + name + '"></div>');
            $form.append('<div><label>描述：</label><textarea name="description">' + description + '</textarea></div>');
            $form.append('<div><label>状态：</label><select name="status"><option value="1">启用</option><option value="0">禁用</option></select></div>');
            $form.append('<div><label>图片：</label><input type="hidden" name="image_id" value="' + imageId + '"></div>');
            
            if (type === 'platform') {
                var shopId = $row.find('.shop_id').data('shop-id');
                var urlPattern = $row.find('.url_pattern').text();
                $form.append('<div><label>店铺ID：</label><input type="number" name="shop_id" value="' + shopId + '"></div>');
                $form.append('<div><label>URL模式：</label><input type="text" name="url_pattern" value="' + urlPattern + '"></div>');
            }
            
            // 显示编辑表单
            $row.hide().after($form);
            $form.find('select[name="status"]').val(status);
            
            // 添加保存和取消按钮
            var $actions = $('<div class="quick-edit-actions">');
            $actions.append('<button type="button" class="button button-primary save-quick-edit">保存</button>');
            $actions.append('<button type="button" class="button cancel-quick-edit">取消</button>');
            $form.append($actions);
            
            // 取消编辑
            $form.on('click', '.cancel-quick-edit', function() {
                $form.remove();
                $row.show();
            });
            
            // 保存编辑
            $form.on('click', '.save-quick-edit', function() {
                var data = {
                    action: 'agent_buy_save_quick_edit',
                    nonce: agent_buy_vars.nonce,
                    id: id,
                    type: type,
                    name: $form.find('[name="name"]').val(),
                    description: $form.find('[name="description"]').val(),
                    status: $form.find('[name="status"]').val(),
                    image_id: $form.find('[name="image_id"]').val()
                };
                
                if (type === 'platform') {
                    data.shop_id = $form.find('[name="shop_id"]').val();
                    data.url_pattern = $form.find('[name="url_pattern"]').val();
                }
                
                $.post(ajaxurl, data, function(response) {
                    if (response.success) {
                        // 更新行数据
                        $row.find('.name strong').text(data.name);
                        $row.find('.description').text(data.description);
                        $row.find('.status').data('status', data.status);
                        if (type === 'platform') {
                            $row.find('.shop_id').data('shop-id', data.shop_id);
                            $row.find('.url_pattern').text(data.url_pattern);
                        }
                        
                        // 关闭编辑表单
                        $form.remove();
                        $row.show();
                    } else {
                        alert(response.data.message || '保存失败');
                    }
                });
            });
        });
    });
})(jQuery);