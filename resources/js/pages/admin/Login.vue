<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { store as adminLoginStore } from '@/routes/admin/login';

const form = useForm({
    username: '',
    password: '',
    remember: false,
});

function submit(): void {
    form.post(adminLoginStore.url());
}
</script>

<template>
    <Head title="Admin đăng nhập" />

    <div
        class="flex min-h-dvh flex-col items-center justify-center bg-background px-4"
    >
        <div
            class="w-full max-w-sm rounded-xl border border-neutral-200  p-6 shadow-md"
        >
            <h1 class="text-center text-lg font-bold text-[#9d174d]">
                Admin / Đăng nhập
            </h1>
            <p class="mt-1 text-center text-xs text-neutral-500">
                Chỉ tài khoản có vai trò admin.
            </p>

            <form class="mt-6 space-y-4 " @submit.prevent="submit">
                <div class="space-y-2">
                    <Label for="username" >Tên đăng nhập</Label>
                    <Input
                        id="username"
                        v-model="form.username"
                        name="username"
                        type="text"
                        required
                        autocomplete="username"
                    />
                    <p
                        v-if="form.errors.username"
                        class="text-xs text-red-600"
                    >
                        {{ form.errors.username }}
                    </p>
                </div>
                <div class="space-y-2">
                    <Label for="password">Mật khẩu</Label>
                    <Input
                        id="password"
                        v-model="form.password"
                        name="password"
                        type="password"
                        required
                        autocomplete="current-password"
                    />
                </div>
                <label class="flex items-center gap-2 text-sm text-neutral-700">
                    <input
                        v-model="form.remember"
                        type="checkbox"
                        name="remember"
                        class="rounded border-neutral-300"
                    />
                    Ghi nhớ đăng nhập
                </label>
                <Button
                    type="submit"
                    class="w-full bg-[#9d174d] hover:bg-[#7a103c]"
                    :disabled="form.processing"
                >
                    <Spinner v-if="form.processing" class="mr-2" />
                    Vào dashboard
                </Button>
            </form>
        </div>
    </div>
</template>
