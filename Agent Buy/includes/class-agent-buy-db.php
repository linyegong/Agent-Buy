<?php
/**
 * 数据库操作类
 */

// 如果直接访问此文件，则中止执行
if (!defined('ABSPATH')) {
    exit;
}

class Agent_Buy_DB {
    /**
     * 店铺表名
     */
    private $shops_table;
    
    /**
     * 代购平台表名
     */
    private $platforms_table;
    
    /**
     * 构造函数
     */
    public function __construct() {
        global $wpdb;
        $this->shops_table = $wpdb->prefix . 'agent_buy_shops';
        $this->platforms_table = $wpdb->prefix . 'agent_buy_platforms';
        
        // 添加这行用于调试的代码
        error_log('Agent Buy DB 初始化 - 店铺表: ' . $this->shops_table . ', 平台表: ' . $this->platforms_table);
    }
    
    /**
     * 获取所有店铺
     */
    public function get_shops() {
        global $wpdb;
        
        $query = "SELECT * FROM {$this->shops_table} ORDER BY menu_order ASC";
        $results = $wpdb->get_results($query, ARRAY_A);
        
        return $results;
    }
    
    /**
     * 获取单个店铺
     */
    public function get_shop($id) {
        global $wpdb;
        
        $query = $wpdb->prepare("SELECT * FROM {$this->shops_table} WHERE id = %d", $id);
        $result = $wpdb->get_row($query, ARRAY_A);
        
        return $result;
    }
    
