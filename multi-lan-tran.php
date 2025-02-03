<?php
/*
Plugin Name: 子比自动汉化插件
Plugin URI: https://www.LittleSheep.cc
Description: 一个适用于子比主题的简单的多语言翻译插件,支持简体中文、英语、日语、韩语、越南语
Version: 1.0.0
Author: LittleSheep
Author URI: https://www.LittleSheep.cc
License: GPL2
*/

// 防止直接访问
if (!defined('ABSPATH')) {
    exit;
}

// 添加设置菜单
function mlt_add_admin_menu() {
    add_options_page(
        '多语言翻译设置', 
        '多语言翻译', 
        'manage_options', 
        'multi-language-translate', 
        'mlt_options_page'
    );
}
add_action('admin_menu', 'mlt_add_admin_menu');

// 注册设置
function mlt_settings_init() {
    register_setting('mlt_options', 'mlt_settings');

    add_settings_section(
        'mlt_section',
        '图标设置',
        'mlt_section_callback',
        'multi-language-translate'
    );

    // 添加语言开关设置部分
    add_settings_section(
        'mlt_language_section',
        '语言设置',
        'mlt_language_section_callback',
        'multi-language-translate'
    );

    // 添加语言开关选项
    add_settings_field(
        'enable_chinese', 
        '启用中文', 
        'mlt_checkbox_field_callback',
        'multi-language-translate',
        'mlt_language_section',
        array('label_for' => 'enable_chinese')
    );

    add_settings_field(
        'enable_english', 
        '启用英语', 
        'mlt_checkbox_field_callback',
        'multi-language-translate',
        'mlt_language_section',
        array('label_for' => 'enable_english')
    );

    add_settings_field(
        'enable_japanese', 
        '启用日语', 
        'mlt_checkbox_field_callback',
        'multi-language-translate',
        'mlt_language_section',
        array('label_for' => 'enable_japanese')
    );

    add_settings_field(
        'enable_korean', 
        '启用韩语', 
        'mlt_checkbox_field_callback',
        'multi-language-translate',
        'mlt_language_section',
        array('label_for' => 'enable_korean')
    );

    add_settings_field(
        'enable_vietnamese', 
        '启用越南语', 
        'mlt_checkbox_field_callback',
        'multi-language-translate',
        'mlt_language_section',
        array('label_for' => 'enable_vietnamese')
    );

    // 添加设置字段
    add_settings_field(
        'main_icon', 
        '主图标SVG路径', 
        'mlt_text_field_callback',
        'multi-language-translate',
        'mlt_section',
        array('label_for' => 'main_icon')
    );

    // 添加中文图标设置
    add_settings_field(
        'chinese_icon', 
        '中文图标SVG路径', 
        'mlt_text_field_callback',
        'multi-language-translate',
        'mlt_section',
        array('label_for' => 'chinese_icon')
    );

    add_settings_field(
        'english_icon', 
        '英语图标SVG路径', 
        'mlt_text_field_callback',
        'multi-language-translate',
        'mlt_section',
        array('label_for' => 'english_icon')
    );

    add_settings_field(
        'japanese_icon', 
        '日语图标SVG路径', 
        'mlt_text_field_callback',
        'multi-language-translate',
        'mlt_section',
        array('label_for' => 'japanese_icon')
    );

    add_settings_field(
        'korean_icon', 
        '韩语图标SVG路径', 
        'mlt_text_field_callback',
        'multi-language-translate',
        'mlt_section',
        array('label_for' => 'korean_icon')
    );

    add_settings_field(
        'vietnamese_icon', 
        '越南语图标SVG路径', 
        'mlt_text_field_callback',
        'multi-language-translate',
        'mlt_section',
        array('label_for' => 'vietnamese_icon')
    );

    // 添加JS设置部分
    add_settings_section(
        'mlt_js_section',
        'JS设置',
        'mlt_js_section_callback',
        'multi-language-translate'
    );

    // 添加JS CDN设置
    add_settings_field(
        'js_cdn', 
        '翻译JS CDN地址', 
        'mlt_text_field_callback',
        'multi-language-translate',
        'mlt_js_section',
        array(
            'label_for' => 'js_cdn',
            'description' => '设置翻译JS的CDN地址，默认使用：https://cdn.staticfile.net/translate.js/3.12.0/translate.js'
        )
    );

    // 添加默认语言设置
    add_settings_field(
        'default_language', 
        '默认语言', 
        'mlt_select_field_callback',
        'multi-language-translate',
        'mlt_js_section',
        array(
            'label_for' => 'default_language',
            'options' => array(
                'chinese_simplified' => '简体中文',
                'english' => '英语',
                'japanese' => '日语',
                'korean' => '韩语',
                'vietnamese' => '越南语'
            ),
            'description' => '设置网站的默认语言'
        )
    );
}
add_action('admin_init', 'mlt_settings_init');

