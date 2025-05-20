<?php
/**
 * 插件的公共部分
 */

// 如果直接访问此文件，则中止执行
if (!defined('ABSPATH')) {
    exit;
}

class Agent_Buy_Public {

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
    }

    /**
     * 注册样式表
     */
    public function enqueue_styles() {
        wp_enqueue_style($this->plugin_name, AGENT_BUY_PLUGIN_URL . 'public/css/agent-buy-public.css', array(), $this->version, 'all');
    }

    /**
     * 注册JavaScript
     */
    public function enqueue_scripts() {
        wp_enqueue_script($this->plugin_name, AGENT_BUY_PLUGIN_URL . 'public/js/agent-buy-public.js', array('jquery'), $this->version, false);
    }

    /**
     * 显示代购链接
     */
    public function display_agent_buy_links() {
        global $product;
        
        if (!$product) {
            return;
        }
        
        $product_id = $product->get_id();
        $links = $this->db->get_product_agent_links($product_id);
        
        if (!empty($links)) {
            include AGENT_BUY_PLUGIN_DIR . 'public/partials/agent-buy-public-display.php';
        }
    }
}
