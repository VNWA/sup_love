<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import {
    index as winsIndex,
    update as updateWin,
} from '@/routes/admin/wheel-prize-wins';

type UserMini = {
    id: number;
    username: string | null;
    name: string | null;
};

type Win = {
    id: number;
    prize_label: string;
    prize_label_ngan: string;
    color: string;
    status: string;
    received_at: string | null;
    admin_note: string | null;
    created_at: string;
    user?: UserMini | null;
};

const props = defineProps<{
    win: Win;
}>();

const form = useForm({
    status: props.win.status,
    admin_note: props.win.admin_note ?? '',
});

function submit(): void {
    form.put(updateWin.url(props.win.id));
}
</script>

<template>
    <Head :title="`Phần thưởng #${win.id}`" />

    <div>
        <Link
            :href="winsIndex().url"
            class="text-sm text-[#DA2778] hover:underline"
        >
            ← Danh sách phần thưởng
        </Link>
        <h1 class="mt-2 text-xl font-bold">Cập nhật phần thưởng</h1>

        <div
            class="mt-4 rounded-lg border border-neutral-200 bg-white p-4 text-sm shadow-sm"
        >
            <div class="flex items-start gap-3">
                <span
                    class="inline-block size-10 shrink-0 rounded border border-neutral-200"
                    :style="{ backgroundColor: win.color }"
                />
                <div>
                    <p class="font-semibold text-neutral-900">{{ win.prize_label }}</p>
                    <p class="text-xs text-neutral-500">
                        Thành viên: @{{ win.user?.username ?? '—' }} · {{ win.created_at }}
                    </p>
                </div>
            </div>
        </div>

        <form class="mt-6 max-w-lg space-y-4" @submit.prevent="submit">
            <div class="space-y-2">
                <Label for="status">Trạng thái *</Label>
                <select
                    id="status"
                    v-model="form.status"
                    class="w-full rounded-md border border-neutral-300 px-2 py-2 text-sm"
                    required
                >
                    <option value="pending">Chưa nhận</option>
                    <option value="received">Đã nhận</option>
                </select>
                <p v-if="form.errors.status" class="text-xs text-red-600">
                    {{ form.errors.status }}
                </p>
            </div>

            <div class="space-y-2">
                <Label for="admin_note">Ghi chú (hiển thị cho thành viên)</Label>
                <textarea
                    id="admin_note"
                    v-model="form.admin_note"
                    rows="4"
                    class="w-full rounded-md border border-neutral-300 px-2 py-2 text-sm"
                    placeholder="Ví dụ: Đã giao quà ngày…"
                />
                <p v-if="form.errors.admin_note" class="text-xs text-red-600">
                    {{ form.errors.admin_note }}
                </p>
            </div>

            <Button
                type="submit"
                class="bg-[#DA2778] font-semibold text-white hover:bg-[#b91560]"
                :disabled="form.processing"
            >
                <Spinner v-if="form.processing" class="mr-2" />
                Lưu
            </Button>
        </form>
    </div>
</template>
