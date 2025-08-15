// Alox Code+ UI: language badge + copy button for Gutenberg <pre.wp-block-code>
document.addEventListener('DOMContentLoaded', function () {
    const blocks = document.querySelectorAll('pre.wp-block-code');

    blocks.forEach(pre => {
        const code = pre.querySelector('code') || pre;

    // Determine language (from Prism class or data-lang)
    const match = (code.className || '').match(/language-([\w+-]+)/i);
    const langRaw = (match && match[1]) || pre.getAttribute('data-lang') || 'text';
    const lang = String(langRaw).toUpperCase();

    // Wrap for toolbar
    const wrapper = document.createElement('div');
    wrapper.className = 'alox-codeui';
    pre.parentNode.insertBefore(wrapper, pre);
    wrapper.appendChild(pre);

    // Toolbar
    const toolbar = document.createElement('div');
    toolbar.className = 'alox-codeui__toolbar';

    const badge = document.createElement('span');
    badge.className = 'alox-codeui__lang';
    badge.textContent = lang;

    const button = document.createElement('button');
    button.type = 'button';
    button.className = 'alox-codeui__copy';
    button.setAttribute('aria-label', 'Copy code');
    button.textContent = 'Copy';

    button.addEventListener('click', async () => {
        const text = code.textContent;
    try {
        await navigator.clipboard.writeText(text);
        button.textContent = 'Copied';
        button.disabled = true;
        setTimeout(() => {
            button.textContent = 'Copy';
        button.disabled = false;
    }, 1400);
    } catch (e) {
        // Fallback
        const range = document.createRange();
        range.selectNodeContents(code);
        const sel = window.getSelection();
        sel.removeAllRanges();
        sel.addRange(range);
        document.execCommand('copy');
        sel.removeAllRanges();
        button.textContent = 'Copied';
        setTimeout(() => (button.textContent = 'Copy'), 1400);
    }
});

    toolbar.appendChild(badge);
    toolbar.appendChild(button);
    wrapper.insertBefore(toolbar, pre);

    // Improve Prism keyboard focus inside scrollable code
    pre.setAttribute('tabindex', '0');
});
});
