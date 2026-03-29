<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { reactive, watch } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { vnFromNow } from '@/composables/useVnFromNow';
import { dashboard } from '@/routes/admin';
import lixiWithdrawalAdmin from '@/routes/admin/lixi-withdrawals';

type UserRef = { id: number; username: string | null; name: string | null };

type Row = {
    id: number;
    user_id: number;
    amount: number;
    bank_name: string | null;
    bank_account_number: string | null;
    bank_account_holder: string | null;
    status: string;
    admin_note: string | null;
    created_at: string;
    processed_at: string | null;
    user?: UserRef | null;
    processed_by?: UserRef | null;
};

type Paginator<T> = {
    data: T[];
    links: { url: string | null; label: string; active: boolean }[];
};

const props = defineProps<{
    withdrawals: Paginator<Row>;
    filterStatus: string | null;
}>();

const rowStatus = reactive<Record<number, 'pending' | 'success' | 'failed'>>({});
const rejectNotes = reactive<Record<number, string>>({});

watch(
    () => props.withdrawals.data,
    (rows) => {
        for (const w of rows) {
            rowStatus[w.id] = w.status as 'pending' | 'success' | 'failed';

            if (!(w.id in rejectNotes)) {
                rejectNotes[w.id] = w.admin_note ?? '';
            }
        }
    },
    { immediate: true, deep: true },
);

function statusLabel(s: string): string {
    if (s === 'pending') {
        return 'Đang chờ';
    }

    if (s === 'success') {
        return 'Thành công';
    }

    if (s === 'failed') {
        return 'Thất bại';
    }

    return s;
}

function statusClass(s: string): string {
    if (s === 'pending') {
        return 'bg-amber-100 text-amber-900';
    }

    if (s === 'success') {
        return 'bg-emerald-100 text-emerald-900';
    }

    if (s === 'failed') {
        return 'bg-red-100 text-red-900';
    }

    return 'bg-neutral-100';
}

function saveRow(id: number): void {
    const st = rowStatus[id];

    if (st === undefined) {
        return;
    }

    router.patch(
        lixiWithdrawalAdmin.update.url({ lixiWithdrawal: id }),
        {
            status: st,
            admin_note:
                st === 'failed' ? rejectNotes[id]?.trim() || null : null,
        },
        { preserveScroll: true },
    );
}

function filterHref(status: string | null): string {
    if (status === null) {
        return lixiWithdrawalAdmin.index.url();
    }

    return lixiWithdrawalAdmin.index.url({ query: { status } });
}
</script>

