<?php
require_once 'include/functions.php';

// Kategori sınıfından örnek oluştur
$categoryObj = new Category();
// Tüm kategorileri veritabanından çek
$categories = $categoryObj->getAll();
?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="assets/images/minilogo.png">
    <style>
        /* Custom styles for transitions and z-index */
        #stickyHeader {
            z-index: 100;
            /* Sticky header needs a high z-index */
            transition: all 0.3s ease-in-out;
            /* Smooth transition for padding/height changes */
            /* Tailwind classes will handle position: sticky and top-0 */
        }

        #mainNav {
            transition: all 0.3s ease-in-out;
            /* Smooth transition for padding/height */
            /* No longer hiding/showing based on scroll direction */
        }

        #menuDropdown {
            z-index: 101;
            /* Dropdown needs to be above the header */
            max-height: 80vh;
            /* Max height for scrollability */
            overflow-y: auto;
            /* Add scrollbar if content exceeds max height */
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            /* Smooth reveal animation */
            width: 100%;
            /* Default to full width on mobile */
        }

        @media (min-width: 1024px) {
            #menuDropdown {
                width: 320px;
                /* Fixed width on desktop */
            }
        }

        #menuBackdrop {
            z-index: 99;
            /* Backdrop below the dropdown */
        }

        #mainLogo img {
            transition: all 0.3s ease-in-out;
            /* Smooth logo size transition */
        }

        /* Style for when the header is sticky */
        .header-sticky {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            /* Apply shadow when sticky */
            /* Add padding adjustments if needed when sticky, e.g., pt-2 pb-2 */
        }

        /* Adjustments when sticky - can be done via JS class toggle or just rely on header shrinking */
        .header-sticky .py-4 {
            padding-top: 0.5rem;
            /* Smaller padding when sticky */
            padding-bottom: 0.5rem;
        }
    </style>
</head>

