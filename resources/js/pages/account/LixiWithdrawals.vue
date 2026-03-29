<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import AccountMainTabs from '@/components/club/AccountMainTabs.vue';
import ClubMobileShell from '@/components/club/ClubMobileShell.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { vnFromNow } from '@/composables/useVnFromNow';
import { bank } from '@/routes/account';
import lixiWithdrawalRoutes from '@/routes/account/lixi-withdrawals';

type Row = {
    id: number;
    amount: number;
    status: string;
    admin_note: string | null;
    created_at: string;
    processed_at: string | null;
};

type Paginator<T> = {
    data: T[];
    links: { url: string | null; label: string; active: boolean }[];
};

const props = defineProps<{
    withdrawals: Paginator<Row>;
    bankComplete: boolean;
}>();

const page = usePage();
const flashSuccess = computed(
    () => (page.props.flash as { success?: string | null } | undefined)?.success,
);

const user = computed(
    () => page.props.auth.user as { point?: number } | null,
);

const form = useForm({
    amount: '' as string | number,
});

function submitWithdraw(): void {
    if (!props.bankComplete) {
        return;
    }

    const n = Math.floor(Number(form.amount));

    if (Number.isNaN(n) || n < 1) {
        return;
    }

    form.amount = n;
    form.post(lixiWithdrawalRoutes.store.url(), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            form.clearErrors();
        },
    });
}

function statusLabel(s: string): string {
    if (s === 'pending') {
        return 'Đang chờ';
    }

    if (s === 'success') {
        return 'Thành công';
    }

    if (s === 'failed') {
        return 'Thất bại';
    }

    return s;
}

function statusClass(s: string): string {
    if (s === 'pending') {
        return 'bg-amber-100 text-amber-900 ring-amber-200';
    }

    if (s === 'success') {
        return 'bg-emerald-100 text-emerald-900 ring-emerald-200';
    }

    if (s === 'failed') {
        return 'bg-red-100 text-red-900 ring-red-200';
    }

    return 'bg-neutral-100 text-neutral-800';
}
</script>

<template>
    <Head title="Rút lì xì" />

    <ClubMobileShell nav-active="account">
        <AccountMainTabs active="lixi" />

        <div class="space-y-5">
            <div>
                <h2
                    class="text-center text-sm font-bold uppercase tracking-wide text-neutral-900"
                >
                    Rút lì xì
                </h2>
                <p class="mt-1 text-center text-xs text-neutral-600">
                    Điểm trong app sẽ được chuyển theo thông tin ngân hàng bạn đã
                    lưu ở mục «Thông tin nhận lì xì» (cần đủ tên ngân hàng, số tài
                    khoản, chủ tài khoản). Yêu cầu chờ admin xử lý.
                </p>
                <p class="mt-2 text-center text-xs font-semibold text-[#9d174d]">
                    Điểm hiện có:
                    {{ user?.point ?? 0 }}
                </p>
            </div>

            <div
                v-if="flashSuccess"
                class="rounded-xl bg-emerald-50 px-3 py-2 text-center text-xs font-medium text-emerald-900 ring-1 ring-emerald-200"
                role="status"
            >
                {{ flashSuccess }}
            </div>

            <div
                v-if="!bankComplete"
                class="rounded-2xl border border-amber-200 bg-amber-50/90 px-3 py-3 text-center text-xs text-amber-950 ring-1 ring-amber-100"
                role="status"
            >
                <p class="font-semibold">Chưa đủ thông tin ngân hàng nhận lì xì.</p>
                <p class="mt-1 text-neutral-700">
                    Vui lòng điền đủ tên ngân hàng, số tài khoản và chủ tài khoản
                    trước khi gửi yêu cầu rút.
                </p>
                <Link
                    :href="bank.url()"
                    class="mt-3 inline-block rounded-full bg-[#DA2778] px-4 py-2 text-xs font-semibold text-white hover:bg-[#b91560]"
                >
                    Cập nhật thông tin nhận lì xì
                </Link>
            </div>

            <form
                class="rounded-2xl border border-pink-100 bg-pink-50/50 p-4 ring-1 ring-pink-100/80"
                @submit.prevent="submitWithdraw"
            >
                <Label for="w-amt" class="text-neutral-800">Số điểm muốn rút</Label>
                <Input
                    id="w-amt"
                    v-model.number="form.amount"
                    type="number"
                    min="1"
                    :max="user?.point ?? undefined"
                    class="mt-2 bg-white"
                    inputmode="numeric"
                    placeholder="Nhập số điểm"
                    :disabled="!bankComplete"
                />
                <p v-if="form.errors.amount" class="mt-1 text-xs text-red-600">
                    {{ form.errors.amount }}
                </p>
                <p v-if="form.errors.bank" class="mt-1 text-xs text-red-600">
                    {{ form.errors.bank }}
                </p>
                <Button
                    type="submit"
                    class="mt-4 w-full bg-[#DA2778] hover:bg-[#b91560]"
                    :disabled="form.processing || !bankComplete"
                >
                    <Spinner v-if="form.processing" class="mr-2" />
                    Gửi yêu cầu rút
                </Button>
            </form>

            <div>
                <h3 class="mb-3 text-center text-xs font-bold uppercase text-neutral-700">
                    Lịch sử rút
                </h3>
                <ul v-if="withdrawals.data.length" class="space-y-3">
                    <li
                        v-for="w in withdrawals.data"
                        :key="w.id"
                        class="rounded-2xl border border-pink-100 bg-white p-3 text-sm shadow-sm ring-1 ring-pink-50"
                    >
                        <div class="flex items-start justify-between gap-2">
                            <span class="font-semibold text-neutral-900"
                                >{{ w.amount }} điểm</span
                            >
                            <span
                                class="shrink-0 rounded-full px-2 py-0.5 text-[10px] font-bold ring-1"
                                :class="statusClass(w.status)"
                            >
                                {{ statusLabel(w.status) }}
                            </span>
                        </div>
                        <p class="mt-1 text-xs text-neutral-500">
                            {{ vnFromNow(w.created_at) }}
                            <span v-if="w.processed_at">
                                · Xử lý: {{ vnFromNow(w.processed_at) }}</span
                            >
                        </p>
                        <p
                            v-if="w.admin_note && w.status === 'failed'"
                            class="mt-2 text-xs text-neutral-700"
                        >
                            <span class="font-semibold">Ghi chú:</span>
                            {{ w.admin_note }}
                        </p>
                    </li>
                </ul>
                <p v-else class="text-center text-xs text-neutral-500">
                    Chưa có yêu cầu rút nào.
                </p>
            </div>

            <div
                v-if="withdrawals.links?.length > 3"
                class="flex flex-wrap justify-center gap-2"
            >
                <a
                    v-for="(l, i) in withdrawals.links"
                    :key="i"
                    :href="l.url || '#'"
                    class="rounded px-2 py-1 text-xs"
                    :class="
                        l.active
                            ? 'bg-[#DA2778] text-white'
                            : 'bg-neutral-100 text-neutral-700'
                    "
                >
                    <span v-html="l.label" />
                </a>
            </div>
        </div>
    </ClubMobileShell>
</template>
