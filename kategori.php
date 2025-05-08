<?php
require_once 'templates/header.php';
require_once 'templates/backtotopbutton.php';
require_once 'include/config.php';
require_once 'include/functions.php';

// Kategori parametresi
$category_slug = isset($_GET['slug']) ? $_GET['slug'] : '';
$category_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Sayfalama parametreleri
$page = isset($_GET['sayfa']) ? (int)$_GET['sayfa'] : 1;
$per_page = 20; // Sayfa başına 20 haber
$offset = ($page - 1) * $per_page;

// Kategori objesi
$categoryObj = new Category();
$categories = $categoryObj->getAll();

// Aktif kategoriyi bul
$active_category = null;
if ($category_id) {
    $active_category = $categoryObj->getById($category_id);
} elseif ($category_slug) {
    foreach ($categories as $cat) {
        if ($cat['slug'] == $category_slug) {
            $active_category = $cat;
            break;
        }
    }
}

// Eğer kategori bulunamadıysa ana sayfaya yönlendir
if (!$active_category) {
    header('Location: index.php');
    exit;
}

// Toplam haber sayısını al
$where_clause = "WHERE category = '" . mysqli_real_escape_string($conn, $active_category['name']) . "'";
$count_result = mysqli_query($conn, "SELECT COUNT(*) as total FROM news $where_clause");
$total_news = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_news / $per_page);

// Haberleri getir
$sql = "SELECT * FROM news $where_clause ORDER BY created_at DESC LIMIT $per_page OFFSET $offset";
$result = mysqli_query($conn, $sql);
$news_list = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($active_category['name']); ?> Haberleri</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <main class="min-h-screen py-8">
        <div class="max-w-6xl mx-auto px-4">
            <div class="flex items-center justify-between mb-8">
                <h1 class="text-3xl font-bold"><?php echo htmlspecialchars($active_category['name']); ?> Haberleri</h1>
                <a href="tumhaberler.php" class="text-blue-600 hover:underline">Tüm Haberler</a>
            </div>
            
            <!-- Kategori Açıklaması -->
            <?php if (!empty($active_category['description'])): ?>
            <div class="bg-white p-6 rounded-lg shadow-sm mb-8">
                <p class="text-gray-700"><?php echo nl2br(htmlspecialchars($active_category['description'])); ?></p>
            </div>
            <?php endif; ?>
            
            <!-- Kategori Listesi -->
            <div class="mb-8">
                <div class="flex flex-wrap gap-2">
                    <?php foreach($categories as $category): ?>
                    <a href="kategori.php?id=<?php echo $category['id']; ?>" 
                       class="px-4 py-2 rounded-full text-sm font-medium <?php echo $active_category['id'] === $category['id'] ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'; ?>">
                        <?php echo htmlspecialchars($category['name']); ?>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Haberler Listesi -->
            <?php if (empty($news_list)): ?>
                <div class="text-center py-10">
                    <p class="text-gray-500">Bu kategoride haber bulunamadı.</p>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach($news_list as $item): ?>
                    <div class="bg-white rounded-lg shadow-sm overflow-hidden hover:shadow-md transition duration-200">
                        <a href="news.php?id=<?php echo $item['id']; ?>" class="block">
                            <div class="relative h-48 overflow-hidden">
                                <?php if (!empty($item['featured_image'])): ?>
                                    <img src="<?php echo $item['featured_image']; ?>" 
                                         alt="<?php echo htmlspecialchars($item['title']); ?>" 
                                         class="w-full h-full object-cover">
                                <?php else: ?>
                                    <div class="bg-gray-100 w-full h-full flex items-center justify-center text-gray-400">
                                        <span>Görsel Yok</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="p-4">
                                <h2 class="text-lg font-semibold mb-1 line-clamp-2"><?php echo htmlspecialchars($item['title']); ?></h2>
                                <div class="flex justify-between text-sm text-gray-500 mt-3">
                                    <span><?php echo date('d F Y', strtotime($item['created_at'])); ?></span>
                                    <span class="text-blue-600 hover:underline">Devamını Oku</span>
                                </div>
                            </div>
                        </a>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <!-- Sayfalama -->
            <?php if ($total_pages > 1): ?>
            <div class="mt-10 flex justify-center">
                <nav class="inline-flex rounded-md shadow-sm -space-x-px" aria-label="Sayfalama">
                    <?php if ($page > 1): ?>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['sayfa' => $page - 1])); ?>" class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-l-md hover:bg-gray-50">
                        <span class="sr-only">Önceki</span>
                        &laquo;
                    </a>
                    <?php endif; ?>
                    
                    <?php
                    // Sayfa numaralarını göster
                    $start_page = max(1, $page - 2);
                    $end_page = min($total_pages, $page + 2);
                    
                    // İlk sayfa ve "..." gösterimi
                    if ($start_page > 1):
                    ?>
                        <a href="?<?php echo http_build_query(array_merge($_GET, ['sayfa' => 1])); ?>" class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50">
                            1
                        </a>
                        <?php if ($start_page > 2): ?>
                        <span class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300">
                            ...
                        </span>
                        <?php endif; ?>
                    <?php endif; ?>
                    
                    <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['sayfa' => $i])); ?>" 
                       class="relative inline-flex items-center px-4 py-2 text-sm font-medium <?php echo $i == $page ? 'z-10 bg-blue-50 border-blue-500 text-blue-600' : 'text-gray-700 bg-white border-gray-300 hover:bg-gray-50'; ?> border">
                        <?php echo $i; ?>
                    </a>
                    <?php endfor; ?>
                    
                    <?php
                    // Son sayfa ve "..." gösterimi
                    if ($end_page < $total_pages):
                    ?>
                        <?php if ($end_page < $total_pages - 1): ?>
                        <span class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300">
                            ...
                        </span>
                        <?php endif; ?>
                        <a href="?<?php echo http_build_query(array_merge($_GET, ['sayfa' => $total_pages])); ?>" class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50">
                            <?php echo $total_pages; ?>
                        </a>
                    <?php endif; ?>
                    
                    <?php if ($page < $total_pages): ?>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['sayfa' => $page + 1])); ?>" class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-r-md hover:bg-gray-50">
                        <span class="sr-only">Sonraki</span>
                        &raquo;
                    </a>
                    <?php endif; ?>
                </nav>
            </div>
            <?php endif; ?>
        </div>
    </main>

    <?php require_once 'templates/footer.php'; ?>
</body>
</html> 