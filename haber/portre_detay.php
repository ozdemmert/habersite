<?php

require_once 'include/config.php';
require_once 'include/functions.php';

// Get portrait by ID
$portre = new Portre();
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    header('Location: portre.php');
    exit;
}

$portrait = $portre->getById($id);
if (!$portrait) {
    header('Location: portre.php');
    exit;
}

// Get related portraits
$all_portres = $portre->getAll();
$related_portres = [];

// Filter out current portrait and get at most 3 related portraits
foreach ($all_portres as $item) {
    if ($item['id'] != $id) {
        $related_portres[] = $item;
        if (count($related_portres) >= 3) {
            break;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($portrait['first_name'] . ' ' . $portrait['lastname']); ?> - Portre</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #f7f7f7;
            color: #333;
            line-height: 1.6;
        }
        .portre-hero {
            background-color: #f7f7f7;
            padding: 3rem 0;
            margin-bottom: 2rem;
            border-bottom: 1px solid #eaeaea;
        }
        .portrait-img {
            border-radius: 8px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            max-height: 500px;
            width: 100%;
            object-fit: cover;
        }
        .portrait-img:hover {
            transform: scale(1.02);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
        }
        .quote-block {
            position: relative;
            font-style: italic;
            padding: 2rem;
            margin: 2rem 0;
            background-color: #f9f9f9;
            border-left: 4px solid #003366;
            border-radius: 0 8px 8px 0;
        }
        .quote-block::before {
            content: '"';
            position: absolute;
            top: 0;
            left: 0.5rem;
            font-size: 4rem;
            font-family: Georgia, serif;
            color: #003366;
            opacity: 0.2;
            line-height: 1;
        }
        .portrait-content {
            line-height: 1.8;
            font-size: 1.05rem;
            color: #333;
        }
        .portrait-content p {
            margin-bottom: 1.5rem;
            text-align: justify;
        }
        .portrait-content h2 {
            font-size: 1.5rem;
            font-weight: 600;
            margin-top: 2rem;
            margin-bottom: 1rem;
            color: #003366;
            border-bottom: 1px solid #eaeaea;
            padding-bottom: 0.5rem;
        }
        .portrait-content h3 {
            font-size: 1.25rem;
            font-weight: 600;
            margin-top: 1.5rem;
            margin-bottom: 0.75rem;
            color: #003366;
        }
        .portrait-content ul, .portrait-content ol {
            margin-bottom: 1.5rem;
            padding-left: 2rem;
        }
        .portrait-content ul li {
            list-style-type: disc;
            margin-bottom: 0.5rem;
        }
        .portrait-content ol li {
            list-style-type: decimal;
            margin-bottom: 0.5rem;
        }
        .portrait-content a {
            color: #003366;
            text-decoration: underline;
        }
        .portrait-content a:hover {
            text-decoration: none;
        }
        .bio-section {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            padding: 2.5rem;
            margin-bottom: 3rem;
        }
        .bio-header {
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #eaeaea;
        }
        .bio-meta {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
            color: #666;
            font-size: 0.9rem;
        }
        .related-card {
            transition: all 0.3s ease;
            border: 1px solid #eaeaea;
            border-radius: 8px;
            overflow: hidden;
            background-color: white;
        }
        .related-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
        }
        .back-button {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background-color: #003366;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            transition: all 0.2s ease;
        }
        .back-button:hover {
            background-color: #002244;
        }
        .section-title {
            position: relative;
            display: inline-block;
            margin-bottom: 1.5rem;
            font-size: 1.75rem;
            font-weight: 700;
        }
        .section-title:after {
            content: '';
            position: absolute;
            bottom: -8px;
            left: 0;
            width: 60px;
            height: 3px;
            background-color: #003366;
        }
    </style>
</head>
<body>
<?php require_once 'templates/header.php'; ?>
    <main class="min-h-screen">
        <!-- Hero Section -->
        <div class="portre-hero">
            <div class="max-w-6xl mx-auto px-4">
                <!-- Breadcrumb -->
                <div class="mb-6">
                    <div class="flex items-center text-sm text-gray-500">
                        <a href="index.php" class="hover:text-gray-700">Ana Sayfa</a>
                        <span class="mx-2">&gt;</span>
                        <a href="portre.php" class="hover:text-gray-700">Portre</a>
                        <span class="mx-2">&gt;</span>
                        <span><?php echo htmlspecialchars($portrait['first_name'] . ' ' . $portrait['lastname']); ?></span>
                    </div>
                </div>
                
                <div class="flex flex-col md:flex-row items-start gap-12">
                    <!-- Portrait Image -->
                    <div class="md:w-1/3">
                        <?php if (!empty($portrait['portre_image'])): ?>
                            <img src="<?php echo $portrait['portre_image']; ?>" 
                                 alt="<?php echo htmlspecialchars($portrait['first_name'] . ' ' . $portrait['lastname']); ?>" 
                                 class="portrait-img"
                            >
                        <?php else: ?>
                            <div class="portrait-img bg-gray-200 w-full h-80 flex items-center justify-center">
                                <span class="text-gray-400">Görsel Yok</span>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Name and Title -->
                    <div class="md:w-2/3">
                        <a href="portre.php" class="back-button mb-6">
                            <i class="fas fa-arrow-left text-sm"></i>
                            <span>Tüm Portreler</span>
                        </a>
                        
                        <h1 class="text-4xl font-bold mb-3">Prof. Dr. <?php echo htmlspecialchars($portrait['first_name'] . ' ' . $portrait['lastname']); ?></h1>
                        <h2 class="text-xl text-gray-600 mb-6"><?php echo htmlspecialchars($portrait['degree']); ?></h2>
                        
                        <?php
                        // Use the quote field if available, otherwise extract from biography
                        if (!empty($portrait['quote'])) {
                            $quote = $portrait['quote'];
                        } else {
                            // Extract quote if exists in biography
                            preg_match('/"([^"]*)"/', $portrait['biography'], $matches);
                            $quote = isset($matches[1]) ? $matches[1] : '';
                        }
                        
                        if ($quote): ?>
                            <div class="quote-block">
                                <p><?php echo htmlspecialchars($quote); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Biography Section -->
        <div class="max-w-4xl mx-auto px-4">
            <div class="bio-section">
                <div class="bio-header">
                    <h2 class="section-title">Biyografi</h2>
                    <div class="bio-meta">
                        <i class="fas fa-user-graduate mr-2"></i>
                        <span><?php echo htmlspecialchars($portrait['degree']); ?></span>
                    </div>
                </div>
                
                <div class="portrait-content">
                    <?php
                    // Remove the quote from biography if it exists and wasn't already defined in the quote field
                    $biography = $portrait['biography'];
                    if (empty($portrait['quote']) && $quote) {
                        $biography = str_replace('"' . $quote . '"', '', $biography);
                    }
                    
                    // Process the biography content for better display
                    // Replace single line breaks with paragraphs
                    $biography = preg_replace('/<p>\s*<\/p>/', '', $biography);
                    
                    // Output the biography content
                    echo $biography;
                    ?>
                </div>
            </div>
        </div>
        
        <!-- Related Portraits -->
        <?php if (!empty($related_portres)): ?>
        <div class="max-w-6xl mx-auto px-4 pb-12">
            <h2 class="section-title mb-8">Diğer Portreler</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <?php foreach ($related_portres as $item): ?>
                <div class="related-card">
                    <a href="portre_detay.php?id=<?php echo $item['id']; ?>" class="block">
                        <?php if (!empty($item['portre_image'])): ?>
                            <img src="<?php echo $item['portre_image']; ?>" 
                                 alt="<?php echo htmlspecialchars($item['first_name'] . ' ' . $item['lastname']); ?>" 
                                 class="w-full h-56 object-cover"
                            >
                        <?php else: ?>
                            <div class="bg-gray-200 w-full h-56 flex items-center justify-center">
                                <span class="text-gray-400">Görsel Yok</span>
                            </div>
                        <?php endif; ?>
                        
                        <div class="p-5">
                            <h3 class="text-xl font-bold mb-1">Prof. Dr. <?php echo htmlspecialchars($item['first_name'] . ' ' . $item['lastname']); ?></h3>
                            <p class="text-gray-600 mb-3"><?php echo htmlspecialchars($item['degree']); ?></p>
                            
                            <div class="flex justify-end">
                                <span class="inline-block px-4 py-2 bg-gray-800 text-white text-sm rounded">
                                    Portreyi Oku
                                </span>
                            </div>
                        </div>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </main>
    <?php require_once 'templates/footer.php'; ?>
</body>
</html> 