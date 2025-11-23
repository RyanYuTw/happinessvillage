<template>
    <div class="min-h-screen bg-yellow-50">
        <!-- Header -->
        <header class="bg-yellow-100 shadow">
            <div class="mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center space-x-3">
                        <img src="/images/logo.png" alt="Logo" class="h-12 w-auto object-contain" style="image-rendering: -webkit-optimize-contrast;">
                        <h1 class="text-lg sm:text-xl font-semibold text-gray-800">教案編輯系統</h1>
                    </div>
                    <div class="flex items-center space-x-2 sm:space-x-4">
                        <Link :href="route('dashboard')" class="text-sm sm:text-base text-gray-700 hover:text-gray-900">
                            返回主頁
                        </Link>
                    </div>
                </div>
            </div>
        </header>

        <div class="flex flex-col md:flex-row" style="height: calc(100vh - 4rem);">
            <!-- Left Sidebar -->
            <nav class="w-full md:w-64 bg-yellow-50 shadow-sm overflow-y-auto">
                <div class="p-4">
                    <ul class="space-y-2">
                        <li>
                            <Link 
                                :href="route('admin.handbooks')" 
                                :class="[
                                    'block px-4 py-3 rounded-md text-base font-medium',
                                    route().current('admin.handbooks*') 
                                        ? 'bg-yellow-200 text-yellow-900' 
                                        : 'text-gray-700 hover:bg-yellow-100 hover:text-yellow-800'
                                ]"
                            >
                                學習手冊
                            </Link>
                        </li>
                        <li>
                            <Link 
                                :href="route('admin.photos')" 
                                :class="[
                                    'block px-4 py-3 rounded-md text-base font-medium',
                                    route().current('admin.photos') 
                                        ? 'bg-yellow-200 text-yellow-900' 
                                        : 'text-gray-700 hover:bg-yellow-100 hover:text-yellow-800'
                                ]"
                            >
                                圖片管理
                            </Link>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main Content -->
            <main class="flex-1 p-4 sm:p-6 overflow-auto">
                <slot />
            </main>
        </div>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
</script>