// 设置页面回调函数
function mlt_section_callback() {
    echo '<p>设置各语言的SVG图标文件路径</p>';
}

// 添加语言设置说明
function mlt_language_section_callback() {
    echo '<p>选择要启用的翻译语言</p>';
}

// JS设置说明回调
function mlt_js_section_callback() {
    echo '<p>配置翻译功能的JS相关设置</p>';
}

// 文本字段回调函数
function mlt_text_field_callback($args) {
    $options = get_option('mlt_settings');
    $value = isset($options[$args['label_for']]) ? $options[$args['label_for']] : '';
    ?>
    <input type="text" 
           id="<?php echo esc_attr($args['label_for']); ?>"
           name="mlt_settings[<?php echo esc_attr($args['label_for']); ?>]"
           value="<?php echo esc_attr($value); ?>"
           class="regular-text"
    >
    <?php
    if (isset($args['description'])) {
        echo '<p class="description">' . esc_html($args['description']) . '</p>';
    }
}

// 复选框回调函数
function mlt_checkbox_field_callback($args) {
    $options = get_option('mlt_settings');
    $checked = isset($options[$args['label_for']]) ? $options[$args['label_for']] : 0;
    ?>
    <input type="checkbox" 
           id="<?php echo esc_attr($args['label_for']); ?>"
           name="mlt_settings[<?php echo esc_attr($args['label_for']); ?>]"
           value="1"
           <?php checked(1, $checked); ?>
    >
    <?php
}

// 添加下拉选择框回调函数
function mlt_select_field_callback($args) {
    $options = get_option('mlt_settings');
    $value = isset($options[$args['label_for']]) ? $options[$args['label_for']] : 'chinese_simplified';
    ?>
    <select 
        id="<?php echo esc_attr($args['label_for']); ?>"
        name="mlt_settings[<?php echo esc_attr($args['label_for']); ?>]">
        <?php foreach ($args['options'] as $key => $label) : ?>
            <option value="<?php echo esc_attr($key); ?>" <?php selected($value, $key); ?>>
                <?php echo esc_html($label); ?>
            </option>
        <?php endforeach; ?>
    </select>
    <?php
    if (isset($args['description'])) {
        echo '<p class="description">' . esc_html($args['description']) . '</p>';
    }
}

// 设置页面
function mlt_options_page() {
    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        <form action="options.php" method="post">
            <?php
            settings_fields('mlt_options');
            do_settings_sections('multi-language-translate');
            submit_button('保存设置');
            ?>
        </form>
    </div>
    <?php
}

// 添加CSS到头部
function mlt_add_custom_css() {
    ?>
    <style>
        .ignore:hover{color:var(--theme-color);transition:color .2s,transform .3s;}
        #translate {display:none;}
    </style>
    <?php
}
add_action('wp_head', 'mlt_add_custom_css');

// 添加JavaScript到头部
function mlt_add_custom_js() {
    $options = get_option('mlt_settings');
    $js_cdn = isset($options['js_cdn']) ? $options['js_cdn'] : 'https://cdn.staticfile.net/translate.js/3.12.0/translate.js';
    $default_language = isset($options['default_language']) ? $options['default_language'] : 'chinese_simplified';
    ?>
    <script src="<?php echo esc_url($js_cdn); ?>"></script>
    <script>
        translate.setAutoDiscriminateLocalLanguage(); 
        translate.language.setLocal('<?php echo esc_js($default_language); ?>'); 
        translate.service.use('client.edge'); 
        function executeTranslation() {
            translate.execute();
        }
        executeTranslation();
        jQuery(document).ajaxComplete(function() {
            executeTranslation();
        });
    </script>
    <?php
}
add_action('wp_head', 'mlt_add_custom_js');

