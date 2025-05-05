<?php
require_once 'include/functions.php';

// Kategori sınıfından örnek oluştur
$categoryObj = new Category();
// Tüm kategorileri veritabanından çek
$categories = $categoryObj->getAll();
?>
<head>

    <link rel="icon" type="image/png" href="assets/images/minilogo.png">
</head>
<header class="w-full border-b border-gray-200 relative z-40">
    <div class="bg-white transition-all duration-300" id="stickyHeader">
        <div class="max-w-5xl mx-auto px-0 py-4">
            <div class="flex items-center justify-between">
                <!-- Menu and Search section - Left side -->
                <div class="flex items-center space-x-4 lg:w-1/3 pl-4">
                    <div class="relative">
                        <button 
                            class="font-medium flex items-center hover:text-blue-900 transition-colors duration-200 z-50 px-3 py-1.5 rounded hover:bg-gray-100"
                            onclick="toggleMenu()"
                        >
                            <div class="flex-shrink-0 w-5 h-4 relative">
                                <span class="absolute left-0 top-0 h-0.5 w-full bg-current transform transition-all duration-300" id="menuBar1"></span>
                                <span class="absolute left-0 top-1/2 h-0.5 w-full bg-current transform -translate-y-1/2 transition-all duration-300" id="menuBar2"></span>
                                <span class="absolute left-0 bottom-0 h-0.5 w-full bg-current transform transition-all duration-300" id="menuBar3"></span>
                            </div>
                            <span class="tracking-wide hidden lg:inline ml-2">MENÜ</span>
                        </button>

                        <!-- Dropdown Menu -->
                        <div 
                            id="menuDropdown"
                            class="fixed lg:absolute left-0 top-[60px] lg:top-full w-full lg:w-80 bg-white shadow-xl rounded-lg overflow-hidden transition-all duration-300 z-50 opacity-0 -translate-y-4 pointer-events-none"
                        >
                            <div class="p-5">
                                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                                    <!-- Sol Sütun -->
                                    <div>
                                        <h3 class="text-sm font-bold text-red-600 mb-4">Bölümler</h3>
                                        <div class="grid grid-cols-1 gap-2">
                                            
                                            <a href="dosya" class="text-sm text-gray-700 hover:text-red-600 transition duration-200">Dosya</a>
                                            <a href="hafiza" class="text-sm text-gray-700 hover:text-red-600 transition duration-200">Hafıza</a>
                                            <a href="storymaps" class="text-sm text-gray-700 hover:text-red-600 transition duration-200">Story Maps</a>
                                            <a href="timeline" class="text-sm text-gray-700 hover:text-red-600 transition duration-200">Zaman Çizelgesi</a>
                                            <a href="4-nokta-1" class="text-sm text-gray-700 hover:text-red-600 transition duration-200">4 Nokta 1</a>
                                            <a href="portre" class="text-sm text-gray-700 hover:text-red-600 transition duration-200">Portre</a>
                                        </div>
                                        
                                        <h3 class="text-sm font-bold text-red-600 mt-8 mb-4">En çok okunan</h3>
                                        <ul class="space-y-2">
                                            <?php foreach ($categories as $category): ?>
                                            <li>
                                                <a 
                                                    href="tumhaberler.php?category=<?php echo urlencode($category['name']); ?>"
                                                    class="text-sm hover:text-blue-900 transition-colors duration-200 block py-1"
                                                >
                                                    <?php echo $category['name']; ?>
                                                </a>
                                            </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                    
                                    <!-- Sağ Sütun -->
                                    <div>
                                        
                                        
                                        <h3 class="text-sm font-bold text-red-600 mt-8 mb-4">Servisler</h3>
                                        <ul class="space-y-2">
                                            <li><a href="iletisim" class="text-sm hover:text-blue-900 transition-colors duration-200 block py-1">İletişim</a></li>
                                            <li><a href="kunye" class="text-sm hover:text-blue-900 transition-colors duration-200 block py-1">Künye</a></li>
                                            <li><a href="hakkimizda" class="text-sm hover:text-blue-900 transition-colors duration-200 block py-1">Hakkımızda</a></li>
                                           
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="relative">
                        <input 
                            type="text" 
                            placeholder="Ara..."
                            class="w-24 sm:w-36 border border-gray-200 rounded-sm px-3 py-1 text-sm focus:outline-none focus:ring-1 focus:ring-blue-900"
                        />
                        <button class="absolute right-2 top-1/2 transform -translate-y-1/2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Logo - Center -->
                <div class="flex-1 lg:flex-none lg:w-1/3 flex justify-center">
                    <a href="index.php" class="transition-all duration-300" id="mainLogo">
                        <img src="assets/images/minilogo.png" alt="Logo" class="h-16 lg:h-24 w-auto">
                    </a>
                </div>

                <!-- Right side empty space -->
                <div class="hidden lg:block lg:w-1/3"></div>
            </div>
        </div>

        <!-- Navigation - Hidden on mobile and tablet -->
        <nav class="hidden lg:block py-3 border-t border-gray-200 transition-all duration-300 bg-white relative z-30 transform" id="mainNav">
            <div class="max-w-5xl mx-auto px-4">
                <ul class="flex justify-center text-sm font-medium space-x-12">
                    <li><a href="index" class="hover:text-red-600 transition duration-300">Ana Sayfa</a></li>
                    
                    <li><a href="dosya" class="hover:text-red-600 transition duration-300">Dosya</a></li>
                    <li><a href="hafiza" class="hover:text-red-600 transition duration-300">Hafıza</a></li>
                    <li><a href="storymaps" class="hover:text-red-600 transition duration-300">Story Maps</a></li>
                    <li><a href="timeline" class="hover:text-red-600 transition duration-300">Zaman Çizelgesi</a></li>
                    <li><a href="4-nokta-1" class="hover:text-blue-900 transition-colors duration-200">4 Nokta 1</a></li>
                    <li><a href="portre" class="hover:text-blue-900 transition-colors duration-200">Portre</a></li>
                </ul>
            </div>
        </nav>
    </div>

    <!-- Semi-transparent backdrop -->
    <div 
        id="menuBackdrop"
        class="fixed inset-0 bg-black bg-opacity-10 z-30 transition-opacity duration-300 opacity-0 pointer-events-none"
        onclick="toggleMenu()"
    ></div>
