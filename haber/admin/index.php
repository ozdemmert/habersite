<?php
require_once '../include/functions.php';
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header('Location: ../login.php');
    exit();
}

$user = new User();
$news = new News();
$category = new Category();

// Get statistics
$total_users = count($user->getAll());
$total_news = count($news->getAll());
$total_categories = count($category->getAll());
?>

<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
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
            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-blue-500 bg-opacity-10">
                            <i class="fas fa-users text-blue-500 text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-gray-500">Toplam Kullanıcı</p>
                            <p class="text-2xl font-semibold"><?php echo $total_users; ?></p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-green-500 bg-opacity-10">
                            <i class="fas fa-newspaper text-green-500 text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-gray-500">Toplam Haber</p>
                            <p class="text-2xl font-semibold"><?php echo $total_news; ?></p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-purple-500 bg-opacity-10">
                            <i class="fas fa-tags text-purple-500 text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-gray-500">Toplam Kategori</p>
                            <p class="text-2xl font-semibold"><?php echo $total_categories; ?></p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-yellow-500 bg-opacity-10">
                            <i class="fas fa-eye text-yellow-500 text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-gray-500">Toplam Görüntülenme</p>
                            <p class="text-2xl font-semibold">0</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-8">
                <h2 class="text-xl font-semibold mb-4">Hızlı İşlemler</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <a href="news.php?action=create"
                        class="flex items-center p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition duration-200">
                        <i class="fas fa-plus-circle text-blue-500 text-xl mr-3"></i>
                        <span>Yeni Haber Ekle</span>
                    </a>
                    <a href="categories.php?action=create"
                        class="flex items-center p-4 bg-green-50 rounded-lg hover:bg-green-100 transition duration-200">
                        <i class="fas fa-folder-plus text-green-500 text-xl mr-3"></i>
                        <span>Yeni Kategori Ekle</span>
                    </a>
                    <a href="users.php?action=create"
                        class="flex items-center p-4 bg-purple-50 rounded-lg hover:bg-purple-100 transition duration-200">
                        <i class="fas fa-user-plus text-purple-500 text-xl mr-3"></i>
                        <span>Yeni Kullanıcı Ekle</span>
                    </a>
                    <a href="dosya.php?action=create"
                        class="flex items-center p-4 bg-yellow-50 rounded-lg hover:bg-yellow-100 transition duration-200">
                        <i class="fas fa-file-upload text-yellow-500 text-xl mr-3"></i>
                        <span>Yeni Dosya Ekle</span>
                    </a>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold mb-4">Son Aktiviteler</h2>
                <div class="space-y-4">
                    <div class="flex items-center p-4 bg-gray-50 rounded-lg">
                        <i class="fas fa-newspaper text-blue-500 text-xl mr-3"></i>
                        <div>
                            <p class="font-medium">Yeni haber eklendi</p>
                            <p class="text-sm text-gray-500">2 saat önce</p>
                        </div>
                    </div>
                    <div class="flex items-center p-4 bg-gray-50 rounded-lg">
                        <i class="fas fa-user text-green-500 text-xl mr-3"></i>
                        <div>
                            <p class="font-medium">Yeni kullanıcı kaydoldu</p>
                            <p class="text-sm text-gray-500">3 saat önce</p>
                        </div>
                    </div>
                    <div class="flex items-center p-4 bg-gray-50 rounded-lg">
                        <i class="fas fa-tags text-purple-500 text-xl mr-3"></i>
                        <div>
                            <p class="font-medium">Yeni kategori eklendi</p>
                            <p class="text-sm text-gray-500">5 saat önce</p>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    </div>

    <script>
        // Mobile menu toggle
        document.querySelector('.md\\:hidden').addEventListener('click', function () {
            document.querySelector('.bg-gray-800').classList.toggle('-translate-x-full');
        });
    </script>
</body>

</html>