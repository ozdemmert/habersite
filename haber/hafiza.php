<?php

require_once 'include/config.php';
require_once 'include/functions.php';

$hafizaObj = new Hafiza();
$categoryObj = new Category();

// Get featured items first - independent of category
$sql = "SELECT * FROM hafiza WHERE is_featured = 1 ORDER BY new_date DESC, created_at DESC LIMIT 3";
$featured_result = mysqli_query($conn, $sql);
$featured_items = mysqli_fetch_all($featured_result, MYSQLI_ASSOC);

// Kategorileri çek
$categories = $categoryObj->getAll();

// Aktif kategori
$active_category = isset($_GET['cat']) ? (int)$_GET['cat'] : 0;

// Sayfalama ayarları
$per_page = 9;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $per_page;

// Hafıza içeriklerini çekme
if ($active_category) {
    $all_hafiza = [];
    $hafiza_ids = [];
    $sql = "SELECT hafiza_id FROM hafiza_category WHERE category_id = $active_category";
    $result = mysqli_query($conn, $sql);
    while ($row = mysqli_fetch_assoc($result)) {
        $hafiza_ids[] = $row['hafiza_id'];
    }
    if (!empty($hafiza_ids)) {
        $ids = implode(',', $hafiza_ids);
        // Exclude featured items from main grid
        $sql = "SELECT * FROM hafiza WHERE id IN ($ids) AND is_featured = 0 ORDER BY new_date DESC, created_at DESC LIMIT $per_page OFFSET $offset";
        $hafiza_result = mysqli_query($conn, $sql);
        $all_hafiza = mysqli_fetch_all($hafiza_result, MYSQLI_ASSOC);
        
        $sql = "SELECT COUNT(*) as total FROM hafiza WHERE id IN ($ids) AND is_featured = 0";
        $count_result = mysqli_query($conn, $sql);
        $total = mysqli_fetch_assoc($count_result)['total'];
    } else {
        $all_hafiza = [];
        $total = 0;
    }
} else {
    // Exclude featured items from main grid
    $sql = "SELECT * FROM hafiza WHERE is_featured = 0 ORDER BY new_date DESC, created_at DESC LIMIT $per_page OFFSET $offset";
    $hafiza_result = mysqli_query($conn, $sql);
    $all_hafiza = mysqli_fetch_all($hafiza_result, MYSQLI_ASSOC);
    $sql = "SELECT COUNT(*) as total FROM hafiza WHERE is_featured = 0";
    $count_result = mysqli_query($conn, $sql);
    $total = mysqli_fetch_assoc($count_result)['total'];
}
$total_pages = ceil($total / $per_page);