<template>
    <Head title="Rút lì xì — Admin" />

    <div>
        <div class="mb-4 flex flex-wrap items-center gap-3">
            <h1 class="text-lg font-bold">Yêu cầu rút lì xì</h1>
            <Link
                :href="dashboard().url"
                class="text-sm text-neutral-600 hover:text-[#DA2778]"
            >
                ← Dashboard
            </Link>
        </div>

        <div class="mb-4 flex flex-wrap gap-2 text-xs font-semibold">
            <Link
                :href="filterHref(null)"
                class="rounded-full px-3 py-1.5"
                :class="
                    filterStatus === null
                        ? 'bg-[#DA2778] text-white'
                        : 'bg-white text-neutral-700 ring-1 ring-neutral-200'
                "
            >
                Tất cả
            </Link>
            <Link
                :href="filterHref('pending')"
                class="rounded-full px-3 py-1.5"
                :class="
                    filterStatus === 'pending'
                        ? 'bg-[#DA2778] text-white'
                        : 'bg-white text-neutral-700 ring-1 ring-neutral-200'
                "
            >
                Đang chờ
            </Link>
            <Link
                :href="filterHref('success')"
                class="rounded-full px-3 py-1.5"
                :class="
                    filterStatus === 'success'
                        ? 'bg-[#DA2778] text-white'
                        : 'bg-white text-neutral-700 ring-1 ring-neutral-200'
                "
            >
                Thành công
            </Link>
            <Link
                :href="filterHref('failed')"
                class="rounded-full px-3 py-1.5"
                :class="
                    filterStatus === 'failed'
                        ? 'bg-[#DA2778] text-white'
                        : 'bg-white text-neutral-700 ring-1 ring-neutral-200'
                "
            >
                Thất bại
            </Link>
        </div>

        <p
            v-if="withdrawals.data.length === 0"
            class="rounded-lg border border-dashed border-neutral-300 bg-neutral-50 px-4 py-8 text-center text-sm text-neutral-600"
        >
            Chưa có yêu cầu nào.
        </p>

        <div
            v-else
            class="overflow-x-auto rounded-lg border border-neutral-200 bg-white shadow-sm"
        >
            <table class="min-w-full text-left text-sm">
                <thead
                    class="border-b border-neutral-200 bg-neutral-50 text-xs font-semibold uppercase text-neutral-600"
                >
                    <tr>
                        <th class="px-3 py-2">Thành viên</th>
                        <th class="px-3 py-2">Điểm</th>
                        <th class="min-w-[10rem] px-3 py-2">NH nhận (lúc gửi)</th>
                        <th class="px-3 py-2">Trạng thái</th>
                        <th class="px-3 py-2">Thời gian</th>
                        <th class="px-3 py-2 text-right">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="w in withdrawals.data"
                        :key="w.id"
                        class="border-b border-neutral-100 align-top"
                    >
                        <td class="px-3 py-2">
                            <span class="font-medium">{{
                                w.user?.username ?? '#' + w.user_id
                            }}</span>
                        </td>
                        <td class="px-3 py-2 font-semibold">{{ w.amount }}</td>
                        <td class="px-3 py-2 text-xs text-neutral-800">
                            <template
                                v-if="
                                    w.bank_name ||
                                    w.bank_account_number ||
                                    w.bank_account_holder
                                "
                            >
                                <div class="font-medium">{{ w.bank_name ?? '—' }}</div>
                                <div class="text-neutral-600">
                                    STK: {{ w.bank_account_number ?? '—' }}
                                </div>
                                <div class="text-neutral-600">
                                    {{ w.bank_account_holder ?? '—' }}
                                </div>
                            </template>
                            <span v-else class="text-neutral-400">—</span>
                        </td>
                        <td class="px-3 py-2">
                            <span
                                class="inline-flex rounded-full px-2 py-0.5 text-xs font-bold"
                                :class="statusClass(w.status)"
                            >
                                {{ statusLabel(w.status) }}
                            </span>
                        </td>
                        <td class="px-3 py-2 text-xs text-neutral-500">
                            {{ vnFromNow(w.created_at) }}
                            <span v-if="w.processed_at" class="block text-[10px]">
                                Xử lý: {{ vnFromNow(w.processed_at) }}
                            </span>
                        </td>
                        <td class="px-3 py-2 text-right">
                            <div
                                class="flex min-w-[14rem] flex-col items-end gap-2"
                            >
                                <div class="w-full max-w-[14rem] text-left">
                                    <Label
                                        :for="'status-' + w.id"
                                        class="text-[10px] text-neutral-500"
                                        >Trạng thái mới</Label
                                    >
                                    <select
                                        :id="'status-' + w.id"
                                        v-model="rowStatus[w.id]"
                                        class="mt-0.5 w-full rounded-md border border-neutral-200 bg-white px-2 py-1.5 text-xs"
                                    >
                                        <option value="pending">
                                            Đang chờ
                                        </option>
                                        <option value="success">
                                            Thành công
                                        </option>
                                        <option value="failed">
                                            Thất bại (hoàn điểm khi từ chờ/thành công)
                                        </option>
                                    </select>
                                </div>
                                <div
                                    v-if="rowStatus[w.id] === 'failed'"
                                    class="w-full max-w-[14rem] text-left"
                                >
                                    <Label
                                        :for="'note-' + w.id"
                                        class="text-[10px] text-neutral-500"
                                        >Ghi chú (khi thất bại)</Label
                                    >
                                    <Input
                                        :id="'note-' + w.id"
                                        v-model="rejectNotes[w.id]"
                                        type="text"
                                        class="mt-0.5 h-8 text-xs"
                                        placeholder="Tuỳ chọn"
                                    />
                                </div>
                                <Button
                                    type="button"
                                    size="sm"
                                    class="bg-[#DA2778] text-white hover:bg-[#c21f68]"
                                    @click="saveRow(w.id)"
                                >
                                    Lưu
                                </Button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div
            v-if="withdrawals.links?.length > 3"
            class="mt-4 flex flex-wrap justify-center gap-2"
        >
            <Link
                v-for="(l, i) in withdrawals.links"
                :key="i"
                :href="l.url || lixiWithdrawalAdmin.index.url()"
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
</template>
