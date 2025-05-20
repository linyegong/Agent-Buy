<?php
/**
 * 插件激活时执行的功能
 */

// 如果直接访问此文件，则中止执行
if (!defined('ABSPATH')) {
    exit;
}

class Agent_Buy_Activator {

    /**
     * 激活插件时执行的方法
     */
    public static function activate() {
        self::create_tables();
    }

    /**
     * 创建插件所需的数据库表
     */
    private static function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // 店铺表
        $table_shops = $wpdb->prefix . 'agent_buy_shops';
        
        // 代购平台表
        $table_platforms = $wpdb->prefix . 'agent_buy_platforms';
        
        $sql_shops = "CREATE TABLE $table_shops (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            name varchar(100) NOT NULL,
            description text,
            image_id bigint(20),
            status tinyint(1) DEFAULT 1,
            menu_order int(11) DEFAULT 0,
            created_at datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
            PRIMARY KEY  (id)
        ) $charset_collate;";
        
        $sql_platforms = "CREATE TABLE $table_platforms (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            shop_id mediumint(9) NOT NULL,
            name varchar(100) NOT NULL,
            url_pattern text NOT NULL,
            description text,
            image_id bigint(20),
            status tinyint(1) DEFAULT 1,
            menu_order int(11) DEFAULT 0,
            created_at datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
            PRIMARY KEY  (id),
            KEY shop_id (shop_id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        dbDelta($sql_shops);
        dbDelta($sql_platforms);
    }
}
