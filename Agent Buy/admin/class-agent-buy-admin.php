  <?php
/**
 * 插件的管理员特定功能
 */

// 如果直接访问此文件，则中止执行
if (!defined('ABSPATH')) {
    exit;
}

class Agent_Buy_Admin {

    /**
     * 插件名称
     */
    private $plugin_name;

    /**
     * 插件版本
     */
    private $version;
    
    /**
     * 数据库操作类实例
     */
    private $db;

    /**
     * 初始化类并设置其属性
     */
    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->db = new Agent_Buy_DB();
        
        // 初始化AJAX钩子
        add_action('wp_ajax_agent_buy_save_quick_edit', array($this, 'save_quick_edit'));
        add_action('wp_ajax_agent_buy_get_platform', array($this, 'get_platform_data'));
        add_action('wp_ajax_agent_buy_get_shop', array($this, 'get_shop_data'));
    }

    /**
     * 注册样式表
     */
    public function enqueue_styles() {
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_style($this->plugin_name, AGENT_BUY_PLUGIN_URL . 'admin/css/agent-buy-admin.css', array(), $this->version, 'all');
        wp_enqueue_media();
    }

    /**
     * 注册JavaScript
     */
    public function enqueue_scripts() {
        wp_enqueue_script('jquery-ui-sortable');
        wp_enqueue_script('jquery-ui-dialog');
        wp_enqueue_style('wp-jquery-ui-dialog');
        wp_enqueue_script('wp-color-picker');
        wp_enqueue_script($this->plugin_name, AGENT_BUY_PLUGIN_URL . 'admin/js/agent-buy-admin.js', array('jquery'), $this->version, false);
        
        wp_localize_script($this->plugin_name, 'agent_buy_params', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('agent_buy_nonce'),
            'i18n' => array(
                'confirm_delete' => __('确定要删除吗？此操作无法撤销。', 'agent-buy'),
                'select_image' => __('选择图片', 'agent-buy'),
                'use_image' => __('使用此图片', 'agent-buy'),
            )
        ));
    }
    
    /**
     * 添加管理菜单
     */
    public function add_plugin_admin_menu() {
        add_menu_page(
            __('代购平台管理', 'agent-buy'),
            __('代购平台', 'agent-buy'),
            'manage_options',
            $this->plugin_name,
            array($this, 'display_plugin_admin_page'),
            'dashicons-cart',
            30
        );
    }

    /**
     * 渲染管理页面
     */
    public function display_plugin_admin_page() {
        // 处理批量操作
        if (isset($_POST['action']) && $_POST['action'] === 'delete' && isset($_POST['shop_ids'])) {
            if (check_admin_referer('agent_buy_bulk_action', 'agent_buy_nonce')) {
                $shop_ids = array_map('intval', $_POST['shop_ids']);
                foreach ($shop_ids as $shop_id) {
                    $this->db->delete_shop($shop_id);
                }
                echo '<div class="notice notice-success"><p>' . __('已成功删除所选项目。', 'agent-buy') . '</p></div>';
            }
        }
        
        if (isset($_POST['action']) && $_POST['action'] === 'delete' && isset($_POST['platform_ids'])) {
            if (check_admin_referer('agent_buy_bulk_action', 'agent_buy_nonce')) {
                $platform_ids = array_map('intval', $_POST['platform_ids']);
                foreach ($platform_ids as $platform_id) {
                    $this->db->delete_platform($platform_id);
                }
                echo '<div class="notice notice-success"><p>' . __('已成功删除所选项目。', 'agent-buy') . '</p></div>';
            }
        }
        
        // 获取数据
        $shops = $this->db->get_shops();
        $platforms = $this->db->get_platforms();
        
        // 包含模板
        include AGENT_BUY_PLUGIN_DIR . 'admin/partials/agent-buy-admin-display.php';
    }

    /**
     * 添加产品元框
     */
    public function add_product_meta_boxes() {
        add_meta_box(
            'agent_buy_product_meta_box',
            __('代购信息', 'agent-buy'),
            array($this, 'render_product_meta_box'),
            'product',
            'normal',
            'default'
        );
    }

    /**
     * 渲染产品元框
     */
    public function render_product_meta_box($post) {
        // 获取所有店铺
        $shops = $this->db->get_shops();
        
        // 获取当前产品的代购ID
        $product_shop_ids = array();
        foreach ($shops as $shop) {
            $product_shop_ids[$shop['id']] = get_post_meta($post->ID, '_agent_buy_' . $shop['id'], true);
        }
        
        // 包含模板
        include AGENT_BUY_PLUGIN_DIR . 'admin/partials/agent-buy-product-meta-box.php';
    }

    /**
     * 保存产品元数据
     */
    public function save_product_meta_data($post_id) {
        // 检查是否是自动保存
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        // 检查权限
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        
        // 获取所有店铺
        $shops = $this->db->get_shops();
        
        // 保存每个店铺的产品ID
        foreach ($shops as $shop) {
            $field_name = 'agent_buy_' . $shop['id'];
            if (isset($_POST[$field_name])) {
                update_post_meta($post_id, '_' . $field_name, sanitize_text_field($_POST[$field_name]));
            }
        }
    }

    /**
     * AJAX保存店铺或平台
     */
    public function ajax_save_shop() {
        check_ajax_referer('agent_buy_nonce', 'nonce');
        
        error_log('Agent Buy Admin - ajax_save_shop: Received POST data: ' . print_r($_POST, true));

        if (!current_user_can('manage_options')) {
            error_log('Agent Buy Admin - ajax_save_shop: Permission denied.');
            wp_send_json_error(array('message' => __('权限不足', 'agent-buy')));
            return; // Ensure execution stops
        }
        
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $data = array(); // Initialize an empty data array

        // Conditionally add fields to $data if they are present in the POST request
        if (isset($_POST['name'])) {
            $data['name'] = sanitize_text_field($_POST['name']);
        }
        if (isset($_POST['description'])) {
            $data['description'] = sanitize_textarea_field($_POST['description']);
        }
        if (isset($_POST['image_id'])) {
            $data['image_id'] = intval($_POST['image_id']);
        }
        
        if (isset($_POST['status'])) {
            $data['status'] = intval($_POST['status']);
        } elseif ($id === 0) { // For new items, if status is not set, default to 1 (enabled)
            $data['status'] = 1;
        }

        $is_platform = isset($_POST['shop_id']) && intval($_POST['shop_id']) > 0;
        
        if ($is_platform) {
            // For platforms, shop_id is crucial for context and linking.
            // It's part of the platform's definition, not something to be updated on an existing platform via this field alone.
            // It should be present in $_POST if we are dealing with a platform.
            $data['shop_id'] = intval($_POST['shop_id']); 
            if (isset($_POST['url_pattern'])) {
                $data['url_pattern'] = esc_url_raw($_POST['url_pattern']);
            } elseif ($id === 0 && empty($data['url_pattern'])) { 
                // New platform requires url_pattern. If not set yet, it will be caught by validation below.
            }
        }

        // Validation and Pre-emptive exit for problematic requests
        if ($id > 0) { // Update operation
            $updatable_fields_provided = false;
            $potential_fields = ['name', 'description', 'image_id', 'status'];
            if ($is_platform) $potential_fields[] = 'url_pattern';

            foreach ($potential_fields as $field) {
                if (isset($_POST[$field])) {
                    $updatable_fields_provided = true;
                    break;
                }
            }
            if (!$updatable_fields_provided) {
                 error_log('Agent Buy Admin - ajax_save_shop: Update requested for ID ' . $id . ' but no updatable fields provided.');
                 $item_type_to_fetch = $is_platform ? 'platform' : 'shop';
                 $item_data_to_fetch = $is_platform ? $this->db->get_platform($id) : $this->db->get_shop($id);
                 if ($item_data_to_fetch) {
                     wp_send_json_success(array(
                         'message' => __('保存成功 (无字段更新)', 'agent-buy'),
                         'item_id' => $id,
                         'item_type' => $item_type_to_fetch,
                         'item_data' => $item_data_to_fetch
                     ));
                 } else {
                     wp_send_json_error(array('message' => __('保存失败: 未找到项目。', 'agent-buy')));
                 }
                 return;
            }
        } else { // New item ($id === 0)
            if (empty($data['name'])) {
                error_log('Agent Buy Admin - ajax_save_shop: Add new item - name is missing or empty. Data: ' . print_r($data, true));
                wp_send_json_error(array('message' => __('保存失败: 名称为必填项。', 'agent-buy')));
                return;
            }
            if ($is_platform && empty($data['url_pattern'])) {
                error_log('Agent Buy Admin - ajax_save_shop: Add new platform - URL Pattern is missing or empty. Data: ' . print_r($data, true));
                wp_send_json_error(array('message' => __('保存失败: 代购平台的URL规则为必填项。', 'agent-buy')));
                return;
            }
            if ($is_platform && empty($data['shop_id'])) {
                 error_log('Agent Buy Admin - ajax_save_shop: Add new platform - shop_id is missing. Data: ' . print_r($data, true));
                 wp_send_json_error(array('message' => __('保存失败: 新建代购平台必须指定所属店铺。', 'agent-buy')));
                 return;
            }
        }
        
        $result = false;

        if ($is_platform) {
            if ($id > 0) { 
                $result = $this->db->update_platform($id, $data);
            } else { 
                $new_platform_id = $this->db->add_platform($data);
                $result = $new_platform_id; 
                if ($new_platform_id) $id = $new_platform_id; // Update $id to new ID for fetching item later
            }
        } else { 
            if ($id > 0) { 
                $result = $this->db->update_shop($id, $data);
            } else { 
                $new_shop_id = $this->db->add_shop($data);
                $result = $new_shop_id;
                if ($new_shop_id) $id = $new_shop_id; // Update $id to new ID
            }
        }
        
        global $wpdb;

        if ($result === false) { 
            error_log('Agent Buy Admin - ajax_save_shop: DB operation returned false. Operation ID (0 for new): ' . ($id > 0 && ($is_platform ? $this->db->get_platform($id) : $this->db->get_shop($id)) ? $id : 'new') . '. DB Error: ' . $wpdb->last_error . ' Data: ' . print_r($data, true));
            wp_send_json_error(array('message' => __('保存失败: 数据库操作错误。', 'agent-buy')));
        } elseif ($id > 0 && $result === 0) { // Update operation, 0 rows affected (no change or error)
            if ($wpdb->last_error) { 
                error_log('Agent Buy Admin - ajax_save_shop: Update affected 0 rows with DB error. ID: ' . $id . '. DB Error: ' . $wpdb->last_error);
                wp_send_json_error(array('message' => __('保存失败: 更新时发生数据库错误。', 'agent-buy')));
            } else { 
                error_log('Agent Buy Admin - ajax_save_shop: Update affected 0 rows (data unchanged). ID: ' . $id);
                $item_id_to_fetch = $id;
                $item_type = $is_platform ? 'platform' : 'shop';
                $item_data = $is_platform ? $this->db->get_platform($item_id_to_fetch) : $this->db->get_shop($item_id_to_fetch);

                wp_send_json_success(array(
                    'message' => __('保存成功 (数据未更改)', 'agent-buy'),
                    'item_id' => $item_id_to_fetch,
                    'item_type' => $item_type,
                    'item_data' => $item_data
                ));
            }
        } else { // Success (add operation returned insert_id, or update operation affected >0 rows)
            $item_id_to_fetch = $id; // $id is now correctly the ID of the item (either existing or newly created)
            
            if ($item_id_to_fetch === false || $item_id_to_fetch === 0) {
                 error_log('Agent Buy Admin - ajax_save_shop: DB operation failed to return/set a valid ID. Result: ' . print_r($result, true) . ' ID: ' . $id);
                 wp_send_json_error(array('message' => __('保存失败: 无法获取项目ID。', 'agent-buy')));
                 return;
            }

            $item_type = $is_platform ? 'platform' : 'shop';
            $item_data = $is_platform ? $this->db->get_platform($item_id_to_fetch) : $this->db->get_shop($item_id_to_fetch);
            
            if (!$item_data) {
                error_log('Agent Buy Admin - ajax_save_shop: Successfully saved but failed to retrieve item. ID: ' . $item_id_to_fetch . ' Type: ' . $item_type);
                // If it was an add operation and $item_data is null, it means get_platform/get_shop failed after add.
                // This could be a serious issue, but the add itself might have succeeded in DB.
                wp_send_json_error(array('message' => __('保存操作可能成功，但无法检索更新后的项目数据。请刷新页面查看。', 'agent-buy')));
                return;
            }

            wp_send_json_success(array(
                'message' => __('保存成功', 'agent-buy'),
                'item_id' => $item_id_to_fetch,
                'item_type' => $item_type,
                'item_data' => $item_data
            ));
        }
    }

    /**
     * AJAX删除店铺或平台
     */
    public function ajax_delete_shop() {
        check_ajax_referer('agent_buy_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('权限不足', 'agent-buy')));
        }
        
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $type = isset($_POST['type']) ? sanitize_text_field($_POST['type']) : '';
        
        if ($id <= 0) {
            // 添加更详细的日志记录
            error_log('Agent Buy Admin - ajax_delete_shop: 无效的ID. POST数据: ' . print_r($_POST, true));
            wp_send_json_error(array('message' => __('无效的ID', 'agent-buy')));
        }
        
        if ($type === 'shop') {
            $result = $this->db->delete_shop($id);
            if ($result === false) {
                wp_send_json_error(array('message' => __('无法删除店铺，请先删除该店铺下的所有代购平台。', 'agent-buy')));
            }
        } elseif ($type === 'platform') {
            $result = $this->db->delete_platform($id);
        } else {
            wp_send_json_error(array('message' => __('无效的类型', 'agent-buy')));
        }
        
        if ($result) {
            wp_send_json_success(array('message' => __('删除成功', 'agent-buy')));
        } else {
            // 添加更详细的错误日志
            error_log('Agent Buy Admin - ajax_delete_shop: 删除失败. ID: ' . $id . ', 类型: ' . $type);
            wp_send_json_error(array('message' => __('删除失败', 'agent-buy')));
        }
    }

    /**
     * 保存快速编辑数据
     */
    public function save_quick_edit() {
        // 验证nonce
        check_ajax_referer('agent_buy_nonce', 'nonce');
        
        $id = intval($_POST['id']);
        $type = sanitize_text_field($_POST['type']);
        
        if (!$id || !in_array($type, array('shop', 'platform'))) {
            wp_send_json_error(array('message' => '无效的请求'));
        }
        
        // 构建数据数组
        $data = array(
            'name' => sanitize_text_field($_POST['name']),
            'description' => sanitize_textarea_field($_POST['description']),
            'status' => intval($_POST['status']),
            'image_id' => intval($_POST['image_id'])
        );
        
        // 添加特定类型的数据
        if ($type === 'platform') {
            $data['shop_id'] = intval($_POST['shop_id']);
            $data['url_pattern'] = esc_url_raw($_POST['url_pattern']);
        }
        
        // 更新数据
        if ($type === 'shop') {
            $result = $this->db->update_shop($id, $data);
        } else {
            $result = $this->db->update_platform($id, $data);
        }
        
        if ($result) {
            // 获取更新后的数据
            $updated_data = $type === 'shop' ? $this->db->get_shop($id) : $this->db->get_platform($id);
            wp_send_json_success(array('message' => '更新成功', 'data' => $updated_data));
        } else {
            wp_send_json_error(array('message' => '更新失败'));
        }
    }

    /**
     * 获取代购平台数据
     */
    public function get_platform_data() {
        // 验证nonce
        check_ajax_referer('agent_buy_nonce', 'nonce');
        
        $id = intval($_GET['id']);
        
        if (!$id) {
            error_log('Agent Buy Admin - get_platform_data: 无效的ID. GET数据: ' . print_r($_GET, true));
            wp_send_json_error(array('message' => '无效的请求'));
        }
        
        $data = $this->db->get_platform($id);
        
        if ($data) {
            // 确保image_url是正确的URL
            if ($data['image_id']) {
                $image_url = wp_get_attachment_url($data['image_id']);
                if ($image_url) {
                    $data['image_url'] = $image_url;
                } else {
                    $data['image_url'] = admin_url('images/placeholder.png');
                }
            } else {
                $data['image_url'] = admin_url('images/placeholder.png');
            }
            
            error_log('Agent Buy Admin - get_platform_data: 返回数据 ' . print_r($data, true));
            
            wp_send_json_success(array('data' => $data));
        } else {
            error_log('Agent Buy Admin - get_platform_data: 数据不存在. ID: ' . $id);
            wp_send_json_error(array('message' => '数据不存在'));
        }
    }

    /**
     * 获取店铺数据
     */
    public function get_shop_data() {
        // 验证nonce
        check_ajax_referer('agent_buy_nonce', 'nonce');
        
        $id = intval($_GET['id']);
        
        if (!$id) {
            error_log('Agent Buy Admin - get_shop_data: 无效的ID. GET数据: ' . print_r($_GET, true));
            wp_send_json_error(array('message' => '无效的请求'));
        }
        
        $data = $this->db->get_shop($id);
        
        if ($data) {
            // 确保image_url是正确的URL
            if ($data['image_id']) {
                $image_url = wp_get_attachment_url($data['image_id']);
                if ($image_url) {
                    $data['image_url'] = $image_url;
                } else {
                    $data['image_url'] = admin_url('images/placeholder.png');
                }
            } else {
                $data['image_url'] = admin_url('images/placeholder.png');
            }
            
            error_log('Agent Buy Admin - get_shop_data: 返回数据 ' . print_r($data, true));
            
            wp_send_json_success(array('data' => $data));
        } else {
            error_log('Agent Buy Admin - get_shop_data: 数据不存在. ID: ' . $id);
            wp_send_json_error(array('message' => '数据不存在'));
        }
    }
    
    /**
     * AJAX更新排序
     */
    public function ajax_update_shop_order() {
        check_ajax_referer('agent_buy_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('权限不足', 'agent-buy')));
        }
        
        $type = isset($_POST['type']) ? sanitize_text_field($_POST['type']) : '';
        $items = isset($_POST['items']) ? $_POST['items'] : array();
        
        if (empty($type) || empty($items) || !is_array($items)) {
            wp_send_json_error(array('message' => __('无效的参数', 'agent-buy')));
        }
        
        $success = true;
        
        foreach ($items as $order => $id) {
            $id = intval($id);
            if ($id > 0) {
                $result = $this->db->update_order($type, $id, $order);
                if (!$result) {
                    $success = false;
                }
            }
        }
        
        if ($success) {
            wp_send_json_success(array('message' => __('排序更新成功', 'agent-buy')));
        } else {
            wp_send_json_error(array('message' => __('排序更新失败', 'agent-buy')));
        }
    }
}
