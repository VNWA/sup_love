<script setup lang="ts">
defineProps<{
    message?: string;
}>();
</script>

<template>
    <div v-show="message" class="bg-white/90 rounded-md px-2 py-1 text-xs">
        <p class="text-xs text-red-600 dark:text-red-500">
            {{ message }}
        </p>
    </div>
</template>
