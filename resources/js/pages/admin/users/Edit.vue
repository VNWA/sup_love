<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import {
    destroy,
    index as usersIndex,
    points as userPoints,
    update,
} from '@/routes/admin/users';

type EditUserPayload = {
    id: number;
    username: string | null;
    name: string | null;
    email: string | null;
    point: number;
};

const props = defineProps<{
    editUser: EditUserPayload;
}>();

const u = props.editUser;

const updateForm = useForm({
    username: u.username ?? '',
    name: u.name ?? '',
    email: u.email ?? '',
    password: '',
    password_confirmation: '',
});

function submitUpdate(): void {
    updateForm.patch(update.url(u.id));
}

function removeUser(): void {
    if (!confirm('Xóa vĩnh viễn người dùng này?')) {
        return;
    }

    router.delete(destroy.url(u.id));
}
</script>

<template>
    <Head :title="`Sửa @${editUser.username}`" />

    <div>
        <div class="flex flex-wrap gap-3 text-sm">
            <Link
                :href="usersIndex().url"
                class="text-[#DA2778] hover:underline"
            >
                ← Danh sách
            </Link>
            <span class="text-neutral-300">|</span>
            <Link
                :href="userPoints.url(editUser.id)"
                class="text-neutral-700 hover:text-[#DA2778] hover:underline"
            >
                Nạp điểm &amp; lịch sử →
            </Link>
        </div>

        <h1 class="mt-2 text-xl font-bold">
            Sửa thông tin @{{ editUser.username }}
        </h1>
        <p class="text-sm text-neutral-600">
            Điểm hiện tại:
            <strong class="text-[#DA2778]">{{ editUser.point }}</strong>
            — chỉnh điểm tại
            <Link
                :href="userPoints.url(editUser.id)"
                class="font-medium text-[#DA2778] hover:underline"
            >
                Nạp điểm
            </Link>
            .
        </p>

        <section class="mt-6 max-w-md">
            <h2 class="font-semibold text-neutral-800">Thông tin tài khoản</h2>
            <form class="mt-3 space-y-3" @submit.prevent="submitUpdate">
                <div class="space-y-1">
                    <Label for="username">Username</Label>
                    <Input id="username" v-model="updateForm.username" required />
                    <p
                        v-if="updateForm.errors.username"
                        class="text-xs text-red-600"
                    >
                        {{ updateForm.errors.username }}
                    </p>
                </div>
                <div class="space-y-1">
                    <Label for="name">Tên</Label>
                    <Input id="name" v-model="updateForm.name" />
                </div>
                <div class="space-y-1">
                    <Label for="email">Email</Label>
                    <Input id="email" v-model="updateForm.email" type="email" />
                </div>
                <div class="space-y-1">
                    <Label for="password">Mật khẩu mới (để trống nếu giữ)</Label>
                    <Input
                        id="password"
                        v-model="updateForm.password"
                        type="password"
                    />
                </div>
                <div class="space-y-1">
                    <Label for="password_confirmation">Xác nhận mật khẩu</Label>
                    <Input
                        id="password_confirmation"
                        v-model="updateForm.password_confirmation"
                        type="password"
                    />
                </div>
                <Button
                    type="submit"
                    class="bg-[#DA2778] hover:bg-[#b91560]"
                    :disabled="updateForm.processing"
                >
                    <Spinner v-if="updateForm.processing" class="mr-2" />
                    Lưu thông tin
                </Button>
            </form>
        </section>

        <div class="mt-10 border-t border-red-100 pt-8">
            <button
                type="button"
                class="text-sm font-medium text-red-600 hover:underline"
                @click="removeUser"
            >
                Xóa người dùng
            </button>
        </div>
    </div>
</template>
