<?php
require_once 'include/config.php';
require_once 'include/functions.php';

// Dosya ID'sini al
$dosya_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Dosya objesi oluştur
$dosyaObj = new Dosya();
$categoryObj = new Category();

// Dosya detaylarını getir
$dosya = $dosyaObj->getById($dosya_id);

// Dosya bulunamadıysa anasayfaya yönlendir
if (!$dosya) {
    header('Location: index.php');
    exit;
}

// Kategoriler
$categories = $categoryObj->getAll();

// İlgili dosyaları getir (aynı kategorideki diğer dosyalar)
$related_dosya = [];
$sql = "SELECT d.* FROM dosya d 
        JOIN dosya_category dc ON d.id = dc.dosya_id 
        JOIN categories c ON dc.category_id = c.id 
        WHERE c.name = '{$dosya['category']}' AND d.id != {$dosya_id} 
        ORDER BY d.created_at DESC LIMIT 3";
$result = mysqli_query($conn, $sql);
if ($result) {
    $related_dosya = mysqli_fetch_all($result, MYSQLI_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($dosya['title']); ?> - Dosya Detay</title>
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
                        <a href="dosya.php" class="hover:text-[#f39200]">Dosya</a>
                        <span class="mx-2">/</span>
                    </li>
                    <li class="flex items-center">
                        <span class="text-[#022d5a]"><?php echo htmlspecialchars($dosya['title']); ?></span>
                    </li>
                </ol>
            </nav>
            
            <!-- Dosya Detay -->
            <div class="mb-10">
                <!-- Kategori ve Tarih -->
                <div class="flex flex-wrap gap-2 mb-3">
                    <a href="dosya.php?cat=<?php echo urlencode($dosya['category']); ?>" 
                       class="px-3 py-1 rounded-full text-xs font-semibold bg-[#022d5a] text-white">
                        <?php echo htmlspecialchars($dosya['category']); ?>
                    </a>
                    <span class="text-[#f39200] text-sm font-medium">
                        <?php echo date('d F Y', strtotime($dosya['created_at'])); ?>
                    </span>
                </div>
                
                <!-- Başlık -->
                <h1 class="text-3xl font-bold mb-6 text-[#022d5a]"><?php echo htmlspecialchars($dosya['title']); ?></h1>
                
                <!-- Featured Image -->
                <?php if (!empty($dosya['featured_image'])): ?>
                <div class="mb-8 overflow-hidden rounded-lg shadow-md">
                    <img src="<?php echo $dosya['featured_image']; ?>" 
                         alt="<?php echo htmlspecialchars($dosya['title']); ?>" 
                         class="w-full object-cover max-h-[500px]">
                </div>
                <?php endif; ?>
                
                <!-- İçerik -->
                <div class="prose content max-w-none text-gray-700 mb-10">
                    <?php echo $dosya['content']; ?>
                </div>
                
                <!-- Paylaşım Linkleri -->
                <div class="flex items-center space-x-4 border-t border-b border-gray-200 py-4 my-8">
                    <span class="font-medium text-gray-700">Paylaş:</span>
                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>" 
                       target="_blank" class="text-[#022d5a] hover:text-[#f39200]">
                        <i class="fab fa-facebook-f"></i> Facebook
                    </a>
                    <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>&text=<?php echo urlencode($dosya['title']); ?>" 
                       target="_blank" class="text-[#022d5a] hover:text-[#f39200]">
                        <i class="fab fa-twitter"></i> Twitter
                    </a>
                    <a href="https://wa.me/?text=<?php echo urlencode($dosya['title'] . ' ' . 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>" 
                       target="_blank" class="text-[#022d5a] hover:text-[#f39200]">
                        <i class="fab fa-whatsapp"></i> WhatsApp
                    </a>
                </div>
            </div>
            
            <!-- İlgili Dosyalar -->
            <?php if (!empty($related_dosya)): ?>
            <div class="mb-16">
                <h2 class="text-2xl font-bold mb-6 border-b pb-2 text-[#022d5a]">İlgili Dosyalar</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <?php foreach($related_dosya as $item): ?>
                    <div class="bg-white border rounded-lg shadow-sm hover:shadow-md transition overflow-hidden flex flex-col h-full">
                        <a href="dosya_detay.php?id=<?php echo $item['id']; ?>" class="flex flex-col h-full">
                            <div class="relative h-48 w-full overflow-hidden">
                                <?php if (!empty($item['featured_image'])): ?>
                                    <img src="<?php echo $item['featured_image']; ?>" 
                                         alt="<?php echo htmlspecialchars($item['title']); ?>" 
                                         class="object-cover w-full h-full">
                                <?php else: ?>
                                    <div class="bg-gray-100 w-full h-full flex items-center justify-center text-gray-400">Görsel Yok</div>
                                <?php endif; ?>
                                <span class="absolute top-3 left-3 px-3 py-1 text-xs font-bold rounded bg-[#022d5a] text-white z-10">
                                    <?php echo htmlspecialchars($item['category']); ?>
                                </span>
                            </div>
                            <div class="flex-1 flex flex-col p-4">
                                <span class="text-xs text-[#f39200] font-medium mb-1">
                                    <?php echo date('d F Y', strtotime($item['created_at'])); ?>
                                </span>
                                <h3 class="font-semibold text-base mb-2 line-clamp-2 text-[#022d5a]">
                                    <?php echo htmlspecialchars($item['title']); ?>
                                </h3>
                                <div class="mt-auto">
                                    <span class="text-sm text-[#f39200] font-semibold hover:underline">Devamını Oku &rarr;</span>
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