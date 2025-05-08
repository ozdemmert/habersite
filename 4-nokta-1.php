<?php
require_once 'include/config.php';
require_once 'include/functions.php';

$fourNokta = new FourNokta();
$category = new Category();

// Kategorileri çek
$categories = $category->getAll();

// Aktif kategori
$active_category = isset($_GET['cat']) ? (int) $_GET['cat'] : 0;

// Sayfalama ayarları
$per_page = 12;
$page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
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

function isActiveCat($cat_id, $active)
{
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

<body class="bg-white"> <?php // Changed background color to white ?> <?php require_once 'templates/header.php'; ?>
    <?php require_once 'templates/backtotopbutton.php'; ?> <?php // Added back to top button include ?>
    <main class="w-[1080px] m-auto py-6"> <?php // Changed main container width and padding ?>
        <div class="max-w-5xl mx-auto px-0 pl-4"> <?php // Adjusted inner container padding ?>
            <nav class="text-sm text-gray-500 mb-2" aria-label="Breadcrumb">
                <ol class="list-none p-0 inline-flex">
                    <li class="flex items-center">
                        <a href="index.php" class="hover:text-[#f39200]">Ana Sayfa</a>
                        <span class="mx-2">/</span>
                    </li>
                    <li class="flex items-center">
                        <span class="text-[#022d5a]">4 Nokta 1</span>
                    </li>
                </ol>
            </nav>
            <h1 class="text-2xl font-bold mb-4 text-[#022d5a]">4 Nokta 1</h1> <?php // Added page title ?>
            <section class="concept-section mb-8 pb-4 border-b border-gray-200">
                <?php // Adjusted section spacing and border ?>
                <div class="max-w-3xl">
                    <h2 class="section-title text-xl font-bold mb-4 text-[#022d5a]">Konsept</h2>
                    <?php // Styled section title ?>
                    <p class="text-sm text-gray-600 mb-4">4 Nokta 1, aynı konuya farklı uzmanların standartlardan 4
                        başlık haline ayrılmış bakış açıları ile tartışıldığı bir içerik formatıdır. Aynı konunun farklı
                        yönleriyle bütüncül bir şekilde ele alınmasını sağlar.                    
                    </p>
                </div>
            </section>
            <section class="current-topic mb-12">
                <h2 class="section-title text-xl font-bold mb-4 text-[#022d5a]">Bu Haftanın Konusu</h2>
                <?php // Styled section title ?> <?php if ($all_items && count($all_items) > 0): ?>
                    <div class="topic-header border-b border-gray-200 pb-6 mb-6">
                        <h3 class="topic-title text-xl font-bold mb-3 text-[#022d5a]">
                            <?php echo htmlspecialchars($all_items[0]['title']); ?>
                        </h3> <?php // Styled topic title ?>
                        <p class="topic-description text-gray-700 mb-6">
                            <?php echo htmlspecialchars($all_items[0]['explanation']); ?>
                        </p>
                    </div>
                    <div class="contributors-grid grid grid-cols-1 md:grid-cols-2 gap-6"> <?php // Adjusted gap ?>
                        <?php
                        $authors = explode(', ', $all_items[0]['authors']);
                        $authors_info = json_decode($all_items[0]['authors_info'], true);
                        $authors_comments = json_decode($all_items[0]['authors_comments'], true);
                        $authors_images = json_decode($all_items[0]['authors_image'], true);

                        for ($i = 0; $i < 4; $i++):
                            ?>
                            <div class="bg-white border rounded-lg shadow-sm hover:shadow-md transition overflow-hidden p-6">
                                <?php // Styled contributor card ?>
                                <div class="flex items-start space-x-4">
                                    <div class="flex-shrink-0"> <?php if (!empty($authors_images[$i])): ?> <img src="
                                                    <?php echo $authors_images[$i]; ?>" alt="
                                                    <?php echo htmlspecialchars($authors[$i]); ?>"
                                                class="h-16 w-16 rounded-full object-cover ring-2 ring-white"> <?php else: ?>
                                            <div
                                                class="h-16 w-16 rounded-full bg-gray-200 flex items-center justify-center ring-2 ring-white text-gray-400">
                                                <?php // Styled fallback image ?> <svg class="h-8 w-8" fill="none"
                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                </svg>
                                            </div> <?php endif; ?>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <h3 class="text-lg font-semibold text-[#022d5a]">
                                            <?php echo htmlspecialchars($authors[$i]); ?>
                                        </h3> <?php // Styled author name ?>
                                        <div class="mt-1 text-sm text-gray-500"> <?php echo $authors_info[$i]; ?> </div>
                                        <div class="mt-4 text-sm text-gray-700 prose prose-sm max-w-none">
                                            <?php echo $authors_comments[$i]; ?>
                                        </div>
                                    </div>
                                </div>
                            </div> <?php endfor; ?>
                    </div> <?php endif; ?>
            </section>
            <!-- Past Topics Section -->
            <section class="past-topics mb-12">
                <h2 class="section-title text-2xl font-bold mb-6 relative">
                    Geçmiş Konular
                    <span class="section-icon absolute bottom-0 right-0 text-gray-300">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </span>
                </h2>

                <!-- Kategori Filtreleri -->
                <div class="mb-8">
                    <div class="flex flex-wrap gap-2">
                        <button onclick="window.location.href='?'"
                            class="inline-flex items-center px-4 py-2 rounded-md text-sm font-medium <?php echo $active_category == 0 ? 'bg-[#022d5a] text-white' : 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-300'; ?> transition-colors duration-200">
                            Tümü
                        </button>
                        <?php foreach ($categories as $cat): ?>
                            <button onclick="window.location.href='?cat=<?php echo $cat['id']; ?>'"
                                class="inline-flex items-center px-4 py-2 rounded-md text-sm font-medium <?php echo isActiveCat($cat['id'], $active_category); ?> bg-white border border-gray-300 transition-colors duration-200">
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- İçerik Grid -->
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 mb-8">
                    <?php if ($all_items):
                        foreach (array_slice($all_items, 1) as $item): ?>
                            <div
                                class="group relative bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow overflow-hidden">
                                <a href="4-nokta-1-detay.php?id=<?php echo $item['id']; ?>" class="block">
                                    <div class="aspect-w-16 aspect-h-9 relative">
                                        <?php if (!empty($item['featured_image'])): ?>
                                            <img src="<?php echo $item['featured_image']; ?>"
                                                alt="<?php echo htmlspecialchars($item['title']); ?>"
                                                class="object-cover w-full h-48">
                                        <?php else: ?>
                                            <div class="w-full h-48 bg-gray-100 flex items-center justify-center">
                                                <svg class="h-12 w-12 text-gray-300" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                            </div>
                                        <?php endif; ?>
                                        <div class="absolute top-4 left-4">
                                            <span
                                                class="inline-flex items-center px-3 py-0.5 rounded-full text-sm font-medium bg-[#022d5a] text-white">
                                                <?php echo htmlspecialchars($item['category']); ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="p-4">
                                        <div class="flex items-center text-sm text-gray-500 mb-2">
                                            <svg class="mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                            <?php echo date('d F Y', strtotime($item['created_at'])); ?>
                                        </div>
                                        <h3
                                            class="text-lg font-semibold text-gray-900 line-clamp-2 group-hover:text-[#022d5a] transition-colors duration-200">
                                            <?php echo htmlspecialchars($item['title']); ?>
                                        </h3>
                                        <p class="mt-2 text-sm text-gray-500 line-clamp-3">
                                            <?php echo htmlspecialchars($item['explanation']); ?>
                                        </p>
                                        <div class="mt-4">
                                            <span
                                                class="inline-flex items-center text-sm font-medium text-[#f39200] group-hover:text-[#022d5a] transition-colors duration-200">
                                                Devamını Oku
                                                <svg class="ml-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M9 5l7 7-7 7" />
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
                                <a href="?<?php echo $active_category ? 'cat=' . $active_category . '&' : ''; ?>page=<?php echo $i; ?>"
                                    class="relative inline-flex items-center px-4 py-2 <?php echo $i == 1 ? 'rounded-l-md' : ''; ?> <?php echo $i == $total_pages ? 'rounded-r-md' : ''; ?> <?php echo $i == $page ? 'z-10 bg-[#022d5a] text-white' : 'bg-white text-gray-500 hover:bg-gray-50'; ?> border border-gray-300 text-sm font-medium transition-colors duration-200">
                                    <?php echo $i; ?>
                                </a>
                            <?php endfor; ?>
                        </nav>
                    </div>
                <?php endif; ?>
            </section>
        </div>
    </main> <?php require_once 'templates/footer.php'; ?>
</body>

</html>