// 添加后台样式
function mlt_admin_styles() {
    if (isset($_GET['page']) && $_GET['page'] == 'multi-language-translate') {
        ?>
        <style>
            .wrap { max-width: 900px; margin: 20px auto; }
            .form-table { background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
            .form-table th { padding: 20px; }
            .form-table td { padding: 15px 20px; }
            h2 { color: #23282d; font-size: 1.5em; margin: 1.5em 0 1em; padding-bottom: 10px; border-bottom: 2px solid #eee; }
            .regular-text { width: 100%; max-width: 400px; }
            .description { color: #666; font-style: italic; margin-top: 5px; }
            .submit { margin-top: 20px; }
        </style>
        <?php
    }
}
add_action('admin_head', 'mlt_admin_styles');

// 修改翻译按钮输出函数
function custom_modify_radius_button($original_content, $user_id) {
    $options = get_option('mlt_settings');
    
    $new_button = '<span class="hover-show inline-block ml10">
        <a href="javascript:translate.changeLanguage(\'chinese_simplified\');" rel="external nofollow" class="toggle-radius">
            <object data="' . esc_url($options['main_icon']) . '" type="image/svg+xml" class="icon" width="20" height="20"></object>
        </a>
        <div class="hover-show-con dropdown-menu drop-newadd">';
    
    // 中文选项
    if (isset($options['enable_chinese']) && $options['enable_chinese']) {
        $new_button .= '<a rel="nofollow" class="btn-newadd" href="javascript:translate.changeLanguage(\'chinese_simplified\');" rel="external nofollow">
            <icon class="c-red">
                <object data="' . esc_url($options['chinese_icon']) . '" type="image/svg+xml" class="icon" width="20" height="20"></object>
            </icon>
            <text class="ignore">简体中文</text>
        </a>';
    }
    
    // 英语选项
    if (isset($options['enable_english']) && $options['enable_english']) {
        $new_button .= '<a rel="nofollow" class="btn-newadd" href="javascript:translate.changeLanguage(\'english\');" rel="external nofollow">
            <icon class="c-blue">
                <object data="' . esc_url($options['english_icon']) . '" type="image/svg+xml" class="icon" width="20" height="20"></object>
            </icon>
            <text class="ignore">English</text>
        </a>';
    }
    
    // 日语选项
    if (isset($options['enable_japanese']) && $options['enable_japanese']) {
        $new_button .= '<a rel="nofollow" class="btn-newadd" href="javascript:translate.changeLanguage(\'japanese\');" rel="external nofollow">
            <icon class="c-yellow">
                <object data="' . esc_url($options['japanese_icon']) . '" type="image/svg+xml" class="icon" width="20" height="20"></object>
            </icon>
            <text class="ignore">日本語</text>
        </a>';
    }
    
    // 韩语选项
    if (isset($options['enable_korean']) && $options['enable_korean']) {
        $new_button .= '<a rel="nofollow" class="btn-newadd" href="javascript:translate.changeLanguage(\'korean\');" rel="external nofollow">
            <icon class="c-green">
                <object data="' . esc_url($options['korean_icon']) . '" type="image/svg+xml" class="icon" width="20" height="20"></object>
            </icon>
            <text class="ignore">한국어</text>
        </a>';
    }
    
    // 越南语选项
    if (isset($options['enable_vietnamese']) && $options['enable_vietnamese']) {
        $new_button .= '<a rel="nofollow" class="btn-newadd" href="javascript:translate.changeLanguage(\'vietnamese\');" rel="external nofollow">
            <icon class="c-purple">
                <object data="' . esc_url($options['vietnamese_icon']) . '" type="image/svg+xml" class="icon" width="20" height="20"></object>
            </icon>
            <text class="ignore">Việt Nam</text>
        </a>';
    }
    
    $new_button .= '</div></span>';
    
    return $original_content . $new_button;
}
add_filter('zib_nav_radius_button', 'custom_modify_radius_button', 10, 2);

// 修改激活函数，添加新的默认值
function mlt_activate() {
    $existing_settings = get_option('mlt_settings');
    
    $site_url = get_site_url();
    $plugin_dir = 'Trans';
    
    $default_settings = array(
        'main_icon' => $site_url . '/wp-content/plugins/' . $plugin_dir . '/assets/svg/main.svg',
        'chinese_icon' => $site_url . '/wp-content/plugins/' . $plugin_dir . '/assets/svg/chinese.svg',
        'english_icon' => $site_url . '/wp-content/plugins/' . $plugin_dir . '/assets/svg/english.svg',
        'japanese_icon' => $site_url . '/wp-content/plugins/' . $plugin_dir . '/assets/svg/japanese.svg',
        'korean_icon' => $site_url . '/wp-content/plugins/' . $plugin_dir . '/assets/svg/korean.svg',
        'vietnamese_icon' => $site_url . '/wp-content/plugins/' . $plugin_dir . '/assets/svg/vietnamese.svg',
        // 默认启用所有语言
        'enable_chinese' => 1,
        'enable_english' => 1,
        'enable_japanese' => 1,
        'enable_korean' => 1,
        'enable_vietnamese' => 1,
        'js_cdn' => 'https://cdn.staticfile.net/translate.js/3.12.0/translate.js',
        'default_language' => 'chinese_simplified'
    );

    if ($existing_settings === false) {
        add_option('mlt_settings', $default_settings);
    } else {
        $merged_settings = wp_parse_args($existing_settings, $default_settings);
        update_option('mlt_settings', $merged_settings);
    }
}
register_activation_hook(__FILE__, 'mlt_activate');

// 停用插件时的操作
function mlt_deactivate() {
    // 可以选择是否删除设置
    // delete_option('mlt_settings');
}
register_deactivation_hook(__FILE__, 'mlt_deactivate');