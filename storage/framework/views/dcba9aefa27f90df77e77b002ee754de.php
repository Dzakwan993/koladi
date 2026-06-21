<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

    
    <?php if (! (app()->environment('local'))): ?>
        <meta http-equiv="Content-Security-Policy"
            content="
            default-src 'self';
            script-src 'self' 'unsafe-inline' 'unsafe-eval'
                https://app.midtrans.com
                https://api.midtrans.com
                https://cdn.jsdelivr.net
                https://js.pusher.com
                https://www.googletagmanager.com
                https://www.google-analytics.com;
            style-src 'self' 'unsafe-inline'
                https://fonts.googleapis.com
                https://cdn.jsdelivr.net;
            font-src 'self'
                https://fonts.gstatic.com;
            img-src 'self' data: https:
                https://www.googletagmanager.com
                https://www.google-analytics.com;
            connect-src 'self'
                https://app.midtrans.com
                https://api.midtrans.com
                wss://ws-ap1.pusher.com
                https://sockjs-ap1.pusher.com
                https://www.google-analytics.com
                https://analytics.google.com
                https://region1.google-analytics.com;
            frame-src 'self'
                https://app.midtrans.com;
        ">
    <?php endif; ?>

    <title>Koladi - <?php echo $__env->yieldContent('title'); ?></title>
    <link rel="icon" type="image/png" href="/images/LogoAtas.svg">

    <!-- Google Fonts - Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>

    <!-- ================================================
         GOOGLE ANALYTICS 4 — KOLADI
         Measurement ID: G-GENT8W6RPG
         Tracking: semua halaman setelah login (dashboard,
                   kanban, statistik, dll)
         ================================================ -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-GENT8W6RPG"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', 'G-GENT8W6RPG', {
            'send_page_view': true,
            'user_id': '<?php echo e(Auth::id() ?? ''); ?>'
        });
    </script>

    <style>
        * {
            box-sizing: border-box;
        }

        html,
        body {
            width: 100%;
            overflow-x: hidden;
        }

        [x-cloak] {
            display: none !important;
        }

        /* 🔥 FIX: Sweet Alert harus di atas onboarding */
        .swal2-container {
            z-index: 11000 !important;
        }
    </style>

    <?php if(session('alert_type')): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: '<?php echo e(session('alert_type')); ?>', // warning, error, success, info
                    title: '<?php echo e(session('alert_title')); ?>',
                    html: '<?php echo e(session('alert_message')); ?>',
                    showCancelButton: <?php echo json_encode(session('alert_button') ? true : false, 15, 512) ?>,
                    confirmButtonText: '<?php echo e(session('alert_button') ?? 'OK'); ?>',
                    cancelButtonText: 'Tutup',
                    confirmButtonColor: '#3b82f6',
                    cancelButtonColor: '#6b7280',
                }).then((result) => {
                    <?php if(session('alert_url')): ?>
                        if (result.isConfirmed) {
                            window.location.href = '<?php echo e(session('alert_url')); ?>';
                        }
                    <?php endif; ?>
                });
            });
        </script>
    <?php endif; ?>
</head>

