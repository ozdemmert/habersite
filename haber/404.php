<?php http_response_code(404); ?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tarih Sayfalarından</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-white">
<?php
require_once 'templates/header.php';
?>
    <main class="min-h-screen">
        <div class="flex flex-col items-center justify-center min-h-[60vh] bg-gray-50 px-4 py-16">
            <div class="text-center">
        <div class="mb-8 flex justify-center">
            <img src="assets/images/minilogo.png" alt="Bandor Gazete Logo" class="w-32 h-32">
        </div>
        
        <h1 class="text-6xl font-bold text-gray-800 mb-4">404</h1>
        <h2 class="text-2xl font-semibold text-gray-700 mb-6">Sayfa Bulunamadı</h2>
        
        <p class="text-gray-600 max-w-md mx-auto mb-8">
            Aradığınız sayfa bulunamadı veya taşınmış olabilir. 
            Lütfen ana sayfaya dönün veya başka bir sayfayı ziyaret edin.
        </p>
        
        <div class="flex justify-center space-x-4">
            <a href="index.php" class="px-6 py-3 bg-blue-900 hover:bg-blue-800 text-white font-medium rounded-md transition duration-300">
                Ana Sayfaya Dön
            </a>
            <a href="javascript:history.back()" class="px-6 py-3 border border-gray-300 hover:bg-gray-100 text-gray-700 font-medium rounded-md transition duration-300">
                Geri Git
            </a>
        </div>
    </div>
</div>  
</main>


    
</body>
<?php
require_once 'templates/footer.php';
?> 