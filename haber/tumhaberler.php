<?php

require_once 'include/config.php';
require_once 'include/functions.php';

// Sayfalama parametreleri
$page = isset($_GET['sayfa']) ? (int)$_GET['sayfa'] : 1;
$per_page = 10; // Sayfa başına 10 haber
$offset = ($page - 1) * $per_page;

// Kategori filtresi
$category_filter = isset($_GET['category']) ? $_GET['category'] : '';

// Haber ve Kategori objelerini oluştur
$newsObj = new News($conn);
$categoryObj = new Category();
$categories = $categoryObj->getAll();

// Toplam haber sayısını al
$where_clause = $category_filter ? "WHERE category = '" . mysqli_real_escape_string($conn, $category_filter) . "'" : "";
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
    <title>Tüm Haberler</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .line-clamp-3 {
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .news-item {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .news-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        
        @media (min-width: 768px) {
            .news-image {
                height: 220px;
            }
        }
    </style>
</head>
<body class="bg-white">
<?php require_once 'templates/header.php'; ?>
    <main class="min-h-screen py-8">
        <div class="max-w-6xl mx-auto px-4">
            <h1 class="text-3xl font-bold mb-6 text-[#022d5a]">Tüm Haberler</h1>
            
            <!-- Kategori Filtreleme -->
            <div class="mb-8">
                <div class="flex flex-wrap gap-2">
                    <a href="tumhaberler.php" class="px-4 py-2 rounded-full text-sm font-medium <?php echo !$category_filter ? 'bg-[#022d5a] text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'; ?>">
                        Tümü
                    </a>
                    <?php foreach($categories as $category): ?>
                    <a href="tumhaberler.php?category=<?php echo urlencode($category['name']); ?>" 
                       class="px-4 py-2 rounded-full text-sm font-medium <?php echo $category_filter === $category['name'] ? 'bg-[#022d5a] text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'; ?>">
                        <?php echo htmlspecialchars($category['name']); ?>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Haberler Listesi -->
            <?php if (empty($news_list)): ?>
                <div class="text-center py-10">
                    <p class="text-gray-500">Haber bulunamadı.</p>
                </div>
            <?php else: ?>
                <div class="space-y-6">
                    <?php foreach($news_list as $item): ?>
                    <div class="bg-white rounded-lg shadow-sm overflow-hidden hover:shadow-md transition duration-200 news-item">
                        <a href="news.php?id=<?php echo $item['id']; ?>" class="block">
                            <div class="flex flex-col md:flex-row">
                                <div class="relative md:w-1/3 h-64 md:h-auto news-image">
                                    <?php if (!empty($item['featured_image'])): ?>
                                        <img src="<?php echo $item['featured_image']; ?>" 
                                             alt="<?php echo htmlspecialchars($item['title']); ?>" 
                                             class="w-full h-full object-cover">
                                    <?php else: ?>
                                        <div class="bg-gray-100 w-full h-full flex items-center justify-center text-gray-400">
                                            <span>Görsel Yok</span>
                                        </div>
                                    <?php endif; ?>
                                    <span class="absolute top-3 left-3 px-3 py-1 text-xs font-bold rounded bg-[#022d5a] text-white">
                                        <?php echo htmlspecialchars($item['category']); ?>
                                    </span>
                                </div>
                                <div class="p-6 md:w-2/3">
                                    <div class="flex items-center text-sm text-[#f39200] font-medium mb-2">
                                        <span><?php echo date('d F Y', strtotime($item['created_at'])); ?></span>
                                    </div>
                                    <h2 class="text-xl font-bold mb-3 text-[#022d5a]"><?php echo htmlspecialchars($item['title']); ?></h2>
                                    <div class="text-gray-600 mb-4 line-clamp-3">
                                        <?php 
                                        // İçerik özetini hazırla
                                        $content = strip_tags($item['content']);
                                        $summary = strlen($content) > 250 ? substr($content, 0, 250) . '...' : $content;
                                        echo $summary;
                                        ?>
                                    </div>
                                    <div class="mt-auto">
                                        <span class="text-[#f39200] font-medium hover:underline">Devamını Oku &rarr;</span>
                                    </div>
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
                       class="relative inline-flex items-center px-4 py-2 text-sm font-medium <?php echo $i == $page ? 'z-10 bg-[#022d5a] border-[#022d5a] text-white' : 'text-gray-700 bg-white border-gray-300 hover:bg-gray-50'; ?> border">
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