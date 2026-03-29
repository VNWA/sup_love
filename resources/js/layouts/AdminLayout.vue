<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { dashboard, logout as adminLogout } from '@/routes/admin';
import { index as wheelChoicesIndex } from '@/routes/admin/wheel-choices';
import { index as wheelRoomsIndex } from '@/routes/admin/wheel-rooms';
import lixiWithdrawalsAdmin from '@/routes/admin/lixi-withdrawals';
import { index as usersIndex } from '@/routes/admin/users';

function signOutAdmin(): void {
    router.post(adminLogout.url());
}
</script>

<template>
    <div class="min-h-dvh bg-neutral-100 text-neutral-900">
        <header
            class="flex flex-wrap items-center gap-4 border-b border-neutral-200 bg-white px-4 py-3 shadow-sm"
        >
            <span class="text-sm font-bold text-[#9d174d]">Admin CLB</span>
            <nav class="flex flex-wrap gap-3 text-sm font-medium">
                <Link
                    :href="dashboard().url"
                    class="text-neutral-700 hover:text-[#DA2778]"
                >
                    Dashboard
                </Link>
                <Link
                    :href="usersIndex().url"
                    class="text-neutral-700 hover:text-[#DA2778]"
                >
                    Người dùng
                </Link>
                <Link
                    :href="wheelChoicesIndex().url"
                    class="text-neutral-700 hover:text-[#DA2778]"
                >
                    Ô vòng quay
                </Link>
                <Link
                    :href="wheelRoomsIndex().url"
                    class="text-neutral-700 hover:text-[#DA2778]"
                >
                    Phòng quay
                </Link>
                <Link
                    :href="lixiWithdrawalsAdmin.index.url()"
                    class="text-neutral-700 hover:text-[#DA2778]"
                >
                    Rút lì xì
                </Link>
            </nav>
            <div class="ml-auto">
                <button
                    type="button"
                    class="text-sm font-medium text-red-600 hover:underline"
                    @click="signOutAdmin"
                >
                    Đăng xuất admin
                </button>
            </div>
        </header>
        <main class="mx-auto max-w-6xl p-4 sm:p-6">
            <slot />
        </main>
    </div>
</template>
