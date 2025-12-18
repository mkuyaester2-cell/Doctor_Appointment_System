<?php
// includes/header.php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/Auth.php';
require_once __DIR__ . '/Session.php';

// Get current user info if logged in
$isLoggedIn = Auth::isLoggedIn();
$userRole = $isLoggedIn ? Session::get('user_type') : null;
$userName = $isLoggedIn ? Session::get('full_name') : 'User'; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo defined('PAGE_TITLE') ? PAGE_TITLE . ' - ' . APP_NAME : APP_NAME; ?></title>
    
    <!-- Tailwind CSS (CDN for Development) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            200: '#bfdbfe',
                            300: '#93c5fd',
                            400: '#60a5fa',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            800: '#1e40af',
                            900: '#1e3a8a',
                        }
                    },
                    fontFamily: {
                        sans: ['Outfit', 'sans-serif'],
                    }
                }
            }
        }
    </script>

    <!-- Google Fonts and fontawesome -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-50 font-sans text-slate-800 antialiased min-h-screen flex flex-col">

<!-- Navigation -->
<nav class="bg-white/80 backdrop-blur-md border-b border-slate-200 sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <!-- Logo -->
            <div class="flex items-center">
                <a href="<?php echo $isLoggedIn ? APP_URL . '/' . $userRole . '/dashboard.php' : APP_URL; ?>" class="flex items-center gap-2 group">
                    <div class="bg-primary-600 text-white p-2 rounded-lg group-hover:bg-primary-700 transition-colors">
                        <i class="fa-solid fa-user-doctor text-xl"></i>
                    </div>
                    <span class="text-2xl font-bold bg-gradient-to-r from-primary-700 to-primary-500 bg-clip-text text-transparent">
                        DocBook
                    </span>
                </a>
            </div>

            <!-- Desktop Menu -->
            <div class="hidden md:flex items-center space-x-8">
                <?php if ($isLoggedIn): ?>
                    <a href="<?php echo APP_URL . '/' . $userRole . ($userRole === 'patient' ? '/my-appointments.php' : '/appointments.php'); ?>" class="text-slate-600 hover:text-primary-600 font-medium transition-colors">Appointments</a>
                <?php else: ?>
                    <a href="<?php echo APP_URL; ?>" class="text-slate-600 hover:text-primary-600 font-medium transition-colors">Home</a>
                <?php endif; ?>
                <a href="<?php echo APP_URL; ?>/patient/browse-doctors.php" class="text-slate-600 hover:text-primary-600 font-medium transition-colors">Find Doctors</a>
                
                <?php if ($isLoggedIn): ?>
                    <div class="relative group">
                        <button class="flex items-center space-x-2 text-slate-700 hover:text-primary-600 font-medium focus:outline-none">
                            <span>My Account</span>
                            <i class="fa-solid fa-chevron-down text-xs transition-transform group-hover:rotate-180"></i>
                        </button>
                        <!-- Dropdown -->
                        <div class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-slate-100 py-2 invisible opacity-0 group-hover:visible group-hover:opacity-100 transition-all transform origin-top-right z-50">
                            <?php if ($userRole === 'patient'): ?>
                                <a href="<?php echo APP_URL; ?>/patient/dashboard.php" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 hover:text-primary-600">Dashboard</a>
                            <?php elseif ($userRole === 'doctor'): ?>
                                <a href="<?php echo APP_URL; ?>/doctor/dashboard.php" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 hover:text-primary-600">Dashboard</a>
                                <a href="<?php echo APP_URL; ?>/doctor/appointments.php" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 hover:text-primary-600">Appointments</a>
                            <?php elseif ($userRole === 'admin'): ?>
                                <a href="<?php echo APP_URL; ?>/admin/dashboard.php" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 hover:text-primary-600">Admin Panel</a>
                            <?php endif; ?>
                            <div class="border-t border-slate-100 my-1"></div>
                            <a href="<?php echo APP_URL; ?>/logout.php" class="block px-4 py-2 text-sm text-red-600 hover:bg-red-50">Sign Out</a>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="flex items-center space-x-4">
                        <a href="<?php echo APP_URL; ?>/login.php" class="text-slate-600 hover:text-primary-600 font-medium transition-colors">Login</a>
                        <div class="h-4 w-px bg-slate-200 mx-2"></div>
                        <a href="<?php echo APP_URL; ?>/doctor/register.php" class="text-emerald-600 hover:text-emerald-700 font-bold transition-colors">Join as Doctor</a>
                        <a href="<?php echo APP_URL; ?>/patient/register.php" class="bg-primary-600 hover:bg-primary-700 text-white px-5 py-2.5 rounded-full font-medium transition-all shadow-lg shadow-primary-500/30 hover:shadow-primary-600/40 transform hover:-translate-y-0.5">
                            Book Now
                        </a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Mobile Menu Button -->
            <div class="md:hidden flex items-center">
                <button class="text-slate-600 hover:text-primary-600 focus:outline-none">
                    <i class="fa-solid fa-bars text-2xl"></i>
                </button>
            </div>
        </div>
    </div>
</nav>

<!-- Main Content Wrapper -->
<main class="flex-grow">
    <?php
    // Display Flash Messages
    $flash = Session::getFlash('success');
    if ($flash): ?>
        <div class="max-w-7xl mx-auto px-4 mt-4">
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg flex items-center gap-3" role="alert">
                <i class="fa-solid fa-circle-check"></i>
                <span><?php echo $flash['message']; ?></span>
            </div>
        </div>
    <?php endif; ?>

    <?php
    $error = Session::getFlash('error');
    if ($error): ?>
        <div class="max-w-7xl mx-auto px-4 mt-4">
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg flex items-center gap-3" role="alert">
                <i class="fa-solid fa-circle-exclamation"></i>
                <span><?php echo $error['message']; ?></span>
            </div>
        </div>
    <?php endif; ?>
