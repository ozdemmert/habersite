<?php
require_once '../include/functions.php';
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header('Location: ../login.php');
    exit();
}

$fourNokta = new FourNokta();
$category = new Category();

// Arama ve Filtreleme Parametreleri
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$filter_category = isset($_GET['category']) ? $_GET['category'] : '';
$filter_status = isset($_GET['status']) ? $_GET['status'] : '';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// SQL Sorgusu Oluştur
$where_conditions = [];
$params = [];

if ($search !== '') {
    $search_term = '%' . $search . '%';
    $where_conditions[] = "(title LIKE ? OR authors LIKE ?)";
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

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Ana sorgu
$sql = "SELECT * FROM 4nokta1 
        $where_clause 
        ORDER BY created_at DESC 
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
$all_items = $result->fetch_all(MYSQLI_ASSOC);

// Toplam kayıt sayısı
$count_sql = "SELECT COUNT(*) as total FROM 4nokta1 $where_clause";
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
                    'explanation' => $_POST['explanation'],
                    'category' => $_POST['category'],
                    'slug' => createSlug($_POST['title']),
                    'authors' => implode(', ', $_POST['authors']),
                    'authors_info' => json_encode($_POST['authors_info']),
                    'authors_comments' => json_encode($_POST['authors_comments']),
                    'meta_description' => $_POST['meta_description'],
                    'user_id' => $_SESSION['user_id'],
                    'published' => isset($_POST['published']) ? 1 : 0
                ];

                // Handle file upload
                if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] === 0) {
                    $upload_dir = '../uploads/4nokta1/';
                    if (!file_exists($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }

                    $file_name = time() . '_' . $_FILES['featured_image']['name'];
                    $file_path = $upload_dir . $file_name;

                    if (move_uploaded_file($_FILES['featured_image']['tmp_name'], $file_path)) {
                        $data['featured_image'] = 'uploads/4nokta1/' . $file_name;
                    }
                }

                // Handle author images upload
                $author_images = [];
                if (!empty($_FILES['authors_image']['name'][0])) {
                    $author_upload_dir = '../uploads/4nokta1/authors/';
                    if (!file_exists($author_upload_dir)) {
                        mkdir($author_upload_dir, 0777, true);
                    }

                    foreach ($_FILES['authors_image']['tmp_name'] as $index => $tmp_name) {
                        if ($_FILES['authors_image']['error'][$index] === 0) {
                            $file_name = time() . '_' . $index . '_' . $_FILES['authors_image']['name'][$index];
                            $file_path = $author_upload_dir . $file_name;

                            if (move_uploaded_file($tmp_name, $file_path)) {
                                $author_images[] = 'uploads/4nokta1/authors/' . $file_name;
                            }
                        }
                    }

                    if (!empty($author_images)) {
                        $data['authors_image'] = json_encode($author_images);
                    }
                }

                if ($fourNokta->create($data)) {
                    header('Location: 4nokta1.php?success=created');
                    exit();
                }
                break;

            case 'update':
                $id = (int) $_POST['id'];
                $data = [
                    'title' => $_POST['title'],
                    'explanation' => $_POST['explanation'],
                    'category' => $_POST['category'],
                    'slug' => createSlug($_POST['title']),
                    'authors' => implode(', ', $_POST['authors']),
                    'authors_info' => json_encode($_POST['authors_info']),
                    'authors_comments' => json_encode($_POST['authors_comments']),
                    'meta_description' => $_POST['meta_description'],
                    'published' => isset($_POST['published']) ? 1 : 0
                ];

                // Handle featured image upload
                if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] === 0) {
                    $upload_dir = '../uploads/4nokta1/';
                    if (!file_exists($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }

                    $file_name = time() . '_' . $_FILES['featured_image']['name'];
                    $file_path = $upload_dir . $file_name;

                    if (move_uploaded_file($_FILES['featured_image']['tmp_name'], $file_path)) {
                        $data['featured_image'] = 'uploads/4nokta1/' . $file_name;
                    }
                }

                // Handle author images upload
                $author_images = [];
                if (!empty($_FILES['authors_image']['name'][0])) {
                    $author_upload_dir = '../uploads/4nokta1/authors/';
                    if (!file_exists($author_upload_dir)) {
                        mkdir($author_upload_dir, 0777, true);
                    }

                    foreach ($_FILES['authors_image']['tmp_name'] as $index => $tmp_name) {
                        if ($_FILES['authors_image']['error'][$index] === 0) {
                            $file_name = time() . '_' . $index . '_' . $_FILES['authors_image']['name'][$index];
                            $file_path = $author_upload_dir . $file_name;

                            if (move_uploaded_file($tmp_name, $file_path)) {
                                $author_images[] = 'uploads/4nokta1/authors/' . $file_name;
                            }
                        }
                    }

                    if (!empty($author_images)) {
                        $data['authors_image'] = json_encode($author_images);
                    }
                }

                if ($fourNokta->update($id, $data)) {
                    header('Location: 4nokta1.php?success=updated');
                    exit();
                }
                break;


            case 'delete':
                $id = (int) $_POST['id'];
                if ($fourNokta->delete($id)) {
                    header('Location: 4nokta1.php?success=deleted');
                    exit();
                }
                break;
        }
    }
}

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

    <link rel="icon" href="../assets/images/minilogo.png">  

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>4 Nokta 1 Yönetimi - Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.ckeditor.com/ckeditor5/40.0.0/classic/ckeditor.js"></script>
</head>

