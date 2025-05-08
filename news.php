<?php

require_once 'include/config.php';
require_once 'include/functions.php';

// Haber ID'sini al
$news_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Haber objesi oluştur
$newsObj = new News($conn);
$categoryObj = new Category();

// Haber detaylarını getir
$news = $newsObj->getById($news_id);

// Haber bulunamadıysa anasayfaya yönlendir
if (!$news) {
    header('Location: index.php');
    exit;
}

// Kategoriler
$categories = $categoryObj->getAll();

// İlgili haberleri getir (aynı kategorideki diğer haberler)
$relatedNews = [];
if (isset($news['category'])) {
    // Kategori bilgisi ile haberleri getir
    $category = mysqli_real_escape_string($conn, $news['category']);
    $sql = "SELECT * FROM news 
            WHERE category = '{$category}' AND id != {$news_id} 
            ORDER BY created_at DESC LIMIT 3";
    $result = mysqli_query($conn, $sql);
    if ($result) {
        $relatedNews = mysqli_fetch_all($result, MYSQLI_ASSOC);
        
        // Kategorileri kontrol et
        foreach ($relatedNews as $key => $related) {
            if (empty($related['category'])) {
                // Kategori boşsa, ilgili haberin kategorisini al
                $relatedNews[$key]['category'] = $news['category'];
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($news['title']); ?> - Haber Detay</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .content img {
            max-width: 100%;
            height: auto;
            margin: 1rem 0;
        }
        
        .content p {
            margin-bottom: 1rem;
            line-height: 1.7;
        }
        
        .content h2, .content h3 {
            margin-top: 1.5rem;
            margin-bottom: 0.75rem;
            font-weight: 600;
        }
        
        .content ul, .content ol {
            margin-left: 1.5rem;
            margin-bottom: 1rem;
        }

        /* İlgili Haberler Kart Stilleri */
        .news-card {
            transition: all 0.3s ease;
            height: 100%;
        }
        
        .news-card:hover {
            transform: translateY(-5px);
        }
        
        .news-card .image-container {
            position: relative;
            height: 180px; /* Sabit yükseklik */
            overflow: hidden;
            background-color: #f3f4f6;
        }
        
        .news-card .image-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: all 0.5s ease;
        }
        
        .news-card:hover .image-container img {
            transform: scale(1.05);
        }
        
        .news-card a {
            text-decoration: none !important;
            color: inherit;
        }
        
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .category-badge {
            position: absolute;
            top: 10px;
            left: 10px;
            background-color: #022d5a;
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            z-index: 10;
            display: inline-block; /* Görünürlüğü artır */
        }
    </style>
</head>
<body class="bg-white">
<?php require_once 'templates/header.php'; ?>
<?php require_once 'templates/backtotopbutton.php'; ?>
    <main class="min-h-screen py-6">
        <div class="max-w-5xl mx-auto px-0 pl-4">
            <!-- Breadcrumb -->
            <nav class="text-sm text-gray-500 mb-2" aria-label="Breadcrumb">
                <ol class="list-none p-0 inline-flex">
                    <li class="flex items-center">
                        <a href="index.php" class="hover:text-[#f39200]">Ana Sayfa</a>
                        <span class="mx-2">/</span>
                    </li>
                    <li class="flex items-center">
                        <a href="index.php" class="hover:text-[#f39200]">Haberler</a>
                        <span class="mx-2">/</span>
                    </li>
                    <li class="flex items-center">
                        <span class="text-[#022d5a]"><?php echo htmlspecialchars($news['title']); ?></span>
                    </li>
                </ol>
            </nav>
            
            <!-- Haber Detay -->
            <div class="mb-10">
                <!-- Kategori ve Tarih -->
                <div class="flex flex-wrap gap-2 mb-3">
                    <a href="tumhaberler.php?category=<?php echo urlencode($news['category']); ?>" 
                       class="px-3 py-1 rounded-full text-xs font-semibold bg-[#022d5a] text-white">
                        <?php echo htmlspecialchars($news['category']); ?>
                    </a>
                    <span class="text-[#f39200] text-sm font-medium">
                        <?php echo date('d F Y', strtotime($news['created_at'])); ?>
                    </span>
                </div>
                
                <!-- Başlık -->
                <h1 class="text-3xl font-bold mb-6 text-[#022d5a]"><?php echo htmlspecialchars($news['title']); ?></h1>
                
                <!-- Featured Image -->
                <?php if (!empty($news['featured_image'])): ?>
                <div class="mb-8 overflow-hidden rounded-lg shadow-md">
                    <img src="<?php echo $news['featured_image']; ?>" 
                         alt="<?php echo htmlspecialchars($news['title']); ?>" 
                         class="w-full object-cover max-h-[500px]">
                </div>
                <?php endif; ?>
                
                <!-- İçerik -->
                <div class="prose content max-w-none text-gray-700 mb-10">
                    <?php echo $news['content']; ?>
                </div>
                
                <!-- Paylaşım Linkleri -->
                <div class="flex items-center space-x-4 border-t border-b border-gray-200 py-4 my-8">
                    <span class="font-medium text-gray-700">Paylaş:</span>
                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>" 
                       target="_blank" class="text-[#022d5a] hover:text-[#f39200]">
                        <i class="fab fa-facebook-f"></i> Facebook
                    </a>
                    <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>&text=<?php echo urlencode($news['title']); ?>" 
                       target="_blank" class="text-[#022d5a] hover:text-[#f39200]">
                        <i class="fab fa-twitter"></i> Twitter
                    </a>
                    <a href="https://wa.me/?text=<?php echo urlencode($news['title'] . ' ' . 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>" 
                       target="_blank" class="text-[#022d5a] hover:text-[#f39200]">
                        <i class="fab fa-whatsapp"></i> WhatsApp
                    </a>
                </div>
            </div>
            
            <!-- İlgili Haberler -->
            <?php if (!empty($relatedNews)): ?>
            <div class="mb-16">
                <h2 class="text-2xl font-bold mb-6 border-b pb-2 text-[#022d5a]">İlgili Haberler</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <?php foreach($relatedNews as $item): ?>
                    <?php if ($item['id'] != $news_id): ?>
                    <div class="bg-white border rounded-lg shadow-sm hover:shadow-md transition overflow-hidden flex flex-col h-full news-card">
                        <a href="news.php?id=<?php echo $item['id']; ?>" class="flex flex-col h-full no-underline">
                            <div class="image-container">
                                <?php if (!empty($item['featured_image'])): ?>
                                    <img src="<?php echo $item['featured_image']; ?>" 
                                         alt="<?php echo htmlspecialchars($item['title']); ?>">
                                <?php else: ?>
                                    <div class="w-full h-full flex items-center justify-center text-gray-400">
                                        <span>Görsel Yok</span>
                                    </div>
                                <?php endif; ?>
                                <?php if (!empty($item['category'])): ?>
                                <span class="category-badge">
                                    <?php echo htmlspecialchars($item['category']); ?>
                                </span>
                                <?php endif; ?>
                            </div>
                            <div class="flex-1 flex flex-col p-4">
                                <span class="text-xs text-[#f39200] font-medium mb-1">
                                    <?php echo date('d F Y', strtotime($item['created_at'])); ?>
                                </span>
                                <h3 class="font-semibold text-base mb-2 line-clamp-2 text-[#022d5a]">
                                    <?php echo htmlspecialchars($item['title']); ?>
                                </h3>
                                <div class="mt-auto">
                                    <a href="news.php?id=<?php echo $item['id']; ?>" class="text-sm text-[#f39200] font-semibold hover:underline inline-block">Devamını Oku &rarr;</a>
                                </div>
                            </div>
                        </a>
                    </div>
                    <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </main>
    
    <?php require_once 'templates/footer.php'; ?>
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</body>
</html> 