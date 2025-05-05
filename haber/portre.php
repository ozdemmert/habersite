<?php

require_once 'include/config.php';
require_once 'include/functions.php';

$portre = new Portre();
$portres = $portre->getAll();

// Get the latest portrait for featured section
$latest_portrait = $portre->getLatest();

// Remove the latest portrait from the list to avoid duplication
if (!empty($latest_portrait) && !empty($portres)) {
    $portres = array_filter($portres, function($item) use ($latest_portrait) {
        return $item['id'] != $latest_portrait['id'];
    });
}

// Pagination for portraits
$per_page = 2; // Show 2 portraits per page
$current_page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$current_page = max(1, $current_page);
$total_items = count($portres);
$total_pages = ceil($total_items / $per_page);
$current_page = min($current_page, max(1, $total_pages));
$offset = ($current_page - 1) * $per_page;

// Get items for current page
$page_items = array_slice($portres, $offset, $per_page);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portre</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #f7f7f7;
        }
        .featured-card {
            transition: all 0.3s ease;
            border: 1px solid #eaeaea;
            border-radius: 8px;
            overflow: hidden;
            background-color: #fff;
            margin-bottom: 40px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
            display: flex;
            width: 100%;
        }
        .featured-image {
            width: 40%;
            flex-shrink: 0;
            background-color: #f3f4f6;
            display: flex;
            align-items: center;
            justify-content: center;
            border-right: 1px solid #eaeaea;
        }
        .featured-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .featured-content {
            flex: 1;
            padding: 30px;
        }
        .portre-card {
            transition: all 0.3s ease;
            border: 1px solid #eaeaea;
            border-radius: 8px;
            overflow: hidden;
            background-color: #fff;
            margin-bottom: 24px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            display: flex;
            width: 100%;
        }
        .portre-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
        .portre-image {
            width: 240px;
            height: 240px;
            flex-shrink: 0;
            background-color: #f3f4f6;
            display: flex;
            align-items: center;
            justify-content: center;
            border-right: 1px solid #eaeaea;
        }
        .portre-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .portre-content {
            flex: 1;
            padding: 24px;
        }
        .quote-block {
            position: relative;
            font-style: italic;
            color: #4a5568;
            padding-left: 10px;
            border-left: 3px solid #f39200;
            font-size: 0.9rem;
            margin: 10px 0;
        }
        .portreyi-oku {
            background-color: #022d5a;
            color: white;
            transition: all 0.2s ease;
            font-size: 0.85rem;
            padding: 6px 12px;
            border-radius: 4px;
            display: inline-block;
        }
        .portreyi-oku:hover {
            background-color: #f39200;
        }
        .page-title {
            position: relative;
            display: inline-block;
            color: #022d5a;
        }
        .page-title:after {
            content: '';
            position: absolute;
            bottom: -8px;
            left: 0;
            width: 60px;
            height: 3px;
            background-color: #f39200;
        }
        .pagination {
            display: flex;
            justify-content: center;
            gap: 4px;
        }
        .pagination a {
            border: 1px solid #e2e8f0;
            padding: 6px 12px;
            border-radius: 4px;
        }
        .pagination a.active {
            background-color: #022d5a;
            color: white;
        }
        .pagination a:hover:not(.active) {
            background-color: #f8fafc;
        }
        @media (max-width: 768px) {
            .portre-card, .featured-card {
                flex-direction: column;
            }
            .portre-image, .featured-image {
                width: 100%;
                height: 200px;
                border-right: none;
                border-bottom: 1px solid #eaeaea;
            }
            .featured-image {
                height: 250px;
            }
        }
    </style>
