<?php
// views/components/TableView.php

class TableView {
    /**
     * Render a data table with actions
     * 
     * @param array $config Configuration array with:
     *   - columns: array of column definitions ['key' => 'Label']
     *   - data: array of data rows
     *   - actions: array of action buttons
     *   - searchable: bool (default: true)
     *   - pagination: array with current_page, total_pages
     */
    public static function render(array $config = []) {
        $columns = $config['columns'] ?? [];
        $data = $config['data'] ?? [];
        $actions = $config['actions'] ?? [];
        $searchable = $config['searchable'] ?? true;
        $pagination = $config['pagination'] ?? null;
        $tableId = $config['id'] ?? 'data-table';
        $emptyMessage = $config['empty_message'] ?? 'Aucune donnée disponible';
        
        ?>
        <div class="table-container">
            <?php if ($searchable): ?>
            <div class="table-header">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" 
                           id="<?= $tableId ?>-search" 
                           placeholder="Rechercher..."
                           class="table-search">
                </div>
                <?php if (!empty($config['add_button'])): ?>
                <a href="<?= htmlspecialchars($config['add_button']['url']) ?>" 
                   class="btn primary">
                    <i class="fas fa-plus"></i>
                    <?= htmlspecialchars($config['add_button']['label']) ?>
                </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <div class="table-responsive">
                <table class="data-table" id="<?= $tableId ?>">
                    <thead>
                        <tr>
                            <?php foreach ($columns as $key => $label): ?>
                                <th data-key="<?= htmlspecialchars($key) ?>">
                                    <?= htmlspecialchars($label) ?>
                                </th>
                            <?php endforeach; ?>
                            <?php if (!empty($actions)): ?>
                                <th class="actions-column">Actions</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($data)): ?>
                            <tr>
                                <td colspan="<?= count($columns) + (!empty($actions) ? 1 : 0) ?>" 
                                    class="empty-state">
                                    <?= htmlspecialchars($emptyMessage) ?>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($data as $row): ?>
                                <tr>
                                    <?php foreach ($columns as $key => $label): ?>
                                        <td data-label="<?= htmlspecialchars($label) ?>">
                                            <?php 
                                            if (isset($row[$key . '_html'])) {
                                                echo $row[$key . '_html'];
                                            } elseif (isset($row[$key])) {
                                                echo htmlspecialchars($row[$key]);
                                            } else {
                                                echo '-';
                                            }
                                            ?>
                                        </td>
                                    <?php endforeach; ?>
                                    
                                    <?php if (!empty($actions)): ?>
                                        <td class="actions-cell">
                                            <div class="action-buttons">
                                                <?php foreach ($actions as $action): ?>
                                                    <?php 
                                                    $url = str_replace('{id}', $row['id'] ?? '', $action['url']);
                                                    $class = $action['class'] ?? 'btn-action';
                                                    $icon = $action['icon'] ?? 'fas fa-edit';
                                                    $label = $action['label'] ?? '';
                                                    $confirm = $action['confirm'] ?? false;
                                                    ?>
                                                    <a href="<?= htmlspecialchars($url) ?>" 
                                                       class="<?= htmlspecialchars($class) ?>"
                                                       title="<?= htmlspecialchars($label) ?>"
                                                       <?= $confirm ? 'onclick="return confirm(\'Êtes-vous sûr ?\')"' : '' ?>>
                                                        <i class="<?= htmlspecialchars($icon) ?>"></i>
                                                        <?php if ($label): ?>
                                                            <span><?= htmlspecialchars($label) ?></span>
                                                        <?php endif; ?>
                                                    </a>
                                                <?php endforeach; ?>
                                            </div>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($pagination && $pagination['total_pages'] > 1): ?>
                <div class="table-pagination">
                    <div class="pagination-info">
                        Page <?= $pagination['current_page'] ?> sur <?= $pagination['total_pages'] ?>
                    </div>
                    <div class="pagination-controls">
                        <?php if ($pagination['current_page'] > 1): ?>
                            <a href="<?= $pagination['base_url'] ?>&page=<?= $pagination['current_page'] - 1 ?>" 
                               class="pagination-btn">
                                <i class="fas fa-chevron-left"></i> Précédent
                            </a>
                        <?php endif; ?>

                        <div class="pagination-numbers">
                            <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                                <a href="<?= $pagination['base_url'] ?>&page=<?= $i ?>" 
                                   class="pagination-number <?= $i === $pagination['current_page'] ? 'active' : '' ?>">
                                    <?= $i ?>
                                </a>
                            <?php endfor; ?>
                        </div>

                        <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
                            <a href="<?= $pagination['base_url'] ?>&page=<?= $pagination['current_page'] + 1 ?>" 
                               class="pagination-btn">
                                Suivant <i class="fas fa-chevron-right"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($searchable): ?>
        <?php endif; ?>
        <?php
    }
}
?>