<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import {
    index as prizesIndex,
    store as storePrize,
} from '@/routes/admin/wheel-prizes';

const form = useForm({
    label: '',
    label_ngan: '',
    color: '#e91e63',
    weight: 10,
    sort_order: 0,
    is_active: true,
});

function submit(): void {
    form.post(storePrize.url());
}
</script>

<template>
    <Head title="Thêm giải vòng quay" />

    <div>
        <Link
            :href="prizesIndex().url"
            class="text-sm text-[#DA2778] hover:underline"
        >
            ← Danh sách giải
        </Link>
        <h1 class="mt-2 text-xl font-bold">Thêm giải thưởng</h1>

        <form class="mt-6 max-w-lg space-y-4" @submit.prevent="submit">
            <div class="space-y-2">
                <Label for="label">Nội dung đầy đủ *</Label>
                <Input id="label" v-model="form.label" required />
                <p v-if="form.errors.label" class="text-xs text-red-600">
                    {{ form.errors.label }}
                </p>
            </div>
            <div class="space-y-2">
                <Label for="label_ngan">Tên hiển thị ngắn trên vòng *</Label>
                <Input id="label_ngan" v-model="form.label_ngan" required />
                <p v-if="form.errors.label_ngan" class="text-xs text-red-600">
                    {{ form.errors.label_ngan }}
                </p>
            </div>
            <div class="space-y-2">
                <Label for="color">Màu (hex, ví dụ #e91e63) *</Label>
                <div class="flex flex-wrap items-center gap-3">
                    <Input
                        id="color"
                        v-model="form.color"
                        class="max-w-[10rem] font-mono"
                        required
                    />
                    <input
                        v-model="form.color"
                        type="color"
                        class="h-10 w-14 cursor-pointer rounded border border-neutral-300 bg-white"
                    />
                </div>
                <p v-if="form.errors.color" class="text-xs text-red-600">
                    {{ form.errors.color }}
                </p>
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div class="space-y-2">
                    <Label for="weight">Trọng số *</Label>
                    <Input
                        id="weight"
                        v-model.number="form.weight"
                        type="number"
                        min="0"
                        required
                    />
                    <p class="text-xs text-neutral-500">
                        Càng lớn càng dễ trúng (trong các giải đang bật).
                    </p>
                    <p v-if="form.errors.weight" class="text-xs text-red-600">
                        {{ form.errors.weight }}
                    </p>
                </div>
                <div class="space-y-2">
                    <Label for="sort_order">Thứ tự hiển thị</Label>
                    <Input
                        id="sort_order"
                        v-model.number="form.sort_order"
                        type="number"
                        min="0"
                    />
                    <p v-if="form.errors.sort_order" class="text-xs text-red-600">
                        {{ form.errors.sort_order }}
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <input
                    id="is_active"
                    v-model="form.is_active"
                    type="checkbox"
                    class="size-4 rounded border-neutral-300"
                />
                <Label for="is_active" class="font-normal">Đang sử dụng</Label>
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