<header class="w-full border-b border-gray-200 relative z-40">
    <div class="bg-white transition-all duration-300 sticky top-0" id="stickyHeader">
        <div class="max-w-5xl mx-auto px-0 py-4 flex items-center justify-between" id="headerContent">
            <div class="flex items-center space-x-4 lg:w-1/3 pl-4">
                <div class="relative">
                    <button
                        class="font-medium flex items-center hover:text-blue-900 transition-colors duration-200 z-50 px-3 py-1.5 rounded hover:bg-gray-100"
                        onclick="toggleMenu()">
                        <div class="flex-shrink-0 w-5 h-4 relative">
                            <span
                                class="absolute left-0 top-0 h-0.5 w-full bg-current transform transition-all duration-300"
                                id="menuBar1"></span>
                            <span
                                class="absolute left-0 top-1/2 h-0.5 w-full bg-current transform -translate-y-1/2 transition-all duration-300"
                                id="menuBar2"></span>
                            <span
                                class="absolute left-0 bottom-0 h-0.5 w-full bg-current transform transition-all duration-300"
                                id="menuBar3"></span>
                        </div>
                        <span class="tracking-wide hidden lg:inline ml-2">MENÜ</span>
                    </button>

                    <div id="menuDropdown"
                        class="fixed lg:absolute left-0 top-[60px] lg:top-full w-full lg:w-80 bg-white shadow-xl rounded-lg overflow-hidden transition-all duration-300 z-50 opacity-0 -translate-y-4 pointer-events-none">
                        <div class="p-5">
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                                <div>
                                    <h3 class="text-sm font-bold text-red-600 mb-4">Bölümler</h3>
                                    <div class="grid grid-cols-1 gap-2">
                                        <a href="dosya"
                                            class="text-sm text-gray-700 hover:text-red-600 transition duration-200">Dosya</a>
                                        <a href="hafiza"
                                            class="text-sm text-gray-700 hover:text-red-600 transition duration-200">Hafıza</a>
                                        <a href="storymaps"
                                            class="text-sm text-gray-700 hover:text-red-600 transition duration-200">Story
                                            Maps</a>
                                        <a href="timeline"
                                            class="text-sm text-gray-700 hover:text-red-600 transition duration-200">Zaman
                                            Çizelgesi</a>
                                        <a href="4-nokta-1"
                                            class="text-sm text-gray-700 hover:text-red-600 transition duration-200">4
                                            Nokta 1</a>
                                        <a href="portre"
                                            class="text-sm text-gray-700 hover:text-red-600 transition duration-200">Portre</a>
                                    </div>

                                    <h3 class="text-sm font-bold text-red-600 mt-8 mb-4">En çok okunan</h3>
                                    <ul class="space-y-2">
                                        <?php foreach ($categories as $category): ?>
                                            <li>
                                                <a href="tumhaberler.php?category=<?php echo urlencode($category['name']); ?>"
                                                    class="text-sm hover:text-blue-900 transition-colors duration-200 block py-1">
                                                    <?php echo htmlspecialchars($category['name']); ?>
                                                </a>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>

                                <div>
                                    <h3 class="text-sm font-bold text-red-600 mt-8 mb-4">Servisler</h3>
                                    <ul class="space-y-2">
                                        <li><a href="iletisim"
                                                class="text-sm hover:text-blue-900 transition-colors duration-200 block py-1">İletişim</a>
                                        </li>
                                        <li><a href="kunye"
                                                class="text-sm hover:text-blue-900 transition-colors duration-200 block py-1">Künye</a>
                                        </li>
                                        <li><a href="hakkimizda"
                                                class="text-sm hover:text-blue-900 transition-colors duration-200 block py-1">Hakkımızda</a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="relative">
                    <input type="text" placeholder="Ara..."
                        class="w-24 sm:w-36 border border-gray-200 rounded-sm px-3 py-1 text-sm focus:outline-none focus:ring-1 focus:ring-blue-900" />
                    <button class="absolute right-2 top-1/2 transform -translate-y-1/2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </button>
                </div>
            </div>

            <div class="flex-1 lg:flex-none lg:w-1/3 flex justify-center">
                <a href="index.php" class="transition-all duration-300" id="mainLogo">
                    <img src="assets/images/minilogo.png" alt="Logo"
                        class="h-16 lg:h-24 w-auto transition-all duration-300 ease-in-out">
                </a>
            </div>

            <div class="hidden lg:block lg:w-1/3"></div>
        </div>

        <nav class="hidden lg:block py-3 border-t border-gray-200 transition-all duration-300 bg-white relative z-30"
            id="mainNav">
            <div class="max-w-5xl mx-auto px-4">
                <ul class="flex justify-center text-sm font-medium space-x-12">
                    <li><a href="index"
                            class="<?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'font-bold text-blue-900' : 'hover:text-red-600'; ?> transition duration-300">Ana
                            Sayfa</a></li>
                    <li><a href="dosya"
                            class="<?php echo basename($_SERVER['PHP_SELF']) == 'dosya.php' ? 'font-bold text-blue-900' : 'hover:text-red-600'; ?> transition duration-300">Dosya</a>
                    </li>
                    <li><a href="hafiza"
                            class="<?php echo basename($_SERVER['PHP_SELF']) == 'hafiza.php' ? 'font-bold text-blue-900' : 'hover:text-red-600'; ?> transition duration-300">Hafıza</a>
                    </li>
                    <li><a href="storymaps"
                            class="<?php echo basename($_SERVER['PHP_SELF']) == 'storymaps.php' ? 'font-bold text-blue-900' : 'hover:text-red-600'; ?> transition duration-300">Story
                            Maps</a></li>
                    <li><a href="timeline"
                            class="<?php echo basename($_SERVER['PHP_SELF']) == 'timeline.php' ? 'font-bold text-blue-900' : 'hover:text-red-600'; ?> transition duration-300">Zaman
                            Çizelgesi</a></li>
                    <li><a href="4-nokta-1"
                            class="<?php echo basename($_SERVER['PHP_SELF']) == '4-nokta-1.php' ? 'font-bold text-blue-900' : 'hover:text-red-600'; ?> transition duration-300">4
                            Nokta 1</a></li>
                    <li><a href="portre"
                            class="<?php echo basename($_SERVER['PHP_SELF']) == 'portre.php' ? 'font-bold text-blue-900' : 'hover:text-red-600'; ?> transition duration-300">Portre</a>
                    </li>
                </ul>
            </div>
        </nav>
    </div>

    <div id="menuBackdrop"
        class="fixed inset-0 bg-black bg-opacity-10 z-30 transition-opacity duration-300 opacity-0 pointer-events-none"
        onclick="toggleMenu()"></div>
</header>

