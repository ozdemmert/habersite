<?php
require_once 'include/config.php';
require_once 'include/functions.php';

$fourNokta = new FourNokta();

// ID kontrolü
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: 4-nokta-1.php');
    exit();
}

$id = (int) $_GET['id'];
$item = $fourNokta->getById($id);

if (!$item) {
    header('Location: 4-nokta-1.php');
    exit();
}

// Yazarları JSON'dan çöz
$authors = explode(', ', $item['authors']);
$authors_info = json_decode($item['authors_info'], true);
$authors_comments = json_decode($item['authors_comments'], true);
$authors_images = json_decode($item['authors_image'], true);
?>
<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($item['title']); ?> - 4 Nokta 1</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
       
        .prose {
            max-width: 65ch;
            color: #374151;
        }
        .prose p {
            margin-top: 1.25em;
            margin-bottom: 1.25em;
        }
        .prose h2 {
            color: #111827;
            font-weight: 700;
            font-size: 1.5em;
            margin-top: 2em;
            margin-bottom: 1em;
            line-height: 1.3333333;
        }
        .prose h3 {
            color: #111827;
            font-weight: 600;
            font-size: 1.25em;
            margin-top: 1.6em;
            margin-bottom: 0.6em;
            line-height: 1.6;
        }
        .prose ul {
            margin-top: 1.25em;
            margin-bottom: 1.25em;
            padding-left: 1.625em;
            list-style-type: disc;
        }
        .prose ol {
            margin-top: 1.25em;
            margin-bottom: 1.25em;
            padding-left: 1.625em;
            list-style-type: decimal;
        }
        .prose li {
            margin-top: 0.5em;
            margin-bottom: 0.5em;
        }
        .prose blockquote {
            font-weight: 500;
            font-style: italic;
            color: #111827;
            border-left-width: 0.25rem;
            border-left-color: #e5e7eb;
            quotes: "\201C""\201D""\2018""\2019";
            margin-top: 1.6em;
            margin-bottom: 1.6em;
            padding-left: 1em;
        }
        .prose blockquote p:first-of-type::before {
            content: open-quote;
        }
        .prose blockquote p:last-of-type::after {
            content: close-quote;
        }
    </style>
</head>

<body class="bg-gray-50">
    <?php require_once 'templates/header.php'; ?>
    <?php require_once 'templates/backtotopbutton.php'; ?>
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
                            <path fill-rule="evenodd"
                                d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                clip-rule="evenodd" />
                        </svg>
                    </li>
                    <li>
                        <a href="4-nokta-1.php" class="text-sm font-medium text-gray-500 hover:text-gray-700">4 Nokta
                            1</a>
                    </li>
                    <li>
                        <svg class="h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                clip-rule="evenodd" />
                        </svg>
                    </li>
                    <li>
                        <span
                            class="text-sm font-medium text-[#022d5a]"><?php echo htmlspecialchars($item['title']); ?></span>
                    </li>
                </ol>
            </nav>

            <article class="bg-white shadow-sm rounded-lg overflow-hidden">
                <!-- Başlık Bölümü -->
                <div class="px-8 py-6 border-b border-gray-200">
                    <div class="flex items-center space-x-2 text-sm text-gray-500 mb-4">
                        <span
                            class="inline-flex items-center px-3 py-0.5 rounded-full text-sm font-medium bg-[#022d5a] text-white">
                            <?php echo htmlspecialchars($item['category']); ?>
                        </span>
                        <span class="flex items-center">
                            <svg class="mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <?php echo date('d F Y', strtotime($item['created_at'])); ?>
                        </span>
                    </div>
                    <h1 class="text-3xl font-bold text-gray-900 mb-4"><?php echo htmlspecialchars($item['title']); ?>
                    </h1>
                    <p class="text-lg text-gray-500"><?php echo htmlspecialchars($item['explanation']); ?></p>
                </div>

                <!-- Öne Çıkan Görsel -->
                <?php if (!empty($item['featured_image'])): ?>
                    <div class="relative">
                        <img src="<?php echo $item['featured_image']; ?>"
                            alt="<?php echo htmlspecialchars($item['title']); ?>" class="w-full h-[400px] object-cover">
                    </div>
                <?php endif; ?>

                <!-- Yazarlar Grid -->
                <div class="px-8 py-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <?php for ($i = 0; $i < 4; $i++): ?>
                            <div class="bg-gray-50 rounded-lg p-6">
                                <div class="flex items-start space-x-4">
                                    <div class="flex-shrink-0">
                                        <?php if (!empty($authors_images[$i])): ?>
                                            <img src="<?php echo $authors_images[$i]; ?>"
                                                alt="<?php echo htmlspecialchars($authors[$i]); ?>"
                                                class="h-16 w-16 rounded-full object-cover ring-2 ring-white">
                                        <?php else: ?>
                                            <div
                                                class="h-16 w-16 rounded-full bg-gray-200 flex items-center justify-center ring-2 ring-white">
                                                <svg class="h-8 w-8 text-gray-400" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                </svg>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <h3 class="text-lg font-semibold text-gray-900">
                                            <?php echo htmlspecialchars($authors[$i]); ?></h3>
                                        <div class="mt-1 text-sm text-gray-500">
                                            <?php echo $authors_info[$i]; ?>
                                        </div>
                                        <div class="mt-4 text-sm text-gray-700 prose prose-sm max-w-none">
                                            <?php echo $authors_comments[$i]; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endfor; ?>
                    </div>
                </div>

                <!-- Meta Açıklama -->
                <?php if (!empty($item['meta_description'])): ?>
                    <div class="px-8 py-6 bg-gray-50 border-t border-gray-200">
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">Özet</h2>
                        <div class="prose prose-lg max-w-none text-gray-500">
                            <?php echo htmlspecialchars($item['meta_description']); ?>
                        </div>
                    </div>
                <?php endif; ?>
            </article>

            <!-- İlgili İçerikler -->
            <?php
            // İlgili içerikleri getir (aynı kategoriden)
            $related_content = [];
            if (!empty($item['category'])) {
                $category = mysqli_real_escape_string($conn, $item['category']);
                $sql = "SELECT * FROM 4nokta1 WHERE category = '{$category}' AND id != {$id} LIMIT 3";
                $result = mysqli_query($conn, $sql);
                if ($result) {
                    $related_content = mysqli_fetch_all($result, MYSQLI_ASSOC);
                }
            }
            ?>

            <?php if (!empty($related_content)): ?>
            <div class="mt-12">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">İlgili İçerikler</h2>
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <?php foreach($related_content as $related): ?>
                    <div class="group relative bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow overflow-hidden">
                        <a href="4-nokta-1-detay.php?id=<?php echo $related['id']; ?>" class="block">
                            <div class="aspect-w-16 aspect-h-9 relative">
                                <?php if (!empty($related['featured_image'])): ?>
                                    <img src="<?php echo $related['featured_image']; ?>" alt="<?php echo htmlspecialchars($related['title']); ?>" class="object-cover w-full h-48">
                                <?php else: ?>
                                    <div class="w-full h-48 bg-gray-100 flex items-center justify-center">
                                        <svg class="h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="p-4">
                                <h3 class="text-lg font-semibold text-gray-900 line-clamp-2 group-hover:text-[#022d5a] transition-colors duration-200">
                                    <?php echo htmlspecialchars($related['title']); ?>
                                </h3>
                                <p class="mt-2 text-sm text-gray-500 line-clamp-3">
                                    <?php echo htmlspecialchars($related['explanation']); ?>
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
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </main>
    <?php require_once 'templates/footer.php'; ?>
</body>

</html>