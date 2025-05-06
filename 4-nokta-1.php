<?php
require_once 'include/config.php';
require_once 'include/functions.php';

$fourNokta = new FourNokta();
$category = new Category();

// Kategorileri çek
$categories = $category->getAll();

// Aktif kategori
$active_category = isset($_GET['cat']) ? (int)$_GET['cat'] : 0;

// Sayfalama ayarları
$per_page = 12;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $per_page;

// 4 Nokta 1 içeriklerini çek
if ($active_category) {
    $all_items = [];
    $sql = "SELECT * FROM 4nokta1 WHERE category = (SELECT name FROM categories WHERE id = $active_category) ORDER BY created_at DESC LIMIT $per_page OFFSET $offset";
    $result = mysqli_query($conn, $sql);
    $all_items = mysqli_fetch_all($result, MYSQLI_ASSOC);
    
    $sql = "SELECT COUNT(*) as total FROM 4nokta1 WHERE category = (SELECT name FROM categories WHERE id = $active_category)";
    $count_result = mysqli_query($conn, $sql);
    $total = mysqli_fetch_assoc($count_result)['total'];
} else {
    $sql = "SELECT * FROM 4nokta1 ORDER BY created_at DESC LIMIT $per_page OFFSET $offset";
    $result = mysqli_query($conn, $sql);
    $all_items = mysqli_fetch_all($result, MYSQLI_ASSOC);
    
    $sql = "SELECT COUNT(*) as total FROM 4nokta1";
    $count_result = mysqli_query($conn, $sql);
    $total = mysqli_fetch_assoc($count_result)['total'];
}

$total_pages = ceil($total / $per_page);

function isActiveCat($cat_id, $active) {
    return $cat_id == $active ? 'bg-[#022d5a] text-white' : 'hover:bg-gray-100 text-gray-700';
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>4 Nokta 1</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
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
<body class="bg-gray-50">
<?php require_once 'templates/header.php'; ?>
    <main class="min-h-screen py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Breadcrumb -->
            <nav class="flex mb-8" aria-label="Breadcrumb">
                <ol class="flex items-center space-x-2">
                    <li>
                        <a href="index.php" class="text-sm font-medium text-gray-500 hover:text-gray-700">Ana Sayfa</a>
                    </li>
                    <li>
                        <svg class="h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                        </svg>
                    </li>
                    <li>
                        <span class="text-sm font-medium text-[#022d5a]">4 Nokta 1</span>
                    </li>
                </ol>
            </nav>

            <!-- Başlık ve Açıklama -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900 sm:text-4xl">4 Nokta 1</h1>
                <p class="mt-4 max-w-3xl text-lg text-gray-500">
                    4 Nokta 1, aynı konuya farklı uzmanların standartlardan 4 başlık haline ayrılmış bakış açıları ile tartışıldığı bir içerik formatıdır. Aynı konunun farklı yönleriyle bütüncül bir şekilde ele alınmasını sağlar.
                </p>
            </div>

            <!-- Kategori Filtreleri -->
            <div class="mb-8">
                <div class="flex flex-wrap gap-2">
                    <button onclick="window.location.href='?'" class="inline-flex items-center px-4 py-2 rounded-md text-sm font-medium <?php echo $active_category == 0 ? 'bg-[#022d5a] text-white' : 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-300'; ?> transition-colors duration-200">
                        Tümü
                    </button>
                    <?php foreach($categories as $cat): ?>
                        <button onclick="window.location.href='?cat=<?php echo $cat['id']; ?>'" class="inline-flex items-center px-4 py-2 rounded-md text-sm font-medium <?php echo isActiveCat($cat['id'], $active_category); ?> bg-white border border-gray-300 transition-colors duration-200">
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- İçerik Grid -->
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 mb-8">
                <?php if ($all_items): foreach($all_items as $item): ?>
                <div class="group relative bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow overflow-hidden">
                    <a href="4-nokta-1-detay.php?id=<?php echo $item['id']; ?>" class="block">
                        <div class="aspect-w-16 aspect-h-9 relative">
                            <?php if (!empty($item['featured_image'])): ?>
                                <img src="<?php echo $item['featured_image']; ?>" alt="<?php echo htmlspecialchars($item['title']); ?>" class="object-cover w-full h-48">
                            <?php else: ?>
                                <div class="w-full h-48 bg-gray-100 flex items-center justify-center">
                                    <svg class="h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                            <?php endif; ?>
                            <div class="absolute top-4 left-4">
                                <span class="inline-flex items-center px-3 py-0.5 rounded-full text-sm font-medium bg-[#022d5a] text-white">
                                    <?php echo htmlspecialchars($item['category']); ?>
                                </span>
                            </div>
                        </div>
                        <div class="p-4">
                            <div class="flex items-center text-sm text-gray-500 mb-2">
                                <svg class="mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <?php echo date('d F Y', strtotime($item['created_at'])); ?>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900 line-clamp-2 group-hover:text-[#022d5a] transition-colors duration-200">
                                <?php echo htmlspecialchars($item['title']); ?>
                            </h3>
                            <p class="mt-2 text-sm text-gray-500 line-clamp-3">
                                <?php echo htmlspecialchars($item['explanation']); ?>
                            </p>
                            <div class="mt-4">
                                <span class="inline-flex items-center text-sm font-medium text-[#f39200] group-hover:text-[#022d5a] transition-colors duration-200">
                                    Devamını Oku
                                    <svg class="ml-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </span>
                            </div>
                        </div>
                    </a>
                </div>
                <?php endforeach; else: ?>
                    <div class="col-span-full flex items-center justify-center py-12 text-gray-500 text-lg">
                        Bu kategoride içerik bulunamadı.
                    </div>
                <?php endif; ?>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
            <div class="flex justify-center">
                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="?<?php echo $active_category ? 'cat='.$active_category.'&' : ''; ?>page=<?php echo $i; ?>" 
                           class="relative inline-flex items-center px-4 py-2 <?php echo $i == 1 ? 'rounded-l-md' : ''; ?> <?php echo $i == $total_pages ? 'rounded-r-md' : ''; ?> <?php echo $i == $page ? 'z-10 bg-[#022d5a] text-white' : 'bg-white text-gray-500 hover:bg-gray-50'; ?> border border-gray-300 text-sm font-medium transition-colors duration-200">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                </nav>
            </div>
            <?php endif; ?>
        </div>
    </main>
    <?php require_once 'templates/footer.php'; ?>
</body>
</html> 