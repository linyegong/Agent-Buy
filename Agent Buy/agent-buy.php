<?php
/**
 * Plugin Name: Agent Buy
 * Plugin URI: #
 * Description: 代购管理插件，支持店铺和代购平台管理，在产品页面展示代购链接。
 * Version: 1.0.0
 * Author: Trae AI
 * Text Domain: agent-buy
 */

// 如果直接访问此文件，则中止执行
if (!defined('ABSPATH')) {
    exit;
}

// 启用错误报告（仅用于调试）
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

define('AGENT_BUY_VERSION', '1.0.1'); // 更新版本号以强制刷新CSS缓存
define('AGENT_BUY_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('AGENT_BUY_PLUGIN_URL', plugin_dir_url(__FILE__));

// 包含必要的文件
require_once AGENT_BUY_PLUGIN_DIR . 'includes/class-agent-buy.php';
require_once AGENT_BUY_PLUGIN_DIR . 'includes/class-agent-buy-activator.php';

// 激活插件时的钩子
register_activation_hook(__FILE__, array('Agent_Buy_Activator', 'activate'));

// 初始化插件
function run_agent_buy() {
    try {
        $plugin = new Agent_Buy();
        $plugin->run();
    } catch (Exception $e) {
        error_log('Agent Buy Plugin Error: ' . $e->getMessage());
        error_log('Stack trace: ' . $e->getTraceAsString());
    }
}

// 运行插件
run_agent_buy();