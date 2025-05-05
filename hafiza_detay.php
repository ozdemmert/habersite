<?php

require_once 'include/config.php';
require_once 'include/functions.php';

// Hafıza ID'sini al
$hafiza_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Hafıza objesi oluştur
$hafizaObj = new Hafiza();
$categoryObj = new Category();

// Hafıza içeriğini getir
$hafiza = $hafizaObj->getById($hafiza_id);

// İçerik bulunamadıysa ana sayfaya yönlendir
if (!$hafiza) {
    header('Location: hafiza.php');
    exit;
}

// Sayfa görüntüleme sayısını artır
$views = (int)$hafiza['views'] + 1;
mysqli_query($conn, "UPDATE hafiza SET views = $views WHERE id = $hafiza_id");

// İlgili içerikleri getir (aynı kategoriden)
$related_content = [];
if (!empty($hafiza['category'])) {
    $category = mysqli_real_escape_string($conn, $hafiza['category']);
    $sql = "SELECT * FROM hafiza WHERE category = '{$category}' AND id != {$hafiza_id} LIMIT 3";
    $result = mysqli_query($conn, $sql);
    if ($result) {
        $related_content = mysqli_fetch_all($result, MYSQLI_ASSOC);
    }
}

?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($hafiza['title']); ?> - Hafıza</title>
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
    </style>
</head>
<body class="bg-white">
<?php require_once 'templates/header.php'; ?>
    <main class="py-6">
        <div class="max-w-5xl mx-auto px-4">
            <!-- Breadcrumb -->
            <nav class="text-sm text-gray-500 mb-2" aria-label="Breadcrumb">
                <ol class="list-none p-0 inline-flex">
                    <li class="flex items-center">
                        <a href="index.php" class="hover:text-[#f39200]">Ana Sayfa</a>
                        <span class="mx-2">/</span>
                    </li>
                    <li class="flex items-center">
                        <a href="hafiza.php" class="hover:text-[#f39200]">Hafıza</a>
                        <span class="mx-2">/</span>
                    </li>
                    <li class="flex items-center">
                        <span class="text-[#022d5a]"><?php echo htmlspecialchars($hafiza['title']); ?></span>
                    </li>
                </ol>
            </nav>
            
            <!-- Hafıza Detay -->
            <div class="mb-10">
                <!-- Kategori ve Tarih -->
                <div class="flex flex-wrap gap-2 mb-3">
                    <a href="hafiza.php?cat=<?php echo urlencode($hafiza['category']); ?>" 
                       class="px-3 py-1 rounded-full text-xs font-semibold bg-[#022d5a] text-white">
                        <?php echo htmlspecialchars($hafiza['category']); ?>
                    </a>
                    <span class="text-[#f39200] text-sm font-medium">
                        <?php echo date('d F Y', strtotime($hafiza['created_at'])); ?>
                    </span>
                </div>
                
                <!-- Başlık -->
                <h1 class="text-3xl font-bold mb-6 text-[#022d5a]"><?php echo htmlspecialchars($hafiza['title']); ?></h1>
                
                <!-- Featured Image -->
                <?php if (!empty($hafiza['featured_image'])): ?>
                <div class="mb-8 overflow-hidden rounded-lg shadow-md">
                    <img src="<?php echo $hafiza['featured_image']; ?>" 
                         alt="<?php echo htmlspecialchars($hafiza['title']); ?>" 
                         class="w-full object-cover max-h-[500px]">
                </div>
                <?php endif; ?>
                
                <!-- İçerik -->
                <div class="prose content max-w-none text-gray-700 mb-10">
                    <?php echo $hafiza['content']; ?>
                </div>
                
                <!-- Paylaşım Linkleri -->
                <div class="flex items-center space-x-4 border-t border-b border-gray-200 py-4 my-8">
                    <span class="font-medium text-gray-700">Paylaş:</span>
                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>" 
                       target="_blank" class="text-[#022d5a] hover:text-[#f39200]">
                        <i class="fab fa-facebook-f"></i> Facebook
                    </a>
                    <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>&text=<?php echo urlencode($hafiza['title']); ?>" 
                       target="_blank" class="text-[#022d5a] hover:text-[#f39200]">
                        <i class="fab fa-twitter"></i> Twitter
                    </a>
                    <a href="https://wa.me/?text=<?php echo urlencode($hafiza['title'] . ' ' . 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>" 
                       target="_blank" class="text-[#022d5a] hover:text-[#f39200]">
                        <i class="fab fa-whatsapp"></i> WhatsApp
                    </a>
                </div>
            </div>
            
            <!-- İlgili İçerikler -->
            <?php if (!empty($related_content)): ?>
            <div class="mb-16">
                <h2 class="text-2xl font-bold mb-6 border-b pb-2 text-[#022d5a]">İlgili İçerikler</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <?php foreach($related_content as $item): ?>
                    <div class="bg-white border rounded-lg shadow-sm hover:shadow-md transition overflow-hidden">
                        <a href="hafiza_detay.php?id=<?php echo $item['id']; ?>" class="block">
                            <div class="relative h-40 overflow-hidden">
                                <?php if (!empty($item['featured_image'])): ?>
                                    <img src="<?php echo $item['featured_image']; ?>" 
                                         alt="<?php echo htmlspecialchars($item['title']); ?>"
                                         class="w-full h-full object-cover">
                                <?php else: ?>
                                    <div class="w-full h-full flex items-center justify-center bg-gray-100 text-gray-400">
                                        <span>Görsel Yok</span>
                                    </div>
                                <?php endif; ?>
                                <span class="absolute top-2 left-2 bg-[#022d5a] text-white text-xs font-semibold px-2 py-1 rounded">
                                    <?php echo htmlspecialchars($item['category']); ?>
                                </span>
                            </div>
                            <div class="p-4">
                                <h3 class="font-semibold mb-2 text-[#022d5a]"><?php echo htmlspecialchars($item['title']); ?></h3>
                                <div class="flex justify-between items-center">
                                    <span class="text-xs text-[#f39200] font-medium"><?php echo date('d F Y', strtotime($item['created_at'])); ?></span>
                                    <span class="text-[#f39200] text-sm font-medium">Devamını Oku</span>
                                </div>
                            </div>
                        </a>
                    </div>
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