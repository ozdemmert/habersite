<?php
require_once 'include/config.php';
require_once 'include/functions.php';

$timelineObj = new Timeline();

// Get timeline item by slug
$slug = isset($_GET['slug']) ? $_GET['slug'] : '';
if (!$slug) {
    header('Location: timeline.php');
    exit;
}

$sql = "SELECT t.*, c.name as category_name 
        FROM timeline t
        LEFT JOIN timeline_category tc ON t.id = tc.timeline_id
        LEFT JOIN categories c ON tc.category_id = c.id
        WHERE t.slug = ?";
        
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $slug);
$stmt->execute();
$result = $stmt->get_result();
$item = $result->fetch_assoc();

if (!$item) {
    header('Location: timeline.php');
    exit;
}

// Update view count
$sql = "UPDATE timeline SET views = views + 1 WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $item['id']);
$stmt->execute();

// Get related items
$sql = "SELECT t.*, c.name as category_name 
        FROM timeline t
        LEFT JOIN timeline_category tc ON t.id = tc.timeline_id
        LEFT JOIN categories c ON tc.category_id = c.id
        WHERE t.id != ? AND c.name = ?
        ORDER BY t.new_date DESC
        LIMIT 3";
        
$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $item['id'], $item['category_name']);
$stmt->execute();
$related_items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

function formatDate($date) {
    return date('d/m/Y', strtotime($date));
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($item['title']); ?> - Zaman Çizelgesi</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <meta name="description" content="<?php echo htmlspecialchars(strip_tags(substr($item['content'], 0, 160))); ?>">
</head>
<body class="bg-white">
    <?php require_once 'templates/header.php'; ?>
    <main class="container mx-auto px-4 py-8">
        <!-- Breadcrumb -->
        <nav class="text-sm text-gray-500 mb-6" aria-label="Breadcrumb">
            <ol class="list-none p-0 inline-flex">
                <li class="flex items-center">
                    <a href="index.php" class="hover:text-[#f39200]">Ana Sayfa</a>
                    <span class="mx-2">/</span>
                </li>
                <li class="flex items-center">
                    <a href="timeline.php" class="hover:text-[#f39200]">Zaman Çizelgesi</a>
                    <span class="mx-2">/</span>
                </li>
                <li class="flex items-center">
                    <span class="text-[#022d5a]"><?php echo htmlspecialchars($item['title']); ?></span>
                </li>
            </ol>
        </nav>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Content -->
            <div class="lg:col-span-2">
                <article class="bg-white rounded-lg shadow-sm">
                    <?php if ($item['featured_image']): ?>
                    <div class="aspect-w-16 aspect-h-9 mb-6">
                        <img src="<?php echo htmlspecialchars($item['featured_image']); ?>" 
                             alt="<?php echo htmlspecialchars($item['title']); ?>"
                             class="w-full h-auto rounded-t-lg object-cover">
                    </div>
                    <?php endif; ?>

                    <div class="p-6">
                        <div class="flex items-center gap-4 text-sm text-gray-500 mb-4">
                            <span>
                                <i class="fas fa-calendar"></i>
                                <?php echo formatDate($item['new_date']); ?>
                            </span>
                            <span>
                                <i class="fas fa-eye"></i>
                                <?php echo number_format($item['views']); ?> görüntülenme
                            </span>
                            <span class="inline-block px-3 py-1 bg-gray-100 rounded-full">
                                <?php echo htmlspecialchars($item['category_name'] ?? $item['category']); ?>
                            </span>
                        </div>

                        <h1 class="text-3xl font-bold text-[#022d5a] mb-6"><?php echo htmlspecialchars($item['title']); ?></h1>
                        
                        <div class="prose max-w-none">
                            <?php echo $item['content']; ?>
                        </div>

                        <!-- Share Buttons -->
                        <div class="flex items-center gap-4 mt-8 pt-6 border-t">
                            <span class="text-gray-700 font-medium">Paylaş:</span>
                            <button onclick="shareContent('<?php echo htmlspecialchars($item['title']); ?>', '<?php echo $item['slug']; ?>')"
                                    class="text-gray-500 hover:text-[#f39200]">
                                <i class="fas fa-share-alt"></i>
                            </button>
                            <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode($currentUrl); ?>&text=<?php echo urlencode($item['title']); ?>"
                               target="_blank" rel="noopener noreferrer"
                               class="text-[#1DA1F2] hover:opacity-80">
                                <i class="fab fa-twitter"></i>
                            </a>
                            <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode($currentUrl); ?>"
                               target="_blank" rel="noopener noreferrer"
                               class="text-[#4267B2] hover:opacity-80">
                                <i class="fab fa-facebook"></i>
                            </a>
                            <a href="https://www.linkedin.com/shareArticle?mini=true&url=<?php echo urlencode($currentUrl); ?>&title=<?php echo urlencode($item['title']); ?>"
                               target="_blank" rel="noopener noreferrer"
                               class="text-[#0077b5] hover:opacity-80">
                                <i class="fab fa-linkedin"></i>
                            </a>
                        </div>
                    </div>
                </article>
            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1">
                <?php if ($related_items): ?>
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-xl font-bold mb-4 text-[#022d5a]">İlgili İçerikler</h2>
                    <div class="space-y-4">
                        <?php foreach($related_items as $related): ?>
                        <div class="group">
                            <a href="timeline_detay.php?slug=<?php echo $related['slug']; ?>" 
                               class="block hover:opacity-90">
                                <?php if ($related['featured_image']): ?>
                                <img src="<?php echo htmlspecialchars($related['featured_image']); ?>" 
                                     alt="<?php echo htmlspecialchars($related['title']); ?>"
                                     class="w-full h-48 object-cover rounded-lg mb-2">
                                <?php endif; ?>
                                <h3 class="font-semibold text-[#022d5a] group-hover:text-[#f39200] transition-colors duration-300">
                                    <?php echo htmlspecialchars($related['title']); ?>
                                </h3>
                                <div class="text-sm text-gray-500 mt-1">
                                    <?php echo formatDate($related['new_date']); ?>
                                </div>
                            </a>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <?php require_once 'templates/footer.php'; ?>

    <script>
        function shareContent(title, slug) {
            if (navigator.share) {
                navigator.share({
                    title: title,
                    url: window.location.href
                })
                .catch(console.error);
            } else {
                const textarea = document.createElement('textarea');
                textarea.value = window.location.href;
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