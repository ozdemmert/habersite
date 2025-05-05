<?php
require_once '../include/functions.php';
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header('Location: ../login.php');
    exit();
}

$news = new News($conn);
$section = new Section($conn);

// Get current selections
$current_selections = $section->getAllGrouped();

// Get all news
$sql = "SELECT n.*, c.name as category_name 
        FROM news n 
        LEFT JOIN categories c ON n.category = c.name 
        WHERE n.published = 1 
        ORDER BY n.created_at DESC";
$result = $conn->query($sql);
$all_news = $result->fetch_all(MYSQLI_ASSOC);

// Helper function to get YouTube video ID
function getYoutubeVideoId($url) {
    $pattern = '/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/i';
    preg_match($pattern, $url, $matches);
    return isset($matches[1]) ? $matches[1] : null;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $success = true;
    
    // Update YouTube video
    if (isset($_POST['youtube_video'])) {
        $youtube_url = $_POST['youtube_video'];
        $video_id = getYoutubeVideoId($youtube_url);
        
        if ($video_id) {
            $data = ['youtube_url' => $youtube_url];
            if (!$section->updateOrCreate('youtube_video', $data)) {
                $success = false;
            }
        } else {
            $error = "Geçersiz YouTube URL'si";
            $success = false;
        }
    }
    
    // Update main sections
    foreach (['main1', 'main2'] as $sectionName) {
        if (isset($_POST[$sectionName])) {
            $data = ['news_id' => $_POST[$sectionName]];
            if (!$section->updateOrCreate($sectionName, $data)) {
                $success = false;
            }
        }
    }

    // Update story sections
    foreach (['story1', 'story2', 'story3'] as $sectionName) {
        if (isset($_POST[$sectionName])) {
            $data = ['news_id' => $_POST[$sectionName]];
            if (!$section->updateOrCreate($sectionName, $data)) {
                $success = false;
            }
        }
    }

    if ($success) {
        header('Location: section.php?success=1');
        exit();
    }
}

$page_title = 'Bölüm Yönetimi';
?>

<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <style>
        .select2-container--default .select2-selection--single {
            height: 38px;
            padding: 5px;
            border-color: #d1d5db;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px;
        }
    </style>
</head>

