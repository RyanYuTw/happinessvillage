<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import Editor from '@/Components/Editor.vue';
import { ref } from 'vue';

const content = ref('<p>Hello World! Try editing this.</p>');
</script>

<template>
    <Head title="HTML Editor" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">HTML Editor Demo</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Content Editor</label>
                        <Editor v-model="content" />
                    </div>
                    
                    <div class="mt-8 p-4 bg-gray-100 dark:bg-gray-900 rounded">
                        <h3 class="text-lg font-medium mb-2 text-gray-900 dark:text-gray-100">Output HTML:</h3>
                        <pre class="whitespace-pre-wrap text-sm text-gray-600 dark:text-gray-400">{{ content }}</pre>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
