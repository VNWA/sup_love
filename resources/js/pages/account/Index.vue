<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import AccountMainTabs from '@/components/club/AccountMainTabs.vue';
import ClubMobileShell from '@/components/club/ClubMobileShell.vue';
import { history as accountHistory, prizeWins as accountPrizeWins } from '@/routes/account';
import type { User } from '@/types';

const page = usePage();
const user = computed(() => page.props.auth.user as User | null);
</script>

<template>
    <Head title="Tài khoản" />

    <ClubMobileShell nav-active="account">
        <AccountMainTabs active="overview" />

        <div class="space-y-4">
            <div
                class="rounded-2xl border border-pink-100 bg-[#fff5f9] p-4 text-center shadow-sm ring-1 ring-pink-100/80"
            >
                <p class="text-xs text-neutral-600">Xin chào</p>
                <p class="text-lg font-bold text-[#9d174d]">
                    {{ user?.username ?? user?.name }}
                </p>
                <p class="mt-2 text-sm text-neutral-700">
                    Điểm hiện có:
                    <span class="font-extrabold text-[#DA2778]">{{
                        user?.point ?? 0
                    }}</span>
                </p>
            </div>

            <div class="space-y-2 text-sm">
                <Link
                    :href="accountPrizeWins().url"
                    class="block rounded-2xl border border-pink-100 bg-white px-4 py-3 font-medium text-[#DA2778] shadow-sm ring-1 ring-pink-100/60 transition hover:bg-pink-50"
                >
                    Phần thưởng quay trúng
                </Link>
                <Link
                    :href="accountHistory().url"
                    class="block rounded-2xl border border-pink-100 bg-white px-4 py-3 font-medium text-[#DA2778] shadow-sm ring-1 ring-pink-100/60 transition hover:bg-pink-50"
                >
                    Lịch sử quay &amp; nạp điểm
                </Link>
                <Link
                    :href="accountHistory.url({ query: { tab: 'spin' } })"
                    class="block rounded-2xl border border-neutral-200 bg-white px-4 py-3 text-neutral-700 shadow-sm transition hover:bg-neutral-50"
                >
                    Chỉ lịch sử quay
                </Link>
                <Link
                    :href="accountHistory.url({ query: { tab: 'topup' } })"
                    class="block rounded-2xl border border-neutral-200 bg-white px-4 py-3 text-neutral-700 shadow-sm transition hover:bg-neutral-50"
                >
                    Chỉ lịch sử nạp điểm
                </Link>
            </div>
        </div>
    </ClubMobileShell>
</template>
