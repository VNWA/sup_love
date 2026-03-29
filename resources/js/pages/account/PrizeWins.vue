<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AccountMainTabs from '@/components/club/AccountMainTabs.vue';
import ClubMobileShell from '@/components/club/ClubMobileShell.vue';
import { vnFromNow } from '@/composables/useVnFromNow';
import { prizeWins as accountPrizeWins } from '@/routes/account';

/** Trùng với `WheelChoice::CONSOLATION_CHOICE_ID` (ô “Chúc bạn may mắn lần sau”). */
const CONSOLATION_CHOICE_ID = 8;

const WISH_LABEL: Record<string, string> = {
    hon_nhan: 'Hôn nhân',
    suc_khoe: 'Sức khỏe',
    tinh_yeu: 'Tình yêu',
    gia_dinh: 'Gia đình',
    su_nghiep: 'Sự nghiệp',
    ban_be: 'Bạn bè',
    du_lich: 'Du lịch',
    tai_chinh: 'Tài chính',
};

type SpinRow = {
    id: number;
    bet_amount: number;
    payout: number;
    was_rigged: boolean;
    wish_category: string | null;
    created_at: string;
    bet_choice: { id: number; name: string };
    result_choice: { id: number; name: string };
    wheel_room?: { id: number; name: string; slug: string } | null;
};

type Paginator<T> = {
    data: T[];
    links: { url: string | null; label: string; active: boolean }[];
};

defineProps<{
    spins: Paginator<SpinRow>;
}>();

function wishLabel(key: string | null): string {
    if (key === null || key === '') {
        return '—';
    }

    return WISH_LABEL[key] ?? key;
}

function isConsolation(s: SpinRow): boolean {
    return s.result_choice?.id === CONSOLATION_CHOICE_ID;
}
</script>

<template>
    <Head title="Lượt quay" />

    <ClubMobileShell nav-active="account">
        <AccountMainTabs active="prizes" />

        <div>
            <h2
                class="mb-2 text-center text-sm font-bold uppercase tracking-wide text-neutral-900"
            >
                Lịch sử lượt quay
            </h2>
            <p class="text-center text-xs text-neutral-600">
                Số tiền mong muốn và kết quả vòng quay (không trừ điểm từ game).
            </p>

            <ul class="mt-6 space-y-3">
                <li
                    v-for="s in spins.data"
                    :key="s.id"
                    class="rounded-2xl border border-pink-100 bg-white p-3 text-sm shadow-sm ring-1 ring-pink-50"
                >
                    <div class="flex justify-between gap-2">
                        <span class="font-medium text-neutral-800">
                            {{ vnFromNow(s.created_at) }}
                        </span>
                        <span
                            class="text-xs font-bold"
                            :class="isConsolation(s) ? 'text-slate-500' : 'text-green-600'"
                        >
                            {{ isConsolation(s) ? 'Ô an ủi' : 'Giải thưởng' }}
                        </span>
                    </div>
                    <p
                        v-if="s.wheel_room?.name"
                        class="mt-1 text-xs text-neutral-600"
                    >
                        <span class="font-semibold">Phòng:</span>
                        {{ s.wheel_room.name }}
                    </p>
                    <p class="mt-2 text-xs text-neutral-700">
                        <span class="font-semibold">Mong muốn:</span>
                        {{ wishLabel(s.wish_category) }} —
                        <span class="font-semibold text-[#DA2778]">{{
                            s.bet_amount.toLocaleString('vi-VN')
                        }}</span>
                        (ghi nhận)
                    </p>
                    <p class="mt-1 text-xs text-neutral-700">
                        <span class="font-semibold">Dừng tại:</span>
                        {{ s.result_choice?.name }}
                    </p>
                </li>
            </ul>

            <div
                v-if="spins.links?.length > 3"
                class="mt-6 flex flex-wrap justify-center gap-2"
            >
                <Link
                    v-for="(l, i) in spins.links"
                    :key="i"
                    :href="l.url || accountPrizeWins().url"
                    class="rounded px-2 py-1 text-xs"
                    :class="
                        l.active
                            ? 'bg-[#DA2778] text-white'
                            : 'bg-neutral-100 text-neutral-700'
                    "
                    preserve-scroll
                >
                    <span v-html="l.label" />
                </Link>
            </div>
        </div>
    </ClubMobileShell>
</template>
