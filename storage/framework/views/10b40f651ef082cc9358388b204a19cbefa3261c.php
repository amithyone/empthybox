<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo $__env->yieldContent('title', 'BiggestLogs - Premium Digital Marketplace'); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.cdnfonts.com" crossorigin>
    <link href="https://fonts.cdnfonts.com/css/proxima-nova" rel="stylesheet">
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
</head>
<body class="bg-dark-100 text-gray-200 min-h-screen font-sans">
    <!-- Custom Alert Toast Container -->
    <div id="alert-container" class="fixed top-16 md:top-20 right-2 md:right-4 z-50 space-y-2 max-w-xs md:max-w-sm w-full px-2 md:px-0"></div>

    <!-- Navigation -->
    <nav class="bg-dark-200 border-b border-red-accent/30 sticky top-0 z-40 backdrop-blur-sm shadow-lg shadow-red-accent/10">
        <div class="max-w-7xl mx-auto px-3 sm:px-4 md:px-6 lg:px-8">
            <div class="flex justify-between items-center h-14 md:h-16">
                <div class="flex items-center">
                    <a href="<?php echo e(route('home')); ?>" class="flex items-center space-x-2 transition">
                        <span class="text-xl md:text-2xl font-bold gradient-text">🔥 BiggestLogs</span>
                    </a>
                </div>
                
                <div class="flex items-center gap-2 md:gap-4 text-sm md:text-base">
                    <?php if(auth()->guard()->check()): ?>
                        <a href="<?php echo e(route('wallet.index')); ?>" class="hidden sm:inline-block px-2 md:px-3 py-1.5 rounded-md hover:bg-dark-300 transition font-medium text-yellow-accent">
                            💰 $<?php echo e(number_format(auth()->user()->wallet->balance ?? 0, 2)); ?>

                        </a>
                        <a href="<?php echo e(route('dashboard')); ?>" class="px-2 md:px-3 py-1.5 rounded-md hover:bg-dark-300 transition">Dashboard</a>
                        <a href="<?php echo e(route('profile.index')); ?>" class="px-2 md:px-3 py-1.5 rounded-md hover:bg-dark-300 transition">Profile</a>
                        <a href="<?php echo e(route('sms.select')); ?>" class="hidden sm:inline-block px-2 md:px-3 py-1.5 rounded-md hover:bg-dark-300 transition text-yellow-accent" title="SMS Service">📱</a>
                        <a href="<?php echo e(route('sms.inbox')); ?>" class="hidden sm:inline-block px-2 md:px-3 py-1.5 rounded-md hover:bg-dark-300 transition" title="SMS Inbox">📬 Inbox</a>
                        <?php if(auth()->user()->is_admin): ?>
                            <a href="<?php echo e(route('admin.dashboard')); ?>" class="px-2 md:px-3 py-1.5 rounded-md hover:bg-dark-300 transition text-red-accent">Admin</a>
                        <?php endif; ?>
                        <form method="POST" action="<?php echo e(route('logout')); ?>" class="inline">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="px-2 md:px-3 py-1.5 rounded-md hover:bg-dark-300 transition">Logout</button>
                        </form>
                    <?php else: ?>
                        <a href="<?php echo e(route('login')); ?>" class="px-2 md:px-3 py-1.5 rounded-md hover:bg-dark-300 transition">Login</a>
                        <a href="<?php echo e(route('register')); ?>" class="bg-gradient-to-r from-red-accent to-yellow-accent hover:from-red-dark hover:to-yellow-dark text-white px-3 md:px-4 py-1.5 rounded-md font-medium transition glow-button relative">Sign Up</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="min-h-screen pb-20 md:pb-24">
        <?php echo $__env->yieldContent('content'); ?>
    </main>

    <!-- Bottom Navigation (Mobile) -->
    <?php if(auth()->guard()->check()): ?>
    <nav class="fixed bottom-0 left-0 right-0 bg-dark-200 border-t border-red-accent/30 z-50 md:hidden backdrop-blur-sm shadow-2xl">
        <div class="flex justify-around items-center h-16 px-1">
            <a href="<?php echo e(route('home')); ?>" class="flex flex-col items-center justify-center flex-1 h-full <?php echo e(request()->routeIs('home') ? 'text-yellow-accent' : 'text-gray-400'); ?> transition">
                <span class="text-xl mb-0.5">🏠</span>
                <span class="text-[10px]">Home</span>
            </a>
            <a href="<?php echo e(route('dashboard')); ?>" class="flex flex-col items-center justify-center flex-1 h-full <?php echo e(request()->routeIs('dashboard') ? 'text-yellow-accent' : 'text-gray-400'); ?> transition">
                <span class="text-xl mb-0.5">📊</span>
                <span class="text-[10px]">Dash</span>
            </a>
            <a href="<?php echo e(route('sms.inbox')); ?>" class="flex flex-col items-center justify-center flex-1 h-full <?php echo e(request()->routeIs('sms.inbox') ? 'text-yellow-accent' : 'text-gray-400'); ?> transition">
                <span class="text-xl mb-0.5">📬</span>
                <span class="text-[10px]">Inbox</span>
            </a>
            <a href="<?php echo e(route('orders.index')); ?>" class="flex flex-col items-center justify-center flex-1 h-full <?php echo e(request()->routeIs('orders.*') ? 'text-yellow-accent' : 'text-gray-400'); ?> transition">
                <span class="text-xl mb-0.5">📦</span>
                <span class="text-[10px]">Orders</span>
            </a>
            <a href="<?php echo e(route('wallet.index')); ?>" class="flex flex-col items-center justify-center flex-1 h-full <?php echo e(request()->routeIs('wallet.*') ? 'text-yellow-accent' : 'text-gray-400'); ?> transition">
                <span class="text-xl mb-0.5">💰</span>
                <span class="text-[10px]">Wallet</span>
            </a>
        </div>
    </nav>
    <?php endif; ?>

    <!-- Footer -->
    <footer class="bg-dark-200 border-t border-red-accent/10 py-6 md:py-8 mt-12">
        <div class="max-w-7xl mx-auto px-3 sm:px-4 md:px-6 lg:px-8">
            <div class="text-center">
                <p class="text-lg md:text-xl font-bold mb-2 gradient-text">🔥 BiggestLogs</p>
                <p class="text-xs md:text-sm text-gray-400">Premium Digital Marketplace</p>
                <p class="text-xs text-gray-500 mt-3 md:mt-4">🔁 We replace bad logs fast — no stress, no delay.</p>
            </div>
        </div>
    </footer>

    <!-- Custom Alert Script -->
    <script>
        function showAlert(message, type = 'success', duration = 5000) {
            const container = document.getElementById('alert-container');
            const alert = document.createElement('div');
            const bgColor = type === 'success' ? 'bg-green-600' : type === 'warning' ? 'bg-yellow-600' : 'bg-red-accent';
            alert.className = `${bgColor} text-white px-4 md:px-6 py-3 md:py-4 rounded-lg shadow-xl transform transition-all duration-300 ease-in-out border border-${type === 'success' ? 'green' : type === 'warning' ? 'yellow' : 'red'}-400/30`;
            alert.innerHTML = `
                <div class="flex items-center justify-between gap-3">
                    <span class="text-sm md:text-base">${message}</span>
                    <button onclick="this.parentElement.parentElement.remove()" class="text-white hover:text-gray-200 transition flex-shrink-0">✕</button>
                </div>
            `;
            container.appendChild(alert);
            
            setTimeout(() => {
                alert.classList.add('opacity-0', 'translate-x-full');
                setTimeout(() => alert.remove(), 300);
            }, duration);
        }
    </script>
    <?php echo $__env->yieldContent('scripts'); ?>
</body>
</html>


<?php /**PATH C:\Users\LENOVO LEGION\Documents\Biggestlogs\resources\views/layouts/app.blade.php ENDPATH**/ ?>