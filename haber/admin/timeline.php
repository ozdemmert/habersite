<?php
require_once '../include/functions.php';
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header('Location: ../login.php');
    exit();
}

$timeline = new Timeline();
$category = new Category();

// Arama ve Filtreleme Parametreleri
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$filter_category = isset($_GET['category']) ? $_GET['category'] : '';
$filter_status = isset($_GET['status']) ? $_GET['status'] : '';
$filter_date = isset($_GET['date']) ? $_GET['date'] : '';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// SQL Sorgusu Oluştur
$where_conditions = [];
$params = [];

if ($search !== '') {
    $search_term = '%' . $search . '%';
    $where_conditions[] = "(title LIKE ? OR content LIKE ?)";
    $params[] = $search_term;
    $params[] = $search_term;
}

if ($filter_category !== '') {
    $where_conditions[] = "category = ?";
    $params[] = $filter_category;
}

if ($filter_status !== '') {
    $where_conditions[] = "published = ?";
    $params[] = ($filter_status === 'published') ? 1 : 0;
}

if ($filter_date !== '') {
    $where_conditions[] = "DATE(new_date) = ?";
    $params[] = $filter_date;
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Ana sorgu
$sql = "SELECT * FROM timeline 
        $where_clause 
        ORDER BY new_date DESC, created_at DESC 
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
$all_timelines = $result->fetch_all(MYSQLI_ASSOC);

// Toplam kayıt sayısı
$count_sql = "SELECT COUNT(*) as total FROM timeline $where_clause";
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
                $data = [
                    'title' => $_POST['title'],
                    'new_date' => $_POST['date'],
                    'content' => $_POST['content'],
                    'category' => $_POST['category'],
                    'user_id' => $_SESSION['user_id'],
                    'published' => isset($_POST['published']) ? 1 : 0,
                ];

                // Handle file upload
                if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] === 0) {
                    $upload_dir = '../uploads/timeline/';
                    if (!file_exists($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }

                    $file_name = time() . '_' . $_FILES['featured_image']['name'];
                    $file_path = $upload_dir . $file_name;

                    if (move_uploaded_file($_FILES['featured_image']['tmp_name'], $file_path)) {
                        $data['featured_image'] = 'uploads/timeline/' . $file_name;
                    }
                }

                if ($timeline->create($data)) {
                    header('Location: timeline.php?success=created');
                    exit();
                }
                break;

            case 'update':
                $id = (int) $_POST['id'];
                $data = [
                    'title' => $_POST['title'],
                    'new_date' => $_POST['date'],
                    'category' => $_POST['category'],
                    'content' => $_POST['content'],
                    'published' => isset($_POST['published']) ? 1 : 0,
                ];

                // Handle file upload
                if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] === 0) {
                    $upload_dir = '../uploads/timeline/';
                    if (!file_exists($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }

                    $file_name = time() . '_' . $_FILES['featured_image']['name'];
                    $file_path = $upload_dir . $file_name;

                    if (move_uploaded_file($_FILES['featured_image']['tmp_name'], $file_path)) {
                        $data['featured_image'] = 'uploads/timeline/' . $file_name;
                    }
                }

                if ($timeline->update($id, $data)) {
                    header('Location: timeline.php?success=updated');
                    exit();
                }
                break;

            case 'delete':
                $id = (int) $_POST['id'];
                if ($timeline->delete($id)) {
                    header('Location: timeline.php?success=deleted');
                    exit();
                }
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
    <title>Timeline Yönetimi - Admin Panel</title>
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
                                echo 'Zaman çizelgesi başarıyla oluşturuldu.';
                                break;
                            case 'updated':
                                echo 'Zaman çizelgesi başarıyla güncellendi.';
                                break;
                            case 'deleted':
                                echo 'Zaman çizelgesi başarıyla silindi.';
                                break;
                        }
                        ?>
                    </span>
                </div>
            <?php endif; ?>

            <!-- Search and Filter Section -->
            <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                <form method="get" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
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
                                    <option value="<?php echo $cat['name']; ?>" <?php echo $filter_category === $cat['name'] ? 'selected' : ''; ?>>
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

                        <!-- Date Filter -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tarih</label>
                            <input type="date" name="date" value="<?php echo $filter_date; ?>" 
                                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
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
                    <?php if ($search || $filter_category || $filter_status || $filter_date): ?>
                        <a href="?" class="text-blue-600 hover:text-blue-800 ml-2">
                            <i class="fas fa-times-circle"></i> Filtreleri Temizle
                        </a>
                    <?php endif; ?>
                </div>
                
                <!-- Add Button -->
                <button onclick="showAddModal()" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md transition duration-150 ease-in-out">
                    <i class="fas fa-plus mr-2"></i>Yeni Zaman Çizelgesi Ekle
                </button>
            </div>

            <!-- Data Table -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Başlık</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Tarih</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                İşlemler</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (empty($all_timelines)): ?>
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                    Kayıt bulunamadı.
                                </td>
                            </tr>
                        <?php else: foreach ($all_timelines as $item): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900"><?php echo $item['title']; ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900"><?php echo $item['new_date']; ?></div>
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

    <!-- Add/Edit Modal -->
    <div id="itemModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full">
        <div class="relative top-20 mx-auto p-5 border w-4/5 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4" id="modalTitle">Yeni Zaman Çizelgesi Ekle
                </h3>
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
                            <label class="block text-sm font-medium text-gray-700">Tarih</label>
                            <input type="date" name="date" id="date" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">İçerik</label>
                            <div id="content" class="content-editor"></div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Öne Çıkan Görsel</label>
                            <input type="file" name="featured_image" id="featured_image" class="mt-1 block w-full">
                            <p class="mt-1 text-sm text-gray-500">Önerilen boyut: 1200x800 piksel</p>
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
            document.getElementById('modalTitle').textContent = 'Yeni Zaman Çizelgesi Ekle';
            document.getElementById('itemForm').reset();
            document.getElementById('itemForm').action.value = 'create';
            document.getElementById('itemId').value = '';
            clearEditorContent('content');
            document.getElementById('itemModal').classList.remove('hidden');
        }

        function editItem(item) {
            document.getElementById('modalTitle').textContent = 'Zaman Çizelgesi Düzenle';
            document.getElementById('itemForm').action.value = 'update';
            document.getElementById('itemId').value = item.id;
            document.getElementById('title').value = item.title;
            document.getElementById('category').value = item.category;
            document.getElementById('date').value = item.new_date;
            setEditorContent('content', item.content);
            document.getElementById('published').checked = item.published == 1;
            document.getElementById('itemModal').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('itemModal').classList.add('hidden');
        }

        function deleteItem(id) {
            if (confirm('Bu zaman çizelgesini silmek istediğinizden emin misiniz?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'timeline.php';
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