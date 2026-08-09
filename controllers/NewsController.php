<?php
// controllers/NewsController.php
class NewsController {
    private $newsModel;
    private $settingModel;

    public function __construct() {
        $this->newsModel = new NewsModel();
        $this->settingModel = new SettingModel();
    }

    public function index() {
        $settings = $this->settingModel->getAllSettingsAsArray();

        // Filtres
        $type   = $_GET['type'] ?? null;
        $search = $_GET['search'] ?? '';
        $page   = max(1, (int)($_GET['page'] ?? 1));
        $limit  = 9;
        $offset = ($page - 1) * $limit;

        $totalNews  = $this->newsModel->countPublishedNews($type, $search);
        $totalPages = max(1, ceil($totalNews / $limit));

        $news = $this->newsModel->getPublishedNewsPaginated(
            $limit,
            $offset,
            $type,
            $search
        );

        // Types pour le filtre
        $allNews = $this->newsModel->getPublishedNews();
        $types = array_unique(array_column($allNews, 'type_news'));
        sort($types);

        NewsView::renderList([
            'settings' => $settings,
            'news' => $news,
            'types' => $types,
            'filterType' => $type,
            'search' => $search,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalNews' => $totalNews
        ]);
    }

    public function detail($id) {
        $settings = $this->settingModel->getAllSettingsAsArray();
        $news = $this->newsModel->getNewsById($id);

        if (!$news || $news['statut'] !== 'publie') {
            header('Location: index.php?url=news');
            exit;
        }

        NewsView::renderDetail([
            'settings' => $settings,
            'news' => $news
        ]);
    }
}
?>