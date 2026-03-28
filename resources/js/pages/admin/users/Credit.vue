<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { store as creditPoints } from '@/routes/admin/users/points';
import { edit as editUser, index as usersIndex } from '@/routes/admin/users';

type Tx = {
    id: number;
    type: string;
    amount: number;
    balance_after: number;
    admin_note: string | null;
    meta: Record<string, unknown> | null;
    created_at: string;
    actor?: { id: number; username: string | null; name: string | null } | null;
};

type CreditUserPayload = {
    id: number;
    username: string | null;
    name: string | null;
    point: number;
    point_transactions: Tx[];
};

const props = defineProps<{
    creditUser: CreditUserPayload;
}>();

const u = props.creditUser;

const creditForm = useForm({
    amount: 5,
    note: 'Nạp điểm ',
});

function submitCredit(): void {
    creditForm.post(creditPoints.url(u.id));
}
</script>

<template>
    <Head :title="`Nạp điểm @${creditUser.username}`" />

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
                :href="editUser.url(creditUser.id)"
                class="text-neutral-700 hover:text-[#DA2778] hover:underline"
            >
                ← Sửa thông tin
            </Link>
        </div>

        <h1 class="mt-2 text-xl font-bold">
            Nạp điểm — @{{ creditUser.username }}
        </h1>
        <p class="text-sm text-neutral-600">
            Số dư hiện tại:
            <strong class="text-[#DA2778]">{{ creditUser.point }}</strong>
        </p>

        <section class="mt-6 max-w-md">
            <h2 class="font-semibold text-neutral-800">Nạp điểm</h2>
            <p class="text-xs text-neutral-500">
                Ghi rõ nội dung / lý do (lưu lịch sử nạp điểm).
            </p>
            <form class="mt-3 space-y-3" @submit.prevent="submitCredit">
                <div class="space-y-1">
                    <Label for="amount">Số điểm</Label>
                    <Input
                        id="amount"
                        v-model.number="creditForm.amount"
                        type="number"
                        min="1"
                        required
                    />
                    <p
                        v-if="creditForm.errors.amount"
                        class="text-xs text-red-600"
                    >
                        {{ creditForm.errors.amount }}
                    </p>
                </div>
                <div class="space-y-1">
                    <Label for="note">Ghi chú *</Label>
                    <textarea
                        id="note"
                        v-model="creditForm.note"
                        required
                        rows="3"
                        class="w-full rounded-md border border-neutral-300 px-3 py-2 text-sm"
                        placeholder="Ví dụ: Thưởng tham gia sự kiện 28/03"
                    />
                    <p
                        v-if="creditForm.errors.note"
                        class="text-xs text-red-600"
                    >
                        {{ creditForm.errors.note }}
                    </p>
                </div>
                <Button
                    type="submit"
                    class="bg-[#DA2778] hover:bg-[#b91560]"
                    :disabled="creditForm.processing"
                >
                    <Spinner v-if="creditForm.processing" class="mr-2" />
                    Nạp điểm
                </Button>
            </form>
        </section>

        <section class="mt-10 border-t border-neutral-200 pt-8">
            <h2 class="font-semibold text-neutral-800">
                Lịch sử giao dịch (50 mới nhất)
            </h2>
            <div class="mt-3 overflow-x-auto rounded-lg border bg-white text-xs">
                <table class="w-full min-w-[520px] text-left">
                    <thead class="bg-neutral-50 text-neutral-500">
                        <tr>
                            <th class="px-2 py-2">Thời gian</th>
                            <th class="px-2 py-2">Loại</th>
                            <th class="px-2 py-2">±</th>
                            <th class="px-2 py-2">Sau GD</th>
                            <th class="px-2 py-2">Ghi chú / giải</th>
                            <th class="px-2 py-2">Bởi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="t in creditUser.point_transactions"
                            :key="t.id"
                            class="border-t"
                        >
                            <td class="px-2 py-2 whitespace-nowrap">
                                {{ t.created_at }}
                            </td>
                            <td class="px-2 py-2">{{ t.type }}</td>
                            <td
                                class="px-2 py-2 font-semibold"
                                :class="
                                    t.amount >= 0 ? 'text-green-600' : 'text-red-600'
                                "
                            >
                                {{ t.amount > 0 ? '+' : '' }}{{ t.amount }}
                            </td>
                            <td class="px-2 py-2">{{ t.balance_after }}</td>
                            <td class="px-2 py-2 max-w-[200px] truncate">
                                <span v-if="t.type === 'admin_credit'">{{
                                    t.admin_note
                                }}</span>
                                <span v-else>{{
                                    (t.meta as { prize_label?: string } | null)
                                        ?.prize_label
                                }}</span>
                            </td>
                            <td class="px-2 py-2">
                                {{
                                    t.actor?.username ??
                                    t.actor?.name ??
                                    '—'
                                }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</template>
