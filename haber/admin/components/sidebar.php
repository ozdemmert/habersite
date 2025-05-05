<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<head>
    <link rel="icon" href="../../assets/images/minilogo.png">  
</head>
<div class="fixed inset-y-0 left-0 w-64 bg-gray-800 text-white flex flex-col py-7 px-4">
    <div class="flex items-center space-x-2 mb-6">
        <i class="fas fa-newspaper text-2xl"></i>
        <span class="text-2xl font-extrabold">Admin Panel</span>
    </div>
    <nav>
        <a href="index.php"
            class="block py-2.5 px-4 rounded transition duration-200 <?php echo $current_page === 'index.php' ? 'bg-gray-900' : 'hover:bg-gray-700'; ?>">
            <i class="fas fa-home mr-2"></i>Dashboard
        </a>
        <a href="news.php"
            class="block py-2.5 px-4 rounded transition duration-200 <?php echo $current_page === 'news.php' ? 'bg-gray-900' : 'hover:bg-gray-700'; ?>">
            <i class="fas fa-newspaper mr-2"></i>Haberler
        </a>
        <a href="categories.php"
            class="block py-2.5 px-4 rounded transition duration-200 <?php echo $current_page === 'categories.php' ? 'bg-gray-900' : 'hover:bg-gray-700'; ?>">
            <i class="fas fa-tags mr-2"></i>Kategoriler
        </a>
        <a href="users.php"
            class="block py-2.5 px-4 rounded transition duration-200 <?php echo $current_page === 'users.php' ? 'bg-gray-900' : 'hover:bg-gray-700'; ?>">
            <i class="fas fa-users mr-2"></i>Kullanıcılar
        </a>
        <a href="dosya.php"
            class="block py-2.5 px-4 rounded transition duration-200 <?php echo $current_page === 'dosya.php' ? 'bg-gray-900' : 'hover:bg-gray-700'; ?>">
            <i class="fas fa-file mr-2"></i>Dosyalar
        </a>
        <a href="storymaps.php"
            class="block py-2.5 px-4 rounded transition duration-200 <?php echo $current_page === 'storymaps.php' ? 'bg-gray-900' : 'hover:bg-gray-700'; ?>">
            <i class="fas fa-map mr-2"></i>Story Maps
        </a>
        <a href="hafiza.php"
            class="block py-2.5 px-4 rounded transition duration-200 <?php echo $current_page === 'hafiza.php' ? 'bg-gray-900' : 'hover:bg-gray-700'; ?>">
            <i class="fas fa-map mr-2"></i>Hafıza Yönetimi
        </a>
        <a href="timeline.php"
            class="block py-2.5 px-4 rounded transition duration-200 <?php echo $current_page === 'timeline.php' ? 'bg-gray-900' : 'hover:bg-gray-700'; ?>">
            <i class="fas fa-clock mr-2"></i>Timeline
        </a>
        <a href="portre.php"
            class="block py-2.5 px-4 rounded transition duration-200 <?php echo $current_page === 'portre.php' ? 'bg-gray-900' : 'hover:bg-gray-700'; ?>">
            <i class="fas fa-user mr-2"></i>Portreler
        </a>
        <a href="4nokta1.php"
            class="block py-2.5 px-4 rounded transition duration-200 <?php echo $current_page === '4nokta1.php' ? 'bg-gray-900' : 'hover:bg-gray-700'; ?>">
            <i class="fas fa-dot-circle mr-2"></i>4 Nokta 1
        </a>
        <a href="social.php" class="block py-2.5 px-4 rounded transition duration-200 hover:bg-gray-700">
            <i class="fas fa-share-alt mr-2"></i>Sosyal Medya
        </a>
        <a href="section.php" class="block py-2.5 px-4 rounded transition duration-200 hover:bg-gray-700">
            <i class="fas fa-cogs mr-2"></i>Bölüm Yönetimi
        </a>
        <a href="../logout.php" class="block py-2.5 px-4 rounded transition duration-200 hover:bg-gray-700">
            <i class="fas fa-sign-out-alt mr-2"></i>Çıkış
        </a>
    </nav>
</div>