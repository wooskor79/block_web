<section class="block-hero"
    style="<?php echo !empty($content['blockBgColor']) ? 'background-color: ' . htmlspecialchars($content['blockBgColor']) . ' !important;' : ''; ?> <?php echo !empty($content['blockTextColor']) ? 'color: ' . htmlspecialchars($content['blockTextColor']) . ' !important;' : ''; ?>">
    <div class="container">
        <?php if (!empty($content['title'])): ?>
            <h1 style="<?php echo !empty($content['blockTextColor']) ? 'color: inherit !important;' : ''; ?>">
                <?php echo htmlspecialchars($content['title']); ?></h1>
        <?php endif; ?>

        <?php if (!empty($content['subtitle'])): ?>
            <p style="<?php echo !empty($content['blockTextColor']) ? 'color: inherit !important; opacity: 0.9;' : ''; ?>">
                <?php echo htmlspecialchars($content['subtitle']); ?></p>
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
</section>