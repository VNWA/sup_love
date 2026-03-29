<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { dashboard } from '@/routes/admin';
import {
    create as createChoice,
    destroy,
    edit as editChoice,
    index as choicesIndex,
} from '@/routes/admin/wheel-choices';

type ChoiceRow = {
    id: number;
    name: string;
    sort_order: number;
};

type Paginator<T> = {
    data: T[];
    links: { url: string | null; label: string; active: boolean }[];
};

defineProps<{
    choices: Paginator<ChoiceRow>;
}>();

function removeChoice(c: ChoiceRow): void {
    if (!confirm(`Xóa ô «${c.name}»?`)) {
        return;
    }

    router.delete(destroy.url(c.id));
}
</script>

<template>
    <Head title="Ô lựa chọn vòng quay" />

    <div>
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-xl font-bold">Ô lựa chọn vòng quay</h1>
                <Link
                    :href="dashboard().url"
                    class="text-xs text-neutral-500 hover:text-[#DA2778]"
                >
                    ← Dashboard
                </Link>
            </div>
            <Link
                :href="createChoice().url"
                class="rounded-lg bg-[#DA2778] px-3 py-2 text-sm font-semibold text-white hover:bg-[#b91560]"
            >
                + Thêm ô
            </Link>
        </div>

        <p class="mb-4 text-sm text-neutral-600">
            Mỗi ô chỉ cần tên (ví dụ: Hôn nhân, Tình yêu). Màu trên vòng quay được
            gán tự động theo thứ tự. Người chơi đặt cược điểm vào một ô rồi quay.
        </p>

        <div
            class="overflow-x-auto rounded-lg border border-neutral-200 bg-white shadow-sm"
        >
            <table class="min-w-full text-left text-sm">
                <thead
                    class="border-b border-neutral-200 bg-neutral-50 text-xs font-semibold uppercase text-neutral-600"
                >
                    <tr>
                        <th class="px-3 py-2">ID</th>
                        <th class="px-3 py-2">Tên</th>
                        <th class="px-3 py-2">Thứ tự</th>
                        <th class="px-3 py-2 text-right">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="c in choices.data"
                        :key="c.id"
                        class="border-b border-neutral-100"
                    >
                        <td class="px-3 py-2 font-mono text-xs">{{ c.id }}</td>
                        <td class="px-3 py-2 font-medium">{{ c.name }}</td>
                        <td class="px-3 py-2">{{ c.sort_order }}</td>
                        <td class="px-3 py-2 text-right">
                            <Link
                                :href="editChoice.url(c.id)"
                                class="text-[#DA2778] hover:underline"
                            >
                                Sửa
                            </Link>
                            <span class="mx-2 text-neutral-300">|</span>
                            <button
                                type="button"
                                class="text-red-600 hover:underline"
                                @click="removeChoice(c)"
                            >
                                Xóa
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div
            v-if="choices.links?.length > 3"
            class="mt-4 flex flex-wrap justify-center gap-2"
        >
            <Link
                v-for="(l, i) in choices.links"
                :key="i"
                :href="l.url || choicesIndex().url"
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
