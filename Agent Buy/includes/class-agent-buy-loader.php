<?php
/**
 * 注册所有动作和过滤器的加载器
 */

// 如果直接访问此文件，则中止执行
if (!defined('ABSPATH')) {
    exit;
}

class Agent_Buy_Loader {

    /**
     * 要注册的动作数组
     */
    protected $actions;

    /**
     * 要注册的过滤器数组
     */
    protected $filters;

    /**
     * 初始化集合
     */
    public function __construct() {
        $this->actions = array();
        $this->filters = array();
    }

    /**
     * 将新动作添加到要注册的动作集合中
     */
    public function add_action($hook, $component, $callback, $priority = 10, $accepted_args = 1) {
        $this->actions = $this->add($this->actions, $hook, $component, $callback, $priority, $accepted_args);
    }

    /**
     * 将新过滤器添加到要注册的过滤器集合中
     */
    public function add_filter($hook, $component, $callback, $priority = 10, $accepted_args = 1) {
        $this->filters = $this->add($this->filters, $hook, $component, $callback, $priority, $accepted_args);
    }

    /**
     * 用于将动作和过滤器添加到集合的实用函数
     */
    private function add($hooks, $hook, $component, $callback, $priority, $accepted_args) {
        $hooks[] = array(
            'hook'          => $hook,
            'component'     => $component,
            'callback'      => $callback,
            'priority'      => $priority,
            'accepted_args' => $accepted_args
        );

        return $hooks;
    }

    /**
     * 注册插件中的所有过滤器和动作
     */
    public function run() {
        foreach ($this->filters as $hook) {
            add_filter($hook['hook'], array($hook['component'], $hook['callback']), $hook['priority'], $hook['accepted_args']);
        }

        foreach ($this->actions as $hook) {
            add_action($hook['hook'], array($hook['component'], $hook['callback']), $hook['priority'], $hook['accepted_args']);
        }
    }
}
