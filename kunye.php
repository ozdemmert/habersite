<?php
require_once 'include/functions.php';
$page_title = "Künye";
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - Haber Sitesi</title>
    <link rel="icon" type="image/png" href="assets/images/minilogo.png">
    <link rel="shortcut icon" type="image/png" href="assets/images/minilogo.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>
<body class="bg-gray-50">
<?php include 'templates/header.php'; ?>
<?php require_once 'templates/backtotopbutton.php'; ?>

<main class="max-w-5xl mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow-sm p-8">
        <h1 class="text-3xl font-bold mb-6">Künye</h1>
        
        <div class="prose max-w-none">
            <h2 class="text-2xl font-semibold mt-8 mb-4">Yönetim</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <div class="border-b pb-4">
                    <h3 class="font-semibold text-lg">Genel Yayın Yönetmeni</h3>
                    <p class="text-gray-600">Ad Soyad</p>
                </div>
                <div class="border-b pb-4">
                    <h3 class="font-semibold text-lg">Yayın Koordinatörü</h3>
                    <p class="text-gray-600">Ad Soyad</p>
                </div>
            </div>

            <h2 class="text-2xl font-semibold mt-8 mb-4">Editörler</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <div class="border-b pb-4">
                    <h3 class="font-semibold text-lg">Sorumlu Yazı İşleri Müdürü</h3>
                    <p class="text-gray-600">Ad Soyad</p>
                </div>
                <div class="border-b pb-4">
                    <h3 class="font-semibold text-lg">Dijital Editör</h3>
                    <p class="text-gray-600">Ad Soyad</p>
                </div>
            </div>

            <h2 class="text-2xl font-semibold mt-8 mb-4">İletişim Bilgileri</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="border-b pb-4">
                    <h3 class="font-semibold text-lg">Adres</h3>
                    <p class="text-gray-600">Örnek Mahallesi, Örnek Sokak No:1<br>34000 İstanbul, Türkiye</p>
                </div>
                <div class="border-b pb-4">
                    <h3 class="font-semibold text-lg">İletişim</h3>
                    <p class="text-gray-600">
                        Tel: +90 (212) XXX XX XX<br>
                        E-posta: info@habersitesi.com
                    </p>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include 'templates/footer.php'; ?> 
</body>
</html> 