<?php
require_once __DIR__ . '/../include/functions.php';
$socialObj = new Social();
$socialLinks = $socialObj->getAll();
$socialMap = [];
foreach ($socialLinks as $s) {
    $socialMap[strtolower($s['platform'])] = $s['social_url'];
}
?>
    <footer class="bg-white w-[1080px] py-4 m-auto">
        <div class="container mx-auto px-4">
            <!-- Newsletter Section -->
            <div class="mb-8">
                <div class="flex justify-center">
                    <div class="w-full max-w-xl">
                        <div class="mb-4 text-center">
                            <p class="text-gray-600">Öne çıkan haberleri ve özel içerikleri kaçırmamak için e-posta bültenimize abone olun.</p>
                        </div>
                        <form class="flex" action="newsletter.php" method="POST">
                            <input type="email" name="email" placeholder="E-posta adresiniz" required
                                class="flex-grow px-4 py-2 border border-gray-300 focus:outline-none focus:border-[#022d5a]">
                            <button type="submit" class="bg-[#022d5a] text-white px-4 py-2 hover:bg-[#f39200] transition duration-300">
                                Abone Ol
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Main Footer Content -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-10">
                <!-- Kurumsal -->
                <div>
                    <h3 class="text-lg font-medium mb-4 text-[#022d5a]">Kurumsal</h3>
                    <ul class="space-y-2">
                        <li><a href="hakkimizda" class="text-gray-600 hover:text-[#f39200] transition duration-300">Hakkında</a></li>
                        <li><a href="ekip" class="text-gray-600 hover:text-[#f39200] transition duration-300">Ekip</a></li>
                        <li><a href="yayin-ilkeleri" class="text-gray-600 hover:text-[#f39200] transition duration-300">Yayın İlkeleri</a></li>
                        <li><a href="topluluk-kurallari" class="text-gray-600 hover:text-[#f39200] transition duration-300">Topluluk Kuralları</a></li>
                        <li><a href="reklam" class="text-gray-600 hover:text-[#f39200] transition duration-300">Reklam</a></li>
                        <li><a href="iletisim" class="text-gray-600 hover:text-[#f39200] transition duration-300">İletişim</a></li>
                    </ul>
                </div>

                <!-- Hukuksal -->
                <div>
                    <h3 class="text-lg font-medium mb-4 text-[#022d5a]">Hukuksal</h3>
                    <ul class="space-y-2">
                        <li><a href="cerez-politikasi" class="text-gray-600 hover:text-[#f39200] transition duration-300">Çerez Politikası</a></li>
                        <li><a href="kvkk-aydinlatma" class="text-gray-600 hover:text-[#f39200] transition duration-300">KVKK Aydınlatma</a></li>
                        <li><a href="gizlilik-politikasi" class="text-gray-600 hover:text-[#f39200] transition duration-300">Gizlilik Politikası</a></li>
                        <li><a href="kullanim-sartlari" class="text-gray-600 hover:text-[#f39200] transition duration-300">Kullanım Şartları</a></li>
                    </ul>
                </div>

                <!-- Sosyal Medya -->
                <div>
                    <h3 class="text-lg font-medium mb-4 text-[#022d5a]">Sosyal Medya</h3>
                    <p class="text-gray-600 mb-4">Güncel haberler ve daha fazlası için sosyal medya hesaplarımızı takip edin.</p>
                    <div class="flex space-x-4">
                        <?php if (!empty($socialMap['facebook'])): ?>
                        <a href="<?php echo htmlspecialchars($socialMap['facebook']); ?>" target="_blank" class="text-gray-600 hover:text-[#f39200] transition duration-300">
                            <i class="fab fa-facebook text-2xl"></i>
                        </a>
                        <?php endif; ?>
                        <?php if (!empty($socialMap['twitter'])): ?>
                        <a href="<?php echo htmlspecialchars($socialMap['twitter']); ?>" target="_blank" class="text-gray-600 hover:text-[#f39200] transition duration-300">
                            <i class="fab fa-twitter text-2xl"></i>
                        </a>
                        <?php endif; ?>
                        <?php if (!empty($socialMap['instagram'])): ?>
                        <a href="<?php echo htmlspecialchars($socialMap['instagram']); ?>" target="_blank" class="text-gray-600 hover:text-[#f39200] transition duration-300">
                            <i class="fab fa-instagram text-2xl"></i>
                        </a>
                        <?php endif; ?>
                        <?php if (!empty($socialMap['youtube'])): ?>
                        <a href="<?php echo htmlspecialchars($socialMap['youtube']); ?>" target="_blank" class="text-gray-600 hover:text-[#f39200] transition duration-300">
                            <i class="fab fa-youtube text-2xl"></i>
                        </a>
                        <?php endif; ?>
                    </div>
                    <div class="mt-6">
                        <a href="index.php">
                            <img src="assets/images/minilogo.png" alt="Logo" class="h-12 w-auto">
                        </a>
                    </div>
                </div>
            </div>

            <!-- Copyright -->
            <div class="mt-10 pt-8 border-t border-gray-200 text-center text-sm text-gray-500">
                <p>&copy; <?php echo date('Y'); ?> Gazete BanDor. Tüm hakları saklıdır.</p>
            </div>
        </div>
    </footer>
</body>
</html> 