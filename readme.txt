=== Alox Code+ ===
Contributors: aloxandco
Tags: code, syntax highlighting, prism, gutenberg, copy to clipboard, developer tools
Requires at least: 5.8
Tested up to: 6.6
Requires PHP: 7.4
Stable tag: 1.0.0
License: MIT
License URI: https://opensource.org/licenses/MIT
Plugin URI: https://codex.alox.co/plugins/alox-code-plus/
Author URI: https://alox.co
Text Domain: alox-code-plus

Lightweight code UI for dev blogs. Automatically adds Prism.js syntax highlighting, copy buttons, and language labels to Gutenberg code blocks—zero configuration needed.

== Description ==

**Alox Code+** is a minimal, fast, and developer-friendly enhancement for Gutenberg code blocks. It adds syntax highlighting using Prism.js, a visible language badge, and a one-click copy-to-clipboard button — all with zero configuration required.

**Features:**

- 🚀 **Automatic Language Detection**
  Detects `language-xxx` classes and loads only required Prism components.

- 🎨 **Beautiful, Theme-Aware UI**
  Includes copy button + language badge styled for light and dark modes.

- 🧠 **Smart Defaults, No Bloat**
  Ships with common languages (PHP, JS, CSS, SQL, Bash, JSON) and supports filters for customization.

- ✅ **Clipboard Support with Fallback**
  Uses the modern Clipboard API with graceful fallback for older browsers.

- ⚙️ **Extensible for Developers**
  Includes filters to override Prism CDN, theme, and language list.

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/alox-code-plus/` directory, or install via the WordPress Plugin Directory.
2. Activate the plugin through the 'Plugins' screen.
3. Add a Gutenberg **Code** block, and add a class like `language-php`, `language-js`, etc.
4. The plugin auto-detects the language and adds highlighting, a badge, and a copy button.

== Frequently Asked Questions ==

= Can I change the Prism theme? =
Yes! Use the `alox_code_plus_prism_theme_url` filter to override the CSS file.

= How do I add support for more languages? =
Use the `alox_code_plus_languages` filter to specify additional Prism components.

= Does it work with other block editors? =
Currently optimized for Gutenberg's `<pre class="wp-block-code">` blocks.

== Filters ==

```php
// Change loaded languages
add_filter('alox_code_plus_languages', function($langs) {
    return ['php', 'javascript', 'yaml', 'json'];
});

// Customize the Prism theme
add_filter('alox_code_plus_prism_theme_url', function($url) {
    return 'https://cdn.jsdelivr.net/npm/prismjs@1.29.0/themes/prism-okaidia.min.css';
});

// Override Prism CDN base
add_filter('alox_code_plus_prism_cdn', function($cdn) {
    return 'https://cdn.customhost.com/prismjs';
});```

== Upgrade Notice ==

= 1.0.0 =
Initial release with Prism highlighting, copy button, and language detection.

== Changelog ==

= 1.0.0 =

Initial release

Language detection from language-xxx classes

Auto-load Prism components

Copy-to-clipboard with fallback

Developer filters for Prism customization
