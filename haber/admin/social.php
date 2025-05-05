<?php
require_once '../include/functions.php';
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header('Location: ../login.php');
    exit();
}

$social = new Social();
$social->createTable();

// Insert default platforms if empty
$defaultPlatforms = ['facebook', 'twitter', 'instagram', 'youtube', 'linkedin', 'tiktok'];
$existing = $social->getAll();
if (empty($existing)) {
    foreach ($defaultPlatforms as $platform) {
        $social->create($platform, '');
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update') {
        $urls = $_POST['urls'] ?? [];
        $success = true;

        foreach ($urls as $id => $url) {
            $data = ['social_url' => $url];
            if (!$social->update($id, $data)) {
                $success = false;
                // Handle error here if needed
            }
        }

        if ($success) {
            header('Location: social.php?success=updated');
            exit();
        }
    }
}

$platforms = $social->getAll();

$page_title = 'Sosyal Medya Yönetimi';
?>

<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body class="bg-gray-100">
    <!-- Fixed Sidebar -->
    <?php include 'components/sidebar.php'; ?>

    <!-- Scrollable Main Content Area -->
    <div class="ml-64 flex-1 h-screen overflow-y-auto">
        <?php include 'components/header.php'; ?>

        <!-- Main Content Area -->
        <main class="p-8">
            <?php if (isset($_GET['success'])): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4"
                    role="alert">
                    <span class="block sm:inline">
                        Sosyal medya bağlantıları başarıyla güncellendi.
                    </span>
                </div>
            <?php endif; ?>

            <div class="mb-6">
                <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-4 rounded">
                    <i class="fas fa-save mr-2"></i>Değişiklikleri Kaydet
                </button>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6">
                <form method="POST" action="social.php">
                    <input type="hidden" name="action" value="update">

                    <div class="space-y-6">
                        <?php foreach ($platforms as $platform): ?>
                            <div class="flex items-center gap-4">
                                <div class="w-24">
                                    <span class="text-gray-700 font-medium capitalize">
                                        <?php echo $platform['platform']; ?>
                                    </span>
                                </div>
                                <div class="flex-1">
                                    <input type="url" name="urls[<?php echo $platform['id']; ?>]"
                                        value="<?php echo htmlspecialchars($platform['social_url']); ?>"
                                        placeholder="https://"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                </div>
                                <div class="w-10 text-center">
                                    <i class="fab fa-<?php echo $platform['platform']; ?> text-xl text-gray-500"></i>
                                </div>
                            </div>
                        <?php endforeach; ?>


                    </div>
                </form>
            </div>
        </main>
    </div>
    </div>
</body>

</html>