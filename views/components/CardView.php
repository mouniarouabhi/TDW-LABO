<?php
class CardView {
    public static function render(array $card = [])
    {
        $img = $card['image'] ?? null;
        $linkText = $card['link_text'] ?? 'Lire plus';
        $meta = $card['meta'] ?? [];

        ?>
        <article class="card">
            <?php if ($img): ?>
                <div class="card-cover" style="background-image:url('<?= htmlspecialchars($img) ?>')"></div>
            <?php endif; ?>

            <div class="card-body">
                <?php if (!empty($card['badge'])): ?>
                    <span class="badge <?= htmlspecialchars($card['badge_class'] ?? '') ?>">
                        <?= htmlspecialchars($card['badge']) ?>
                    </span>
                <?php endif; ?>

                <?php if (!empty($card['title'])): ?>
                    <h3><?= htmlspecialchars($card['title']) ?></h3>
                <?php endif; ?>

                <?php if (!empty($card['text'])): ?>
                    <p>
                        <?= htmlspecialchars(mb_strlen($card['text']) > 30 
                            ? mb_substr($card['text'], 0, 30) . '...' 
                            : $card['text']) ?>
                    </p>
                <?php endif; ?>

                <?php if (!empty($meta)): ?>
                    <div class="card-meta">
                        <?php foreach ($meta as $item): ?>
                            <span>
                                <?php if (!empty($item['icon'])): ?>
                                    <i class="<?= htmlspecialchars($item['icon']) ?>"></i>
                                <?php endif; ?>
                                <?= htmlspecialchars($item['label']) ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($card['link'])): ?>
                    <a class="link" href="<?= htmlspecialchars($card['link']) ?>" 
                       <?= !empty($card['external']) ? 'target="_blank" rel="noopener"' : '' ?>>
                        <?= htmlspecialchars($linkText) ?>
                    </a>
                <?php endif; ?>
            </div>
        </article>
        <?php
    }
}
?>