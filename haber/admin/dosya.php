<?php
require_once '../include/functions.php';
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header('Location: ../login.php');
    exit();
}

$dosya = new Dosya();
$category = new Category();

// Arama ve Filtreleme Parametreleri
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$filter_category = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$filter_status = isset($_GET['status']) ? $_GET['status'] : '';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// SQL Sorgusu Oluştur
$where_conditions = [];
$params = [];

if ($search !== '') {
    $search_term = '%' . $search . '%';
    $where_conditions[] = "(d.title LIKE ? OR d.category LIKE ?)";
    $params[] = $search_term;
    $params[] = $search_term;
}

if ($filter_category > 0) {
    $where_conditions[] = "dc.category_id = ?";
    $params[] = $filter_category;
}

if ($filter_status !== '') {
    $where_conditions[] = "d.published = ?";
    $params[] = ($filter_status === 'published') ? 1 : 0;
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Ana sorgu
$sql = "SELECT DISTINCT d.* FROM dosya d 
        LEFT JOIN dosya_category dc ON d.id = dc.dosya_id 
        $where_clause 
        ORDER BY d.created_at DESC 
        LIMIT ? OFFSET ?";

$params[] = $per_page;
$params[] = $offset;

// Prepared statement
$stmt = $conn->prepare($sql);
if ($params) {
    $types = str_repeat('s', count($params));
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$all_files = $result->fetch_all(MYSQLI_ASSOC);

// Toplam kayıt sayısı
$count_sql = "SELECT COUNT(DISTINCT d.id) as total FROM dosya d 
              LEFT JOIN dosya_category dc ON d.id = dc.dosya_id 
              $where_clause";

if ($params) {
    array_pop($params); // Remove LIMIT
    array_pop($params); // Remove OFFSET
    $stmt = $conn->prepare($count_sql);
    $types = str_repeat('s', count($params));
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $total = $stmt->get_result()->fetch_assoc()['total'];
} else {
    $total = $conn->query($count_sql)->fetch_assoc()['total'];
}

$total_pages = ceil($total / $per_page);

// Get categories for filter
$categories = $category->getAll();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create':
                $featuredCount = $conn->query("SELECT COUNT(*) as count FROM dosya WHERE is_featured = 1")->fetch_assoc()['count'];
                if ($featuredCount >= 3 && isset($_POST['is_featured']) && $_POST['is_featured'] == 1) {
                    header('Location: dosya.php?error=max_featured');
                    exit();
                }

                $data = [
                    'title' => $_POST['title'],
                    'category' => $_POST['category'],
                    'slug' => createSlug($_POST['title']),
                    'content' => $_POST['content'],
                    'meta_description' => $_POST['meta_description'],
                    'user_id' => $_SESSION['user_id'],
                    'published' => isset($_POST['published']) ? 1 : 0,
                    'is_featured' => isset($_POST['is_featured']) ? 1 : 0
                ];

                // Handle file upload
                if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] === 0) {
                    $upload_dir = '../uploads/dosya/';
                    if (!file_exists($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }

                    $file_name = time() . '_' . $_FILES['featured_image']['name'];
                    $file_path = $upload_dir . $file_name;

                    if (move_uploaded_file($_FILES['featured_image']['tmp_name'], $file_path)) {
                        $data['featured_image'] = 'uploads/dosya/' . $file_name;
                    }
                }

                if ($dosya->create($data)) {
                    // Build your redirect query
                    $last_id = $conn->insert_id;
                    $cat_id = $category->getByName($_POST['category'])['id'];
                    if ($cat_id) {
                        $conn->query("INSERT INTO dosya_category (dosya_id, category_id) VALUES ($last_id, $cat_id)");
                    }
                    $qs = 'success=created';
                    if ($warn = $dosya->getWarningMessage()) {
                        $qs .= '&warning=' . urlencode($warn);
                    }
                    header("Location: dosya.php?{$qs}");
                } else {
                    $err = urlencode($dosya->getErrorMessage());
                    header("Location: dosya.php?error={$err}");
                    exit();
                }
                break;

            case 'update':
                $id = (int) $_POST['id'];

                // Check if the number of featured items is already 3
                $featuredCount = $conn->query("SELECT COUNT(*) as count FROM dosya WHERE is_featured = 1 AND id != $id")->fetch_assoc()['count'];
                if ($featuredCount >= 3 && isset($_POST['is_featured']) && $_POST['is_featured'] == 1) {
                    header('Location: dosya.php?error=max_featured');
                    exit();
                }

                $data = [
                    'title' => $_POST['title'],
                    'category' => $_POST['category'],
                    'slug' => createSlug($_POST['title']),
                    'content' => $_POST['content'],
                    'meta_description' => $_POST['meta_description'],
                    'published' => isset($_POST['published']) ? 1 : 0,
                    'is_featured' => isset($_POST['is_featured']) ? 1 : 0
                ];

                // Handle file upload
                if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] === 0) {
                    $upload_dir = '../uploads/dosya/';
                    if (!file_exists($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }

                    $file_name = time() . '_' . $_FILES['featured_image']['name'];
                    $file_path = $upload_dir . $file_name;

                    if (move_uploaded_file($_FILES['featured_image']['tmp_name'], $file_path)) {
                        $data['featured_image'] = 'uploads/dosya/' . $file_name;
                    }
                }

                if ($dosya->update($id, $data)) {
                    $cat_id = $category->getByName($_POST['category'])['id'];
                    if ($cat_id) {
                        $conn->query("DELETE FROM dosya_category WHERE dosya_id = $id");
                        $conn->query("INSERT INTO dosya_category (dosya_id, category_id) VALUES ($id, $cat_id)");
                    }
                    header('Location: dosya.php?success=updated');
                } else {
                    $errMsg = urlencode($dosya->getErrorMessage());
                    header("Location: dosya.php?error={$errMsg}");
                    exit();
                }
                break;

            case 'delete':
                $id = (int) $_POST['id'];
                if ($dosya->delete($id)) {
                    $conn->query("DELETE FROM dosya_category WHERE dosya_id = $id");
                    header('Location: dosya.php?success=deleted');
                    exit();
                }
                break;
        }
    }
}

