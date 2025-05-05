<?php
require_once '../include/functions.php';
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header('Location: ../login.php');
    exit();
}

$portre = new Portre();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            // Inside the 'create' case
            case 'create':
                // Check if the file was uploaded without errors
                if (!isset($_FILES['portre_image']) || $_FILES['portre_image']['error'] !== UPLOAD_ERR_OK) {
                    header('Location: portre.php?error=no_image');
                    exit();
                }

                $data = [
                    'first_name' => $_POST['first_name'],
                    'lastname' => $_POST['lastname'],
                    'biography' => $_POST['biography'],
                    'degree' => $_POST['degree'],
                    'quote' => $_POST['quote'],
                    'user_id' => $_SESSION['user_id'],
                    'username' => $_SESSION['username']
                ];

                // Proceed with file upload
                $upload_dir = '../uploads/portre/';
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }

                $file_name = time() . '_' . $_FILES['portre_image']['name'];
                $file_path = $upload_dir . $file_name;

                if (move_uploaded_file($_FILES['portre_image']['tmp_name'], $file_path)) {
                    $data['portre_image'] = 'uploads/portre/' . $file_name;
                } else {
                    header('Location: portre.php?error=upload_failed');
                    exit();
                }

                $result = $portre->create($data);
                
                if (!$result) {
                    $error = $portre->getErrorMessage();
                    header("Location: portre.php?error=" . urlencode($error));
                    exit;
                }
                
                header("Location: portre.php?success=created");
                exit;

            case 'update':
                $id = (int) $_POST['id'];
                $data = [
                    'first_name' => $_POST['first_name'],
                    'lastname' => $_POST['lastname'],
                    'biography' => $_POST['biography'],
                    'degree' => $_POST['degree'],
                    'quote' => $_POST['quote']
                ];

                // Handle file upload
                if (isset($_FILES['portre_image']) && $_FILES['portre_image']['error'] === 0) {
                    $upload_dir = '../uploads/portre/';
                    if (!file_exists($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }

                    $file_name = time() . '_' . $_FILES['portre_image']['name'];
                    $file_path = $upload_dir . $file_name;

                    if (move_uploaded_file($_FILES['portre_image']['tmp_name'], $file_path)) {
                        $data['portre_image'] = 'uploads/portre/' . $file_name;
                    }
                }

                if (!isset($data['portre_image'])) {
                    $existing = $portre->getById($id);
                    if ($existing && isset($existing['portre_image'])) {
                        $data['portre_image'] = $existing['portre_image'];
                    }
                }

                if ($portre->update($id, $data)) {
                    header('Location: portre.php?success=updated');
                    exit();
                }
                break;

            case 'delete':
                $id = (int) $_POST['id'];
                if ($portre->delete($id)) {
                    header('Location: portre.php?success=deleted');
                    exit();
                }
                break;
        }
    }
}

// Get all portres
$all_portres = $portre->getAll();
?>

<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portre Yönetimi - Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.ckeditor.com/ckeditor5/40.0.0/classic/ckeditor.js"></script>
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
                                echo 'Portre başarıyla oluşturuldu.';
                                break;
                            case 'updated':
                                echo 'Portre başarıyla güncellendi.';
                                break;
                            case 'deleted':
                                echo 'Portre başarıyla silindi.';
                                break;
                        }
                        ?>
                    </span>
                </div>
            <?php endif; ?>

            <!-- Add Portre Button -->
            <div class="mb-6">
                <button onclick="showAddModal()"
                    class="bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-4 rounded">
                    <i class="fas fa-plus mr-2"></i>Yeni Portre Ekle
                </button>
            </div>

            <!-- Portres List -->
            <div class="bg-white rounded-lg shadow-md">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Ad Soyad</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Unvan</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    İşlemler</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($all_portres as $item): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <?php if ($item['portre_image']): ?>
                                                <img class="h-10 w-10 rounded-full object-cover mr-3"
                                                    src="../<?php echo $item['portre_image']; ?>" alt="">
                                            <?php endif; ?>
                                            <div class="text-sm font-medium text-gray-900">
                                                <?php echo $item['first_name'] . ' ' . $item['lastname']; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900"><?php echo $item['degree']; ?></div>
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

    <!-- Add/Edit Portre Modal -->
    <div id="itemModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full">
        <div class="relative top-20 mx-auto p-5 border w-4/5 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4" id="modalTitle">Yeni Portre Ekle</h3>
                <form id="itemForm"     method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="create">
                    <input type="hidden" name="id" id="itemId">
                    <input type="hidden" name="biography" id="biographyEditorInput">

                    <div class="grid grid-cols-1 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">İsim</label>
                            <input type="text" name="first_name" id="first_name" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Soyisim</label>
                            <input type="text" name="lastname" id="lastname" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Ünvan</label>
                            <input type="text" name="degree" id="degree" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Önemli Söz</label>
                            <input type="text" name="quote" id="quote"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="Şahsiyetin önemli bir sözü">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Biyografi</label>
                            <div id="biography" class="content-editor"></div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Görsel</label>
                            <input type="file" name="portre_image" id="image" class="mt-1 block w-full">
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
    <script src="js/editor.js"></script>

    <script>
        // Mobile menu toggle
        document.querySelector('.md\\:hidden').addEventListener('click', function () {
            document.querySelector('.bg-gray-800').classList.toggle('-translate-x-full');
        });

        let editor;

        // Modal Functions
        function showAddModal() {
            document.getElementById('modalTitle').textContent = 'Yeni Portre Ekle';
            document.getElementById('itemForm').reset();
            document.getElementById('itemForm').action.value = 'create';
            document.getElementById('itemId').value = '';
            clearEditorContent('biography');
            document.getElementById('itemModal').classList.remove('hidden');
        }

        function editItem(item) {
            document.getElementById('modalTitle').textContent = 'Portre Düzenle';
            document.getElementById('itemForm').action.value = 'update';
            document.getElementById('itemId').value = item.id;
            document.getElementById('first_name').value = item.first_name;
            document.getElementById('lastname').value = item.lastname;
            setEditorContent('biography', item.biography);
            document.getElementById('itemModal').classList.remove('hidden');
            document.getElementById('degree').value = item.degree;
            document.getElementById('quote').value = item.quote || '';
        }

        function closeModal() {
            document.getElementById('itemModal').classList.add('hidden');
        }

        function deleteItem(id) {
            if (confirm('Bu portreyi silmek istediğinizden emin misiniz?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'portre.php';
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
            const biography = getEditorContent('biography');

            // Update hidden input with editor content
            document.getElementById('biographyEditorInput').value = biography;

            // Validate content
            if (!biography.trim()) {
                alert('Lütfen biyografi alanını doldurun.');
                return;
            }

            // Submit the form
            this.submit();
        });
    </script>
</body>

</html>