<body class="bg-gray-100">
    <!-- Sidebar -->
    <?php include 'components/sidebar.php'; ?>

    <!-- Main Content Wrapper -->
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
                                echo 'İçerik başarıyla oluşturuldu.';
                                break;
                            case 'updated':
                                echo 'İçerik başarıyla güncellendi.';
                                break;
                            case 'deleted':
                                echo 'İçerik başarıyla silindi.';
                                break;
                        }
                        ?>
                    </span>
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
                                   placeholder="Başlık veya yazar ara..." 
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
                    <i class="fas fa-plus mr-2"></i>Yeni 4 Nokta 1 Ekle
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
                                Kategori</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Yazarlar</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Durum</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Tarih</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                İşlemler</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (empty($all_items)): ?>
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                    Kayıt bulunamadı.
                                </td>
                            </tr>
                        <?php else: foreach ($all_items as $item): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <?php if ($item['featured_image']): ?>
                                            <img class="h-10 w-10 rounded-full object-cover mr-3"
                                                src="../<?php echo $item['featured_image']; ?>" alt="">
                                        <?php endif; ?>
                                        <div class="text-sm font-medium text-gray-900"><?php echo $item['title']; ?>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900"><?php echo $item['category']; ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900"><?php echo $item['authors']; ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $item['published'] ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'; ?>">
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

    <!-- Add/Edit Modal -->
    <div id="itemModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full">
        <div class="relative top-20 mx-auto p-5 border w-4/5 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4" id="modalTitle">Yeni İçerik Ekle</h3>
                <form id="itemForm" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="create">
                    <input type="hidden" name="id" id="itemId">
                    <input type="hidden" name="authors_info[]" id="authorsInfoInput1">
                    <input type="hidden" name="authors_info[]" id="authorsInfoInput2">
                    <input type="hidden" name="authors_info[]" id="authorsInfoInput3">
                    <input type="hidden" name="authors_info[]" id="authorsInfoInput4">
                    <input type="hidden" name="authors_comments[]" id="authorsCommentsInput1">
                    <input type="hidden" name="authors_comments[]" id="authorsCommentsInput2">
                    <input type="hidden" name="authors_comments[]" id="authorsCommentsInput3">
                    <input type="hidden" name="authors_comments[]" id="authorsCommentsInput4">

                    <div class="grid grid-cols-1 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Başlık</label>
                            <input type="text" name="title" id="title" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Açıklama</label>
                            <input type="text" name="explanation" id="explanation" required
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

                        <!-- Yazar 1 -->
                        <div class="border p-4 rounded-lg">
                            <h4 class="font-medium mb-2">1. Yazar</h4>
                            <div class="grid grid-cols-1 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Yazar Adı</label>
                                    <input type="text" name="authors[]" required
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Yazar Resmi</label>
                                    <input type="file" name="authors_image[]"
                                        class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Yazar Hakkında</label>
                                    <div id="authors_info_1" class="content-editor"></div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Yazar Yorumu</label>
                                    <div id="authors_comments_1" class="content-editor"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Yazar 2 -->
                        <div class="border p-4 rounded-lg">
                            <h4 class="font-medium mb-2">2. Yazar</h4>
                            <div class="grid grid-cols-1 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Yazar Adı</label>
                                    <input type="text" name="authors[]" required
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Yazar Resmi</label>
                                    <input type="file" name="authors_image[]"
                                        class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Yazar Hakkında</label>
                                    <div id="authors_info_2" class="content-editor"></div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Yazar Yorumu</label>
                                    <div id="authors_comments_2" class="content-editor"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Yazar 3 -->
                        <div class="border p-4 rounded-lg">
                            <h4 class="font-medium mb-2">3. Yazar</h4>
                            <div class="grid grid-cols-1 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Yazar Adı</label>
                                    <input type="text" name="authors[]" required
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Yazar Resmi</label>
                                    <input type="file" name="authors_image[]"
                                        class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Yazar Hakkında</label>
                                    <div id="authors_info_3" class="content-editor"></div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Yazar Yorumu</label>
                                    <div id="authors_comments_3" class="content-editor"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Yazar 4 -->
                        <div class="border p-4 rounded-lg">
                            <h4 class="font-medium mb-2">4. Yazar</h4>
                            <div class="grid grid-cols-1 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Yazar Adı</label>
                                    <input type="text" name="authors[]" required
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Yazar Resmi</label>
                                    <input type="file" name="authors_image[]"
                                        class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Yazar Hakkında</label>
                                    <div id="authors_info_4" class="content-editor"></div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Yazar Yorumu</label>
                                    <div id="authors_comments_4" class="content-editor"></div>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Meta Açıklama</label>
                            <textarea name="meta_description" id="meta_description"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Öne Çıkan Görsel</label>
                            <input type="file" name="featured_image" id="featured_image" class="mt-1 block w-full">
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

        let authorsInfoEditor1, authorsCommentsEditor1;
        let authorsInfoEditor2, authorsCommentsEditor2;
        let authorsInfoEditor3, authorsCommentsEditor3;
        let authorsInfoEditor4, authorsCommentsEditor4;

        // Initialize CKEditors
        ClassicEditor
            .create(document.querySelector('#authors_info_1'), {
                toolbar: ['heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', '|', 'undo', 'redo'],
                placeholder: '1. Yazara ait bilgi girin...'
            })
            .then(newEditor => {
                authorsInfoEditor1 = newEditor;
            })
            .catch(error => {
                console.error(error);
            });

        ClassicEditor
            .create(document.querySelector('#authors_comments_1'), {
                toolbar: ['heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', '|', 'undo', 'redo'],
                placeholder: '1. Yazara ait yorum girin...'
            })
            .then(newEditor => {
                authorsCommentsEditor1 = newEditor;
            })
            .catch(error => {
                console.error(error);
            });

        ClassicEditor
            .create(document.querySelector('#authors_info_2'), {
                toolbar: ['heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', '|', 'undo', 'redo'],
                placeholder: '2. Yazara ait bilgi girin...'
            })
            .then(newEditor => {
                authorsInfoEditor2 = newEditor;
            })
            .catch(error => {
                console.error(error);
            });

        ClassicEditor
            .create(document.querySelector('#authors_comments_2'), {
                toolbar: ['heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', '|', 'undo', 'redo'],
                placeholder: '2. Yazara ait yorum girin...'
            })
            .then(newEditor => {
                authorsCommentsEditor2 = newEditor;
            })
            .catch(error => {
                console.error(error);
            });

        ClassicEditor
            .create(document.querySelector('#authors_info_3'), {
                toolbar: ['heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', '|', 'undo', 'redo'],
                placeholder: '3. Yazara ait bilgi girin...'
            })
            .then(newEditor => {
                authorsInfoEditor3 = newEditor;
            })
            .catch(error => {
                console.error(error);
            });

        ClassicEditor
            .create(document.querySelector('#authors_comments_3'), {
                toolbar: ['heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', '|', 'undo', 'redo'],
                placeholder: '3. Yazara ait yorum girin...'
            })
            .then(newEditor => {
                authorsCommentsEditor3 = newEditor;
            })
            .catch(error => {
                console.error(error);
            });

        ClassicEditor
            .create(document.querySelector('#authors_info_4'), {
                toolbar: ['heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', '|', 'undo', 'redo'],
                placeholder: '4. Yazara ait bilgi girin...'
            })
            .then(newEditor => {
                authorsInfoEditor4 = newEditor;
            })
            .catch(error => {
                console.error(error);
            });

        ClassicEditor
            .create(document.querySelector('#authors_comments_4'), {
                toolbar: ['heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', '|', 'undo', 'redo'],
                placeholder: '4. Yazara ait yorum girin...'
            })
            .then(newEditor => {
                authorsCommentsEditor4 = newEditor;
            })
            .catch(error => {
                console.error(error);
            });

        // Modal Functions
        function showAddModal() {
            document.getElementById('modalTitle').textContent = 'Yeni 4 Nokta Ekle';
            document.getElementById('itemForm').reset();
            document.getElementById('itemForm').action.value = 'create';
            document.getElementById('itemId').value = '';

            // Clear all editors
            if (authorsInfoEditor1) authorsInfoEditor1.setData('');
            if (authorsCommentsEditor1) authorsCommentsEditor1.setData('');
            if (authorsInfoEditor2) authorsInfoEditor2.setData('');
            if (authorsCommentsEditor2) authorsCommentsEditor2.setData('');
            if (authorsInfoEditor3) authorsInfoEditor3.setData('');
            if (authorsCommentsEditor3) authorsCommentsEditor3.setData('');
            if (authorsInfoEditor4) authorsInfoEditor4.setData('');
            if (authorsCommentsEditor4) authorsCommentsEditor4.setData('');

            document.getElementById('itemModal').classList.remove('hidden');
        }

        function editItem(item) {
            document.getElementById('modalTitle').textContent = '4 Nokta Düzenle';
            document.getElementById('itemForm').action.value = 'update';
            document.getElementById('itemId').value = item.id;
            document.getElementById('title').value = item.title;
            document.getElementById('category').value = item.category;

            // Set author names
            const authorNames = item.authors.split(', ');
            document.querySelectorAll('input[name="authors[]"]').forEach((input, index) => {
                input.value = authorNames[index] || '';
            });

            // Set editor content
            const authorsInfo = JSON.parse(item.authors_info || '[]');
            const authorsComments = JSON.parse(item.authors_comments || '[]');

            if (authorsInfoEditor1) authorsInfoEditor1.setData(authorsInfo[0] || '');
            if (authorsCommentsEditor1) authorsCommentsEditor1.setData(authorsComments[0] || '');
            if (authorsInfoEditor2) authorsInfoEditor2.setData(authorsInfo[1] || '');
            if (authorsCommentsEditor2) authorsCommentsEditor2.setData(authorsComments[1] || '');
            if (authorsInfoEditor3) authorsInfoEditor3.setData(authorsInfo[2] || '');
            if (authorsCommentsEditor3) authorsCommentsEditor3.setData(authorsComments[2] || '');
            if (authorsInfoEditor4) authorsInfoEditor4.setData(authorsInfo[3] || '');
            if (authorsCommentsEditor4) authorsCommentsEditor4.setData(authorsComments[3] || '');

            document.getElementById('meta_description').value = item.meta_description;
            document.getElementById('published').checked = item.published == 1;
            document.getElementById('itemModal').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('itemModal').classList.add('hidden');
        }

        function deleteItem(id) {
            if (confirm('Bu 4 noktayı silmek istediğinizden emin misiniz?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '4nokta1.php';
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
            const authorsInfo = [
                authorsInfoEditor1 ? authorsInfoEditor1.getData() : '',
                authorsInfoEditor2 ? authorsInfoEditor2.getData() : '',
                authorsInfoEditor3 ? authorsInfoEditor3.getData() : '',
                authorsInfoEditor4 ? authorsInfoEditor4.getData() : ''
            ];

            const authorsComments = [
                authorsCommentsEditor1 ? authorsCommentsEditor1.getData() : '',
                authorsCommentsEditor2 ? authorsCommentsEditor2.getData() : '',
                authorsCommentsEditor3 ? authorsCommentsEditor3.getData() : '',
                authorsCommentsEditor4 ? authorsCommentsEditor4.getData() : ''
            ];

            // Update hidden inputs with editor content
            document.getElementById('authorsInfoInput1').value = authorsInfo[0];
            document.getElementById('authorsInfoInput2').value = authorsInfo[1];
            document.getElementById('authorsInfoInput3').value = authorsInfo[2];
            document.getElementById('authorsInfoInput4').value = authorsInfo[3];

            document.getElementById('authorsCommentsInput1').value = authorsComments[0];
            document.getElementById('authorsCommentsInput2').value = authorsComments[1];
            document.getElementById('authorsCommentsInput3').value = authorsComments[2];
            document.getElementById('authorsCommentsInput4').value = authorsComments[3];

            // Validate content
            for (let i = 0; i < 4; i++) {
                if (!authorsInfo[i].trim()) {
                    alert(`Lütfen ${i + 1}. yazara ait bilgi alanını doldurun.`);
                    return;
                }
                if (!authorsComments[i].trim()) {
                    alert(`Lütfen ${i + 1}. yazara ait yorum alanını doldurun.`);
                    return;
                }
            }

            // Submit the form
            this.submit();
        });
    </script>
</body>

</html>