// Separate featured and other files
$featured_dosya = array_filter($all_files, fn($n) => $n['is_featured']);
$other_dosya = array_filter($all_files, fn($n) => !$n['is_featured']);

// Helper function to create slug
function createSlug($str)
{
    $str = mb_strtolower($str, 'UTF-8');
    $str = str_replace(['ı', 'ğ', 'ü', 'ş', 'ö', 'ç'], ['i', 'g', 'u', 's', 'o', 'c'], $str);
    $str = preg_replace('/[^a-z0-9-]/', '-', $str);
    $str = preg_replace('/-+/', '-', $str);
    return trim($str, '-');
}
?>

<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dosya Yönetimi - Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.ckeditor.com/ckeditor5/40.0.0/classic/ckeditor.js"></script>
    <script src="js/editor.js"></script>
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
                                echo 'Dosya başarıyla oluşturuldu.';
                                break;
                            case 'updated':
                                echo 'Dosya başarıyla güncellendi.';
                                break;
                            case 'deleted':
                                echo 'Dosya başarıyla silindi.';
                                break;
                        }
                        ?>
                    </span>
                </div>
            <?php endif; ?>

            <?php if (!empty($_GET['warning'])): ?>
                <div class="bg-yellow-100 border border-yellow-400 text-yellow-800 px-4 py-3 rounded mb-4">
                    <?php echo htmlspecialchars(urldecode($_GET['warning']), ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($_GET['error'])): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <?php echo htmlspecialchars(urldecode($_GET['error']), ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php endif; ?>

            <!-- Search and Filter Section -->
            <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                <form method="get" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <!-- Search Input -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Arama</label>
                            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                                   placeholder="Başlık veya içerik ara..." 
                                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        
                        <!-- Category Filter -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Kategori</label>
                            <select name="category" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Tüm Kategoriler</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>" <?php echo $filter_category == $cat['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cat['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <!-- Status Filter -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Durum</label>
                            <select name="status" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Tüm Durumlar</option>
                                <option value="published" <?php echo $filter_status === 'published' ? 'selected' : ''; ?>>Yayında</option>
                                <option value="draft" <?php echo $filter_status === 'draft' ? 'selected' : ''; ?>>Taslak</option>
                            </select>
                        </div>
                        
                        <!-- Search Button -->
                        <div class="flex items-end">
                            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md transition duration-150 ease-in-out">
                                <i class="fas fa-search mr-2"></i>Ara
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Results Info -->
            <div class="flex justify-between items-center mb-6">
                <div class="text-sm text-gray-600">
                    Toplam <?php echo $total; ?> kayıt bulundu.
                    <?php if ($search || $filter_category || $filter_status): ?>
                        <a href="?" class="text-blue-600 hover:text-blue-800 ml-2">
                            <i class="fas fa-times-circle"></i> Filtreleri Temizle
                        </a>
                    <?php endif; ?>
                </div>
                
                <!-- Add Button -->
                <button onclick="showAddModal()" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md transition duration-150 ease-in-out">
                    <i class="fas fa-plus mr-2"></i>Yeni Dosya Ekle
                </button>
            </div>

            <!-- Data Table -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Başlık</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kategori</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Durum</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tarih</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">İşlemler</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (empty($all_files)): ?>
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                    Kayıt bulunamadı.
                                </td>
                            </tr>
                        <?php else: foreach ($all_files as $item): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <?php if ($item['featured_image']): ?>
                                            <img class="h-10 w-10 rounded-full object-cover mr-3" 
                                                 src="../<?php echo $item['featured_image']; ?>" alt="">
                                        <?php endif; ?>
                                        <div class="text-sm font-medium text-gray-900">
                                            <?php echo htmlspecialchars($item['title']); ?>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900"><?php echo htmlspecialchars($item['category']); ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $item['published'] ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'; ?>">
                                        <?php echo $item['published'] ? 'Yayında' : 'Taslak'; ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo date('d.m.Y H:i', strtotime($item['created_at'])); ?>
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
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
            <div class="flex justify-between items-center mt-6">
                <div class="text-sm text-gray-500">
                    Sayfa <?php echo $page; ?> / <?php echo $total_pages; ?>
                </div>
                <div class="flex gap-1">
                    <?php if ($page > 1): ?>
                        <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>" 
                           class="px-3 py-1 rounded border bg-white text-gray-700 hover:bg-gray-50">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    <?php endif; ?>
                    
                    <?php
                    $start = max(1, $page - 2);
                    $end = min($total_pages, $page + 2);
                    
                    if ($start > 1): ?>
                        <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => 1])); ?>" 
                           class="px-3 py-1 rounded border bg-white text-gray-700 hover:bg-gray-50">1</a>
                        <?php if ($start > 2): ?>
                            <span class="px-3 py-1">...</span>
                        <?php endif; ?>
                    <?php endif; ?>
                    
                    <?php for ($i = $start; $i <= $end; $i++): ?>
                        <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>" 
                           class="px-3 py-1 rounded border <?php echo $i == $page ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50'; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                    
                    <?php if ($end < $total_pages): ?>
                        <?php if ($end < $total_pages - 1): ?>
                            <span class="px-3 py-1">...</span>
                        <?php endif; ?>
                        <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $total_pages])); ?>" 
                           class="px-3 py-1 rounded border bg-white text-gray-700 hover:bg-gray-50"><?php echo $total_pages; ?></a>
                    <?php endif; ?>
                    
                    <?php if ($page < $total_pages): ?>
                        <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>" 
                           class="px-3 py-1 rounded border bg-white text-gray-700 hover:bg-gray-50">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </main>
    </div>
    </div>

    <!-- Add/Edit File Modal -->
    <div id="itemModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full">
        <div class="relative top-20 mx-auto p-5 border w-4/5 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4" id="modalTitle">Yeni Dosya Ekle</h3>
                <form id="itemForm" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="create">
                    <input type="hidden" name="id" id="itemId">
                    <input type="hidden" name="content" id="contentInput">

                    <div class="grid grid-cols-1 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Başlık</label>
                            <input type="text" name="title" id="title" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Kategori</label>
                            <select name="category" id="category" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat['name']; ?>"><?php echo $cat['name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">İçerik</label>
                            <div id="content" class="content-editor"></div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Dosya</label>
                            <div id="currentFile" class="mb-2 text-sm text-gray-600 hidden">
                                <span>Mevcut Dosya: </span>
                                <a href="#" class="text-blue-600 hover:text-blue-800" target="_blank"></a>
                            </div>
                            <input type="file" name="file" id="file" class="mt-1 block w-full">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Öne Çıkan Görsel</label>
                            <div id="currentImage" class="mb-2 hidden">
                                <img src="" alt="Mevcut görsel" class="h-32 w-32 object-cover rounded-lg">
                            </div>
                            <input type="file" name="featured_image" id="featured_image" class="mt-1 block w-full">
                            <p class="mt-1 text-sm text-gray-500">Önerilen boyut: 1200x800 piksel</p>
                        </div>

                        <div>
                            <label class="flex items-center">
                                <input type="checkbox" name="is_featured" id="is_featured"
                                    class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <span class="ml-2 text-sm text-gray-600">Öne Çıkan Dosya</span>
                            </label>
                        </div>

                        <div>
                            <label class="flex items-center">
                                <input type="checkbox" name="published" id="published"
                                    class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <span class="ml-2 text-sm text-gray-600">Yayınla</span>
                            </label>
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
            document.getElementById('modalTitle').textContent = 'Yeni Dosya Ekle';
            document.getElementById('itemForm').reset();
            document.getElementById('itemForm').action.value = 'create';
            document.getElementById('itemId').value = '';
            clearEditorContent('content');
            document.getElementById('currentImage').classList.add('hidden');
            document.getElementById('currentFile').classList.add('hidden');
            document.getElementById('itemModal').classList.remove('hidden');
        }

        function editItem(item) {
            document.getElementById('modalTitle').textContent = 'Dosya Düzenle';
            document.getElementById('itemForm').action.value = 'update';
            document.getElementById('itemId').value = item.id;
            document.getElementById('title').value = item.title;
            document.getElementById('category').value = item.category;
            setEditorContent('content', item.content);
            document.getElementById('published').checked = item.published == 1;
            document.getElementById('is_featured').checked = item.is_featured == 1;

            // Mevcut görseli göster
            const currentImage = document.getElementById('currentImage');
            if (item.featured_image) {
                currentImage.classList.remove('hidden');
                currentImage.querySelector('img').src = '../' + item.featured_image;
            } else {
                currentImage.classList.add('hidden');
            }

            // Mevcut dosyayı göster
            const currentFile = document.getElementById('currentFile');
            if (item.file_path) {
                currentFile.classList.remove('hidden');
                const fileLink = currentFile.querySelector('a');
                fileLink.href = '../' + item.file_path;
                fileLink.textContent = item.file_path.split('/').pop();
            } else {
                currentFile.classList.add('hidden');
            }

            document.getElementById('itemModal').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('itemModal').classList.add('hidden');
        }

        function deleteItem(id) {
            if (confirm('Bu dosyayı silmek istediğinizden emin misiniz?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'dosya.php';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="${id}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Form submission handler
        document.getElementById('itemForm').addEventListener('submit', function (e) {
            e.preventDefault();

            // Get CKEditor content
            const content = getEditorContent('content');

            // Update hidden input with editor content
            document.getElementById('contentInput').value = content;

            // Validate content
            if (!content.trim()) {
                alert('Lütfen içerik alanını doldurun.');
                return;
            }

            // Submit the form
            this.submit();
        });
    </script>
</body>

</html>