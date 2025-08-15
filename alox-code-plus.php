<?php
/**
 * Plugin Name: Alox Code+
 * Plugin URI: https://codex.alox.co/plugins/alox-code-plus/
 * Description: Lightweight code UI for dev blogs. Adds Prism highlighting, language badges, and one-click copy to Gutenberg code blocks.
 * Version: 1.0.0
 * Author: Alox & Co
 * Author URI: https://codex.alox.co
 * License: MIT
 * Text Domain: alox-code-plus
 */

if (!defined('ABSPATH')) exit;

final class Alox_Code_Plus {
    const VERSION = '1.0.0';
    private static $instance = null;

    public static function instance() {
        return self::$instance ?: self::$instance = new self();
    }

    private function __construct() {
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
        add_filter('the_content',       [$this, 'ensure_language_class'], 20);
        add_filter('the_content',       [$this, 'add_data_lang_from_class'], 21);
        add_filter('script_loader_tag', [$this, 'add_defer'], 10, 3);
    }

    /**
     * Enqueue Prism and UI assets only where needed.
     */
    public function enqueue_assets() {
        if (!is_singular()) return;

        $prism_base = apply_filters('alox_code_plus_prism_cdn', 'https://cdn.jsdelivr.net/npm/prismjs@1.29.0');
        $theme_url  = apply_filters('alox_code_plus_prism_theme_url', $prism_base . '/themes/prism-tomorrow.min.css');

        // Prism theme + core
        wp_enqueue_style('alox-prism', $theme_url, [], null);
        wp_enqueue_script('alox-prism-core', $prism_base . '/prism.min.js', [], null, true);

        // Component dependency map
        $depmap = [
            'markup'             => ['alox-prism-core'],
            'clike'              => ['alox-prism-core'],
            'markup-templating'  => ['alox-prism-core', 'alox-prism-markup'],

            // Common languages
            'php'        => ['alox-prism-core', 'alox-prism-clike', 'alox-prism-markup-templating'],
            'javascript' => ['alox-prism-core', 'alox-prism-clike'],
            'css'        => ['alox-prism-core'],
            'bash'       => ['alox-prism-core'],
            'json'       => ['alox-prism-core'],
            'sql'        => ['alox-prism-core'],
        ];

        // Default set. You can still filter this.
        $langs = apply_filters('alox_code_plus_languages', [
            'markup', 'clike', 'markup-templating', // keep these three first
            'php', 'javascript', 'css', 'bash', 'json', 'sql'
        ]);

        // Enqueue components with correct deps
        foreach ($langs as $lang) {
            $handle = "alox-prism-$lang";
            $deps   = isset($depmap[$lang]) ? $depmap[$lang] : ['alox-prism-core'];
            wp_enqueue_script(
                $handle,
                $prism_base . "/components/prism-{$lang}.min.js",
                $deps,
                null,
                true
            );
        }

        // UI layer
        $base = plugin_dir_url(__FILE__);
        wp_enqueue_style('alox-code-ui', $base . 'assets/css/code-ui.css', [], self::VERSION);
        wp_enqueue_script('alox-code-ui', $base . 'assets/js/code-ui.js', array_keys(array_flip($langs)) ? ['alox-prism-core'] : ['alox-prism-core'], self::VERSION, true);
    }

    /**
     * If editors forget to add class="language-xxx", default to "language-markup".
     * Only touches Gutenberg <pre class="wp-block-code"><code> blocks.
     */
    public function ensure_language_class($content) {
        $pattern = '#<pre\s+class="([^"]*wp-block-code[^"]*)"(.*?)>\s*<code(.*?)>(.*?)</code>\s*</pre>#is';
        $content = preg_replace_callback($pattern, function ($m) {
            $preClass = $m[1];
            $preAttrs = $m[2];
            $codeAttrs = $m[3];

            // If code tag lacks a language class, append language-markup.
            if (strpos($codeAttrs, 'language-') === false) {
                // Add class attribute or append to it.
                if (preg_match('/class="([^"]*)"/i', $codeAttrs, $cm)) {
                    $new = preg_replace(
                        '/class="([^"]*)"/i',
                        'class="$1 language-markup"',
                        $codeAttrs
                    );
                    $codeAttrs = $new;
                } else {
                    $codeAttrs = rtrim($codeAttrs) . ' class="language-markup"';
                }
            }
            // Return reconstructed block.
            return '<pre class="' . $preClass . '"' . $preAttrs . '><code' . $codeAttrs . '>' . $m[4] . '</code></pre>';
        }, $content);

        return $content;
    }

    /**
     * Add data-lang attribute to <pre> based on the detected language class
     * so the badge can display a friendly label. Honors any existing data-lang.
     */
    public function add_data_lang_from_class($content) {
        $pattern = '#<pre\s+class="([^"]*wp-block-code[^"]*)"(.*?)>\s*<code([^>]*)>#is';
        $content = preg_replace_callback($pattern, function ($m) {
            $preClass = $m[1];
            $preAttrs = $m[2];
            $codeAttrs = $m[3];

            // Skip if data-lang already present.
            if (stripos($preAttrs, 'data-lang=') !== false) return $m[0];

            // Extract language from code tag class
            $lang = 'text';
            if (preg_match('/language-([\w+-]+)/i', $codeAttrs, $lm)) {
                $lang = $lm[1];
            }

            // Inject data-lang into the <pre> open tag.
            $preOpen = '<pre class="' . $preClass . '"' . $preAttrs . ' data-lang="' . esc_attr($lang) . '">';
            return $preOpen . '<code' . $codeAttrs . '>';
        }, $content);

        return $content;
    }

    /**
     * Defer our scripts to avoid blocking render.
     */
    public function add_defer($tag, $handle, $src) {
        $targets = ['alox-prism-core', 'alox-code-ui'];
        if (in_array($handle, $targets, true) || strpos($handle, 'alox-prism-') === 0) {
            if (strpos($tag, 'defer') === false) {
                $tag = str_replace('<script ', '<script defer ', $tag);
            }
        }
        return $tag;
    }
}

Alox_Code_Plus::instance();

/**
 * Developer API:
 *
 * add_filter('alox_code_plus_languages', function($langs) {
 *     return ['php','javascript','css','bash','json','sql','yaml'];
 * });
 *
 * add_filter('alox_code_plus_prism_theme_url', function($url) {
 *     return 'https://cdn.jsdelivr.net/npm/prismjs@1.29.0/themes/prism-okaidia.min.css';
 * });
 */
