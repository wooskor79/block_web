<section class="block-cards"
    style="<?php echo !empty($content['blockBgColor']) ? 'background-color: ' . htmlspecialchars($content['blockBgColor']) . ' !important;' : ''; ?> <?php echo !empty($content['blockTextColor']) ? 'color: ' . htmlspecialchars($content['blockTextColor']) . ' !important;' : ''; ?>">
    <div class="container">
        <div
            class="cards-grid <?php echo in_array($content['columns'] ?? '', ['2', '3', '4']) ? 'grid-' . $content['columns'] : ''; ?>">
            <?php if (!empty($content['cards']) && is_array($content['cards'])): ?>
                <?php foreach ($content['cards'] as $card): ?>
                    <div class="card">
                        <?php if (!empty($card['title'])): ?>
                            <h3>
                                <?php echo htmlspecialchars($card['title']); ?>
                            </h3>
                        <?php endif; ?>

                        <?php if (!empty($card['text'])): ?>
                            <p>
                                <?php echo htmlspecialchars($card['text']); ?>
                            </p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>