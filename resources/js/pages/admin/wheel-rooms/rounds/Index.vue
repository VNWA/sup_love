<script setup lang="ts">
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted } from 'vue';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { vnFromNow } from '@/composables/useVnFromNow';
import { subscribeWheelRoom } from '@/echo';
import { dashboard } from '@/routes/admin';
import { edit as editRoom, index as roomsIndex } from '@/routes/admin/wheel-rooms';
import { end as endRound, index as roundsIndex, store as storeRound } from '@/routes/admin/wheel-rooms/rounds';

type ChoiceOpt = { id: number; name: string };

type RoundRow = {
    id: number;
    round_number: number;
    status: string;
    wheel_spins_count: number;
    started_at: string | null;
    ended_at: string | null;
    created_at: string;
    result_choice: { id: number; name: string } | null;
};

type Paginator<T> = {
    data: T[];
    links: { url: string | null; label: string; active: boolean }[];
};

const props = defineProps<{
    room: {
        id: number;
        name: string;
        slug: string;
        is_active: boolean;
    };
    openRound: {
        id: number;
        round_number: number;
        result_choice_id: number;
        result_choice: { id: number; name: string } | null;
        started_at: string | null;
    } | null;
    rounds: Paginator<RoundRow>;
    choicesForRound: ChoiceOpt[];
}>();

const page = usePage();
const flashSuccess = computed(
    () => (page.props.flash as { success?: string | null } | undefined)?.success,
);

const roundForm = useForm({
    result_choice_id: null as number | null,
});

function submitNewRound(): void {
    if (roundForm.result_choice_id === null) {
        return;
    }

    roundForm.post(storeRound.url({ wheelRoom: props.room.id }), {
        preserveScroll: true,
        onSuccess: () => {
            roundForm.reset();
            roundForm.clearErrors();
        },
    });
}

function ketThucVong(): void {
    if (props.openRound === null) {
        return;
    }

    router.post(
        endRound.url({
            wheelRoom: props.room.id,
            wheelRound: props.openRound.id,
        }),
        {},
        { preserveScroll: true },
    );
}

function statusLabel(s: string): string {
    if (s === 'open') {
        return 'Đang mở';
    }
    if (s === 'closed') {
        return 'Đã kết thúc';
    }

    return s;
}

function statusClass(s: string): string {
    if (s === 'open') {
        return 'bg-emerald-100 text-emerald-900';
    }
    if (s === 'closed') {
        return 'bg-neutral-100 text-neutral-800';
    }

    return 'bg-neutral-100';
}

let unsub: (() => void) | null = null;

onMounted(() => {
    unsub = subscribeWheelRoom(props.room.id, {
        onRoundStarted: () => {
            router.reload({
                only: ['openRound', 'rounds', 'choicesForRound', 'room'],
            });
        },
        onRoundEnded: () => {
            router.reload({
                only: ['openRound', 'rounds', 'choicesForRound', 'room'],
            });
        },
    });
});

onUnmounted(() => {
    unsub?.();
});
</script>