</header>

<script>
let isMenuOpen = false;

function updateDropdownPosition() {
    if (!isMenuOpen) return;
    
    const dropdown = document.getElementById('menuDropdown');
    const header = document.getElementById('stickyHeader');
    const menuButton = document.querySelector('button[onclick="toggleMenu()"]');
    
    if (window.scrollY > 100) {
        const headerHeight = header.offsetHeight;
        const buttonRect = menuButton.getBoundingClientRect();
        
        dropdown.classList.add('sticky-dropdown');
        dropdown.style.top = headerHeight + 'px';
        
        // Mobil olmayan görünümde, menü düğmesinin sol kenarına hizala
        if (window.innerWidth >= 1024) {
            dropdown.style.left = buttonRect.left + 'px';
        } else {
            dropdown.style.left = '0';
        }
    } else {
        dropdown.classList.remove('sticky-dropdown');
        dropdown.style.position = '';
        dropdown.style.top = '';
        dropdown.style.left = '';
        dropdown.style.width = '';
    }
}

function toggleMenu() {
    isMenuOpen = !isMenuOpen;
    const dropdown = document.getElementById('menuDropdown');
    const backdrop = document.getElementById('menuBackdrop');
    const bar1 = document.getElementById('menuBar1');
    const bar2 = document.getElementById('menuBar2');
    const bar3 = document.getElementById('menuBar3');
    const header = document.getElementById('stickyHeader');
    const menuButton = document.querySelector('button[onclick="toggleMenu()"]');
    
    if (isMenuOpen) {
        // Dropdown pozisyonunu güncelle
        updateDropdownPosition();
        
        dropdown.classList.remove('opacity-0', '-translate-y-4', 'pointer-events-none');
        dropdown.classList.add('opacity-100', 'translate-y-0', 'pointer-events-auto');
        backdrop.classList.remove('opacity-0', 'pointer-events-none');
        backdrop.classList.add('opacity-100', 'pointer-events-auto');
        
        // Animate hamburger to X
        bar1.classList.add('rotate-45', 'top-1/2', '-translate-y-1/2');
        bar2.classList.add('opacity-0');
        bar3.classList.add('-rotate-45', 'top-1/2', '-translate-y-1/2');
    } else {
        dropdown.classList.remove('sticky-dropdown');
        dropdown.classList.add('opacity-0', '-translate-y-4', 'pointer-events-none');
        dropdown.classList.remove('opacity-100', 'translate-y-0', 'pointer-events-auto');
        backdrop.classList.add('opacity-0', 'pointer-events-none');
        backdrop.classList.remove('opacity-100', 'pointer-events-auto');
        
        // Animate X back to hamburger
        bar1.classList.remove('rotate-45', 'top-1/2', '-translate-y-1/2');
        bar2.classList.remove('opacity-0');
        bar3.classList.remove('-rotate-45', 'top-1/2', '-translate-y-1/2');
    }
}