function isActiveCat($cat_id, $active) {
    return $cat_id == $active ? 'bg-[#022d5a] text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-100';
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tarih Sayfalarından</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .line-clamp-3 {
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .featured-card {
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            border: none;
            background: linear-gradient(to right, #fff, #f9f9f9);
        }
        .featured-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        .featured-category {
            background: linear-gradient(to right, #022d5a, #06417d);
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
        }
        .featured-date {
            color: #f39200;
            font-weight: 500;
            letter-spacing: 0.025em;
        }
        .featured-title {
            font-family: 'Georgia', serif;
            letter-spacing: -0.025em;
            color: #022d5a;
        }
        .featured-read-more {
            display: inline-flex;
            align-items: center;
            color: #f39200;
            font-weight: 600;
            transition: all 0.2s;
        }
        .featured-read-more:hover {
            color: #022d5a;
        }
        .featured-read-more i {
            transition: transform 0.2s;
            margin-left: 4px;
        }
        .featured-read-more:hover i {
            transform: translateX(3px);
        }
        .featured-section {
            background-color: #fcfcfc;
            padding: 2rem 0;
            border-bottom: 1px solid #f0f0f0;
        }
    </style>
</head>
<body class="bg-white">
<?php
require_once 'templates/header.php';
?>
    <main class="w-full">
        
        <!-- Featured Items - Fixed at top regardless of category -->
        <?php if (!empty($featured_items)): ?>
        <div class="bg-white featured-section mb-6">
            <div class="max-w-6xl mx-auto px-4">
                <div class="flex flex-col space-y-5">
                    <?php foreach($featured_items as $featured): ?>
                    <div class="featured-card rounded-xl overflow-hidden flex">
                        <!-- Image on left -->
                        <div class="w-1/3 relative">
                            <?php if (!empty($featured['featured_image'])): ?>
                                <img src="<?php echo $featured['featured_image']; ?>" 
                                     alt="<?php echo htmlspecialchars($featured['title']); ?>" 
                                     class="w-full h-full object-cover">
                            <?php else: ?>
                                <div class="bg-gray-100 w-full h-full flex items-center justify-center text-gray-400">
                                    <span>Görsel Yok</span>
                                </div>
                            <?php endif; ?>
                            <span class="absolute top-3 left-3 px-4 py-1.5 text-xs font-bold rounded-full featured-category text-white">
                                <?php echo htmlspecialchars($featured['category']); ?>
                            </span>
                        </div>
                        
                        <!-- Content on right -->
                        <div class="w-2/3 p-6 flex flex-col justify-between">
                            <div>
                                <?php if (isset($featured['new_date']) && !empty($featured['new_date'])): ?>
                                <div class="featured-date text-xs mb-3"><?php echo date('d F Y', strtotime($featured['new_date'])); ?></div>
                                <?php else: ?>
                                <div class="featured-date text-xs mb-3"><?php echo date('d F Y', strtotime($featured['created_at'])); ?></div>
                                <?php endif; ?>
                                <h2 class="featured-title text-xl font-bold mb-3 line-clamp-2"><?php echo htmlspecialchars($featured['title']); ?></h2>
                                <?php if (isset($featured['content'])): ?>
                                <p class="text-gray-600 mb-4 line-clamp-3">
                                    <?php echo mb_substr(strip_tags($featured['content']), 0, 180) . '...'; ?>
                                </p>
                                <?php endif; ?>
                            </div>
                            <div>
                                <a href="hafiza_detay.php?id=<?php echo $featured['id']; ?>" class="featured-read-more">
                                    Devamını Oku <i class="fas fa-arrow-right text-xs"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="max-w-6xl mx-auto px-4">
            <!-- Sayfa Başlığı -->
            <h1 class="text-3xl font-bold mb-6 border-b border-[#f39200] pb-2 inline-block text-[#022d5a]">Tarih Sayfalarından</h1>
            
            <!-- Kategori Sekmeleri -->
            <div class="flex flex-wrap gap-2 mb-8">
                <a href="hafiza.php" class="px-4 py-1 rounded-full text-sm font-medium <?php echo $active_category == 0 ? 'bg-[#022d5a] text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-100'; ?>">Tümü</a>
                <?php foreach($categories as $cat): ?>
                    <a href="hafiza.php?cat=<?php echo $cat['id']; ?>" class="px-4 py-1 rounded-full text-sm font-medium <?php echo isActiveCat($cat['id'], $active_category); ?>"><?php echo htmlspecialchars($cat['name']); ?></a>
                <?php endforeach; ?>
            </div>
            
            <!-- İçerik Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-10">
                <?php if (!empty($all_hafiza)): foreach($all_hafiza as $hafiza): ?>
                <div class="bg-white shadow rounded-lg overflow-hidden hover:shadow-md transition duration-200">
                    <a href="hafiza_detay.php?id=<?php echo $hafiza['id']; ?>" class="block">
                        <div class="relative h-48 overflow-hidden">
                            <?php if (!empty($hafiza['featured_image'])): ?>
                                <img src="<?php echo $hafiza['featured_image']; ?>" 
                                     alt="<?php echo htmlspecialchars($hafiza['title']); ?>" 
                                     class="w-full h-full object-cover">
                            <?php else: ?>
                                <div class="bg-gray-100 w-full h-full flex items-center justify-center text-gray-400">
                                    <span>Görsel Yok</span>
                                </div>
                            <?php endif; ?>
                            <span class="absolute top-3 left-3 px-3 py-1 text-xs font-bold rounded bg-[#022d5a] text-white">
                                <?php echo htmlspecialchars($hafiza['category']); ?>
                            </span>
                        </div>
                        <div class="p-4">
                            <?php if (isset($hafiza['new_date']) && !empty($hafiza['new_date'])): ?>
                            <div class="text-xs text-[#f39200] font-medium mb-1"><?php echo date('d F Y', strtotime($hafiza['new_date'])); ?></div>
                            <?php else: ?>
                            <div class="text-xs text-[#f39200] font-medium mb-1"><?php echo date('d F Y', strtotime($hafiza['created_at'])); ?></div>
                            <?php endif; ?>
                            <h2 class="text-lg font-semibold mb-2 line-clamp-2 text-[#022d5a]"><?php echo htmlspecialchars($hafiza['title']); ?></h2>
                            <?php if (isset($hafiza['content'])): ?>
                            <p class="text-sm text-gray-600 mb-3 line-clamp-3">
                                <?php echo mb_substr(strip_tags($hafiza['content']), 0, 150) . '...'; ?>
                            </p>
                            <?php endif; ?>
                            <div class="flex justify-between items-center">
                                <span class="text-[#f39200] text-sm font-medium hover:underline">Devamını Oku</span>
                            </div>
                        </div>
                    </a>
                </div>
                <?php endforeach; elseif (empty($all_hafiza)): ?>
                    <div class="col-span-3 text-center text-gray-500 py-12">Bu kategoride içerik bulunamadı.</div>
                <?php endif; ?>
            </div>
            
            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
            <div class="flex justify-center gap-1 mb-8">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?<?php echo $active_category ? 'cat='.$active_category.'&' : ''; ?>page=<?php echo $i; ?>" class="px-3 py-1 rounded border <?php echo $i == $page ? 'bg-[#022d5a] text-white border-[#022d5a]' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-100'; ?> text-sm font-medium">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
            </div>
            <?php endif; ?>
        </div>
    </main>
    <?php require_once 'templates/footer.php'; ?>
</body>
</html> 