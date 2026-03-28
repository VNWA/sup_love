<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { index as prizesIndex } from '@/routes/admin/wheel-prizes';
import { index as usersIndex } from '@/routes/admin/users';

defineProps<{
    stats: {
        users_count: number;
        points_credited_today: number;
        spins_today: number;
    };
}>();
</script>

<template>
    <Head title="Admin dashboard" />

    <div>
        <h1 class="text-xl font-bold text-neutral-900">Dashboard</h1>
        <p class="mt-1 text-sm text-neutral-600">
            Tổng quan nhanh hệ thống điểm &amp; vòng quay.
        </p>

        <div class="mt-6 grid gap-4 sm:grid-cols-3">
            <div
                class="rounded-lg border border-pink-100 bg-white p-4 shadow-sm"
            >
                <p class="text-xs font-medium text-neutral-500">Người dùng</p>
                <p class="mt-1 text-2xl font-bold text-[#DA2778]">
                    {{ stats.users_count }}
                </p>
            </div>
            <div
                class="rounded-lg border border-pink-100 bg-white p-4 shadow-sm"
            >
                <p class="text-xs font-medium text-neutral-500">
                    Điểm nạp hôm nay
                </p>
                <p class="mt-1 text-2xl font-bold text-[#DA2778]">
                    +{{ stats.points_credited_today }}
                </p>
            </div>
            <div
                class="rounded-lg border border-pink-100 bg-white p-4 shadow-sm"
            >
                <p class="text-xs font-medium text-neutral-500">
                    Lượt quay hôm nay
                </p>
                <p class="mt-1 text-2xl font-bold text-[#DA2778]">
                    {{ stats.spins_today }}
                </p>
            </div>
        </div>

        <div class="mt-8 flex flex-wrap gap-3">
            <Link
                :href="usersIndex().url"
                class="inline-flex rounded-lg bg-[#DA2778] px-4 py-2 text-sm font-semibold text-white hover:bg-[#b91560]"
            >
                Quản lý người dùng
            </Link>
            <Link
                :href="prizesIndex().url"
                class="inline-flex rounded-lg border border-[#DA2778] bg-white px-4 py-2 text-sm font-semibold text-[#DA2778] hover:bg-pink-50"
            >
                Giải vòng quay
            </Link>
        </div>
    </div>
</template>
