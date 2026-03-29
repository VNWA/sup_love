<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { vnFromNow } from '@/composables/useVnFromNow';
import { dashboard } from '@/routes/admin';
import { edit as editRoom, index as roomsIndex } from '@/routes/admin/wheel-rooms';
import { index as roundsIndex } from '@/routes/admin/wheel-rooms/rounds';

type RoomRow = {
    id: number;
    name: string;
    slug: string;
    is_active: boolean;
    created_at: string;
    updated_at: string;
};

type Paginator<T> = {
    data: T[];
    links: { url: string | null; label: string; active: boolean }[];
};

defineProps<{
    rooms: Paginator<RoomRow>;
}>();
</script>

<template>
    <Head title="Phòng vòng quay" />

    <div>
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-xl font-bold">Phòng vòng quay</h1>
                <Link
                    :href="dashboard().url"
                    class="text-xs text-neutral-500 hover:text-[#DA2778]"
                >
                    ← Dashboard
                </Link>
            </div>
        </div>

        <p class="mb-4 text-sm text-neutral-600">
            Admin khởi tạo từng <strong>vòng quay</strong> trong phòng (chọn ô kết
            quả trước), kết thúc vòng rồi mới mở vòng tiếp theo. Thành viên nhập
            <strong>ID phòng</strong>
            hoặc
            <strong>mã phòng (slug)</strong>
            ở trang chủ để vào chơi; trạng thái vòng cập nhật realtime.
        </p>

        <div
            class="overflow-x-auto rounded-lg border border-neutral-200 bg-white shadow-sm"
        >
            <table class="min-w-full text-left text-sm">
                <thead
                    class="border-b border-neutral-200 bg-neutral-50 text-xs font-semibold uppercase text-neutral-600"
                >
                    <tr>
                        <th class="px-3 py-2">Phòng</th>
                        <th class="px-3 py-2">Slug</th>
                        <th class="px-3 py-2">Trạng thái</th>
                        <th class="px-3 py-2">Cập nhật</th>
                        <th class="px-3 py-2 text-right">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="r in rooms.data"
                        :key="r.id"
                        class="border-b border-neutral-100"
                    >
                        <td class="px-3 py-2 font-medium">{{ r.name }}</td>
                        <td class="px-3 py-2 font-mono text-xs">{{ r.slug }}</td>
                        <td class="px-3 py-2">
                            <span
                                v-if="r.is_active"
                                class="inline-flex rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-semibold text-emerald-800"
                            >
                                Đang mở
                            </span>
                            <span
                                v-else
                                class="inline-flex rounded-full bg-neutral-200 px-2.5 py-0.5 text-xs font-semibold text-neutral-700"
                            >
                                Tạm dừng
                            </span>
                        </td>
                        <td class="px-3 py-2 text-xs text-neutral-500">
                            {{ vnFromNow(r.updated_at) }}
                        </td>
                        <td class="px-3 py-2 text-right">
                            <div class="flex flex-wrap items-center justify-end gap-2">
                                <Link
                                    :href="roundsIndex.url({ wheelRoom: r.id })"
                                    class="inline-flex rounded-full bg-[#DA2778] px-3 py-1 text-xs font-semibold text-white hover:bg-[#b91560]"
                                >
                                    Vòng quay
                                </Link>
                                <Link
                                    :href="editRoom.url(r.id)"
                                    class="text-xs text-neutral-600 hover:text-[#DA2778]"
                                >
                                    Sửa phòng
                                </Link>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div
            v-if="rooms.links?.length > 3"
            class="mt-4 flex flex-wrap justify-center gap-2"
        >
            <Link
                v-for="(l, i) in rooms.links"
                :key="i"
                :href="l.url || roomsIndex().url"
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