<body class="bg-[#f3f6fc] flex h-screen overflow-hidden">
    <?php if(session('error-pembayaran')): ?>
        <div id="error-toast"
            class="fixed top-4 right-4 z-[10000] transform translate-x-0 opacity-0 transition-all duration-500 ease-out">
            <div
                class="bg-gradient-to-r from-red-500 to-red-600 text-white px-6 py-4 rounded-xl shadow-2xl border border-red-400/50 backdrop-blur-sm max-w-md">
                <div class="flex items-start gap-3">
                    <!-- Icon -->
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="flex-1 min-w-0">
                        <h4 class="font-bold text-sm mb-1">Akses Ditolak</h4>
                        <p class="text-sm text-white/90 leading-relaxed">
                            <?php echo e(session('error-pembayaran')); ?>

                        </p>
                    </div>

                    <!-- Close Button -->
                    <button onclick="closeErrorToast()"
                        class="flex-shrink-0 text-white/80 hover:text-white transition-colors ml-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Progress Bar -->
                <div class="mt-3 h-1 bg-white/20 rounded-full overflow-hidden">
                    <div id="toast-progress"
                        class="h-full bg-white/60 rounded-full transition-all duration-[5000ms] ease-linear"
                        style="width: 100%"></div>
                </div>
            </div>
        </div>

        <script>
            // 🔥 Cek apakah user dari back button
            const isBackButton = (
                performance.getEntriesByType('navigation')[0]?.type === 'back_forward'
            );

            let shouldShowToast = !isBackButton;

            document.addEventListener('DOMContentLoaded', function() {

                if (!shouldShowToast) {
                    const toast = document.getElementById('error-toast');
                    if (toast) toast.remove();
                    return;
                }

                const toast = document.getElementById('error-toast');
                const progress = document.getElementById('toast-progress');

                if (toast) {
                    setTimeout(() => {
                        toast.style.opacity = '1';
                        toast.style.transform = 'translateX(0)';
                    }, 100);

                    setTimeout(() => {
                        progress.style.width = '0%';
                    }, 200);

                    setTimeout(() => {
                        closeErrorToast();
                    }, 5200);
                }
            });

            function closeErrorToast() {
                const toast = document.getElementById('error-toast');
                if (toast) {
                    toast.style.opacity = '0';
                    toast.style.transform = 'translateX(100%)';
                    setTimeout(() => toast.remove(), 500);
                }
            }
        </script>
    <?php endif; ?>

    
    <?php if (isset($component)) { $__componentOriginal2880b66d47486b4bfeaf519598a469d6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2880b66d47486b4bfeaf519598a469d6 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.sidebar','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('sidebar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2880b66d47486b4bfeaf519598a469d6)): ?>
<?php $attributes = $__attributesOriginal2880b66d47486b4bfeaf519598a469d6; ?>
<?php unset($__attributesOriginal2880b66d47486b4bfeaf519598a469d6); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2880b66d47486b4bfeaf519598a469d6)): ?>
<?php $component = $__componentOriginal2880b66d47486b4bfeaf519598a469d6; ?>
<?php unset($__componentOriginal2880b66d47486b4bfeaf519598a469d6); ?>
<?php endif; ?>

    <div class="flex-1 flex flex-col min-w-0">
        
        <?php if (isset($component)) { $__componentOriginal57b7ac81b71e7fe2d81fa75baf439455 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal57b7ac81b71e7fe2d81fa75baf439455 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.topbar','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('topbar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal57b7ac81b71e7fe2d81fa75baf439455)): ?>
<?php $attributes = $__attributesOriginal57b7ac81b71e7fe2d81fa75baf439455; ?>
<?php unset($__attributesOriginal57b7ac81b71e7fe2d81fa75baf439455); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal57b7ac81b71e7fe2d81fa75baf439455)): ?>
<?php $component = $__componentOriginal57b7ac81b71e7fe2d81fa75baf439455; ?>
<?php unset($__componentOriginal57b7ac81b71e7fe2d81fa75baf439455); ?>
<?php endif; ?>

        
        <main class="flex-1 overflow-x-hidden overflow-y-auto">
            <?php echo $__env->yieldContent('content'); ?>
        </main>

        <?php if (isset($component)) { $__componentOriginalf46e6f0ca7a7a4ef4e6d1c7b633d02f4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf46e6f0ca7a7a4ef4e6d1c7b633d02f4 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.hak-akses','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('hak-akses'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf46e6f0ca7a7a4ef4e6d1c7b633d02f4)): ?>
<?php $attributes = $__attributesOriginalf46e6f0ca7a7a4ef4e6d1c7b633d02f4; ?>
<?php unset($__attributesOriginalf46e6f0ca7a7a4ef4e6d1c7b633d02f4); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf46e6f0ca7a7a4ef4e6d1c7b633d02f4)): ?>
<?php $component = $__componentOriginalf46e6f0ca7a7a4ef4e6d1c7b633d02f4; ?>
<?php unset($__componentOriginalf46e6f0ca7a7a4ef4e6d1c7b633d02f4); ?>
<?php endif; ?>
        <?php if (isset($component)) { $__componentOriginalc4aa703c2d7e7c3c410977db93145d2e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc4aa703c2d7e7c3c410977db93145d2e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.atur-hak','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('atur-hak'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc4aa703c2d7e7c3c410977db93145d2e)): ?>
<?php $attributes = $__attributesOriginalc4aa703c2d7e7c3c410977db93145d2e; ?>
<?php unset($__attributesOriginalc4aa703c2d7e7c3c410977db93145d2e); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc4aa703c2d7e7c3c410977db93145d2e)): ?>
<?php $component = $__componentOriginalc4aa703c2d7e7c3c410977db93145d2e; ?>
<?php unset($__componentOriginalc4aa703c2d7e7c3c410977db93145d2e); ?>
<?php endif; ?>
    </div>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Alpine.js dan Collapse Plugin -->
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Pusher -->
    <script src="https://js.pusher.com/7.2/pusher.min.js"></script>

    <!-- Midtrans Snap -->
    <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="<?php echo e(config('midtrans.client_key')); ?>">
    </script>

    <script>
        window.roleContext = {
            type: 'company',
            workspaceId: null
        };

        window.setRoleContext = function(ctx) {
            window.roleContext = {
                type: ctx?.type || 'company',
                workspaceId: ctx?.workspaceId || null,
            };
        };

        window.Laravel = {
            userId: '<?php echo e(Auth::id()); ?>',
            csrfToken: '<?php echo e(csrf_token()); ?>'
        };
    </script>

    <?php echo $__env->make('components.sweet-alert', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo $__env->yieldPushContent('scripts'); ?>

</body>

</html>
<?php /**PATH C:\xampp\htdocs\koladi\resources\views/layouts/app.blade.php ENDPATH**/ ?>