    /**
     * 添加店铺
     */
    public function add_shop($data) {
        global $wpdb;
        
        // 检查表是否存在
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$this->shops_table}'");
        if (!$table_exists) {
            // 表不存在，尝试重新创建
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            require_once(AGENT_BUY_PLUGIN_DIR . 'includes/class-agent-buy-activator.php');
            Agent_Buy_Activator::activate();
            
            // 再次检查表是否创建成功
            $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$this->shops_table}'");
            if (!$table_exists) {
                error_log('Agent Buy Plugin: 无法创建店铺表');
                return false;
            }
        }
        
        $result = $wpdb->insert(
            $this->shops_table,
            array(
                'name' => $data['name'],
                'description' => isset($data['description']) ? $data['description'] : '',
                'image_id' => isset($data['image_id']) ? $data['image_id'] : 0,
                'status' => isset($data['status']) ? $data['status'] : 1,
                'menu_order' => isset($data['menu_order']) ? $data['menu_order'] : 0
            ),
            array('%s', '%s', '%d', '%d', '%d')
        );
        
        // 记录错误信息
        if ($result === false) {
            error_log('Agent Buy Plugin: 添加店铺失败 - ' . $wpdb->last_error);
            return false;
        }
        
        return $wpdb->insert_id;
    }
    
    /**
     * 更新店铺
     */
    public function update_shop($id, $data) {
        global $wpdb;
        
        error_log('Agent Buy DB - update_shop: 开始更新店铺 ID=' . $id . ', 数据: ' . print_r($data, true));
        
        $update_data = array();
        $update_format = array();
        
        if (isset($data['name'])) {
            $update_data['name'] = $data['name'];
            $update_format[] = '%s';
        }
        
        if (isset($data['description'])) {
            $update_data['description'] = $data['description'];
            $update_format[] = '%s';
        }
        
        if (isset($data['image_id'])) {
            $update_data['image_id'] = $data['image_id'];
            $update_format[] = '%d';
        }
        
        if (isset($data['status'])) {
            $update_data['status'] = $data['status'];
            $update_format[] = '%d';
        }
        
        if (isset($data['menu_order'])) {
            $update_data['menu_order'] = $data['menu_order'];
            $update_format[] = '%d';
        }
        
        // 确保有数据要更新
        if (empty($update_data)) {
            error_log('Agent Buy DB - update_shop: 没有可更新的数据');
            return false;
        }
        
        // 检查表是否存在
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$this->shops_table}'");
        if (!$table_exists) {
            // 表不存在，尝试重新创建
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            require_once(AGENT_BUY_PLUGIN_DIR . 'includes/class-agent-buy-activator.php');
            Agent_Buy_Activator::activate();
        }
        
        $result = $wpdb->update(
            $this->shops_table,
            $update_data,
            array('id' => $id),
            $update_format,
            array('%d')
        );
        
        // 记录错误信息
        if ($result === false) {
            error_log('Agent Buy Plugin: 更新店铺失败 - ' . $wpdb->last_error);
        } else {
            error_log('Agent Buy DB - update_shop: 更新结果 - 影响行数: ' . $result);
        }
        
        return $result;
    }
    
    /**
     * 删除店铺
     */
    public function delete_shop($id) {
        global $wpdb;

        // 检查店铺下是否有代购平台
        $platforms = $this->get_platforms($id);
        if (!empty($platforms)) {
            return false; // 如果存在平台，则不删除店铺，返回false
        }
        
        // 如果没有关联平台，则删除店铺
        return $wpdb->delete(
            $this->shops_table,
            array('id' => $id),
            array('%d')
        );
    }
    
    /**
     * 获取所有代购平台
     */
    public function get_platforms($shop_id = null, $status = null) {
        global $wpdb;
        
        $sql = "SELECT p.*, s.name as shop_name 
                FROM {$this->platforms_table} p 
                LEFT JOIN {$this->shops_table} s ON p.shop_id = s.id";
        
        $conditions = array();
        $params = array();

        if ($shop_id !== null) {
            $conditions[] = "p.shop_id = %d";
            $params[] = $shop_id;
        }

        if ($status !== null) {
            $conditions[] = "p.status = %d";
            $params[] = $status;
        }
        
        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }
        
        $sql .= " ORDER BY p.menu_order ASC";
        
        if (!empty($params)) {
            $query = $wpdb->prepare($sql, ...$params);
        } else {
            $query = $sql;
        }
        
        $results = $wpdb->get_results($query, ARRAY_A);
        
        return $results;
    }
    
    /**
     * 获取单个代购平台
     */
    public function get_platform($id) {
        global $wpdb;
        
        $query = $wpdb->prepare(
            "SELECT p.*, s.name as shop_name 
             FROM {$this->platforms_table} p 
             LEFT JOIN {$this->shops_table} s ON p.shop_id = s.id 
             WHERE p.id = %d",
            $id
        );
        
        $result = $wpdb->get_row($query, ARRAY_A);
        
        return $result;
    }
    
    /**
     * 添加代购平台
     */
    public function add_platform($data) {
        global $wpdb;
        
        $wpdb->insert(
            $this->platforms_table,
            array(
                'shop_id' => $data['shop_id'],
                'name' => $data['name'],
                'url_pattern' => $data['url_pattern'],
                'description' => isset($data['description']) ? $data['description'] : '',
                'image_id' => isset($data['image_id']) ? $data['image_id'] : 0,
                'status' => isset($data['status']) ? $data['status'] : 1,
                'menu_order' => isset($data['menu_order']) ? $data['menu_order'] : 0
            ),
            array('%d', '%s', '%s', '%s', '%d', '%d', '%d')
        );
        
        return $wpdb->insert_id;
    }
    
    /**
     * 更新代购平台
     */
    public function update_platform($id, $data) {
        global $wpdb;
        
        $update_data = array();
        $update_format = array();
        
        if (isset($data['shop_id'])) {
            $update_data['shop_id'] = $data['shop_id'];
            $update_format[] = '%d';
        }
        
        if (isset($data['name'])) {
            $update_data['name'] = $data['name'];
            $update_format[] = '%s';
        }
        
        if (isset($data['url_pattern'])) {
            $update_data['url_pattern'] = $data['url_pattern'];
            $update_format[] = '%s';
        }
        
        if (isset($data['description'])) {
            $update_data['description'] = $data['description'];
            $update_format[] = '%s';
        }
        
        if (isset($data['image_id'])) {
            $update_data['image_id'] = $data['image_id'];
            $update_format[] = '%d';
        }
        
        if (isset($data['status'])) {
            $update_data['status'] = $data['status'];
            $update_format[] = '%d';
        }
        
        if (isset($data['menu_order'])) {
            $update_data['menu_order'] = $data['menu_order'];
            $update_format[] = '%d';
        }
        
        return $wpdb->update(
            $this->platforms_table,
            $update_data,
            array('id' => $id),
            $update_format,
            array('%d')
        );
    }
    
    /**
     * 删除代购平台
     */
    public function delete_platform($id) {
        global $wpdb;
        
        return $wpdb->delete(
            $this->platforms_table,
            array('id' => $id),
            array('%d')
        );
    }
    
    /**
     * 获取产品的代购链接
     */
    public function get_product_agent_links($product_id) {
        global $wpdb;
        
        $links = array();
        $shops = $this->get_shops();
        
        foreach ($shops as $shop) {
            $shop_id = $shop['id'];
            $shop_name = $shop['name'];
            
            // 获取产品的店铺ID
            $product_shop_id = get_post_meta($product_id, '_agent_buy_' . $shop_id, true);
            
            if (!empty($product_shop_id)) {
                // 获取该店铺下的所有代购平台 (只获取启用的平台)
                $platforms = $this->get_platforms($shop_id, 1); // Pass status 1 to get only active platforms
                
                foreach ($platforms as $platform) {
                    // 状态已在 get_platforms 中过滤，无需再次检查 $platform['status'] == 1
                    $url = str_replace('{0}', $product_shop_id, $platform['url_pattern']);
                    
                    $links[] = array(
                        'shop_name' => $shop_name,
                        'platform_name' => $platform['name'],
                        'url' => $url,
                        'image_id' => $platform['image_id']
                    );
                }
            }
        }
        
        return $links;
    }
    
    /**
     * 更新排序
     */
    public function update_order($table, $id, $order) {
        global $wpdb;
        
        $table_name = $table === 'shops' ? $this->shops_table : $this->platforms_table;
        
        return $wpdb->update(
            $table_name,
            array('menu_order' => $order),
            array('id' => $id),
            array('%d'),
            array('%d')
        );
    }
}
