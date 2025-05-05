<?php
$page_title = isset($page_title) ? $page_title : 'Admin Panel';
?>

<head>
    <link rel="icon" href="../../assets/images/minilogo.png">  
</head>

<header class="bg-white shadow-lg">
    <div class="flex items-center justify-between px-8 py-4">
        <div class="flex items-center">
            <button class="text-gray-500 focus:outline-none md:hidden">
                <i class="fas fa-bars text-2xl"></i>
            </button>
            <h1 class="text-2xl font-semibold text-gray-800 ml-4"><?php echo $page_title; ?></h1>
        </div>
        <div class="flex items-center">
            <div class="relative">
                <button class="flex items-center text-gray-500 focus:outline-none">
                    <img class="h-8 w-8 rounded-full object-cover" src="https://ui-avatars.com/api/?name=Admin&background=0D8ABC&color=fff" alt="Admin">
                    <span class="mx-2"><?php echo $_SESSION['username']; ?></span>
                    <i class="fas fa-chevron-down text-sm"></i>
                </button>
            </div>
        </div>
    </div>
</header> 