<script>
    let isMenuOpen = false;
    const stickyHeader = document.getElementById('stickyHeader');
    const menuDropdown = document.getElementById('menuDropdown');
    const backdrop = document.getElementById('menuBackdrop');
    const bar1 = document.getElementById('menuBar1');
    const bar2 = document.getElementById('menuBar2');
    const bar3 = document.getElementById('menuBar3');
    const logoImg = document.querySelector('#mainLogo img'); // Select the img element

    function toggleMenu() {
        isMenuOpen = !isMenuOpen;

        if (isMenuOpen) {
            // Set dropdown top position dynamically for mobile/tablet
            if (window.innerWidth < 1024) {
                // Get the current height of the sticky header
                const headerHeight = stickyHeader.offsetHeight;
                menuDropdown.style.top = headerHeight + 'px';
            }

            menuDropdown.classList.remove('opacity-0', '-translate-y-4', 'pointer-events-none');
            menuDropdown.classList.add('opacity-100', 'translate-y-0', 'pointer-events-auto');
            backdrop.classList.remove('opacity-0', 'pointer-events-none');
            backdrop.classList.add('opacity-100', 'pointer-events-auto');

            // Animate hamburger to X
            bar1.classList.add('rotate-45', 'top-1/2', '-translate-y-1/2');
            bar2.classList.add('opacity-0');
            bar3.classList.add('-rotate-45', 'top-1/2', '-translate-y-1/2');

            // Prevent scrolling when menu is open (optional, but good for full-screen menus)
            document.body.style.overflow = 'hidden';

        } else {
            // Reset dropdown top position for mobile/tablet when closing
            if (window.innerWidth < 1024) {
                menuDropdown.style.top = '60px'; // Reset to original value or remove style
            }


            menuDropdown.classList.add('opacity-0', '-translate-y-4', 'pointer-events-none');
            menuDropdown.classList.remove('opacity-100', 'translate-y-0', 'pointer-events-auto');
            backdrop.classList.add('opacity-0', 'pointer-events-none');
            backdrop.classList.remove('opacity-100', 'pointer-events-auto');

            // Animate X back to hamburger
            bar1.classList.remove('rotate-45', 'top-1/2', '-translate-y-1/2');
            bar2.classList.remove('opacity-0');
            bar3.classList.remove('-rotate-45', 'top-1/2', '-translate-y-1/2');

            // Restore scrolling
            document.body.style.overflow = '';
        }
    }

    // Handle scroll events for sticky header effects
    window.addEventListener('scroll', function () {
        if (window.scrollY > 100) {
            stickyHeader.classList.add('header-sticky');
            // Shrink logo using Tailwind classes
            logoImg.classList.remove('h-16', 'lg:h-24');
            logoImg.classList.add('h-12', 'lg:h-16'); // Adjust sizes as needed

            // Optionally reduce header padding when sticky
            // stickyHeader.querySelector('.py-4').classList.add('py-2');


        } else {
            stickyHeader.classList.remove('header-sticky');
            // Restore logo size
            logoImg.classList.remove('h-12', 'lg:h-16');
            logoImg.classList.add('h-16', 'lg:h-24');

            // Optionally restore header padding
            // stickyHeader.querySelector('.py-4').classList.remove('py-2');
        }

        // If the menu is open and window resizes or scrolls, update its position for fixed dropdowns (mobile)
        // This handles cases where the header height might change due to content loading
        // or if the user scrolls while the menu is open on mobile
        if (isMenuOpen && window.innerWidth < 1024) {
            const headerHeight = stickyHeader.offsetHeight;
            menuDropdown.style.top = headerHeight + 'px';
        }
    });

    // Update dropdown position on resize if the menu is open (important for mobile fixed positioning)
    window.addEventListener('resize', function () {
        if (isMenuOpen && window.innerWidth < 1024) {
            const headerHeight = stickyHeader.offsetHeight;
            menuDropdown.style.top = headerHeight + 'px';
        } else if (isMenuOpen && window.innerWidth >= 1024) {
            // On desktop, recalculate position relative to the button if it changes
            // This might require re-adding the button positioning logic for desktop if the button moves significantly
            // Or ensure the desktop dropdown is purely relative/absolute positioning handled by CSS
            // Let's assume the current absolute positioning on desktop inside the relative button works.
            // If not, we'd need a more complex desktop repositioning here.
            // For now, remove the dynamic top setting for desktop.
            menuDropdown.style.top = ''; // Let CSS handle it (lg:top-full)
        }
    });


    // Close menu when clicking outside on desktop (already handled by backdrop click)
    // Optionally close menu on ESC key press
    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && isMenuOpen) {
            toggleMenu();
        }
    });


</script>