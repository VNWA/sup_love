<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { dashboard } from '@/routes/admin';
import { index as roundsIndex } from '@/routes/admin/wheel-rooms/rounds';
import { index as roomsIndex, update as updateRoom } from '@/routes/admin/wheel-rooms';

const props = defineProps<{
    room: {
        id: number;
        name: string;
        slug: string;
        is_active: boolean;
    };
}>();

const form = useForm({
    name: props.room.name,
    slug: props.room.slug,
    is_active: props.room.is_active,
});

function submit(): void {
    form.put(updateRoom.url(props.room.id));
}
</script>

<template>
    <Head :title="`Phòng: ${room.name}`" />

    <div class="max-w-lg">
        <div class="flex flex-wrap gap-3 text-sm">
            <Link
                :href="roomsIndex().url"
                class="text-[#DA2778] hover:underline"
            >
                ← Danh sách phòng
            </Link>
            <span class="text-neutral-300">|</span>
            <Link
                :href="dashboard().url"
                class="text-neutral-500 hover:text-[#DA2778]"
            >
                Dashboard
            </Link>
        </div>

        <h1 class="mt-4 text-xl font-bold">Phòng: {{ room.name }}</h1>

        <div
            class="mt-4 rounded-xl border border-pink-100 bg-gradient-to-r from-pink-50 to-rose-50 px-4 py-4 ring-1 ring-pink-100/80"
        >
            <p class="text-sm font-semibold text-neutral-900">
                Quản lý vòng quay
            </p>
            <p class="mt-1 text-xs text-neutral-600">
                Khởi tạo / kết thúc vòng, xem lịch sử và số lượt quay trong phòng.
            </p>
            <Button
                as-child
                class="mt-3 w-full bg-[#DA2778] hover:bg-[#b91560]"
            >
                <Link :href="roundsIndex.url({ wheelRoom: room.id })">
                    Mở trang vòng quay
                </Link>
            </Button>
        </div>

        <form class="mt-8 space-y-4" @submit.prevent="submit">
            <h2 class="text-sm font-semibold text-neutral-800">
                Thông tin phòng
            </h2>
            <div class="space-y-1">
                <Label for="name">Tên phòng</Label>
                <Input id="name" v-model="form.name" required maxlength="120" />
                <p v-if="form.errors.name" class="text-xs text-red-600">
                    {{ form.errors.name }}
                </p>
            </div>
            <div class="space-y-1">
                <Label for="slug">Slug (URL / cấu hình)</Label>
                <Input id="slug" v-model="form.slug" required maxlength="64" />
                <p v-if="form.errors.slug" class="text-xs text-red-600">
                    {{ form.errors.slug }}
                </p>
            </div>
            <div class="flex items-center gap-2">
                <input
                    id="is_active"
                    v-model="form.is_active"
                    type="checkbox"
                    class="size-4 rounded border-neutral-300"
                />
                <Label for="is_active" class="cursor-pointer">Phòng đang mở</Label>
            </div>

            <Button
                type="submit"
                class="bg-[#DA2778] hover:bg-[#b91560]"
                :disabled="form.processing"
            >
                <Spinner v-if="form.processing" class="mr-2" />
                Lưu
            </Button>
        </form>
    </div>
</template>