// Handle scroll events for sticky header
window.addEventListener('scroll', function() {
    const header = document.getElementById('stickyHeader');
    const nav = document.getElementById('mainNav');
    const logo = document.getElementById('mainLogo');
    
    if (window.scrollY > 100) {
        header.classList.add('fixed', 'top-0', 'left-0', 'right-0', 'shadow-md');
        // Animate navbar out
        nav.style.transform = 'translateY(-100%)';
        nav.style.opacity = '0';
        nav.style.height = '0';
        nav.style.margin = '0';
        nav.style.padding = '0';
        nav.style.border = 'none';
        // Make logo smaller
        logo.classList.remove('text-4xl', 'lg:text-7xl');
        logo.classList.add('text-3xl', 'lg:text-5xl');
        
        // Menü açıksa, konumunu güncelle
        if (isMenuOpen) {
            updateDropdownPosition();
        }
    } else {
        header.classList.remove('fixed', 'top-0', 'left-0', 'right-0', 'shadow-md');
        // Animate navbar in
        nav.style.transform = 'translateY(0)';
        nav.style.opacity = '1';
        nav.style.height = '';
        nav.style.margin = '';
        nav.style.padding = '';
        nav.style.border = '';
        // Restore logo size
        logo.classList.remove('text-3xl', 'lg:text-5xl');
        logo.classList.add('text-4xl', 'lg:text-7xl');
        
        // Menü açıksa, normal konuma getir
        if (isMenuOpen) {
            updateDropdownPosition();
        }
    }
});

// Pencere boyutu değiştiğinde dropdown konumunu güncelle
window.addEventListener('resize', updateDropdownPosition);
</script> 

<style>
body {
    padding-top: 0; /* Remove fixed padding since header is not always fixed */
}

#mainNav {
    transition: all 0.3s ease-in-out;
    transform: translateY(0);
    opacity: 1;
    height: auto;
    margin: 0;
    padding: 0.75rem 0;
    border-top: 1px solid #e5e7eb;
}

#stickyHeader {
    transition: all 0.3s ease-in-out;
    z-index: 100; /* Sticky header için yüksek z-index değeri */
}

#menuDropdown {
    z-index: 101; /* Dropdown menü için daha yüksek z-index değeri */
    max-height: 80vh; /* Maksimum yükseklik sınırı ekleyelim */
    overflow-y: auto; /* İçerik aştığında kaydırma çubuğu ekler */
    transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    width: 100%; /* Mobil görünümde tam genişlik */
}

@media (min-width: 1024px) {
    #menuDropdown {
        width: 320px; /* Desktop görünümünde sabit genişlik */
    }
}

#menuDropdown.sticky-dropdown {
    position: fixed !important;
    border-radius: 0;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

@media (min-width: 1024px) {
    #menuDropdown.sticky-dropdown {
        border-radius: 0.5rem; /* Masaüstü görünümünde köşeleri yuvarlak */
        border-top-left-radius: 0; /* Sol üst köşe yuvarlak değil */
        width: 320px; /* Masaüstü görünümünde sabit genişlik */
    }
}

#menuBackdrop {
    z-index: 99; /* Backdrop için daha düşük z-index değeri */
}

#mainLogo {
    transition: all 0.3s ease-in-out;
}
</style> 