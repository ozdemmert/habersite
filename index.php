<?php

require_once 'include/functions.php';

// Özet oluşturma yardımcı fonksiyonu
function createSummary($content, $length = 150) {
    if (empty($content)) return '';
    
    // HTML etiketlerini kaldır
    $text = strip_tags($content);
    // Gereksiz boşlukları temizle
    $text = preg_replace('/\s+/', ' ', $text);
    // Belirtilen uzunlukta kes
    $summary = mb_substr($text, 0, $length, 'UTF-8');
    
    // Son kelimeyi tamamla
    if (mb_strlen($text) > $length) {
        $lastSpace = mb_strrpos($summary, ' ', 0, 'UTF-8');
        if ($lastSpace !== false) {
            $summary = mb_substr($summary, 0, $lastSpace, 'UTF-8');
        }
        $summary .= '...';
    }
    
    return $summary;
}

$newsObj = new News($conn);
$sectionObj = new Section($conn);

// Get section data
$sections = $sectionObj->getAllGrouped();

// Get featured news
$featuredNews = $newsObj->getAll(['is_featured' => 1], 3);

// Get latest news
$latestNews = $newsObj->getAll([], 6);

// Get story news
$storyNews = [];
if (!empty($sections['story1'])) $storyNews[] = $newsObj->getById($sections['story1']);
if (!empty($sections['story2'])) $storyNews[] = $newsObj->getById($sections['story2']);
if (!empty($sections['story3'])) $storyNews[] = $newsObj->getById($sections['story3']);

// Get main news
$mainNews1 = !empty($sections['main1']) ? $newsObj->getById($sections['main1']) : null;
$mainNews2 = !empty($sections['main2']) ? $newsObj->getById($sections['main2']) : null;

// Get YouTube video URL
$youtube_url = isset($sections['youtube_video']) ? $sections['youtube_video'] : '';
$video_id = '';
if ($youtube_url) {
    // Extract video ID from URL
    preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $youtube_url, $matches);
    if (isset($matches[1])) {
        $video_id = $matches[1];
    }
}

