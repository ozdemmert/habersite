<?php
require_once 'include/functions.php';
$page_title = "İletişim";
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
        <h1 class="text-3xl font-bold mb-6">İletişim</h1>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <!-- İletişim Bilgileri -->
            <div>
                <h2 class="text-2xl font-semibold mb-4">İletişim Bilgileri</h2>
                <div class="space-y-4">
                    <div>
                        <h3 class="font-semibold text-lg">Adres</h3>
                        <p class="text-gray-600">Örnek Mahallesi, Örnek Sokak No:1<br>34000 İstanbul, Türkiye</p>
                    </div>
                    <div>
                        <h3 class="font-semibold text-lg">Telefon</h3>
                        <p class="text-gray-600">+90 (212) XXX XX XX</p>
                    </div>
                    <div>
                        <h3 class="font-semibold text-lg">E-posta</h3>
                        <p class="text-gray-600">info@habersitesi.com</p>
                    </div>
                </div>
            </div>

            <!-- İletişim Formu -->
            <div>
                <h2 class="text-2xl font-semibold mb-4">Bize Ulaşın</h2>
                <form action="process_contact.php" method="POST" class="space-y-4">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Adınız Soyadınız</label>
                        <input type="text" name="name" id="name" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">E-posta Adresiniz</label>
                        <input type="email" name="email" id="email" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label for="subject" class="block text-sm font-medium text-gray-700">Konu</label>
                        <input type="text" name="subject" id="subject" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label for="message" class="block text-sm font-medium text-gray-700">Mesajınız</label>
                        <textarea name="message" id="message" rows="4" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                    </div>
                    
                    <div>
                        <button type="submit"
                            class="w-full bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                            Gönder
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>

<?php include 'templates/footer.php'; ?> 
</body>
</html> 