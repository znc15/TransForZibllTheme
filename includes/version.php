<?php
if (!defined('ABSPATH')) {
    exit;
}

// 获取GitHub最新版本号
function get_github_latest_version() {
    $cache_key = 'mlt_github_version';
    $cached_version = get_transient($cache_key);
    
    if (false !== $cached_version) {
        return $cached_version;
    }
    
    $response = wp_remote_get('https://api.github.com/repos/znc15/TransForZibllTheme/releases/latest');
    
    if (is_wp_error($response)) {
        return '1.2.1';
    }
    
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body);
    
    if (empty($data->tag_name)) {
        return '1.2.1';
    }
    
    $version = ltrim($data->tag_name, 'v');
    
    // 缓存版本号12小时
    set_transient($cache_key, $version, 12 * HOUR_IN_SECONDS);

    
    return $version;
}

// 获取translate.js的最新版本号
function get_translate_js_latest_version() {
    $cache_key = 'translate_js_version';
    $cached_version = get_transient($cache_key);
    
    if (false !== $cached_version) {
        return $cached_version;
    }
    
    $response = wp_remote_get('https://api.github.com/repos/xnx3/translate/releases/latest');
    
    if (is_wp_error($response)) {
        return '3.12.0'; // 如果请求失败，返回当前使用的版本
    }
    
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body);
    
    if (empty($data->tag_name)) {
        return '3.12.0';
    }
    
    $version = ltrim($data->tag_name, 'v');
    
    // 缓存版本号12小时
    set_transient($cache_key, $version, 12 * HOUR_IN_SECONDS);
    
    return $version;
}

// 获取当前插件版本
function get_mlt_current_version() {
    return '1.2.1';
} 