<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import {
    bank as accountBank,
    history as accountHistory,
    index as accountIndex,
    prizeWins as accountPrizeWins,
} from '@/routes/account';
import lixiWithdrawalRoutes from '@/routes/account/lixi-withdrawals';
import { logout as memberLogout } from '@/routes';

defineProps<{
    active:
        | 'overview'
        | 'history'
        | 'prizes'
        | 'bank'
        | 'lixi';
}>();

function signOut(): void {
    router.post(memberLogout.url());
}

const tabClass = (on: boolean): string =>
    on
        ? 'bg-[#DA2778] text-white shadow-sm ring-1 ring-pink-300/60'
        : 'bg-white text-neutral-700 ring-1 ring-pink-100 hover:bg-pink-50';
</script>

<template>
    <div class="mb-4">
        <h2
            class="mb-3 text-center text-sm font-bold uppercase tracking-wide text-neutral-900"
        >
            Tài khoản thành viên
        </h2>
        <nav
            class="flex max-w-full flex-wrap items-center justify-center gap-1.5 sm:gap-2"
            aria-label="Mục tài khoản"
        >
            <Link
                :href="accountIndex().url"
                class="rounded-full px-2.5 py-1.5 text-[11px] font-semibold transition sm:text-xs"
                :class="tabClass(active === 'overview')"
            >
                Tổng quan
            </Link>
            <Link
                :href="accountHistory().url"
                class="rounded-full px-2.5 py-1.5 text-[11px] font-semibold transition sm:text-xs"
                :class="tabClass(active === 'history')"
            >
                Lịch sử
            </Link>
            <Link
                :href="accountPrizeWins().url"
                class="rounded-full px-2.5 py-1.5 text-[11px] font-semibold transition sm:text-xs"
                :class="tabClass(active === 'prizes')"
            >
                Lượt quay
            </Link>
            <Link
                :href="accountBank().url"
                class="rounded-full px-2.5 py-1.5 text-[11px] font-semibold transition sm:text-xs"
                :class="tabClass(active === 'bank')"
            >
                NH nhận lì xì
            </Link>
            <Link
                :href="lixiWithdrawalRoutes.index.url()"
                class="rounded-full px-2.5 py-1.5 text-[11px] font-semibold transition sm:text-xs"
                :class="tabClass(active === 'lixi')"
            >
                Rút lì xì
            </Link>
            <button
                type="button"
                class="rounded-full px-2.5 py-1.5 text-[11px] font-semibold text-red-600 ring-1 ring-red-100 transition hover:bg-red-50 sm:text-xs"
                @click="signOut"
            >
                Đăng xuất
            </button>
        </nav>
    </div>
</template>
