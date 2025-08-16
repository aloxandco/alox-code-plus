<?php
/*
Plugin Name: Alox Code+
Plugin URI: https://codex.alox.co/wordpress-plugins/alox-code-plus/
Description: A free, zero-config WordPress plugin that beautifies Gutenberg Code blocks with Prism.js. code, syntax highlighting, prism, wordpress, plugin, gutenberg, copy to clipboard, developer tools
Version: 1.0.0
Author: Alox & Co
Author URI: https://alox.co
License: GPL-2.0+
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: alox-code-plus

Copyright (C) 2025 Alox & Co (https://alox.co)
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2 or later.
This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

final class Alox_Code_Plus {
    const VERSION = '1.0.0';
    private static $instance = null;

    public static function instance() {
        return self::$instance ?: self::$instance = new self();
    }

    private function __construct() {
        add_action( 'plugins_loaded', [ $this, 'load_textdomain' ] );
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_assets' ] );
        add_filter( 'the_content', [ $this, 'ensure_language_class' ], 20 );
        add_filter( 'the_content', [ $this, 'add_data_lang_from_class' ], 21 );
        add_filter( 'script_loader_tag', [ $this, 'add_defer' ], 10, 3 );
    }

    /**
     * Load plugin translations.
     */
    public function load_textdomain() {
        load_plugin_textdomain( 'alox-code-plus', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
    }

    /**
     * Enqueue PrismJS and plugin UI assets.
     */
    public function enqueue_assets() {
        if ( ! is_singular() ) {
            return;
        }

        $prism_base = apply_filters( 'alox_code_plus_prism_cdn', 'https://cdn.jsdelivr.net/npm/prismjs@1.29.0' );
        $theme_url  = apply_filters( 'alox_code_plus_prism_theme_url', $prism_base . '/themes/prism-tomorrow.min.css' );

        // Prism theme + core
        wp_enqueue_style( 'alox-prism', $theme_url, [], null );
        wp_enqueue_script( 'alox-prism-core', $prism_base . '/prism.min.js', [], null, true );

        // Dependencies map
        $depmap = [
            'markup'            => [ 'alox-prism-core' ],
            'clike'             => [ 'alox-prism-core' ],
            'markup-templating' => [ 'alox-prism-core', 'alox-prism-markup' ],
            'php'               => [ 'alox-prism-core', 'alox-prism-clike', 'alox-prism-markup-templating' ],
            'javascript'        => [ 'alox-prism-core', 'alox-prism-clike' ],
            'css'               => [ 'alox-prism-core' ],
            'bash'              => [ 'alox-prism-core' ],
            'json'              => [ 'alox-prism-core' ],
            'sql'               => [ 'alox-prism-core' ],
        ];

        // Default language list (can be filtered)
        $langs = apply_filters( 'alox_code_plus_languages', [
            'markup', 'clike', 'markup-templating',
            'php', 'javascript', 'css', 'bash', 'json', 'sql'
        ] );

        foreach ( $langs as $lang ) {
            $handle = "alox-prism-$lang";
            $deps   = isset( $depmap[ $lang ] ) ? $depmap[ $lang ] : [ 'alox-prism-core' ];
            wp_enqueue_script(
                $handle,
                "$prism_base/components/prism-{$lang}.min.js",
                $deps,
                null,
                true
            );
        }

        // Plugin UI (badge + copy)
        $base = plugin_dir_url( __FILE__ );
        wp_enqueue_style( 'alox-code-ui', $base . 'assets/css/code-ui.css', [], self::VERSION );
        wp_enqueue_script( 'alox-code-ui', $base . 'assets/js/code-ui.js', [ 'alox-prism-core' ], self::VERSION, true );
    }

    /**
     * If <code> lacks a language class, add "language-markup".
     */
    public function ensure_language_class( $content ) {
        $pattern = '#<pre\s+class="([^"]*wp-block-code[^"]*)"(.*?)>\s*<code(.*?)>(.*?)</code>\s*</pre>#is';

        return preg_replace_callback( $pattern, function ( $m ) {
            $preClass  = $m[1];
            $preAttrs  = $m[2];
            $codeAttrs = $m[3];
            $codeInner = $m[4];

            // If <code> already has a language- class, return as-is
            if ( strpos( $codeAttrs, 'language-' ) !== false ) {
                return "<pre class=\"{$preClass}\"{$preAttrs}><code{$codeAttrs}>{$codeInner}</code></pre>";
            }

            // Try to detect language from <pre>
            $lang = 'markup';
            if ( preg_match( '/language-([\w+-]+)/i', $preClass, $match ) ) {
                $lang = $match[1];
            }

            // Append class to <code>
            if ( preg_match( '/class="([^"]*)"/i', $codeAttrs ) ) {
                $codeAttrs = preg_replace( '/class="([^"]*)"/i', 'class="$1 language-' . esc_attr( $lang ) . '"', $codeAttrs );
            } else {
                $codeAttrs = rtrim( $codeAttrs ) . ' class="language-' . esc_attr( $lang ) . '"';
            }

            return "<pre class=\"{$preClass}\"{$preAttrs}><code{$codeAttrs}>{$codeInner}</code></pre>";
        }, $content );
    }

    /**
     * Add data-lang="xxx" to <pre> based on <code class="language-xxx">.
     */
    public function add_data_lang_from_class( $content ) {
        $pattern = '#<pre\s+class="([^"]*wp-block-code[^"]*)"(.*?)>\s*<code([^>]*)>#is';
        return preg_replace_callback( $pattern, function ( $m ) {
            if ( stripos( $m[2], 'data-lang=' ) !== false ) {
                return $m[0];
            }

            $lang = 'text';
            if ( preg_match( '/language-([\w+-]+)/i', $m[3], $lm ) ) {
                $lang = $lm[1];
            }

            return '<pre class="' . $m[1] . '"' . $m[2] . ' data-lang="' . esc_attr( $lang ) . '"><code' . $m[3] . '>';
        }, $content );
    }

    /**
     * Add defer to Prism and plugin scripts.
     */
    public function add_defer( $tag, $handle, $src ) {
        if ( strpos( $handle, 'alox-prism-' ) === 0 || in_array( $handle, [ 'alox-prism-core', 'alox-code-ui' ], true ) ) {
            if ( strpos( $tag, 'defer' ) === false ) {
                $tag = str_replace( '<script ', '<script defer ', $tag );
            }
        }
        return $tag;
    }
}

Alox_Code_Plus::instance();

/**
 * Developer API Example:
 *
 * // Add or change language components
 * add_filter('alox_code_plus_languages', function($langs) {
 *     return ['php','javascript','css','bash','json','sql','yaml'];
 * });
 *
 * // Customize Prism theme
 * add_filter('alox_code_plus_prism_theme_url', function($url) {
 *     return 'https://cdn.jsdelivr.net/npm/prismjs@1.29.0/themes/prism-okaidia.min.css';
 * });
 */
