<?php

require_once 'include/config.php';
require_once 'include/functions.php';

$dosyaObj = new Dosya();
$categoryObj = new Category();

// Kategorileri çek
$categories = $categoryObj->getAll();

// Aktif kategori
$active_category = isset($_GET['cat']) ? (int)$_GET['cat'] : 0;

// Sayfalama ayarları
$per_page = 9;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $per_page;

// Dosya çekme
if ($active_category) {
    $all_dosya = [];
    $dosya_ids = [];
    $sql = "SELECT dosya_id FROM dosya_category WHERE category_id = $active_category";
    $result = mysqli_query($conn, $sql);
    while ($row = mysqli_fetch_assoc($result)) {
        $dosya_ids[] = $row['dosya_id'];
    }
    if (!empty($dosya_ids)) {
        $ids = implode(',', $dosya_ids);
        $sql = "SELECT * FROM dosya WHERE id IN ($ids) ORDER BY created_at DESC LIMIT $per_page OFFSET $offset";
        $dosya_result = mysqli_query($conn, $sql);
        $all_dosya = mysqli_fetch_all($dosya_result, MYSQLI_ASSOC);
        
        $sql = "SELECT COUNT(*) as total FROM dosya WHERE id IN ($ids)";
        $count_result = mysqli_query($conn, $sql);
        $total = mysqli_fetch_assoc($count_result)['total'];
    } else {
        $all_dosya = [];
        $total = 0;
    }
} else {
    $sql = "SELECT * FROM dosya ORDER BY created_at DESC LIMIT $per_page OFFSET $offset";
    $dosya_result = mysqli_query($conn, $sql);
    $all_dosya = mysqli_fetch_all($dosya_result, MYSQLI_ASSOC);
    $sql = "SELECT COUNT(*) as total FROM dosya";
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
    <title>Dosyalarımız</title>
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
                        <span class="text-[#022d5a]">Dosya</span>
                    </li>
                </ol>
            </nav>
            <!-- Sayfa Başlığı -->
            <h1 class="text-2xl font-bold mb-2 text-[#022d5a]">Dosyalarımız</h1>
            <!-- Kategori Sekmeleri -->
            <div class="flex flex-wrap gap-2 mb-6">
                <a href="?" class="px-4 py-1 rounded-full font-semibold <?php echo $active_category == 0 ? 'bg-[#022d5a] text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-100'; ?>">Tümü</a>
                <?php foreach($categories as $cat): ?>
                    <a href="?cat=<?php echo $cat['id']; ?>" class="px-4 py-1 rounded-full font-semibold <?php echo isActiveCat($cat['id'], $active_category); ?>"><?php echo htmlspecialchars($cat['name']); ?></a>
                <?php endforeach; ?>
            </div>
            <!-- Dosya Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                <?php if ($all_dosya): foreach($all_dosya as $dosya): ?>
                <div class="bg-white border rounded-lg shadow-sm hover:shadow-md transition overflow-hidden flex flex-col h-full">
                    <a href="dosya_detay.php?id=<?php echo $dosya['id']; ?>" class="flex flex-col h-full">
                        <div class="relative h-48 w-full overflow-hidden">
                            <?php if (!empty($dosya['featured_image'])): ?>
                                <img src="<?php echo $dosya['featured_image']; ?>" alt="<?php echo htmlspecialchars($dosya['title']); ?>" class="object-cover w-full h-full">
                            <?php else: ?>
                                <div class="bg-gray-100 w-full h-full flex items-center justify-center text-gray-400">Görsel Yok</div>
                            <?php endif; ?>
                            <span class="absolute top-3 left-3 px-3 py-1 text-xs font-bold rounded bg-[#022d5a] text-white z-10">
                                <?php echo htmlspecialchars($dosya['category']); ?>
                            </span>
                        </div>
                        <div class="flex-1 flex flex-col p-4">
                            <span class="text-xs text-[#f39200] font-medium mb-1"><?php echo date('d F Y', strtotime($dosya['created_at'])); ?></span>
                            <h3 class="font-semibold text-base mb-2 line-clamp-2 text-[#022d5a]"><?php echo htmlspecialchars($dosya['title']); ?></h3>
                            <p class="text-sm text-gray-600 mb-3 line-clamp-3"><?php echo isset($dosya['content']) ? mb_substr(strip_tags($dosya['content']),0,120).'...' : ''; ?></p>
                            <div class="mt-auto">
                                <span class="text-sm text-[#f39200] font-semibold hover:underline">Devamını Oku &rarr;</span>
                            </div>
                        </div>
                    </a>
                </div>
                <?php endforeach; else: ?>
                    <div class="col-span-3 text-center text-gray-500">Bu kategoride dosya bulunamadı.</div>
                <?php endif; ?>
            </div>
            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
            <div class="flex justify-center gap-1 mb-8">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?<?php echo $active_category ? 'cat='.$active_category.'&' : ''; ?>page=<?php echo $i; ?>" class="px-3 py-1 rounded border <?php echo $i == $page ? 'bg-[#022d5a] text-white border-[#022d5a]' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-100'; ?> font-semibold">
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