</head>
<body>
<?php require_once 'templates/header.php'; ?>
    <main class="min-h-screen py-8">
        <div class="max-w-5xl mx-auto px-4">
            <!-- Breadcrumb -->
            <div class="mb-4">
                <div class="flex items-center text-sm text-gray-500">
                    <a href="index.php" class="hover:text-[#f39200]">Ana Sayfa</a>
                    <span class="mx-2">&gt;</span>
                    <span class="text-[#022d5a]">Portre</span>
                </div>
            </div>

            <!-- Featured Portrait -->
            <?php if (!empty($latest_portrait)): ?>
            <div class="featured-card">
                <div class="featured-image">
                    <?php if (!empty($latest_portrait['portre_image'])): ?>
                        <img src="<?php echo $latest_portrait['portre_image']; ?>" 
                            alt="<?php echo htmlspecialchars($latest_portrait['first_name'] . ' ' . $latest_portrait['lastname']); ?>">
                    <?php else: ?>
                        <span class="text-gray-400">Görsel Yok</span>
                    <?php endif; ?>
                </div>
                <div class="featured-content">
                    <h2 class="text-2xl font-bold mb-1 text-[#022d5a]">Prof. Dr. <?php echo htmlspecialchars($latest_portrait['first_name'] . ' ' . $latest_portrait['lastname']); ?></h2>
                    <p class="text-[#f39200] mb-4"><?php echo htmlspecialchars($latest_portrait['degree']); ?></p>
                    
                    <?php if (!empty($latest_portrait['quote'])): ?>
                        <div class="quote-block mb-4">
                            "<?php echo htmlspecialchars($latest_portrait['quote']); ?>"
                        </div>
                    <?php endif; ?>
                    
                    <p class="text-gray-600 mb-6">
                        <?php 
                        // Shorten the biography
                        $short_bio = strip_tags($latest_portrait['biography']);
                        $short_bio = mb_strlen($short_bio) > 220 ? mb_substr($short_bio, 0, 220) . '...' : $short_bio;
                        echo htmlspecialchars($short_bio); 
                        ?>
                    </p>
                    
                    <div>
                        <a href="portre_detay.php?id=<?php echo $latest_portrait['id']; ?>" class="portreyi-oku">
                            Devamını Oku
                        </a>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Other Portraits Section Title -->
            <h1 class="page-title text-3xl font-bold mb-2">Diğer Portreler</h1>
            <p class="text-[#f39200] mb-8">Önemli şahsiyetlerin hayatları ve hikayeleri</p>

            <!-- Portraits List (horizontal cards) -->
            <div class="mb-10">
                <?php foreach ($page_items as $item): ?>
                <div class="portre-card">
                    <div class="portre-image">
                        <?php if (!empty($item['portre_image'])): ?>
                            <img src="<?php echo $item['portre_image']; ?>" 
                                alt="<?php echo htmlspecialchars($item['first_name'] . ' ' . $item['lastname']); ?>">
                        <?php else: ?>
                            <span class="text-gray-400">Görsel Yok</span>
                        <?php endif; ?>
                    </div>
                    <div class="portre-content">
                        <h2 class="text-xl font-bold mb-1 text-[#022d5a]">Prof. Dr. <?php echo htmlspecialchars($item['first_name'] . ' ' . $item['lastname']); ?></h2>
                        <p class="text-[#f39200] text-sm mb-4"><?php echo htmlspecialchars($item['degree']); ?></p>
                        
                        <?php if (!empty($item['quote'])): ?>
                            <div class="quote-block mb-4">
                                "<?php echo htmlspecialchars($item['quote']); ?>"
                            </div>
                        <?php else: ?>
                            <?php
                            // Extract first quote from biography if the quote field is empty
                            preg_match('/"([^"]*)"/', $item['biography'], $matches);
                            $biography_quote = isset($matches[1]) ? $matches[1] : '';
                            
                            if ($biography_quote): ?>
                                <div class="quote-block mb-4">
                                    "<?php echo htmlspecialchars($biography_quote); ?>"
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                        
                        <p class="text-sm text-gray-600 mb-4">
                            <?php 
                            // Remove any quotes from biography if using a quote from biography
                            $biography = $item['biography'];
                            if (empty($item['quote']) && !empty($biography_quote)) {
                                $biography = str_replace('"' . $biography_quote . '"', '', $biography);
                            }
                            
                            // Shorten the biography
                            $short_bio = strip_tags($biography);
                            $short_bio = mb_strlen($short_bio) > 180 ? mb_substr($short_bio, 0, 180) . '...' : $short_bio;
                            echo htmlspecialchars($short_bio); 
                            ?>
                        </p>
                        
                        <div>
                            <a href="portre_detay.php?id=<?php echo $item['id']; ?>" class="portreyi-oku">
                                Portreyi Oku
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
            <div class="pagination mb-8">
                <?php if ($current_page > 1): ?>
                    <a href="?page=<?php echo $current_page - 1; ?>">&lt;</a>
                <?php endif; ?>
                
                <?php 
                $start = max(1, $current_page - 1);
                $end = min($total_pages, $start + 2);
                $start = max(1, $end - 2);
                
                for ($i = $start; $i <= $end; $i++): 
                ?>
                    <a href="?page=<?php echo $i; ?>" class="<?php echo $i === $current_page ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
                
                <?php if ($current_page < $total_pages): ?>
                    <a href="?page=<?php echo $current_page + 1; ?>">&gt;</a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            
            <?php if (empty($page_items)): ?>
            <div class="text-center py-10">
                <p class="text-gray-500">Henüz portre bulunmamaktadır.</p>
            </div>
            <?php endif; ?>
        </div>
    </main>
    <?php require_once 'templates/footer.php'; ?>
</body>
</html> 