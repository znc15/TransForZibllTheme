<?php
/*
Plugin Name: 子比自动汉化插件
Plugin URI: https://www.LittleSheep.cc
Description: 一个适用于子比主题的简单的多语言翻译插件
Version: 1.2.0
Author: LittleSheep
Author URI: https://www.LittleSheep.cc
License: GPLV2
*/

// 防止直接访问
if (!defined('ABSPATH')) {
    exit;
}

// 包含更新检查文件
require_once plugin_dir_path(__FILE__) . 'includes/updates.php';

// 包含版本检查文件
require_once plugin_dir_path(__FILE__) . 'includes/version.php';

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
        'mlt_js_section',
        '插件设置',
        'mlt_js_section_callback',
        'multi-language-translate'
    );

    add_settings_field(
        'js_cdn', 
        '翻译JS路径', 
        'mlt_text_field_callback',
        'multi-language-translate',
        'mlt_js_section',
        array(
            'label_for' => 'js_cdn',
            'description' => '设置翻译JS的路径，默认使用本地文件：/wp-content/plugins/Trans/assets/js/translate.min.js'
        )
    );

    add_settings_field(
        'default_language', 
        '默认语言代码', 
        'mlt_text_field_callback',
        'multi-language-translate',
        'mlt_js_section',
        array(
            'label_for' => 'default_language',
            'description' => '设置网站的默认语言代码，例如：chinese_simplified、english、japanese、korean、vietnamese'
        )
    );

    add_settings_field(
        'main_icon',
        '主图标设置',
        'mlt_main_icon_callback',
        'multi-language-translate',
        'mlt_js_section',
        array(
            'label_for' => 'main_icon',
            'description' => '设置语言切换按钮的主图标'
        )
    );

    add_settings_section(
        'mlt_custom_languages_section',
        '自定义语言',
        'mlt_custom_languages_section_callback',
        'multi-language-translate'
    );

    add_settings_field(
        'custom_languages',
        '自定义语言列表',
        'mlt_custom_languages_callback',
        'multi-language-translate',
        'mlt_custom_languages_section'
    );

    add_settings_section(
        'mlt_updates_section',
        '更新检查',
        'mlt_updates_section_callback',
        'multi-language-translate'
    );

    add_settings_section(
        'mlt_about_section',
        '关于',
        'mlt_about_section_callback',
        'multi-language-translate'
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

// 修改JS CDN设置字段回调函数
function mlt_text_field_callback($args) {
    $options = get_option('mlt_settings');
    $value = isset($options[$args['label_for']]) ? $options[$args['label_for']] : '';
    
    if ($args['label_for'] === 'js_cdn') {
        ?>
        <div class="js-cdn-field">
            <input type="text" 
                   id="<?php echo esc_attr($args['label_for']); ?>"
                   name="mlt_settings[<?php echo esc_attr($args['label_for']); ?>]"
                   value="<?php echo esc_attr($value); ?>"
                   class="regular-text js-url-input"
            >
            <button type="button" class="button upload-js-button">上传JS文件</button>
            <?php if (isset($args['description'])): ?>
                <p class="description"><?php echo esc_html($args['description']); ?></p>
            <?php endif; ?>
        </div>

        <script>
        jQuery(document).ready(function($) {
            $('.upload-js-button').click(function(e) {
                e.preventDefault();
                var button = $(this);
                var urlInput = button.siblings('.js-url-input');

                var frame = wp.media({
                    title: '选择或上传JS文件',
                    button: {
                        text: '使用此文件'
                    },
                    multiple: false,
                    library: {
                        type: 'application/javascript'
                    }
                });

                frame.on('select', function() {
                    var attachment = frame.state().get('selection').first().toJSON();
                    urlInput.val(attachment.url).trigger('change');
                });

                frame.open();
            });
        });
        </script>

        <style>
        .js-cdn-field {
            margin-bottom: 15px;
        }
        .js-cdn-field input[type="text"] {
            margin-bottom: 10px;
        }
        </style>
        <?php
    } else {
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

// 添加前端和后台样式
function mlt_enqueue_styles() {
    // 字体样式
    wp_enqueue_style(
        'mlt-font-styles',
        plugins_url('/assets/css/font.css', __FILE__),
        array(),
        '1.2.0'
    );
    
    // 前端样式
    wp_enqueue_style(
        'mlt-styles',
        plugins_url('/assets/css/admin.css', __FILE__),
        array('mlt-font-styles'),
        '1.2.0'
    );
}
add_action('wp_enqueue_scripts', 'mlt_enqueue_styles');

// 后台样式
function mlt_admin_styles() {
    if (isset($_GET['page']) && $_GET['page'] == 'multi-language-translate') {
        // 加载Dashicons
        wp_enqueue_style('dashicons');
        
        // 字体样式
        wp_enqueue_style(
            'mlt-font-styles',
            plugins_url('/assets/css/font.css', __FILE__),
            array('dashicons'),
            '1.2.0'
        );
        
        // 后台样式
        wp_enqueue_style(
            'mlt-styles',
            plugins_url('/assets/css/admin.css', __FILE__),
            array('mlt-font-styles', 'dashicons'),
            '1.2.0'
        );

        // 更新检查样式
        wp_enqueue_style(
            'mlt-updates-styles',
            plugins_url('/assets/css/updates.css', __FILE__),
            array('dashicons'),
            '1.2.0'
        );
    }
}
add_action('admin_enqueue_scripts', 'mlt_admin_styles');

// 修改添加JavaScript的函数
function mlt_add_custom_js() {
    $options = get_option('mlt_settings');
    $js_cdn = isset($options['js_cdn']) ? $options['js_cdn'] : 'https://cdn.staticfile.net/translate.js/3.12.0/translate.js';
    $default_language = isset($options['default_language']) ? $options['default_language'] : 'chinese_simplified';
    ?>
    <script>
    // 等待DOM加载完成
    document.addEventListener('DOMContentLoaded', function() {
        // 创建一个标志变量，用于跟踪JS是否已加载
        window.translationJsLoaded = false;
        
        // 创建加载翻译JS的函数
        function loadTranslationJs() {
            if (window.translationJsLoaded) return;
            
            var script = document.createElement('script');
            script.src = '<?php echo esc_url($js_cdn); ?>';
            script.onload = function() {
                window.translationJsLoaded = true;
                translate.setAutoDiscriminateLocalLanguage();
                translate.language.setLocal('<?php echo esc_js($default_language); ?>');
                translate.service.use('client.edge');
                executeTranslation();
            };
            document.head.appendChild(script);
        }
        
        // 监听翻译按钮的悬停和点击事件
        document.addEventListener('mouseover', function(e) {
            if (e.target.closest('.toggle-radius') || e.target.closest('.btn-newadd')) {
                loadTranslationJs();
            }
        });
        
        // 添加点击事件监听
        document.addEventListener('click', function(e) {
            if (e.target.closest('.toggle-radius') || e.target.closest('.btn-newadd')) {
                loadTranslationJs();
            }
        });
        
        // 执行翻译的函数
        function executeTranslation() {
            if (window.translate) {
                translate.execute();
            }
        }
        
        // AJAX完成后重新执行翻译
        if (window.jQuery) {
            jQuery(document).ajaxComplete(function() {
                if (window.translationJsLoaded) {
                    executeTranslation();
                }
            });
        }
    });
    </script>
    <?php
}
add_action('wp_head', 'mlt_add_custom_js');

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
    
    // 添加自定义语言选项
    if (isset($options['custom_languages']) && is_array($options['custom_languages'])) {
        foreach ($options['custom_languages'] as $language) {
            if (!empty($language['name']) && !empty($language['code']) && !empty($language['icon'])) {
                $new_button .= '<a rel="nofollow" class="btn-newadd" href="javascript:translate.changeLanguage(\'' . esc_js($language['code']) . '\');" rel="external nofollow">
                    <icon class="c-purple">
                        <object data="' . esc_url($language['icon']) . '" type="image/svg+xml" class="icon" width="20" height="20"></object>
                    </icon>
                    <text class="ignore">' . esc_html($language['name']) . '</text>
                </a>';
            }
        }
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
        'js_cdn' => $site_url . '/wp-content/plugins/' . $plugin_dir . '/assets/js/translate.min.js',
        'default_language' => 'chinese_simplified', // 默认语言代码
        'main_icon' => $site_url . '/wp-content/plugins/' . $plugin_dir . '/assets/svg/translate.svg',
        'custom_languages' => array(
            array(
                'name' => '简体中文',
                'code' => 'chinese_simplified',
                'icon' => $site_url . '/wp-content/plugins/' . $plugin_dir . '/assets/svg/chinese.svg'
            ),
            array(
                'name' => 'English',
                'code' => 'english',
                'icon' => $site_url . '/wp-content/plugins/' . $plugin_dir . '/assets/svg/english.svg'
            ),
            array(
                'name' => '日本語',
                'code' => 'japanese',
                'icon' => $site_url . '/wp-content/plugins/' . $plugin_dir . '/assets/svg/japanese.svg'
            )
        )
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
    delete_option('mlt_settings');
}
register_deactivation_hook(__FILE__, 'mlt_deactivate');

// 自定义语言设置说明回调
function mlt_custom_languages_section_callback() {
    echo '<p>在这里添加自定义语言及其对应的SVG图标</p>';
}

// 自定义语言字段回调函数
function mlt_custom_languages_callback() {
    $options = get_option('mlt_settings');
    $custom_languages = isset($options['custom_languages']) ? $options['custom_languages'] : array();
    wp_enqueue_media(); // 加载媒体上传相关脚本
    ?>
    <div id="custom-languages-container">
        <?php foreach ($custom_languages as $index => $language): ?>
        <div class="custom-language-item">
            <div class="language-field">
                <label>自定义语言:</label>
                <input type="text" 
                       class="regular-text"
                       name="mlt_settings[custom_languages][<?php echo $index; ?>][name]" 
                       value="<?php echo esc_attr($language['name']); ?>" 
                       placeholder="例如: 简体中文、English、日本語、한국어、Việt Nam"
                >
            </div>
            <div class="language-field">
                <label>语言代码:</label>
                <input type="text" 
                       class="regular-text"
                       name="mlt_settings[custom_languages][<?php echo $index; ?>][code]" 
                       value="<?php echo esc_attr($language['code']); ?>" 
                       placeholder="例如: chinese_simplified、english、japanese、korean、vietnamese"
                >
                <p class="description">请输入translate.js支持的语言代码</p>
            </div>
            <div class="language-field svg-upload-field">
                <label>SVG图标:</label>
                <div class="svg-input-group">
                    <input type="text" 
                           class="regular-text svg-url-input"
                           name="mlt_settings[custom_languages][<?php echo $index; ?>][icon]" 
                           value="<?php echo esc_attr($language['icon']); ?>" 
                           placeholder="SVG图标URL"
                    >
                    <button type="button" class="button upload-svg-button">选择SVG</button>
                    <div class="svg-preview">
                        <?php if (!empty($language['icon'])): ?>
                        <img src="<?php echo esc_url($language['icon']); ?>" alt="SVG预览">
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <button type="button" class="button button-link-delete remove-language">删除此语言</button>
        </div>
        <?php endforeach; ?>
    </div>
    <button type="button" class="button button-primary" id="add-custom-language">添加新语言</button>

    <script>
    jQuery(document).ready(function($) {
        var container = $('#custom-languages-container');
        var index = <?php echo count($custom_languages); ?>;

        // 添加新语言的模板
        function getTemplate(index) {
            return `
                <div class="custom-language-item">
                    <div class="language-field">
                        <label>自定义语言:</label>
                        <input type="text" 
                               class="regular-text"
                               name="mlt_settings[custom_languages][${index}][name]" 
                               placeholder="例如: 简体中文、English、日本語、한국어、Việt Nam">
                    </div>
                    <div class="language-field">
                        <label>语言代码:</label>
                        <input type="text" 
                               class="regular-text"
                               name="mlt_settings[custom_languages][${index}][code]" 
                               placeholder="例如: chinese_simplified、english、japanese、korean、vietnamese">
                        <p class="description">请输入translate.js支持的语言代码</p>
                    </div>
                    <div class="language-field svg-upload-field">
                        <label>SVG图标:</label>
                        <div class="svg-input-group">
                            <input type="text" 
                                   class="regular-text svg-url-input"
                                   name="mlt_settings[custom_languages][${index}][icon]" 
                                   placeholder="SVG图标URL">
                            <button type="button" class="button upload-svg-button">选择SVG</button>
                            <div class="svg-preview"></div>
                        </div>
                    </div>
                    <button type="button" class="button button-link-delete remove-language">删除此语言</button>
                </div>
            `;
        }

        // 添加新语言
        $('#add-custom-language').click(function() {
            container.append(getTemplate(index));
            index++;
        });

        // 删除语言
        $(document).on('click', '.remove-language', function() {
            $(this).closest('.custom-language-item').slideUp(300, function() {
                $(this).remove();
            });
        });

        // SVG上传功能
        $(document).on('click', '.upload-svg-button', function(e) {
            e.preventDefault();
            var button = $(this);
            var urlInput = button.siblings('.svg-url-input');
            var previewDiv = button.siblings('.svg-preview');

            var frame = wp.media({
                title: '选择或上传SVG图标',
                button: {
                    text: '使用此图标'
                },
                multiple: false
            });

            frame.on('select', function() {
                var attachment = frame.state().get('selection').first().toJSON();
                urlInput.val(attachment.url);
                previewDiv.html('<img src="' + attachment.url + '" alt="SVG预览">');
            });

            frame.open();
        });
    });
    </script>

    <?php
}

// 添加主图标设置回调函数
function mlt_main_icon_callback($args) {
    $options = get_option('mlt_settings');

    $site_url = get_site_url();
    $default_icon = $site_url . '/wp-content/plugins/Trans/assets/svg/main.svg';

    $main_icon = isset($options[$args['label_for']]) && !empty($options[$args['label_for']]) 
        ? $options[$args['label_for']] 
        : $default_icon;
    
    wp_enqueue_media();
    ?>
    <div class="main-icon-field">
        <div class="svg-input-group">
            <input type="text" 
                   id="<?php echo esc_attr($args['label_for']); ?>"
                   class="regular-text svg-url-input"
                   name="mlt_settings[<?php echo esc_attr($args['label_for']); ?>]"
                   value="<?php echo esc_attr($main_icon); ?>"
                   placeholder="<?php echo esc_attr($default_icon); ?>"
            >
            <button type="button" class="button upload-svg-button">选择SVG</button>
            <div class="svg-preview">
                <?php if (!empty($main_icon)): ?>
                <img src="<?php echo esc_url($main_icon); ?>" alt="SVG预览">
                <?php endif; ?>
            </div>
        </div>
        <?php if (isset($args['description'])): ?>
            <p class="description"><?php echo esc_html($args['description']); ?></p>
        <?php endif; ?>
        <p class="description">默认图标路径：<?php echo esc_html($default_icon); ?></p>
    </div>

    <script>
    jQuery(document).ready(function($) {
        // 主图标上传功能
        $('.main-icon-field .upload-svg-button').click(function(e) {
            e.preventDefault();
            var button = $(this);
            var urlInput = button.siblings('.svg-url-input');
            var previewDiv = button.siblings('.svg-preview');

            var frame = wp.media({
                title: '选择或上传SVG主图标',
                button: {
                    text: '使用此图标'
                },
                multiple: false
            });

            frame.on('select', function() {
                var attachment = frame.state().get('selection').first().toJSON();
                urlInput.val(attachment.url);
                previewDiv.html('<img src="' + attachment.url + '" alt="SVG预览">');
            });

            frame.open();
        });
    });
    </script>

    <?php
}

// 修改关于部分的回调函数，移除更新检查相关内容
function mlt_about_section_callback() {
    ?>
    <div class="about-section">
        <p><strong>作者：</strong> <a href="https://www.LittleSheep.cc" target="_blank">LittleSheep</a></p>
        <p><strong>引用：</strong> 本插件使用了 <a href="https://github.com/xnx3/translate" target="_blank">translate.js</a> 提供的翻译功能</p>
        <p><strong>基于：</strong> https://www.zibll.com/forum-post/29011.html 修改而成</p>
    </div>

    <?php
}

// 添加媒体上传脚本
function mlt_admin_enqueue_scripts($hook) {
    if ('settings_page_multi-language-translate' === $hook) {
        wp_enqueue_media();
    }
}
add_action('admin_enqueue_scripts', 'mlt_admin_enqueue_scripts');