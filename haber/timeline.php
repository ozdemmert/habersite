<?php
require_once 'include/config.php';
require_once 'include/functions.php';

$timelineObj = new Timeline();
$categoryObj = new Category();

// Get categories
$categories = $categoryObj->getAll();

// Active category
$active_category = isset($_GET['cat']) ? $_GET['cat'] : '';

// Pagination settings
$per_page = 9;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $per_page;

// Get timeline items
if ($active_category) {
    $sql = "SELECT * FROM timeline 
            WHERE category = ?
            ORDER BY new_date DESC, created_at DESC
            LIMIT ? OFFSET ?";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sii", $active_category, $per_page, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
    $timeline_items = $result->fetch_all(MYSQLI_ASSOC);
    
    // Get total count for pagination
    $count_sql = "SELECT COUNT(*) as total 
                  FROM timeline 
                  WHERE category = ?";
    $count_stmt = $conn->prepare($count_sql);
    $count_stmt->bind_param("s", $active_category);
    $count_stmt->execute();
    $total = $count_stmt->get_result()->fetch_assoc()['total'];
} else {
    $sql = "SELECT * FROM timeline 
            ORDER BY new_date DESC, created_at DESC
            LIMIT ? OFFSET ?";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $per_page, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
    $timeline_items = $result->fetch_all(MYSQLI_ASSOC);
    
    // Get total count
    $total = $conn->query("SELECT COUNT(*) as total FROM timeline")->fetch_assoc()['total'];
}

$total_pages = ceil($total / $per_page);

function isActiveCat($cat_name, $active) {
    return $cat_name === $active ? 'bg-[#022d5a] text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-100';
}

function formatDate($date) {
    return date('d/m/Y', strtotime($date));
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zaman Çizelgesi</title>
    <script src="https://cdn.tailwindcss.com"></script>
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
    </style>
</head>
<body class="bg-white">
    <?php require_once 'templates/header.php'; ?>
    <main class="container w-[1080px] mx-auto px-4 py-8">
        <!-- Breadcrumb -->
        <nav class="text-sm text-gray-500 mb-2" aria-label="Breadcrumb">
            <ol class="list-none p-0 inline-flex">
                <li class="flex items-center">
                    <a href="index.php" class="hover:text-[#f39200]">Ana Sayfa</a>
                    <span class="mx-2">/</span>
                </li>
                <li class="flex items-center">
                    <span class="text-[#022d5a]">Zaman Çizelgesi</span>
                </li>
            </ol>
        </nav>

        <!-- Timeline Banner -->
        <section class="mb-8">
            <h1 class="text-3xl font-bold mb-3 relative pb-2 text-[#022d5a]">
                Zaman Çizelgesi
                <span class="absolute bottom-0 left-0 w-24 h-1 bg-[#f39200]"></span>
            </h1>
            <p class="text-gray-700">
                Önemli olayların kronolojik sırası
            </p>
        </section>

        <!-- Timeline Filter Section -->
        <section class="mb-10">
            <h2 class="text-xl font-bold mb-4 text-[#022d5a]">
                Zaman Çizelgesi Seçin
            </h2>
            
            <div class="flex flex-wrap gap-2 mb-6">
                <a href="?" class="px-4 py-1 rounded-full font-semibold <?php echo isActiveCat('', $active_category); ?>">
                    Tümü
                </a>
                <?php foreach($categories as $cat): ?>
                    <a href="?cat=<?php echo urlencode($cat['name']); ?>" 
                       class="px-4 py-1 rounded-full font-semibold <?php echo isActiveCat($cat['name'], $active_category); ?>">
                        <?php echo htmlspecialchars($cat['name']); ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Timeline Display -->
        <section class="relative">
            <!-- Vertical line -->
            <div class="absolute left-16 top-0 bottom-0 w-1 bg-gray-200"></div>
            
            <!-- Timeline Events -->
            <div class="space-y-12">
                <?php if ($timeline_items): foreach($timeline_items as $item): ?>
                    <div class="relative flex">
                        <!-- Year Circle -->
                        <div class="absolute left-16 -translate-x-1/2 w-10 h-10 rounded-full bg-white border-4 border-gray-200 flex items-center justify-center z-10">
                            <span class="text-xs font-bold"><?php echo date('Y', strtotime($item['new_date'])); ?></span>
                        </div>
                        
                        <!-- Event Content -->
                        <div class="ml-24 bg-white p-4 rounded-lg shadow-sm hover:shadow-md transition-shadow duration-300 w-full">
                            <div class="flex justify-between items-start mb-3">
                                <h3 class="text-xl font-bold text-[#022d5a]"><?php echo htmlspecialchars($item['title']); ?></h3>
                                <span class="text-sm text-gray-500">
                                    <?php echo formatDate($item['new_date']); ?>
                                </span>
                            </div>
                            
                            <p class="text-gray-600 mb-3 line-clamp-3"><?php echo strip_tags($item['content']); ?></p>
                            
                            <div class="flex justify-between items-center">
                                <span class="inline-block px-3 py-1 bg-gray-100 text-xs font-medium rounded-full text-gray-700">
                                    <?php echo htmlspecialchars($item['category']); ?>
                                </span>
                                
                                <div class="flex gap-3">
                                    <a href="timeline_detay.php?slug=<?php echo $item['slug']; ?>" 
                                       class="text-[#022d5a] hover:text-[#f39200] text-sm font-medium transition-colors duration-300">
                                        Detayları Gör
                                    </a>
                                    <button onclick="shareContent('<?php echo $item['title']; ?>', '<?php echo $item['slug']; ?>')" 
                                            class="text-gray-500 hover:text-[#f39200] text-sm font-medium transition-colors duration-300">
                                        Paylaş
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; else: ?>
                    <div class="text-center text-gray-500 py-8">Bu kategoride zaman çizelgesi bulunamadı.</div>
                <?php endif; ?>
            </div>
        </section>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <div class="flex justify-center gap-1 mt-8">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="?<?php echo $active_category ? 'cat='.urlencode($active_category).'&' : ''; ?>page=<?php echo $i; ?>" 
                   class="px-3 py-1 rounded border <?php echo $i == $page ? 'bg-[#022d5a] text-white border-[#022d5a]' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-100'; ?> font-semibold">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
    </main>

    <?php require_once 'templates/footer.php'; ?>

    <script>
        function shareContent(title, slug) {
            if (navigator.share) {
                navigator.share({
                    title: title,
                    url: window.location.origin + '/timeline_detay.php?slug=' + slug
                })
                .catch(console.error);
            } else {
                // Fallback for browsers that don't support Web Share API
                const url = window.location.origin + '/timeline_detay.php?slug=' + slug;
                const textarea = document.createElement('textarea');
                textarea.value = url;
                document.body.appendChild(textarea);
                textarea.select();
                document.execCommand('copy');
                document.body.removeChild(textarea);
                alert('Bağlantı kopyalandı!');
            }
        }
    </script>
</body>
</html> 