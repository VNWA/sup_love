<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { dashboard } from '@/routes/admin';
import {
    edit as editWin,
    index as winsIndex,
} from '@/routes/admin/wheel-prize-wins';

type UserMini = {
    id: number;
    username: string | null;
    name: string | null;
};

type WinRow = {
    id: number;
    user_id: number;
    prize_label: string;
    prize_label_ngan: string;
    color: string;
    status: string;
    received_at: string | null;
    admin_note: string | null;
    created_at: string;
    user?: UserMini | null;
};

type Paginator<T> = {
    data: T[];
    links: { url: string | null; label: string; active: boolean }[];
};

const props = defineProps<{
    wins: Paginator<WinRow>;
    filters: { status: string; username: string };
}>();

function statusLabel(s: string): string {
    if (s === 'received') {
        return 'Đã nhận';
    }

    if (s === 'pending') {
        return 'Chưa nhận';
    }

    return s;
}

function applyFilters(ev: Event): void {
    const fd = new FormData(ev.target as HTMLFormElement);
    const status = String(fd.get('status') ?? '').trim();
    const username = String(fd.get('username') ?? '').trim();

    router.get(
        winsIndex().url,
        {
            status: status || undefined,
            username: username || undefined,
        },
        { preserveState: true },
    );
}
</script>

<template>
    <Head title="Phần thưởng thành viên" />

    <div>
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-xl font-bold">Phần thưởng quay trúng</h1>
                <Link
                    :href="dashboard().url"
                    class="text-xs text-neutral-500 hover:text-[#DA2778]"
                >
                    ← Dashboard
                </Link>
            </div>
        </div>

        <form
            class="mb-4 flex flex-wrap items-end gap-3 rounded-lg border border-neutral-200 bg-white p-3 shadow-sm"
            @submit.prevent="applyFilters"
        >
            <div>
                <label class="mb-1 block text-xs font-semibold text-neutral-600" for="f-status"
                    >Trạng thái</label
                >
                <select
                    id="f-status"
                    name="status"
                    class="rounded-md border border-neutral-300 px-2 py-1.5 text-sm"
                >
                    <option value="" :selected="filters.status === ''">Tất cả</option>
                    <option value="pending" :selected="filters.status === 'pending'">
                        Chưa nhận
                    </option>
                    <option value="received" :selected="filters.status === 'received'">
                        Đã nhận
                    </option>
                </select>
            </div>
            <div class="min-w-[10rem] flex-1">
                <label class="mb-1 block text-xs font-semibold text-neutral-600" for="f-user"
                    >Username</label
                >
                <input
                    id="f-user"
                    name="username"
                    type="search"
                    class="w-full rounded-md border border-neutral-300 px-2 py-1.5 text-sm"
                    :value="filters.username"
                    placeholder="Lọc theo @username"
                    autocomplete="off"
                />
            </div>
            <button
                type="submit"
                class="rounded-lg bg-[#DA2778] px-3 py-2 text-sm font-semibold text-white hover:bg-[#b91560]"
            >
                Lọc
            </button>
        </form>

        <div class="overflow-x-auto rounded-lg border border-neutral-200 bg-white shadow-sm">
            <table class="min-w-full text-left text-sm">
                <thead
                    class="border-b border-neutral-200 bg-neutral-50 text-xs font-semibold uppercase text-neutral-600"
                >
                    <tr>
                        <th class="px-3 py-2">Thành viên</th>
                        <th class="px-3 py-2">Giải</th>
                        <th class="px-3 py-2">Trạng thái</th>
                        <th class="px-3 py-2">Thời gian</th>
                        <th class="px-3 py-2 text-right">Sửa</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="w in wins.data"
                        :key="w.id"
                        class="border-b border-neutral-100 hover:bg-neutral-50/80"
                    >
                        <td class="px-3 py-2">
                            <span class="font-medium">@{{ w.user?.username ?? w.user_id }}</span>
                        </td>
                        <td class="max-w-xs px-3 py-2">
                            <div class="flex items-center gap-2">
                                <span
                                    class="inline-block size-6 shrink-0 rounded border border-neutral-200"
                                    :style="{ backgroundColor: w.color }"
                                />
                                <span class="truncate text-neutral-800">{{ w.prize_label }}</span>
                            </div>
                        </td>
                        <td class="px-3 py-2">
                            <span
                                class="rounded-full px-2 py-0.5 text-xs font-semibold"
                                :class="
                                    w.status === 'received'
                                        ? 'bg-emerald-100 text-emerald-800'
                                        : 'bg-amber-100 text-amber-900'
                                "
                            >
                                {{ statusLabel(w.status) }}
                            </span>
                        </td>
                        <td class="whitespace-nowrap px-3 py-2 text-xs text-neutral-600">
                            {{ w.created_at }}
                        </td>
                        <td class="px-3 py-2 text-right">
                            <Link
                                :href="editWin.url(w.id)"
                                class="text-[#DA2778] hover:underline"
                            >
                                Sửa
                            </Link>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div
            v-if="wins.links?.length > 3"
            class="mt-4 flex flex-wrap justify-center gap-2"
        >
            <Link
                v-for="(l, i) in wins.links"
                :key="i"
                :href="l.url || '#'"
                class="rounded px-2 py-1 text-xs"
                :class="
                    l.active ? 'bg-[#DA2778] text-white' : 'bg-neutral-100 text-neutral-700'
                "
                :preserve-scroll="true"
            >
                <span v-html="l.label" />
            </Link>
        </div>
    </div>
</template>
