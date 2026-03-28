<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AccountMainTabs from '@/components/club/AccountMainTabs.vue';
import ClubMobileShell from '@/components/club/ClubMobileShell.vue';
import { prizeWins as accountPrizeWins } from '@/routes/account';

type WinRow = {
    id: number;
    prize_label: string;
    prize_label_ngan: string;
    color: string;
    status: string;
    received_at: string | null;
    admin_note: string | null;
    created_at: string;
};

type Paginator<T> = {
    data: T[];
    links: { url: string | null; label: string; active: boolean }[];
};

const props = defineProps<{
    wins: Paginator<WinRow>;
    status: string;
}>();

function filterHref(s: string): string {
    if (s === 'all') {
        return accountPrizeWins.url();
    }

    return accountPrizeWins.url({ query: { status: s } });
}

function statusLabel(s: string): string {
    if (s === 'received') {
        return 'Đã nhận';
    }

    if (s === 'pending') {
        return 'Chưa nhận';
    }

    return s;
}

const filterTabClass = (on: boolean): string =>
    on
        ? 'bg-[#DA2778] text-white shadow-sm'
        : 'bg-neutral-100 text-neutral-700 hover:bg-pink-50';
</script>

<template>
    <Head title="Phần thưởng" />

    <ClubMobileShell nav-active="account">
        <AccountMainTabs active="prizes" />

        <div>
            <h2
                class="mb-2 text-center text-sm font-bold uppercase tracking-wide text-neutral-900"
            >
                Phần thưởng quay trúng
            </h2>
            <p class="text-center text-xs text-neutral-600">
                Theo dõi giải đã trúng; trạng thái do CLB cập nhật khi đã giao
                thưởng.
            </p>

            <div class="mt-4 flex flex-wrap justify-center gap-2 text-xs font-semibold">
                <Link
                    :href="filterHref('all')"
                    class="rounded-full px-3 py-1.5 transition"
                    :class="filterTabClass(status === 'all')"
                >
                    Tất cả
                </Link>
                <Link
                    :href="filterHref('pending')"
                    class="rounded-full px-3 py-1.5 transition"
                    :class="filterTabClass(status === 'pending')"
                >
                    Chưa nhận
                </Link>
                <Link
                    :href="filterHref('received')"
                    class="rounded-full px-3 py-1.5 transition"
                    :class="filterTabClass(status === 'received')"
                >
                    Đã nhận
                </Link>
            </div>

            <ul class="mt-6 space-y-3">
                <li
                    v-for="w in wins.data"
                    :key="w.id"
                    class="rounded-2xl border border-pink-100 bg-white p-3 text-sm shadow-sm ring-1 ring-pink-50"
                >
                    <div class="flex items-start justify-between gap-2">
                        <div class="flex min-w-0 flex-1 items-start gap-2">
                            <span
                                class="mt-0.5 inline-block size-6 shrink-0 rounded border border-pink-100 shadow-inner"
                                :style="{ backgroundColor: w.color }"
                                :title="w.color"
                            />
                            <div class="min-w-0">
                                <p class="font-semibold text-neutral-900">
                                    {{ w.prize_label }}
                                </p>
                                <p class="text-xs text-neutral-500">
                                    {{ w.created_at }}
                                    <span v-if="w.received_at">
                                        · Nhận: {{ w.received_at }}
                                    </span>
                                </p>
                            </div>
                        </div>
                        <span
                            class="shrink-0 rounded-full px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide"
                            :class="
                                w.status === 'received'
                                    ? 'bg-emerald-100 text-emerald-800'
                                    : 'bg-amber-100 text-amber-900'
                            "
                        >
                            {{ statusLabel(w.status) }}
                        </span>
                    </div>
                    <p
                        v-if="w.admin_note"
                        class="mt-2 border-t border-pink-50 pt-2 text-xs text-neutral-700"
                    >
                        <span class="font-semibold text-neutral-800">Ghi chú CLB:</span>
                        {{ w.admin_note }}
                    </p>
                </li>
            </ul>

            <p
                v-if="wins.data.length === 0"
                class="mt-8 text-center text-sm text-neutral-500"
            >
                Chưa có phần thưởng nào. Hãy quay vòng may mắn!
            </p>

            <div
                v-if="wins.links?.length > 3"
                class="mt-6 flex flex-wrap justify-center gap-2"
            >
                <Link
                    v-for="(l, i) in wins.links"
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
