export function initArticleReactions(root = document) {
    root.querySelectorAll('[data-article-reactions]').forEach((container) => {
        if (container.dataset.reactionsBound === 'true') {
            return;
        }

        container.dataset.reactionsBound = 'true';

        const toggleUrl = container.dataset.toggleUrl;
        const csrfToken = container.dataset.csrfToken;
        let emojiGlyphs = {};

        try {
            emojiGlyphs = JSON.parse(container.dataset.emojiGlyphs || '{}');
        } catch (error) {
            emojiGlyphs = {};
        }

        if (!toggleUrl || !csrfToken) {
            return;
        }

        container.querySelectorAll('.article-reactions__button').forEach((button) => {
            button.addEventListener('click', async () => {
                const emoji = button.dataset.emoji;
                if (!emoji) {
                    return;
                }

                button.disabled = true;

                try {
                    const response = await fetch(toggleUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: new URLSearchParams({
                            emoji,
                            _token: csrfToken,
                        }),
                    });

                    if (response.status === 401) {
                        window.location.href = container.dataset.loginUrl || '/login';
                        return;
                    }

                    if (!response.ok) {
                        return;
                    }

                    const payload = await response.json();
                    updateReactionContainer(container, payload, emojiGlyphs);
                } finally {
                    button.disabled = false;
                }
            });
        });
    });
}

function updateReactionContainer(container, payload, emojiGlyphs) {
    const counts = payload.counts || {};
    const userReactions = payload.userReactions || [];
    const countsContainer = container.querySelector('.article-reactions__counts');

    if (!countsContainer) {
        return;
    }

    countsContainer.innerHTML = '';

    Object.keys(counts).forEach((emoji) => {
        const count = counts[emoji];
        if (count <= 0) {
            return;
        }

        const span = document.createElement('span');
        span.className = 'article-reactions__count';
        span.dataset.emojiCount = emoji;

        if (userReactions.includes(emoji)) {
            span.classList.add('is-active');
        }

        const glyph = emojiGlyphs[emoji] || container.querySelector(`[data-emoji="${emoji}"] .article-reactions__icon`)?.textContent?.trim() || '';
        span.textContent = `${glyph} ${count}`;
        countsContainer.appendChild(span);
    });

    container.querySelectorAll('.article-reactions__button').forEach((button) => {
        const emoji = button.dataset.emoji;
        const isActive = userReactions.includes(emoji);
        button.classList.toggle('is-active', isActive);
        button.setAttribute('aria-pressed', isActive ? 'true' : 'false');
    });
}

document.addEventListener('DOMContentLoaded', () => {
    initArticleReactions();
});
