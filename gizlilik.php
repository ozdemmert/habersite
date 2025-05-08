<?php
require_once 'include/functions.php';
$page_title = "Gizlilik Politikası";
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
<div id="headerPlaceholder" class="hidden transition-all duration-300"></div>

<main class="max-w-5xl mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow-sm p-8">
        <h1 class="text-3xl font-bold mb-6">Gizlilik Politikası</h1>
        
        <div class="prose max-w-none">
            <p class="text-gray-600 mb-4">
                Bu gizlilik politikası, Haber Sitesi'nin kullanıcılarına ait kişisel verilerin nasıl toplandığını, kullanıldığını ve korunduğunu açıklamaktadır.
            </p>

            <h2 class="text-2xl font-semibold mt-8 mb-4">Toplanan Bilgiler</h2>
            <p class="text-gray-600 mb-4">
                Sitemizi kullanırken aşağıdaki bilgiler toplanabilir:
            </p>
            <ul class="list-disc pl-6 text-gray-600 space-y-2">
                <li>Ad ve soyad</li>
                <li>E-posta adresi</li>
                <li>İletişim bilgileri</li>
                <li>IP adresi</li>
                <li>Tarayıcı bilgileri</li>
                <li>Ziyaret edilen sayfalar ve süreleri</li>
            </ul>

            <h2 class="text-2xl font-semibold mt-8 mb-4">Bilgilerin Kullanımı</h2>
            <p class="text-gray-600 mb-4">
                Toplanan bilgiler aşağıdaki amaçlar için kullanılmaktadır:
            </p>
            <ul class="list-disc pl-6 text-gray-600 space-y-2">
                <li>Hizmetlerimizi sunmak ve geliştirmek</li>
                <li>Kullanıcı deneyimini kişiselleştirmek</li>
                <li>İletişim ve destek sağlamak</li>
                <li>Güvenlik ve dolandırıcılık önleme</li>
            </ul>

            <h2 class="text-2xl font-semibold mt-8 mb-4">Çerezler</h2>
            <p class="text-gray-600 mb-4">
                Sitemiz, kullanıcı deneyimini geliştirmek için çerezler kullanmaktadır. Çerezler, tarayıcınız aracılığıyla cihazınıza yerleştirilen küçük metin dosyalarıdır.
            </p>

            <h2 class="text-2xl font-semibold mt-8 mb-4">Veri Güvenliği</h2>
            <p class="text-gray-600 mb-4">
                Kişisel verilerinizin güvenliği bizim için önemlidir. Verilerinizi korumak için uygun teknik ve organizasyonel önlemler alınmaktadır.
            </p>

            <h2 class="text-2xl font-semibold mt-8 mb-4">Haklarınız</h2>
            <p class="text-gray-600 mb-4">
                KVKK kapsamında aşağıdaki haklara sahipsiniz:
            </p>
            <ul class="list-disc pl-6 text-gray-600 space-y-2">
                <li>Kişisel verilerinizin işlenip işlenmediğini öğrenme</li>
                <li>Kişisel verileriniz işlenmişse buna ilişkin bilgi talep etme</li>
                <li>Kişisel verilerinizin işlenme amacını ve bunların amacına uygun kullanılıp kullanılmadığını öğrenme</li>
                <li>Yurt içinde veya yurt dışında kişisel verilerinizin aktarıldığı üçüncü kişileri bilme</li>
                <li>Kişisel verilerinizin eksik veya yanlış işlenmiş olması hâlinde bunların düzeltilmesini isteme</li>
            </ul>

            <h2 class="text-2xl font-semibold mt-8 mb-4">İletişim</h2>
            <p class="text-gray-600 mb-4">
                Gizlilik politikamız hakkında sorularınız için bizimle iletişime geçebilirsiniz:
            </p>
            <p class="text-gray-600">
                E-posta: info@habersitesi.com<br>
                Telefon: +90 (212) XXX XX XX
            </p>
        </div>
    </div>
</main>

<?php include 'templates/footer.php'; ?> 
</body>
</html> 