<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { index as usersIndex, store as storeUser } from '@/routes/admin/users';

const form = useForm({
    username: '',
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    point: 0,
});

function submit(): void {
    form.post(storeUser.url());
}
</script>

<template>
    <Head title="Tạo người dùng" />

    <div>
        <Link
            :href="usersIndex().url"
            class="text-sm text-[#DA2778] hover:underline"
        >
            ← Danh sách
        </Link>
        <h1 class="mt-2 text-xl font-bold">Tạo người dùng</h1>

        <form class="mt-6 max-w-md space-y-4" @submit.prevent="submit">
            <div class="space-y-2">
                <Label for="username">Username *</Label>
                <Input id="username" v-model="form.username" required />
                <p v-if="form.errors.username" class="text-xs text-red-600">
                    {{ form.errors.username }}
                </p>
            </div>
            <div class="space-y-2">
                <Label for="name">Tên hiển thị</Label>
                <Input id="name" v-model="form.name" />
            </div>
            <div class="space-y-2">
                <Label for="email">Email</Label>
                <Input id="email" v-model="form.email" type="email" />
                <p v-if="form.errors.email" class="text-xs text-red-600">
                    {{ form.errors.email }}
                </p>
            </div>
            <div class="space-y-2">
                <Label for="password">Mật khẩu *</Label>
                <Input
                    id="password"
                    v-model="form.password"
                    type="password"
                    required
                />
            </div>
            <div class="space-y-2">
                <Label for="password_confirmation">Xác nhận mật khẩu *</Label>
                <Input
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    type="password"
                    required
                />
            </div>
            <div class="space-y-2">
                <Label for="point">Điểm khởi tạo</Label>
                <Input
                    id="point"
                    v-model.number="form.point"
                    type="number"
                    min="0"
                />
                <p class="text-xs text-neutral-500">
                    Ghi nhận vào lịch sử nạp điểm nếu &gt; 0.
                </p>
            </div>
            <Button
                type="submit"
                class="bg-[#DA2778] hover:bg-[#b91560]"
                :disabled="form.processing"
            >
                <Spinner v-if="form.processing" class="mr-2" />
                Tạo
            </Button>
        </form>
    </div>
</template>