<body class="bg-gray-100">
    <?php include 'components/sidebar.php'; ?>

    <div class="ml-64 flex-1 h-screen overflow-y-auto">
        <?php include 'components/header.php'; ?>

        <main class="p-8">
            <?php if (isset($_GET['success'])): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
                    <span class="block sm:inline">Bölüm seçimleri başarıyla güncellendi!</span>
                </div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                    <span class="block sm:inline"><?php echo $error; ?></span>
                </div>
            <?php endif; ?>

            <form method="POST" action="section.php">
                <div class="mb-6">
                    <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-4 rounded">
                        <i class="fas fa-save mr-2"></i>Değişiklikleri Kaydet
                    </button>
                </div>

                <div class="bg-white rounded-lg shadow-md p-6 space-y-8">
                    <!-- YouTube Video Section -->
                    <div class="space-y-6">
                        <h2 class="text-xl font-bold mb-4">YouTube Video</h2>
                        <div class="flex items-center gap-4 p-4 bg-gray-50 rounded">
                            <label class="w-32 font-medium">Video URL</label>
                            <input type="text" name="youtube_video" 
                                value="<?php echo isset($current_selections['youtube_video']['value']) ? htmlspecialchars($current_selections['youtube_video']['value']) : ''; ?>"
                                placeholder="https://www.youtube.com/watch?v=..." 
                                class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <?php if (isset($current_selections['youtube_video']['value']) && !empty($current_selections['youtube_video']['value'])): ?>
                            <div class="mt-4">
                                <h3 class="text-sm font-medium text-gray-700 mb-2">Mevcut Video:</h3>
                                <div class="aspect-w-16 aspect-h-9">
                                    <iframe src="https://www.youtube.com/embed/<?php echo getYoutubeVideoId($current_selections['youtube_video']['value']); ?>" 
                                            class="w-full h-64 rounded-lg"
                                            allowfullscreen></iframe>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Main Sections -->
                    <div class="space-y-6">
                        <h2 class="text-xl font-bold mb-4">Ana Bölümler</h2>
                        <?php foreach (['main1', 'main2'] as $sectionName): ?>
                            <div class="space-y-4">
                                <div class="flex items-center gap-4 p-4 bg-gray-50 rounded">
                                    <label class="w-32 font-medium"><?php echo ucfirst($sectionName); ?></label>
                                    <select name="<?php echo $sectionName; ?>" class="news-select flex-1 rounded-md border-gray-300 shadow-sm">
                                        <option value="">Haber Seçin</option>
                                        <?php foreach ($all_news as $news_item): ?>
                                            <option value="<?php echo $news_item['id']; ?>"
                                                data-category="<?php echo htmlspecialchars($news_item['category_name']); ?>"
                                                data-image="<?php echo htmlspecialchars($news_item['featured_image']); ?>"
                                                data-content="<?php echo htmlspecialchars(strip_tags($news_item['content'])); ?>"
                                                <?php if (isset($current_selections[$sectionName]['value']) && $current_selections[$sectionName]['value'] == $news_item['id']) echo 'selected'; ?>>
                                                <?php echo htmlspecialchars($news_item['title']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="news-preview hidden p-4 bg-gray-50 rounded">
                                    <div class="grid grid-cols-3 gap-4">
                                        <div class="col-span-1">
                                            <img src="" alt="" class="w-full h-48 object-cover rounded-lg preview-image">
                                        </div>
                                        <div class="col-span-2">
                                            <h3 class="font-medium text-lg mb-2 preview-title"></h3>
                                            <span class="inline-block px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800 mb-2 preview-category"></span>
                                            <p class="text-sm text-gray-600 preview-content"></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Story Sections -->
                    <div class="space-y-6">
                        <h2 class="text-xl font-bold mb-4">Hikaye Bölümleri</h2>
                        <?php foreach (['story1', 'story2', 'story3'] as $sectionName): ?>
                            <div class="space-y-4">
                                <div class="flex items-center gap-4 p-4 bg-gray-50 rounded">
                                    <label class="w-32 font-medium"><?php echo ucfirst($sectionName); ?></label>
                                    <select name="<?php echo $sectionName; ?>" class="news-select flex-1 rounded-md border-gray-300 shadow-sm">
                                        <option value="">Haber Seçin</option>
                                        <?php foreach ($all_news as $news_item): ?>
                                            <option value="<?php echo $news_item['id']; ?>"
                                                data-category="<?php echo htmlspecialchars($news_item['category_name']); ?>"
                                                data-image="<?php echo htmlspecialchars($news_item['featured_image']); ?>"
                                                data-content="<?php echo htmlspecialchars(strip_tags($news_item['content'])); ?>"
                                                <?php if (isset($current_selections[$sectionName]['value']) && $current_selections[$sectionName]['value'] == $news_item['id']) echo 'selected'; ?>>
                                                <?php echo htmlspecialchars($news_item['title']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="news-preview hidden p-4 bg-gray-50 rounded">
                                    <div class="grid grid-cols-3 gap-4">
                                        <div class="col-span-1">
                                            <img src="" alt="" class="w-full h-48 object-cover rounded-lg preview-image">
                                        </div>
                                        <div class="col-span-2">
                                            <h3 class="font-medium text-lg mb-2 preview-title"></h3>
                                            <span class="inline-block px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800 mb-2 preview-category"></span>
                                            <p class="text-sm text-gray-600 preview-content"></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </form>
        </main>
    </div>

    <script>
        $(document).ready(function() {
            // Initialize Select2 for all news selects
            $('.news-select').select2({
                placeholder: 'Haber seçin veya arayın...',
                allowClear: true,
                width: '100%',
                templateResult: formatNewsOption,
                templateSelection: formatNewsSelection
            });

            // Handle news selection change
            $('.news-select').on('change', function() {
                const preview = $(this).closest('.space-y-4').find('.news-preview');
                const selectedOption = $(this).find('option:selected');
                
                if (selectedOption.val()) {
                    preview.removeClass('hidden');
                    preview.find('.preview-image').attr('src', '../' + selectedOption.data('image'));
                    preview.find('.preview-title').text(selectedOption.text());
                    preview.find('.preview-category').text(selectedOption.data('category'));
                    preview.find('.preview-content').text(selectedOption.data('content').substring(0, 200) + '...');
                } else {
                    preview.addClass('hidden');
                }
            });

            // Trigger change event for initially selected items
            $('.news-select').each(function() {
                if ($(this).val()) {
                    $(this).trigger('change');
                }
            });
        });

        // Format news option in dropdown
        function formatNewsOption(option) {
            if (!option.id) return option.text;
            
            const $option = $(option.element);
            const category = $option.data('category');
            const image = $option.data('image');
            
            return $(`
                <div class="flex items-center gap-2">
                    ${image ? `<img src="../${image}" class="w-10 h-10 object-cover rounded">` : ''}
                    <div>
                        <div class="font-medium">${option.text}</div>
                        <div class="text-sm text-gray-500">${category || 'Kategorisiz'}</div>
                    </div>
                </div>
            `);
        }

        // Format selected news option
        function formatNewsSelection(option) {
            if (!option.id) return option.text;
            
            const $option = $(option.element);
            const image = $option.data('image');
            
            return $(`
                <div class="flex items-center gap-2">
                    ${image ? `<img src="../${image}" class="w-8 h-8 object-cover rounded">` : ''}
                    <span>${option.text}</span>
                </div>
            `);
        }
    </script>
</body>
</html>