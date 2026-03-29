<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AccountMainTabs from '@/components/club/AccountMainTabs.vue';
import ClubMobileShell from '@/components/club/ClubMobileShell.vue';
import { vnFromNow } from '@/composables/useVnFromNow';
import { history as accountHistory } from '@/routes/account';

type Tx = {
    id: number;
    type: string;
    amount: number;
    balance_after: number;
    admin_note: string | null;
    meta: Record<string, unknown> | null;
    created_at: string;
    actor?: { username: string | null; name: string | null } | null;
};

type Paginator<T> = {
    data: T[];
    links: { url: string | null; label: string; active: boolean }[];
};

const props = defineProps<{
    transactions: Paginator<Tx>;
    tab: string;
}>();

function tabHref(t: string): string {
    if (t === 'all') {
        return accountHistory.url();
    }

    return accountHistory.url({ query: { tab: t } });
}

function typeLabel(type: string): string {
    if (type === 'wheel_spin') {
        return 'Quay thưởng';
    }

    if (type === 'admin_credit') {
        return 'Nạp điểm';
    }

    if (type === 'admin_debit') {
        return 'Trừ điểm';
    }

    if (type === 'lixi_withdrawal_request') {
        return 'Rút lì xì (chờ)';
    }

    if (type === 'lixi_withdrawal_refund') {
        return 'Hoàn lì xì';
    }

    return type;
}

const filterTabClass = (on: boolean): string =>
    on
        ? 'bg-[#DA2778] text-white shadow-sm'
        : 'bg-neutral-100 text-neutral-700 hover:bg-pink-50';
</script>

<template>
    <Head title="Lịch sử" />

    <ClubMobileShell nav-active="account">
        <AccountMainTabs active="history" />

        <div>
            <h2
                class="mb-2 text-center text-sm font-bold uppercase tracking-wide text-neutral-900"
            >
                Lịch sử giao dịch
            </h2>
            <p class="text-center text-xs text-neutral-600">
                Quay vòng, nạp/trừ điểm admin, rút lì xì — tất cả ghi nhận ở đây.
            </p>

            <div class="mt-4 flex flex-wrap justify-center gap-2 text-xs font-semibold">
                <Link
                    :href="tabHref('all')"
                    class="rounded-full px-3 py-1.5 transition"
                    :class="filterTabClass(tab === 'all')"
                >
                    Tất cả
                </Link>
                <Link
                    :href="tabHref('spin')"
                    class="rounded-full px-3 py-1.5 transition"
                    :class="filterTabClass(tab === 'spin')"
                >
                    Chỉ lượt quay
                </Link>
            </div>

            <ul class="mt-6 space-y-3">
                <li
                    v-for="t in transactions.data"
                    :key="t.id"
                    class="rounded-2xl border border-pink-100 bg-white p-3 text-sm shadow-sm ring-1 ring-pink-50"
                >
                    <div class="flex justify-between gap-2">
                        <span class="font-medium text-neutral-800">{{
                            typeLabel(t.type)
                        }}</span>
                        <span
                            class="font-bold"
                            :class="
                                t.amount >= 0 ? 'text-green-600' : 'text-red-600'
                            "
                        >
                            {{ t.amount > 0 ? '+' : '' }}{{ t.amount }} điểm
                        </span>
                    </div>
                    <p class="mt-1 text-xs text-neutral-500">
                        {{ vnFromNow(t.created_at) }} · Số dư sau:
                        {{ t.balance_after }}
                    </p>
                    <p
                        v-if="
                            (t.type === 'admin_credit' ||
                                t.type === 'admin_debit') &&
                            t.admin_note
                        "
                        class="mt-2 text-xs text-neutral-700"
                    >
                        <span class="font-semibold">Ghi chú:</span>
                        {{ t.admin_note }}
                    </p>
                    <template v-if="t.type === 'wheel_spin' && t.meta">
                        <p class="mt-2 space-y-0.5 text-xs text-neutral-700">
                            <span
                                v-if="t.meta.wheel_room_name"
                                class="block text-neutral-600"
                            >
                                <span class="font-semibold">Phòng:</span>
                                {{ String(t.meta.wheel_room_name) }}
                            </span>
                            <span class="block">
                                <span class="font-semibold">Mong muốn:</span>
                                {{ String(t.meta.wish_category ?? '') }} ·
                                <span class="font-semibold">Số tiền ghi nhận:</span>
                                {{ String(t.meta.bet_amount ?? '') }}
                            </span>
                            <span class="block">
                                <span class="font-semibold">Dừng tại:</span>
                                {{ String(t.meta.result_choice_name ?? '') }}
                            </span>
                            <span
                                v-if="t.meta.is_consolation"
                                class="block text-neutral-600"
                            >
                                Ô an ủi — không đổi số dư điểm.
                            </span>
                            <span
                                v-else
                                class="block font-semibold text-green-700"
                            >
                                Trúng giải (ghi nhận) — không đổi số dư điểm.
                            </span>
                        </p>
                    </template>
                </li>
            </ul>

            <div
                v-if="transactions.links?.length > 3"
                class="mt-6 flex flex-wrap justify-center gap-2"
            >
                <Link
                    v-for="(l, i) in transactions.links"
                    :key="i"
                    :href="l.url || '#'"
                    class="rounded px-2 py-1 text-xs"
                    :class="
                        l.active
                            ? 'bg-[#DA2778] text-white'
                            : 'bg-neutral-100 text-neutral-700'
                    "
                    :preserve-scroll="true"
                >
                    <span v-html="l.label" />
                </Link>
            </div>
        </div>
    </ClubMobileShell>
</template>
