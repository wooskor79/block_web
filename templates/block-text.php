<section class="block-text"
    style="<?php echo !empty($content['blockBgColor']) ? 'background-color: ' . htmlspecialchars($content['blockBgColor']) . ' !important;' : ''; ?> <?php echo !empty($content['blockTextColor']) ? 'color: ' . htmlspecialchars($content['blockTextColor']) . ' !important;' : ''; ?>">
    <div class="container">
        <?php if (!empty($content['heading'])): ?>
            <h2 style="<?php echo !empty($content['blockTextColor']) ? 'color: inherit !important;' : ''; ?>">
                <?php echo htmlspecialchars($content['heading']); ?></h2>
        <?php endif; ?>

        <?php if (!empty($content['text'])): ?>
            <div class="text-content"
                style="<?php echo !empty($content['blockTextColor']) ? 'color: inherit !important;' : ''; ?>">
                <?php echo nl2br(htmlspecialchars($content['text'])); ?>
            </div>
        <?php endif; ?>
    </div>
</section>