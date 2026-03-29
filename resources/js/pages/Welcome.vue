<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Check } from 'lucide-vue-next';
import ClubMobileShell from '@/components/club/ClubMobileShell.vue';
import { game } from '@/routes';

withDefaults(
    defineProps<{
        canRegister: boolean;
    }>(),
    {
        canRegister: true,
    },
);
</script>

<template>
    <Head title="Trang chủ" />

    <ClubMobileShell nav-active="home">
      
     <Link
                :href="game().url"
            >
        <!-- Banner 1: tours / journey -->
        <article
            class="relative mb-4 overflow-hidden rounded-2xl bg-gradient-to-br from-pink-200 via-rose-100 to-amber-100 shadow-md ring-1 ring-pink-200/80"
        >
           <img src="/banner1.jpeg" alt="Banner 1" class="w-full h-full object-cover rounded-2xl">
        </article>
 <article
            class="relative mb-4 overflow-hidden rounded-2xl bg-gradient-to-br from-pink-200 via-rose-100 to-amber-100 shadow-md ring-1 ring-pink-200/80"
        >
           <img src="/banner2.jpeg" alt="Banner 2" class="w-full h-full object-cover rounded-2xl">
        </article>
            </Link>

       
    </ClubMobileShell>
</template>