// Get latest biography (portre)
$portreObj = new Portre($conn);
$latestPortre = $portreObj->getLatest();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gazete BanDor</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
   
       
</head>
<body class="bg-white w-[1080px] m-auto">
<?php require_once 'templates/header.php'; ?>
    <main class="min-h-screen py-4">
        <div class="content-wrapper">
            <!-- Featured Stories Grid -->
            <div class="grid grid-cols-3 gap-3 mb-12">
                <?php foreach($storyNews as $index => $news): ?>
                <div class="relative h-[400px] overflow-hidden group rounded-lg shadow-md">
                    <a href="news.php?id=<?php echo $news['id']; ?>">
                        <img src="<?php echo !empty($news['featured_image']) ? $news['featured_image'] : 'https://picsum.photos/seed/'.$index.'/800/1600'; ?>" 
                             alt="<?php echo htmlspecialchars($news['title']); ?>" 
                             class="w-full h-full object-cover transition duration-500 group-hover:scale-105">
                        <div class="absolute inset-0 bg-gradient-to-t from-[#022d5a] to-transparent opacity-70"></div>
                        <div class="absolute top-3 left-3">
                            <span class="category-badge accent-bg"><?php echo htmlspecialchars($news['category']); ?></span>
                        </div>
                        <div class="absolute bottom-0 left-0 p-4 w-full text-white">
                            <h2 class="text-base font-bold line-clamp-3 group-hover:text-[#f39200] transition duration-200">
                                <?php echo htmlspecialchars($news['title']); ?>
                            </h2>
                            <div class="text-xs mt-2"><?php echo date('d.m.Y', strtotime($news['created_at'])); ?></div>
                        </div>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Main Story -->
            <?php if ($mainNews1): ?>
            <div class="mb-16 border-t border-gray-200 pt-8">
                <div class="flex flex-col">
                    <span class="text-xs font-semibold accent-color uppercase"><?php echo htmlspecialchars($mainNews1['category']); ?></span>
                    <h1 class="text-2xl font-bold mt-1 mb-2 primary-color">
                        <a href="news.php?id=<?php echo $mainNews1['id']; ?>" class="hover:accent-color transition-colors duration-200">
                            <?php echo htmlspecialchars($mainNews1['title']); ?>
                        </a>
                    </h1>
                    <p class="text-sm text-gray-700 mb-4"><?php echo htmlspecialchars(isset($mainNews1['summary']) ? $mainNews1['summary'] : createSummary($mainNews1['content'])); ?></p>
                    
                    <!-- Main Story Image -->
                    <div class="mb-8">
                        <a href="news.php?id=<?php echo $mainNews1['id']; ?>">
                            <img src="<?php echo !empty($mainNews1['featured_image']) ? $mainNews1['featured_image'] : 'https://picsum.photos/seed/tech/1200/600'; ?>" 
                                 alt="<?php echo htmlspecialchars($mainNews1['title']); ?>" 
                                 class="w-full h-auto rounded-lg shadow-lg">
                        </a>
                    </div>
                </div>
                
                <?php if ($mainNews2): ?>
                <!-- Three Column Related Stories -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-8">
                    <div class="border border-gray-200 p-4 text-center rounded-lg hover:shadow-md transition-all duration-300">
                        <a href="news.php?id=<?php echo $mainNews2['id']; ?>" class="hover:accent-color transition-colors duration-200">
                            <p class="text-sm font-medium primary-color"><?php echo htmlspecialchars($mainNews2['title']); ?></p>
                            <p class="text-xs accent-color mt-1"><?php echo htmlspecialchars($mainNews2['category']); ?> hakkında daha fazla</p>
                        </a>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
                
                <!-- Biography (Portre) Section -->
                <?php if (isset($latestPortre) && !empty($latestPortre)): ?>
                <div class="mb-12 border-t border-gray-200 pt-8">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-xl font-bold primary-color">Son Eklenen Portre</h2>
                        <span class="text-xs text-white primary-bg px-2 py-1 rounded-md">Biyografi</span>
                    </div>
                    <div class="bg-white shadow-lg rounded-lg overflow-hidden border border-gray-100">
                        <div class="md:flex">
                            <div class="md:w-1/3">
                                <img src="<?php echo !empty($latestPortre['portre_image']) ? $latestPortre['portre_image'] : 'https://picsum.photos/seed/portrait/600/800'; ?>" 
                                     alt="<?php echo htmlspecialchars($latestPortre['first_name'] . ' ' . $latestPortre['lastname']); ?>" 
                                     class="w-full h-full object-cover">
                            </div>
                            <div class="p-6 md:w-2/3">
                                <h2 class="text-2xl font-bold mb-2 primary-color"><?php echo htmlspecialchars($latestPortre['first_name'] . ' ' . $latestPortre['lastname']); ?></h2>
                                <div class="text-sm text-gray-600 mb-4">
                                    <?php if (!empty($latestPortre['degree'])): ?>
                                    <span class="mr-3 font-medium accent-color"><?php echo htmlspecialchars($latestPortre['degree']); ?></span>
                                    <?php endif; ?>
                                    <?php if (!empty($latestPortre['quote'])): ?>
                                    <div class="quote-block mt-2 italic text-gray-600 border-l-4 pl-3 border-accent-color">
                                        "<?php echo htmlspecialchars($latestPortre['quote']); ?>"
                                    </div>
                                    <?php endif; ?>
                                </div>
                                <p class="text-gray-700 mb-4"><?php echo htmlspecialchars(createSummary($latestPortre['biography'], 300)); ?></p>
                                <a href="portre_detay.php?id=<?php echo $latestPortre['id']; ?>" class="inline-block px-4 py-2 primary-bg text-white font-medium rounded hover:bg-opacity-90 transition-colors">
                                    Devamını Oku
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- YouTube Video Section -->
                <?php if ($video_id): ?>
                <div class="mb-12 border-t border-gray-200 pt-8">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-xl font-bold primary-color">Video İçerik</h2>
                        <span class="text-xs text-white accent-bg px-2 py-1 rounded-md"><i class="fab fa-youtube mr-1"></i>YouTube</span>
                    </div>
                    <div class="bg-white shadow-lg rounded-lg overflow-hidden border border-gray-100">
                        <div class="relative pb-[56.25%] h-0 overflow-hidden">
                            <iframe 
                                class="absolute top-0 left-0 w-full h-full"
                                src="https://www.youtube.com/embed/<?php echo $video_id; ?>?autoplay=0&rel=0"
                                title="YouTube video player"
                                frameborder="0"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                allowfullscreen>
                            </iframe>
                        </div>
                        <div class="p-4 border-t border-gray-100">
                            <p class="text-gray-500 text-sm accent-color font-medium">En güncel ve öne çıkan video içerikler burada</p>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            <!-- Öne Çıkan Haberler -->
            <div class="mb-16 border-t border-gray-200 pt-8">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-bold primary-color">Öne Çıkan Haberler</h2>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <?php foreach($featuredNews as $news): ?>
                    <div class="bg-white shadow rounded overflow-hidden transition-all duration-300 hover:shadow-lg border border-gray-100">
                        <a href="news.php?id=<?php echo $news['id']; ?>">
                            <div class="relative">
                                <img src="<?php echo !empty($news['featured_image']) ? $news['featured_image'] : 'https://picsum.photos/seed/featured'.$news['id'].'/600/400'; ?>" 
                                     alt="<?php echo htmlspecialchars($news['title']); ?>" 
                                     class="w-full h-48 object-cover">
                                <div class="absolute top-2 left-2">
                                    <span class="category-badge primary-bg"><?php echo htmlspecialchars($news['category']); ?></span>
                                </div>
                            </div>
                            <div class="p-4">
                                <h3 class="font-bold mb-2 line-clamp-2 primary-color hover:accent-color transition-colors"><?php echo htmlspecialchars($news['title']); ?></h3>
                                <p class="text-sm text-gray-600 line-clamp-3 mb-3"><?php echo htmlspecialchars(isset($news['summary']) ? $news['summary'] : createSummary($news['content'])); ?></p>
                                <div class="flex justify-between text-xs text-gray-500">
                                    <span class="accent-color font-medium"><?php echo date('d.m.Y', strtotime($news['created_at'])); ?></span>
                                </div>
                            </div>
                        </a>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Son Haberler -->
            <?php if (!empty($latestNews)): ?>
            <div class="mb-16 border-t border-gray-200 pt-8">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-bold primary-color">Son Haberler</h3>
                    <a href="tumhaberler.php" class="accent-color hover:text-accent-color/80 text-sm font-medium">
                        Tümünü Gör →
                    </a>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach($latestNews as $news): ?>
                    <div class="bg-white shadow rounded overflow-hidden transition-all duration-300 hover:shadow-lg border border-gray-100">
                        <a href="news.php?id=<?php echo $news['id']; ?>">
                            <div class="relative h-48">
                                <img src="<?php echo !empty($news['featured_image']) ? $news['featured_image'] : 'https://picsum.photos/seed/random/400/300'; ?>" 
                                     alt="<?php echo htmlspecialchars($news['title']); ?>" 
                                     class="w-full h-full object-cover">
                                <?php if (!empty($news['category'])): ?>
                                <div class="absolute top-2 left-2">
                                    <span class="category-badge accent-bg"><?php echo htmlspecialchars($news['category']); ?></span>
                                </div>
                                <?php endif; ?>
                            </div>
                            <div class="p-4">
                                <h3 class="font-bold mb-2 line-clamp-2 primary-color hover:accent-color transition-colors"><?php echo htmlspecialchars($news['title']); ?></h3>
                                <p class="text-sm text-gray-600 line-clamp-3 mb-3"><?php echo htmlspecialchars(isset($news['summary']) ? $news['summary'] : createSummary($news['content'])); ?></p>
                                <div class="flex justify-between text-xs text-gray-500">
                                    <span class="accent-color font-medium"><?php echo date('d.m.Y', strtotime($news['created_at'])); ?></span>
                                </div>
                            </div>
                        </a>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <?php require_once 'templates/footer.php'; ?>
    </main>
</body>
</html> 