<?php
// views/layout/MenuView.php

class MenuView {
    public static function render(string $active = 'home') {
        $items = [
            'home' => ['label' => 'Accueil', 'href' => 'index.php'],
            'projects' => ['label' => 'Projets', 'href' => 'index.php?url=project'],
            'publications' => ['label' => 'Publications', 'href' => 'index.php?url=publication'],
            'equipments' => ['label' => 'Équipements', 'href' => 'index.php?url=equipment'],
            'teams' => ['label' => 'Membres', 'href' => 'index.php?url=team'],
            'contact' => ['label' => 'Contact', 'href' => 'index.php?url=contact'],
            'opportunités' => ['label'=>'Opportunités','href'=>'index.php?url=opportunity']
        ];
        ?>
        <nav class="main-nav">
            <ul>
                <?php foreach ($items as $key => $item): ?>
                    <li class="<?= $active === $key ? 'active' : '' ?>">
                        <a href="<?= $item['href'] ?>"><?= htmlspecialchars($item['label']) ?></a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </nav>
<?php
    }
}
?>

