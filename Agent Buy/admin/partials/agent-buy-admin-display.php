<?php
/**
 * 代购管理后台界面
 */

// 如果直接访问此文件，则中止执行
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap agent-buy-admin">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <div class="agent-buy-container">
        <!-- 左侧添加/编辑表单 -->
        <div class="agent-buy-form-container">
            <div class="agent-buy-form-wrapper">
                <h2 id="agent-buy-form-title"><?php _e('添加店铺/代购平台', 'agent-buy'); ?></h2>
                
                <form id="agent-buy-form" method="post">
                    <input type="hidden" id="agent-buy-id" name="id" value="0">
                    <input type="hidden" id="agent-buy-type" name="type" value="shop">
                    
                    <div class="form-field">
                        <label for="agent-buy-name"><?php _e('名称', 'agent-buy'); ?> <span class="required">*</span></label>
                        <input type="text" id="agent-buy-name" name="name" required>
                        <p class="description"><?php _e('店铺名称或代购平台名称，添加的是店铺名称，产品页前台不显示；添加的是代购平台，则产品页显示名称或LOGO图。', 'agent-buy'); ?></p>
                    </div>
                    
                    <div class="form-field">
                        <label for="agent-buy-shop-id"><?php _e('所属店铺', 'agent-buy'); ?></label>
                        <select id="agent-buy-shop-id" name="shop_id">
                            <option value="0"><?php _e('无（创建为店铺）', 'agent-buy'); ?></option>
                            <?php foreach ($shops as $shop) : ?>
                                <option value="<?php echo esc_attr($shop['id']); ?>"><?php echo esc_html($shop['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <p class="description"><?php _e('选择"无"则创建为店铺，若选中已创建的店铺，则为代购平台', 'agent-buy'); ?></p>
                    </div>
                    
                    <div class="form-field" id="agent-buy-url-field" style="display: none;">
                        <label for="agent-buy-url"><?php _e('代购网址', 'agent-buy'); ?> <span class="required">*</span></label>
                        <input type="text" id="agent-buy-url" name="url_pattern">
                        <p class="description"><?php _e('「代购网址」链接规则(含{0}占位符)。如果添加的是店铺，则可以不填网址，如果添加的是代购平台，则必须填写', 'agent-buy'); ?></p>
                    </div>
                    
                    <div class="form-field">
                        <label for="agent-buy-description"><?php _e('描述', 'agent-buy'); ?></label>
                        <textarea id="agent-buy-description" name="description" rows="5"></textarea>
                        <p class="description"><?php _e('描述前台不显示，仅后台备注查看。', 'agent-buy'); ?></p>
                    </div>
                    
                    <div class="form-field">
                        <label for="agent-buy-image"><?php _e('图像', 'agent-buy'); ?></label>
                        <div class="agent-buy-image-container">
                            <div class="agent-buy-image-preview">
                                <img src="<?php echo esc_url(admin_url('images/placeholder.png')); ?>" id="agent-buy-image-preview" style="max-width: 100px; max-height: 100px;">
                            </div>
                            <input type="hidden" id="agent-buy-image-id" name="image_id" value="0">
                            <button type="button" class="button agent-buy-upload-image"><?php _e('上传图像', 'agent-buy'); ?></button>
                            <button type="button" class="button agent-buy-remove-image" style="display: none;"><?php _e('移除图像', 'agent-buy'); ?></button>
                        </div>
                        <p class="description"><?php _e('用于上传店铺或代购平台logo', 'agent-buy'); ?></p>
                    </div>
                    
                    <div class="form-field">
                        <label for="agent-buy-status"><?php _e('状态', 'agent-buy'); ?></label>
                        <select id="agent-buy-status" name="status">
                            <option value="1"><?php _e('启用', 'agent-buy'); ?></option>
                            <option value="0"><?php _e('禁用', 'agent-buy'); ?></option>
                        </select>
                    </div>
                    
                    <p class="submit">
                        <button type="submit" class="button button-primary"><?php _e('保存', 'agent-buy'); ?></button>
                        <button type="button" class="button agent-buy-cancel" style="display: none;"><?php _e('取消', 'agent-buy'); ?></button>
                    </p>
                </form>
            </div>
        </div>
        
        <!-- 右侧列表 -->
        <div class="agent-buy-list-container">
            <!-- 店铺列表 -->
            <div class="agent-buy-list-wrapper">
                <h2><?php _e('店铺列表', 'agent-buy'); ?></h2>
                
                <form method="post">
                    <?php wp_nonce_field('agent_buy_bulk_action', 'agent_buy_nonce'); ?>
                    
                    <div class="tablenav top">
                        <div class="alignleft actions bulkactions">
                            <select name="action">
                                <option value="-1">批量操作</option>
                                <option value="delete">删除</option>
                            </select>
                            <input type="submit" class="button action" value="应用">
                        </div>
                        <div class="tablenav-pages one-page">
                            <span class="displaying-num"><?php printf(_n('%s 项', '%s 项', count($shops), 'agent-buy'), number_format_i18n(count($shops))); ?></span>
                        </div>
                    </div>
                    
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th class="check-column"><input type="checkbox"></th>
                                <th class="image column-image" scope="col">图像</th>
                                <th class="name column-name" scope="col">名称</th>
                                <th>描述</th>
                                <th>状态</th>
                                <th>排序</th>
                            </tr>
                        </thead>
                        <tbody id="the-list">
                            <?php if (empty($shops)) : ?>
                                <tr>
                                    <td colspan="5" class="no-items">没有店铺</td>
                                </tr>
                            <?php else : ?>
                                <?php foreach ($shops as $shop) : ?>
                                    <tr data-id="<?php echo esc_attr($shop['id']); ?>" data-type="shop">
                                        <td class="check-column">
                                            <input type="checkbox" name="shop_ids[]" value="<?php echo esc_attr($shop['id']); ?>">
                                        </td>
                                        <td class="image column-image">
                                            <img src="<?php 
                                                // 使用插件目录的占位符图片
                                                $placeholder_url = plugins_url('admin/images/placeholder.png', WP_PLUGIN_DIR . '/Agent Buy/agent-buy.php');
                                                
                                                // 检查文件是否存在，如果不存在则使用默认占位符
                                                if (file_exists(WP_PLUGIN_DIR . '/Agent Buy/admin/images/placeholder.png')) {
                                                    echo esc_url($placeholder_url);
                                                } else {
                                                    echo esc_url(admin_url('images/placeholder.png'));
                                                }
                                            ?>" class="agent-buy-thumbnail" style="max-width: 32px; max-height: 32px; vertical-align: middle;">
                                        </td>
                                        <td class="name column-name">
                                            <strong><?php echo esc_html($shop['name']); ?></strong>
                                            <div class="row-actions">
                                                <span class="edit">
                                                    <a href="#" class="agent-buy-edit" data-id="<?php echo esc_attr($shop['id']); ?>" data-type="shop" title="编辑">
                                                        <span class="dashicons dashicons-edit"></span>
                                                    </a> |
                                                </span>
                                                <span class="delete">
                                                    <a href="#" class="agent-buy-delete" data-id="<?php echo esc_attr($shop['id']); ?>" data-type="shop" title="删除">
                                                        <span class="dashicons dashicons-trash"></span>
                                                    </a>
                                                </span>
                                            </div>
                                        </td>
                                        <td class="description column-description">
                                            <?php echo esc_html($shop['description']); ?>
                                        </td>
                                        <td class="status column-status">
                                            <?php echo $shop['status'] ? '启用' : '禁用'; ?>
                                        </td>
                                        <td class="menu-order column-menu-order">
                                            <?php echo esc_html($shop['menu_order']); ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                    
                    <div class="tablenav bottom">
                        <div class="alignleft actions bulkactions">
                            <select name="action">
                                <option value="-1">批量操作</option>
                                <option value="delete">删除</option>
                            </select>
                            <input type="submit" class="button action" value="应用">
                        </div>
                        <div class="tablenav-pages one-page">
                            <span class="displaying-num"><?php printf(_n('%s 项', '%s 项', count($shops), 'agent-buy'), number_format_i18n(count($shops))); ?></span>
                        </div>
                    </div>
                </form>
                
                <!-- 快速编辑面板 -->
                <div id="agent-buy-quick-edit" class="hidden quick-edit-form">
                    <h3>快速编辑</h3>
                    <form method="post">
                        <input type="hidden" id="quick-edit-id" name="id">
                        <input type="hidden" id="quick-edit-type" name="type">
                        
                        <div class="quick-edit-form-fields">
                            <div class="inline-edit-group">
                                <label for="quick-edit-name">名称</label>
                                <input type="text" id="quick-edit-name" name="name">
                            </div>
                            
                            <div class="inline-edit-group">
                                <label for="quick-edit-description">描述</label>
                                <textarea id="quick-edit-description" name="description"></textarea>
                            </div>
                        
                            <div class="inline-edit-group">
                                <label class="inline-edit-label">
                                    <span>状态</span>
                                    <select id="quick-edit-status" name="status">
                                        <option value="1">启用</option>
                                        <option value="0">禁用</option>
                                    </select>
                                </label>
                            </div>
                            
                            <div class="inline-edit-group">
                                <label class="inline-edit-label">
                                    <span>排序</span>
                                    <input type="number" id="quick-edit-menu-order" name="menu_order" min="0" step="1">
                                </label>
                            </div>
                            <div class="inline-edit-group image-group">
                                <label class="inline-edit-label">
                                    <span>图像</span>
                                    <div class="agent-buy-image-container">
                                        <div class="agent-buy-image-preview">
                                            <img src="<?php echo esc_url(admin_url('images/placeholder.png')); ?>" id="quick-edit-image-preview">
                                        </div>
                                        <input type="hidden" id="quick-edit-image-id" name="image_id">
                                        <div class="button-group">
                                            <button type="button" class="button agent-buy-upload-quick-edit-image">上传图像</button>
                                            <button type="button" class="button agent-buy-remove-quick-edit-image" style="display: none;">移除图像</button>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        
                        </div>
                        
                        <div class="quick-edit-actions">
                            <button type="button" class="button cancel-quick-edit">取消</button>
                            <button type="submit" class="button button-primary save-quick-edit">更新</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- 代购平台列表 -->
            <div class="agent-buy-list-wrapper">
                <h2><?php _e('代购平台列表', 'agent-buy'); ?></h2>
                
                <form method="post">
                    <?php wp_nonce_field('agent_buy_bulk_action', 'agent_buy_nonce'); ?>
                    
                    <div class="tablenav top">
                        <div class="alignleft actions bulkactions">
                            <select name="action">
                                <option value="-1"><?php _e('批量操作', 'agent-buy'); ?></option>
                                <option value="delete"><?php _e('删除', 'agent-buy'); ?></option>
                            </select>
                            <input type="submit" class="button action" value="<?php esc_attr_e('应用', 'agent-buy'); ?>">
                        </div>
                        <div class="tablenav-pages one-page">
                            <span class="displaying-num"><?php printf(_n('%s 项', '%s 项', count($platforms), 'agent-buy'), number_format_i18n(count($platforms))); ?></span>
                        </div>
                    </div>
                    
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th class="check-column"><input type="checkbox"></th>
                                <th class="image column-image" scope="col">图像</th>
                                <th class="name column-name" scope="col">名称</th>
                                <th>所属店铺</th>
                                <th>代购网址</th>
                                <th>状态</th>
                            </tr>
                        </thead>
                        <tbody id="the-list">
                            <?php if (empty($platforms)) : ?>
                                <tr>
                                    <td colspan="5" class="no-items">没有代购平台</td>
                                </tr>
                            <?php else : ?>
                                <?php foreach ($platforms as $platform) : ?>
                                    <tr data-id="<?php echo esc_attr($platform['id']); ?>" data-type="platform">
                                        <td class="check-column">
                                            <input type="checkbox" name="platform_ids[]" value="<?php echo esc_attr($platform['id']); ?>">
                                        </td>
                                        <td class="image column-image">
                                            <img src="<?php 
                                                // 使用插件目录的占位符图片
                                                $placeholder_url = plugins_url('admin/images/placeholder.png', WP_PLUGIN_DIR . '/Agent Buy/agent-buy.php');
                                                
                                                // 检查文件是否存在，如果不存在则使用默认占位符
                                                if (file_exists(WP_PLUGIN_DIR . '/Agent Buy/admin/images/placeholder.png')) {
                                                    echo esc_url($placeholder_url);
                                                } else {
                                                    echo esc_url(admin_url('images/placeholder.png'));
                                                }
                                            ?>" class="agent-buy-thumbnail" style="max-width: 32px; max-height: 32px; vertical-align: middle;">
                                        </td>
                                        <td class="name column-name">
                                            <strong><?php echo esc_html($platform['name']); ?></strong>
                                            <div class="row-actions">
                                                <span class="edit">
                                                    <a href="#" class="agent-buy-edit" data-id="<?php echo esc_attr($platform['id']); ?>" data-type="platform" title="编辑">
                                                        <span class="dashicons dashicons-edit"></span>
                                                </a> |
                                            </span>
                                                <span class="delete">
                                                    <a href="#" class="agent-buy-delete" data-id="<?php echo esc_attr($platform['id']); ?>" data-type="platform" title="删除">
                                                        <span class="dashicons dashicons-trash"></span>
                                                    </a>
                                                </span>
                                            </div>
                                        </td>
                                        <td class="name column-name">
                                            <?php if ($platform['shop_id']): ?>
                                                <?php 
                                                    $shop = $this->db->get_shop($platform['shop_id']);
                                                    if ($shop) {
                                                        echo esc_html($shop['name']);
                                                    } else {
                                                        echo '店铺ID: ' . esc_html($platform['shop_id']) . ' 不存在';
                                                    }
                                                ?>
                                            <?php else: ?>
                                                未知店铺
                                            <?php endif; ?>
                                        </td>
                                        <td class="url column-url">
                                            <?php echo esc_html($platform['url_pattern']); ?>
                                        </td>
                                        <td class="status column-status">
                                            <?php echo $platform['status'] ? '启用' : '禁用'; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                    
                    <div class="tablenav bottom">
                        <div class="alignleft actions bulkactions">
                            <select name="action">
                                <option value="-1">批量操作</option>
                                <option value="delete">删除</option>
                            </select>
                            <input type="submit" class="button action" value="应用">
                        </div>
                        <div class="tablenav-pages one-page">
                            <span class="displaying-num"><?php printf(_n('%s 项', '%s 项', count($platforms), 'agent-buy'), number_format_i18n(count($platforms))); ?></span>
                        </div>
                    </div>
                </form>
                
                <!-- 快速编辑面板 -->
                <div id="agent-buy-quick-edit" class="hidden quick-edit-form">
                    <h3>快速编辑</h3>
                    <form method="post">
                        <input type="hidden" id="quick-edit-id" name="id">
                        <input type="hidden" id="quick-edit-type" name="type">
                        
                        <div class="quick-edit-form-fields">
                            <div class="inline-edit-group">
                                <label for="quick-edit-name">名称</label>
                                <input type="text" id="quick-edit-name" name="name">
                            </div>
                            
                            <div class="inline-edit-group">
                                <label for="quick-edit-description">描述</label>
                                <textarea id="quick-edit-description" name="description"></textarea>
                            </div>
                        
                            <div class="inline-edit-group">
                                <label class="inline-edit-label">
                                    <span>状态</span>
                                    <select id="quick-edit-status" name="status">
                                        <option value="1">启用</option>
                                        <option value="0">禁用</option>
                                    </select>
                                </label>
                            </div>
                            <div class="inline-edit-group image-group">
                                <label class="inline-edit-label">
                                    <span>图像</span>
                                    <div class="agent-buy-image-container">
                                        <div class="agent-buy-image-preview">
                                            <img src="<?php echo esc_url(admin_url('images/placeholder.png')); ?>" id="quick-edit-image-preview">
                                        </div>
                                        <input type="hidden" id="quick-edit-image-id" name="image_id">
                                        <div class="button-group">
                                            <button type="button" class="button agent-buy-upload-quick-edit-image">上传图像</button>
                                            <button type="button" class="button agent-buy-remove-quick-edit-image" style="display: none;">移除图像</button>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        
                        <div class="inline-edit-group">
                            <label class="inline-edit-label">
                                <span>所属店铺</span>
                                <select id="quick-edit-shop-id" name="shop_id">
                                    <option value="0">无（创建为店铺）</option>
                                    <?php foreach ($shops as $shop) : ?>
                                        <option value="<?php echo esc_attr($shop['id']); ?>"><?php echo esc_html($shop['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </label>
                        </div>
                        
                        <div class="inline-edit-group">
                            <label class="inline-edit-label">
                                <span>代购网址</span>
                                <input type="text" id="quick-edit-url-pattern" name="url_pattern">
                            </label>
                        </div>
                        </div>
                        
                        <div class="quick-edit-actions">
                            <button type="button" class="button cancel-quick-edit">取消</button>
                            <button type="submit" class="button button-primary save-quick-edit">更新</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
