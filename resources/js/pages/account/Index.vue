<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import AccountMainTabs from '@/components/club/AccountMainTabs.vue';
import ClubMobileShell from '@/components/club/ClubMobileShell.vue';
import {
    bank as accountBank,
    history as accountHistory,
    prizeWins as accountPrizeWins,
} from '@/routes/account';
import lixiWithdrawalRoutes from '@/routes/account/lixi-withdrawals';
import type { User } from '@/types';

const page = usePage();
const user = computed(() => page.props.auth.user as User | null);

const flashSuccess = computed(
    () => (page.props.flash as { success?: string | null } | undefined)?.success,
);
</script>

<template>

    <Head title="Tài khoản" />

    <ClubMobileShell nav-active="account">
        <AccountMainTabs active="overview" />

        <div class="space-y-4">
            <div v-if="flashSuccess"
                class="rounded-xl bg-emerald-50 px-3 py-2 text-center text-xs font-medium text-emerald-900 ring-1 ring-emerald-200"
                role="status">
                {{ flashSuccess }}
            </div>

            <div
                class="rounded-2xl border border-pink-100 bg-[#fff5f9] p-4 text-center shadow-sm ring-1 ring-pink-100/80">
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

            <p class="text-center text-xs leading-relaxed text-white ">
                Dùng các mục phía trên: Lịch sử (mọi giao dịch điểm), Lượt quay,
                thông tin ngân hàng nhận lì xì và rút lì xì.
            </p>

            <div class="space-y-2 text-sm">
                <Link :href="accountPrizeWins().url"
                    class="block rounded-2xl border border-pink-100 bg-white px-4 py-3 font-medium text-[#DA2778] shadow-sm ring-1 ring-pink-100/60 transition hover:bg-pink-50">
                    Lịch sử lượt quay
                </Link>
                <Link :href="accountHistory().url"
                    class="block rounded-2xl border border-pink-100 bg-white px-4 py-3 font-medium text-[#DA2778] shadow-sm ring-1 ring-pink-100/60 transition hover:bg-pink-50">
                    Lịch sử quay &amp; điểm
                </Link>
                <Link :href="accountBank().url"
                    class="block rounded-2xl border border-pink-100 bg-white px-4 py-3 font-medium text-[#DA2778] shadow-sm ring-1 ring-pink-100/60 transition hover:bg-pink-50">
                    Thông tin ngân hàng nhận lì xì
                </Link>
                <Link :href="lixiWithdrawalRoutes.index.url()"
                    class="block rounded-2xl border border-pink-100 bg-white px-4 py-3 font-medium text-[#DA2778] shadow-sm ring-1 ring-pink-100/60 transition hover:bg-pink-50">
                    Rút lì xì
                </Link>
            </div>
        </div>
    </ClubMobileShell>
</template>
