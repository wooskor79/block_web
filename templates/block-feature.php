<section class="block-feature"
    style="<?php echo !empty($content['blockBgColor']) ? 'background-color: ' . htmlspecialchars($content['blockBgColor']) . ' !important;' : ''; ?> <?php echo !empty($content['blockTextColor']) ? 'color: ' . htmlspecialchars($content['blockTextColor']) . ' !important;' : ''; ?>">
    <div class="container">
        <div class="feature-wrapper"
            style="display: flex; gap: 40px; align-items: center; <?php echo ($content['imagePosition'] ?? '') === 'right' ? 'flex-direction: row-reverse;' : ''; ?>">
            <div class="feature-image" style="flex: 1;">
                <?php if (!empty($content['imageUrl'])): ?>
                    <img src="<?php echo htmlspecialchars($content['imageUrl']); ?>"
                        alt="<?php echo htmlspecialchars($content['imageAlt'] ?? ''); ?>"
                        style="width: 100%; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
                <?php endif; ?>
            </div>
            <div class="feature-content" style="flex: 1;">
                <?php if (!empty($content['heading'])): ?>
                    <h2 style="<?php echo !empty($content['blockTextColor']) ? 'color: inherit !important;' : ''; ?>">
                        <?php echo htmlspecialchars($content['heading']); ?></h2>
                <?php endif; ?>

                <?php if (!empty($content['text'])): ?>
                    <p
                        style="font-size: 1.1rem; color: var(--color-text-muted); line-height: 1.7; <?php echo !empty($content['blockTextColor']) ? 'color: inherit !important; opacity: 0.9;' : ''; ?>">
                        <?php echo nl2br(htmlspecialchars($content['text'])); ?>
                    </p>
                <?php endif; ?>

                <?php if (!empty($content['buttonText']) && !empty($content['buttonLink'])): ?>
                    <?php
                    $link = $content['buttonLink'];
                    if (preg_match('/^[a-z0-9-_]+$/', $link) && !str_starts_with($link, 'http')) {
                        $link = '?page=' . $link;
                    }
                    ?>
                    <a href="<?php echo htmlspecialchars($link); ?>" class="btn">
                        <?php echo htmlspecialchars($content['buttonText']); ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- Mobile Responsive Styles for Feature Block -->
<style>
    @media (max-width: 768px) {
        .block-feature .feature-wrapper {
            flex-direction: column !important;
        }
    }
</style>