<?php
require_once 'templates/header.php';
require_once 'include/functions.php';

$newsObj = new News($conn);

// Pagination settings
$items_per_page = 9;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$current_page = max(1, $current_page);

// Get total count of featured news
$total_items = $newsObj->countFeatured();
$total_pages = ceil($total_items / $items_per_page);

// Get featured news for current page
$featuredNews = $newsObj->getAll(['is_featured' => 1], $items_per_page, ($current_page - 1) * $items_per_page);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Öne Çıkan Haberler - Haber Sitesi</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <main class="min-h-screen py-8">
        <div class="max-w-7xl mx-auto px-4">
            <!-- Page Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold mb-2">Öne Çıkan Haberler</h1>
                <p class="text-gray-600">En önemli ve güncel haberlerimiz</p>
            </div>

            <!-- News Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                <?php foreach($featuredNews as $news): ?>
                <div class="bg-white shadow rounded overflow-hidden">
                    <a href="news.php?id=<?php echo $news['id']; ?>">
                        <div class="relative h-48">
                            <img src="<?php echo !empty($news['image']) ? $news['image'] : 'https://picsum.photos/seed/featured'.$news['id'].'/600/400'; ?>" 
                                 alt="<?php echo htmlspecialchars($news['title']); ?>" 
                                 class="w-full h-full object-cover">
                            <div class="absolute top-2 left-2">
                                <span class="category-badge"><?php echo htmlspecialchars($news['category']); ?></span>
                            </div>
                        </div>
                        <div class="p-4">
                            <h3 class="font-bold mb-2 line-clamp-2"><?php echo htmlspecialchars($news['title']); ?></h3>
                            <p class="text-sm text-gray-600 line-clamp-3 mb-3"><?php echo htmlspecialchars($news['summary']); ?></p>
                            <div class="flex justify-between text-xs text-gray-500">
                                <span><?php echo htmlspecialchars($news['author']); ?></span>
                                <span><?php echo date('d.m.Y', strtotime($news['created_at'])); ?></span>
                            </div>
                        </div>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
            <div class="flex justify-center space-x-2">
                <?php if ($current_page > 1): ?>
                <a href="?page=<?php echo $current_page - 1; ?>" 
                   class="px-4 py-2 border rounded hover:bg-gray-100">
                    Önceki
                </a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="?page=<?php echo $i; ?>" 
                   class="px-4 py-2 border rounded <?php echo $i === $current_page ? 'bg-blue-500 text-white' : 'hover:bg-gray-100'; ?>">
                    <?php echo $i; ?>
                </a>
                <?php endfor; ?>

                <?php if ($current_page < $total_pages): ?>
                <a href="?page=<?php echo $current_page + 1; ?>" 
                   class="px-4 py-2 border rounded hover:bg-gray-100">
                    Sonraki
                </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </main>

    <?php require_once 'templates/footer.php'; ?>
</body>
</html> 