<template>
    <Head :title="`Vòng quay — ${room.name}`" />

    <div class="max-w-4xl">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <div class="flex flex-wrap gap-3 text-sm">
                    <Link
                        :href="roomsIndex().url"
                        class="text-[#DA2778] hover:underline"
                    >
                        ← Danh sách phòng
                    </Link>
                    <span class="text-neutral-300">|</span>
                    <Link
                        :href="editRoom.url(room.id)"
                        class="text-neutral-600 hover:text-[#DA2778]"
                    >
                        Sửa thông tin phòng
                    </Link>
                    <span class="text-neutral-300">|</span>
                    <Link
                        :href="dashboard().url"
                        class="text-neutral-500 hover:text-[#DA2778]"
                    >
                        Dashboard
                    </Link>
                </div>
                <h1 class="mt-3 text-xl font-bold">
                    Vòng quay — {{ room.name }}
                </h1>
                <p class="mt-1 text-xs text-neutral-500">
                    Mã phòng:
                    <span class="font-mono">{{ room.slug }}</span>
                    · ID
                    {{ room.id }}
                </p>
            </div>
        </div>

        <div
            v-if="flashSuccess"
            class="mt-4 rounded-xl bg-emerald-50 px-3 py-2 text-center text-sm font-medium text-emerald-900 ring-1 ring-emerald-200"
            role="status"
        >
            {{ flashSuccess }}
        </div>

        <div class="mt-6 grid gap-6 lg:grid-cols-2">
            <div class="space-y-4">
                <h2 class="text-sm font-semibold text-neutral-900">
                    Vòng đang diễn ra
                </h2>

                <div
                    v-if="openRound"
                    class="rounded-xl border border-pink-100 bg-pink-50/90 px-4 py-4 ring-1 ring-pink-100"
                >
                    <p class="font-semibold text-neutral-900">
                        Vòng
                        <span class="text-[#DA2778]">#{{ openRound.round_number }}</span>
                        <span
                            class="ml-2 inline-flex rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-bold text-emerald-900"
                        >
                            Đang mở
                        </span>
                    </p>
                    <p class="mt-2 text-sm text-neutral-700">
                        Ô kết quả:
                        <strong class="text-[#9d174d]">{{
                            openRound.result_choice?.name ?? '—'
                        }}</strong>
                    </p>
                    <p
                        v-if="openRound.started_at"
                        class="mt-1 text-xs text-neutral-500"
                    >
                        Bắt đầu: {{ vnFromNow(openRound.started_at) }}
                    </p>
                    <Button
                        type="button"
                        class="mt-4 w-full border-red-200 bg-white text-red-700 hover:bg-red-50"
                        variant="outline"
                        @click="ketThucVong"
                    >
                        Kết thúc vòng này
                    </Button>
                </div>

                <div
                    v-else
                    class="rounded-xl border border-dashed border-neutral-200 bg-neutral-50 px-4 py-8 text-center text-sm text-neutral-600"
                >
                    <p class="font-medium text-neutral-800">
                        Không có vòng đang mở
                    </p>
                    <p class="mt-1 text-xs">
                        Dùng phần bên cạnh để khởi tạo vòng mới (chọn ô kết quả
                        trước).
                    </p>
                </div>
            </div>

            <div
                class="rounded-xl border border-neutral-200 bg-white p-4 shadow-sm ring-1 ring-neutral-100"
            >
                <h2 class="text-sm font-semibold text-neutral-900">
                    Khởi tạo vòng quay mới
                </h2>
                <p class="mt-1 text-xs text-neutral-600">
                    Mỗi thành viên chỉ quay một lần trong vòng. Phải kết thúc vòng
                    hiện tại (nếu có) trước khi tạo vòng tiếp theo.
                </p>

                <form v-if="!openRound" class="mt-4 space-y-3" @submit.prevent="submitNewRound">
                    <div class="space-y-1">
                        <Label for="result_choice_id">Ô kết quả (vòng quay dừng tại đây)</Label>
                        <select
                            id="result_choice_id"
                            v-model.number="roundForm.result_choice_id"
                            class="w-full rounded-md border border-neutral-300 bg-white px-3 py-2 text-sm"
                            required
                        >
                            <option disabled :value="null">— Chọn ô —</option>
                            <option
                                v-for="c in choicesForRound"
                                :key="c.id"
                                :value="c.id"
                            >
                                {{ c.name }}
                            </option>
                        </select>
                        <p
                            v-if="roundForm.errors.result_choice_id"
                            class="text-xs text-red-600"
                        >
                            {{ roundForm.errors.result_choice_id }}
                        </p>
                    </div>
                    <Button
                        type="submit"
                        class="w-full bg-emerald-600 hover:bg-emerald-700"
                        :disabled="roundForm.processing"
                    >
                        <Spinner v-if="roundForm.processing" class="mr-2" />
                        Tạo vòng quay mới
                    </Button>
                </form>

                <p
                    v-else
                    class="mt-4 rounded-lg bg-amber-50 px-3 py-2 text-xs text-amber-950 ring-1 ring-amber-100"
                >
                    Đang có vòng mở — kết thúc vòng hiện tại trước khi tạo vòng
                    mới.
                </p>
            </div>
        </div>

        <div class="mt-10">
            <h2 class="text-sm font-semibold text-neutral-900">
                Lịch sử vòng quay
            </h2>
            <p class="mt-1 text-xs text-neutral-600">
                Danh sách các vòng đã tạo trong phòng này (mới nhất trước).
            </p>

            <div
                class="mt-4 overflow-x-auto rounded-lg border border-neutral-200 bg-white shadow-sm"
            >
                <table class="min-w-full text-left text-sm">
                    <thead
                        class="border-b border-neutral-200 bg-neutral-50 text-xs font-semibold uppercase text-neutral-600"
                    >
                        <tr>
                            <th class="px-3 py-2">Vòng</th>
                            <th class="px-3 py-2">Trạng thái</th>
                            <th class="px-3 py-2">Ô kết quả</th>
                            <th class="px-3 py-2 text-right">Lượt quay</th>
                            <th class="px-3 py-2">Bắt đầu</th>
                            <th class="px-3 py-2">Kết thúc</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="w in rounds.data"
                            :key="w.id"
                            class="border-b border-neutral-100 align-top"
                        >
                            <td class="px-3 py-2 font-semibold">{{ w.round_number }}</td>
                            <td class="px-3 py-2">
                                <span
                                    class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-bold"
                                    :class="statusClass(w.status)"
                                >
                                    {{ statusLabel(w.status) }}
                                </span>
                            </td>
                            <td class="px-3 py-2 text-neutral-800">
                                {{ w.result_choice?.name ?? '—' }}
                            </td>
                            <td class="px-3 py-2 text-right tabular-nums">
                                {{ w.wheel_spins_count }}
                            </td>
                            <td class="px-3 py-2 text-xs text-neutral-500">
                                {{
                                    w.started_at
                                        ? vnFromNow(w.started_at)
                                        : '—'
                                }}
                            </td>
                            <td class="px-3 py-2 text-xs text-neutral-500">
                                {{
                                    w.ended_at
                                        ? vnFromNow(w.ended_at)
                                        : '—'
                                }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <p
                v-if="rounds.data.length === 0"
                class="mt-4 text-center text-xs text-neutral-500"
            >
                Chưa có vòng quay nào.
            </p>

            <div
                v-if="rounds.links?.length > 3"
                class="mt-4 flex flex-wrap justify-center gap-2"
            >
                <Link
                    v-for="(l, i) in rounds.links"
                    :key="i"
                    :href="l.url || roundsIndex.url({ wheelRoom: room.id })"
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
    </div>
</template>
