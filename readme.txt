=== Alox Code+ ===
Contributors: aloxco
Author: Alox & Co
Author URI: https://codex.alox.co
Plugin Name: Alox Code+
Plugin URI: https://codex.alox.co/plugins/alox-code-plus/
Requires at least: 5.8
Tested up to: 6.6
Requires PHP: 7.4
Stable tag: 1.0.0
License: MIT
Text Domain: alox-code-plus

Lightweight code UI for dev blogs. Adds Prism highlighting, language badges, and one-click copy to Gutenberg code blocks.

== Description ==
- Auto-detects code language from class="language-xxx"
- Adds data-lang for badge display
- Copy button with Clipboard API and fallback
- Defer-loaded Prism core and selected languages

== Filters ==
- alox_code_plus_languages -> array of Prism component slugs
- alox_code_plus_prism_theme_url -> Prism theme CSS URL
- alox_code_plus_prism_cdn -> Base CDN URL for Prism assets

== Changelog ==
= 1.0.0 =
Initial release.