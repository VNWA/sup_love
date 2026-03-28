<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { dashboard } from '@/routes/admin';
import {
    create as createPrize,
    destroy,
    edit as editPrize,
    index as prizesIndex,
} from '@/routes/admin/wheel-prizes';

type PrizeRow = {
    id: number;
    label: string;
    label_ngan: string;
    color: string;
    weight: number;
    sort_order: number;
    is_active: boolean;
};

type Paginator<T> = {
    data: T[];
    links: { url: string | null; label: string; active: boolean }[];
};

defineProps<{
    prizes: Paginator<PrizeRow>;
}>();

function removePrize(p: PrizeRow): void {
    if (!confirm(`Xóa giải «${p.label_ngan}»?`)) {
        return;
    }

    router.delete(destroy.url(p.id));
}
</script>

<template>
    <Head title="Giải thưởng vòng quay" />

    <div>
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-xl font-bold">Giải thưởng vòng quay</h1>
                <Link
                    :href="dashboard().url"
                    class="text-xs text-neutral-500 hover:text-[#DA2778]"
                >
                    ← Dashboard
                </Link>
            </div>
            <Link
                :href="createPrize().url"
                class="rounded-lg bg-[#DA2778] px-3 py-2 text-sm font-semibold text-white hover:bg-[#b91560]"
            >
                + Thêm giải
            </Link>
        </div>

        <p class="mb-4 text-sm text-neutral-600">
            Nội dung hiển thị trên vòng quay (text đầy đủ + tên ngắn) và màu ô.
            Trọng số càng lớn càng dễ trúng (trong các giải đang bật).
        </p>

        <div class="overflow-x-auto rounded-lg border border-neutral-200 bg-white shadow-sm">
            <table class="min-w-full text-left text-sm">
                <thead class="border-b border-neutral-200 bg-neutral-50 text-xs font-semibold uppercase text-neutral-600">
                    <tr>
                        <th class="px-3 py-2">Màu</th>
                        <th class="px-3 py-2">Ngắn</th>
                        <th class="px-3 py-2">Nội dung</th>
                        <th class="px-3 py-2">Trọng số</th>
                        <th class="px-3 py-2">Thứ tự</th>
                        <th class="px-3 py-2">Bật</th>
                        <th class="px-3 py-2 text-right">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="p in prizes.data"
                        :key="p.id"
                        class="border-b border-neutral-100 hover:bg-neutral-50/80"
                    >
                        <td class="px-3 py-2">
                            <span
                                class="inline-block size-8 rounded border border-neutral-200 shadow-inner"
                                :style="{ backgroundColor: p.color }"
                                :title="p.color"
                            />
                            <span class="ml-2 font-mono text-xs text-neutral-500">{{
                                p.color
                            }}</span>
                        </td>
                        <td class="px-3 py-2 font-medium">{{ p.label_ngan }}</td>
                        <td class="max-w-xs truncate px-3 py-2 text-neutral-700">
                            {{ p.label }}
                        </td>
                        <td class="px-3 py-2">{{ p.weight }}</td>
                        <td class="px-3 py-2">{{ p.sort_order }}</td>
                        <td class="px-3 py-2">
                            <span
                                :class="
                                    p.is_active
                                        ? 'text-green-600'
                                        : 'text-neutral-400'
                                "
                            >
                                {{ p.is_active ? 'Có' : 'Không' }}
                            </span>
                        </td>
                        <td class="px-3 py-2 text-right">
                            <Link
                                :href="editPrize.url(p.id)"
                                class="text-[#DA2778] hover:underline"
                            >
                                Sửa
                            </Link>
                            <button
                                type="button"
                                class="ml-3 text-red-600 hover:underline"
                                @click="removePrize(p)"
                            >
                                Xóa
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div
            v-if="prizes.links?.length > 3"
            class="mt-4 flex flex-wrap justify-center gap-2"
        >
            <Link
                v-for="(l, i) in prizes.links"
                :key="i"
                :href="l.url || prizesIndex().url"
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
</template>
