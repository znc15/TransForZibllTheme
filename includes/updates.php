<?php
if (!defined('ABSPATH')) {
    exit;
}

// 注册更新页面的样式
function mlt_updates_enqueue_styles() {
    if (isset($_GET['page']) && $_GET['page'] == 'multi-language-translate') {
        wp_enqueue_style(
            'mlt-updates-styles', 
            plugins_url('/assets/css/updates.css', dirname(__FILE__)),
            array(),
            '1.2.0'
        );
    }
}
add_action('admin_enqueue_scripts', 'mlt_updates_enqueue_styles');

// 更新检查部分的回调函数
function mlt_updates_section_callback() {
    // 获取当前插件版本
    $current_version = get_mlt_current_version();
    
    ?>
    <div class="updates-section">
        <div class="update-item">
            <h3>插件版本</h3>
            <p><strong>当前版本：</strong><?php echo esc_html($current_version); ?></p>
            <p><strong>最新版本：</strong><span id="plugin-latest-version">检测中...</span></p>
            <div id="plugin-update-status">
                <p class="update-checking">
                    <span class="dashicons dashicons-update"></span>
                    正在检查更新...
                </p>
            </div>
        </div>

        <div class="update-item">
            <h3>翻译核心</h3>
            <p><strong>当前版本：</strong><span id="current-translate-js-version">检测中...</span></p>
            <p><strong>最新版本：</strong><span id="latest-translate-js-version">检测中...</span></p>
            <div id="translate-js-update-status">
                <p class="update-checking">
                    <span class="dashicons dashicons-update"></span>
                    正在检查更新...
                </p>
            </div>
        </div>
    </div>

    <button type="button" id="check-updates-button" class="button button-primary">
        <span class="dashicons dashicons-update"></span>
        检查更新
    </button>

    <script>
    jQuery(document).ready(function($) {
        function checkTranslateJsVersion() {
            var script = document.createElement('script');
            script.src = '<?php echo esc_url(get_option('mlt_settings')['js_cdn'] ?? "/wp-content/plugins/Trans/assets/js/translate.min.js"); ?>';
            
            script.onload = function() {
                if (window.translate && window.translate.version) {
                    var currentVersion = window.translate.version.split('.').slice(0, 3).join('.');
                    $('#current-translate-js-version').text(currentVersion);
                    
                    var latestVersion = $('#latest-translate-js-version').text();
                    if (latestVersion !== '检测中...') {
                        updateVersionStatus(currentVersion, latestVersion);
                    }
                }
            };
            
            script.onerror = function() {
                $('#current-translate-js-version').text('获取失败');
                $('#translate-js-update-status').html(
                    '<p class="update-error"><span class="dashicons dashicons-warning"></span>版本检查失败</p>'
                );
            };
            
            document.head.appendChild(script);
        }

        function updateVersionStatus(currentVersion, latestVersion) {
            var $status = $('#translate-js-update-status');
            
            if (version_compare(latestVersion, currentVersion, '>')) {
                $status.html(`
                    <p class="update-available">
                        <span class="dashicons dashicons-warning"></span>
                        发现新版本！请前往 <a href="https://github.com/xnx3/translate/releases" target="_blank">GitHub</a> 查看更新
                    </p>
                `);
            } else {
                $status.html(`
                    <p class="update-current">
                        <span class="dashicons dashicons-yes-alt"></span>
                        当前已是最新版本
                    </p>
                `);
            }
        }

        function updatePluginStatus(currentVersion, latestVersion) {
            var $status = $('#plugin-update-status');
            
            if (version_compare(latestVersion, currentVersion, '>')) {
                $status.html(`
                    <p class="update-available">
                        <span class="dashicons dashicons-warning"></span>
                        发现新版本！请前往 <a href="https://github.com/znc15/TransForZibllTheme/releases/" target="_blank">GitHub</a> 下载更新
                    </p>
                `);
            } else {
                $status.html(`
                    <p class="update-current">
                        <span class="dashicons dashicons-yes-alt"></span>
                        当前已是最新版本
                    </p>
                `);
            }
        }

        function version_compare(v1, v2, operator) {
            var i = 0;
            var x = 0;
            var compare = 0;
            var vm = {
                'dev': -6,
                'alpha': -5,
                'a': -5,
                'beta': -4,
                'b': -4,
                'RC': -3,
                'rc': -3,
                '#': -2,
                'p': 1,
                'pl': 1
            };

            var _prepVersion = function(v) {
                v = ('' + v).replace(/[_\-+]/g, '.');
                v = v.replace(/([^.\d]+)/g, '.$1.').replace(/\.{2,}/g, '.');
                return (!v.length ? [-8] : v.split('.'));
            };

            var _numVersion = function(v) {
                return !v ? 0 : (isNaN(v) ? vm[v] || -7 : parseInt(v, 10));
            };

            v1 = _prepVersion(v1);
            v2 = _prepVersion(v2);
            x = Math.max(v1.length, v2.length);
            for (i = 0; i < x; i++) {
                if ((compare = _numVersion(v1[i]) - _numVersion(v2[i])) != 0) {
                    return compare;
                }
            }
            return 0;
        }

        function checkUpdates() {
            var $button = $('#check-updates-button');
            $button.prop('disabled', true).addClass('updating');
            
            $('#plugin-latest-version').text('检测中...');
            $('#current-translate-js-version').text('检测中...');
            $('#latest-translate-js-version').text('检测中...');
            
            $('#plugin-update-status, #translate-js-update-status').html(
                '<p class="update-checking"><span class="dashicons dashicons-update"></span>正在检查更新...</p>'
            );
            
            $.get('https://api.github.com/repos/znc15/TransForZibllTheme/releases/latest')
                .done(function(data) {
                    var latestVersion = data.tag_name.replace('v', '');
                    $('#plugin-latest-version').text(latestVersion);
                    updatePluginStatus('<?php echo $current_version; ?>', latestVersion);
                })
                .fail(function() {
                    $('#plugin-latest-version').text('获取失败');
                    $('#plugin-update-status').html(
                        '<p class="update-error"><span class="dashicons dashicons-warning"></span>检查更新失败</p>'
                    );
                });
            
            $.get('https://api.github.com/repos/xnx3/translate/releases/latest')
                .done(function(data) {
                    var latestVersion = data.tag_name.replace('v', '');
                    $('#latest-translate-js-version').text(latestVersion);
                    checkTranslateJsVersion();
                })
                .fail(function() {
                    $('#latest-translate-js-version').text('获取失败');
                    $('#translate-js-update-status').html(
                        '<p class="update-error"><span class="dashicons dashicons-warning"></span>检查更新失败</p>'
                    );
                })
                .always(function() {
                    $button.prop('disabled', false).removeClass('updating');
                });
        }

        window.onload = function() {
            setTimeout(function() {
                checkUpdates();
            }, 1000);
        };

        $('#check-updates-button').click(checkUpdates);
    });
    </script>
    <?php
} 