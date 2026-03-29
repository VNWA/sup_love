<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { dashboard } from '@/routes/admin';
import { index as choicesIndex, store as storeChoice } from '@/routes/admin/wheel-choices';

const form = useForm({
    name: '',
    sort_order: 0 as number,
    color: '#e91e63',
});

function submit(): void {
    form.post(storeChoice.url());
}
</script>

<template>
    <Head title="Thêm ô vòng quay" />

    <div class="max-w-md">
        <Link
            :href="choicesIndex().url"
            class="text-sm text-[#DA2778] hover:underline"
        >
            ← Danh sách ô
        </Link>
        <Link
            :href="dashboard().url"
            class="ml-3 text-sm text-neutral-500 hover:text-[#DA2778]"
        >
            Dashboard
        </Link>

        <h1 class="mt-4 text-xl font-bold">Thêm ô lựa chọn</h1>

        <form class="mt-6 space-y-4" @submit.prevent="submit">
            <div class="space-y-1">
                <Label for="name">Tên *</Label>
                <Input
                    id="name"
                    v-model="form.name"
                    required
                    maxlength="120"
                    placeholder="Ví dụ: Gia đình"
                />
                <p v-if="form.errors.name" class="text-xs text-red-600">
                    {{ form.errors.name }}
                </p>
            </div>
            <div class="space-y-1">
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
            <div class="space-y-1">
                <Label for="color">Màu ô (hex)</Label>
                <Input
                    id="color"
                    v-model="form.color"
                    maxlength="16"
                    placeholder="#e91e63"
                />
                <p v-if="form.errors.color" class="text-xs text-red-600">
                    {{ form.errors.color }}
                </p>
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
