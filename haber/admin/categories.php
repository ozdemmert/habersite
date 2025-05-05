<?php
require_once '../include/functions.php';
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header('Location: ../login.php');
    exit();
}

$category = new Category();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create':
                $data = [
                    'name' => $_POST['name'],
                    'slug' => createSlug($_POST['name']),
                    'description' => $_POST['description']
                ];

                if ($category->create($data)) {
                    header('Location: categories.php?success=created');
                    exit();
                }
                break;

            case 'update':
                $id = (int) $_POST['id'];
                $data = [
                    'name' => $_POST['name'],
                    'slug' => createSlug($_POST['name']),
                    'description' => $_POST['description']
                ];

                if ($category->update($id, $data)) {
                    header('Location: categories.php?success=updated');
                    exit();
                }
                break;

            case 'delete':
                $id = (int) $_POST['id'];
                if ($category->delete($id)) {
                    header('Location: categories.php?success=deleted');
                    exit();
                }
                break;
        }
    }
}

// Get all categories
$all_categories = $category->getAll();

// Helper function to create slug
function createSlug($str)
{
    $str = mb_strtolower($str, 'UTF-8');
    $str = str_replace(['ı', 'ğ', 'ü', 'ş', 'ö', 'ç'], ['i', 'g', 'u', 's', 'o', 'c'], $str);
    $str = preg_replace('/[^a-z0-9-]/', '-', $str);
    $str = preg_replace('/-+/', '-', $str);
    $base_slug = trim($str, '-');
    
    // Check if slug exists
    global $category;
    $slug = $base_slug;
    $counter = 1;
    
    while ($category->slugExists($slug)) {
        $slug = $base_slug . '-' . $counter;
        $counter++;
    }
    
    return $slug;
}

$page_title = 'Kategori Yönetimi';
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
                        <?php
                        switch ($_GET['success']) {
                            case 'created':
                                echo 'Kategori başarıyla oluşturuldu.';
                                break;
                            case 'updated':
                                echo 'Kategori başarıyla güncellendi.';
                                break;
                            case 'deleted':
                                echo 'Kategori başarıyla silindi.';
                                break;
                        }
                        ?>
                    </span>
                </div>
            <?php endif; ?>

            <!-- Add Button -->
            <div class="mb-6">
                <button onclick="showAddModal()"
                    class="bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-4 rounded">
                    <i class="fas fa-plus mr-2"></i>Yeni Kategori Ekle
                </button>
            </div>

            <!-- Categories List -->
            <div class="bg-white rounded-lg shadow-md">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Kategori Adı</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Açıklama</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    İşlemler</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($all_categories as $item): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900"><?php echo $item['name']; ?></div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900"><?php echo $item['description']; ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <button onclick="editItem(<?php echo htmlspecialchars(json_encode($item)); ?>)"
                                            class="text-indigo-600 hover:text-indigo-900 mr-3">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button onclick="deleteItem(<?php echo $item['id']; ?>)"
                                            class="text-red-600 hover:text-red-900">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
    </div>

    <!-- Add/Edit Modal -->
    <div id="itemModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full">
        <div class="relative top-20 mx-auto p-5 border w-4/5 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4" id="modalTitle">Yeni Kategori Ekle</h3>
                <form id="itemForm" method="POST">
                    <input type="hidden" name="action" value="create">
                    <input type="hidden" name="id" id="itemId">

                    <div class="grid grid-cols-1 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Kategori Adı</label>
                            <input type="text" name="name" id="name" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Açıklama</label>
                            <textarea name="description" id="description"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeModal()"
                            class="bg-gray-500 hover:bg-gray-600 text-white font-semibold py-2 px-4 rounded">
                            İptal
                        </button>
                        <button type="submit"
                            class="bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-4 rounded">
                            Kaydet
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Mobile menu toggle
        document.querySelector('.md\\:hidden').addEventListener('click', function () {
            document.querySelector('.bg-gray-800').classList.toggle('-translate-x-full');
        });

        // Modal Functions
        function showAddModal() {
            document.getElementById('modalTitle').textContent = 'Yeni Kategori Ekle';
            document.getElementById('itemForm').reset();
            document.getElementById('itemForm').action.value = 'create';
            document.getElementById('itemId').value = '';
            document.getElementById('itemModal').classList.remove('hidden');
        }

        function editItem(item) {
            document.getElementById('modalTitle').textContent = 'Kategori Düzenle';
            document.getElementById('itemForm').action.value = 'update';
            document.getElementById('itemId').value = item.id;
            document.getElementById('name').value = item.name;
            document.getElementById('description').value = item.description;
            document.getElementById('itemModal').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('itemModal').classList.add('hidden');
        }

        function deleteItem(id) {
            if (confirm('Bu kategoriyi silmek istediğinizden emin misiniz?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'categories.php';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="${id}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>

</html>