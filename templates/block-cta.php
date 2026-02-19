<section class="block-cta"
    style="background-color: var(--color-primary); color: white; text-align: center; <?php echo !empty($content['blockBgColor']) ? 'background-color: ' . htmlspecialchars($content['blockBgColor']) . ' !important;' : ''; ?> <?php echo !empty($content['blockTextColor']) ? 'color: ' . htmlspecialchars($content['blockTextColor']) . ' !important;' : ''; ?>">
    <div class="container">
        <?php if (!empty($content['heading'])): ?>
            <h2
                style="color: white; margin-bottom: 20px; <?php echo !empty($content['blockTextColor']) ? 'color: inherit !important;' : ''; ?>">
                <?php echo htmlspecialchars($content['heading']); ?></h2>
        <?php endif; ?>

        <?php if (!empty($content['text'])): ?>
            <p
                style="color: #cbd5e1; font-size: 1.2rem; max-width: 700px; margin: 0 auto 30px; <?php echo !empty($content['blockTextColor']) ? 'color: inherit !important; opacity: 0.9;' : ''; ?>">
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
            <a href="<?php echo htmlspecialchars($link); ?>" class="btn"
                style="background: white; color: var(--color-primary) !important;">
                <?php echo htmlspecialchars($content['buttonText']); ?>
            </a>
        <?php endif; ?>
    </div>
</section>