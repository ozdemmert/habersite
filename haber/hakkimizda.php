<?php
require_once 'include/functions.php';
$page_title = "Hakkımızda";
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

<main class="max-w-5xl mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow-sm p-8">
        <h1 class="text-3xl font-bold mb-6">Hakkımızda</h1>
        
        <div class="prose max-w-none">
            <p class="text-gray-600 mb-4">
                Gazete BanDor olarak, 2024 yılından bu yana Türkiye'nin en güvenilir haber kaynaklarından biri olarak hizmet vermekteyiz. Amacımız, okuyucularımıza tarafsız, doğru ve güncel haberleri ulaştırmaktır.
            </p>
            
            <h2 class="text-2xl font-semibold mt-8 mb-4">Misyonumuz</h2>
            <p class="text-gray-600 mb-4">
                Toplumu bilgilendirmek, eğitmek ve bilinçlendirmek için tarafsız, doğru ve güncel haberleri okuyucularımıza ulaştırmak. Her türlü haberi, etik değerler çerçevesinde, toplumun çıkarlarını gözeterek sunmak.
            </p>
            
            <h2 class="text-2xl font-semibold mt-8 mb-4">Vizyonumuz</h2>
            <p class="text-gray-600 mb-4">
                Türkiye'nin en güvenilir ve tercih edilen haber kaynağı olmak. Dijital çağın gerekliliklerini yerine getirerek, okuyucularımıza en iyi haber deneyimini sunmak.
            </p>
            
            <h2 class="text-2xl font-semibold mt-8 mb-4">Değerlerimiz</h2>
            <ul class="list-disc pl-6 text-gray-600 space-y-2">
                <li>Tarafsızlık ve objektiflik</li>
                <li>Doğruluk ve güvenilirlik</li>
                <li>Etik değerlere bağlılık</li>
                <li>Toplumsal sorumluluk</li>
                <li>Yenilikçilik ve sürekli gelişim</li>
            </ul>
        </div>
    </div>
</main>

<?php include 'templates/footer.php'; ?> 
</body>
</html> 