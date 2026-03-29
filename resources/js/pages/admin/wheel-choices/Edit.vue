<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { dashboard } from '@/routes/admin';
import {
    index as choicesIndex,
    update as updateChoice,
} from '@/routes/admin/wheel-choices';

const props = defineProps<{
    choice: { id: number; name: string; sort_order: number; color: string | null };
}>();

const form = useForm({
    name: props.choice.name,
    sort_order: props.choice.sort_order,
    color: props.choice.color ?? '#e91e63',
});

function submit(): void {
    form.put(updateChoice.url(props.choice.id));
}
</script>

<template>
    <Head title="Sửa ô vòng quay" />

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

        <h1 class="mt-4 text-xl font-bold">Sửa ô #{{ choice.id }}</h1>

        <form class="mt-6 space-y-4" @submit.prevent="submit">
            <div class="space-y-1">
                <Label for="name">Tên *</Label>
                <Input
                    id="name"
                    v-model="form.name"
                    required
                    maxlength="120"
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
                Cập nhật
            </Button>
        </form>
    </div>
</template>
