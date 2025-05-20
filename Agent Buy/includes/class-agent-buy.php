<?php
/**
 * 插件的主类文件
 */

// 如果直接访问此文件，则中止执行
if (!defined('ABSPATH')) {
    exit;
}

class Agent_Buy {
    /**
     * 插件加载器
     */
    protected $loader;

    /**
     * 插件名称
     */
    protected $plugin_name;

    /**
     * 插件版本
     */
    protected $version;

    /**
     * 初始化插件名称和版本
     */
    public function __construct() {
        $this->plugin_name = 'agent-buy';
        $this->version = AGENT_BUY_VERSION;
        
        $this->load_dependencies();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }

    /**
     * 加载插件依赖
     */
    private function load_dependencies() {
        // 加载插件加载器
        require_once AGENT_BUY_PLUGIN_DIR . 'includes/class-agent-buy-loader.php';
        
        // 加载管理员类
        require_once AGENT_BUY_PLUGIN_DIR . 'admin/class-agent-buy-admin.php';
        
        // 加载公共类
        require_once AGENT_BUY_PLUGIN_DIR . 'public/class-agent-buy-public.php';
        
        // 加载数据库操作类
        require_once AGENT_BUY_PLUGIN_DIR . 'includes/class-agent-buy-db.php';
        
        $this->loader = new Agent_Buy_Loader();
    }

    /**
     * 注册所有与管理员相关的钩子
     */
    private function define_admin_hooks() {
        $plugin_admin = new Agent_Buy_Admin($this->get_plugin_name(), $this->get_version());
        
        // 添加管理菜单
        $this->loader->add_action('admin_menu', $plugin_admin, 'add_plugin_admin_menu');
        
        // 注册样式和脚本
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
        
        // 添加产品元框
        $this->loader->add_action('add_meta_boxes', $plugin_admin, 'add_product_meta_boxes');
        
        // 保存产品元数据
        $this->loader->add_action('save_post_product', $plugin_admin, 'save_product_meta_data');
        
        // AJAX处理
        $this->loader->add_action('wp_ajax_agent_buy_save_shop', $plugin_admin, 'ajax_save_shop');
        $this->loader->add_action('wp_ajax_agent_buy_delete_shop', $plugin_admin, 'ajax_delete_shop');
        $this->loader->add_action('wp_ajax_agent_buy_update_shop_order', $plugin_admin, 'ajax_update_shop_order');
    }

    /**
     * 注册所有与公共部分相关的钩子
     */
    private function define_public_hooks() {
        $plugin_public = new Agent_Buy_Public($this->get_plugin_name(), $this->get_version());
        
        // 注册样式和脚本
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');
        
        // 在产品简短描述下方显示代购链接
        $this->loader->add_action('woocommerce_after_shop_loop_item', $plugin_public, 'display_agent_buy_links', 15);
        $this->loader->add_action('woocommerce_single_product_summary', $plugin_public, 'display_agent_buy_links', 25);
    }

    /**
     * 运行加载器以执行所有钩子
     */
    public function run() {
        $this->loader->run();
    }

    /**
     * 获取插件名称
     */
    public function get_plugin_name() {
        return $this->plugin_name;
    }

    /**
     * 获取加载器
     */
    public function get_loader() {
        return $this->loader;
    }

    /**
     * 获取插件版本
     */
    public function get_version() {
        return $this->version;
    }
}
