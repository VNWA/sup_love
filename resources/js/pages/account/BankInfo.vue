<script setup lang="ts">
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import AccountMainTabs from '@/components/club/AccountMainTabs.vue';
import ClubMobileShell from '@/components/club/ClubMobileShell.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { update as updateBank } from '@/routes/account/bank';

const props = defineProps<{
    bank: {
        bank_name: string | null;
        bank_account_number: string | null;
        bank_account_holder: string | null;
    };
}>();

const page = usePage();
const flashSuccess = computed(
    () => (page.props.flash as { success?: string | null } | undefined)?.success,
);

const form = useForm({
    bank_name: props.bank.bank_name ?? '',
    bank_account_number: props.bank.bank_account_number ?? '',
    bank_account_holder: props.bank.bank_account_holder ?? '',
});

function submit(): void {
    form.put(updateBank.url());
}
</script>

<template>

    <Head title="Thông tin nhận lì xì" />

    <ClubMobileShell nav-active="account">
        <AccountMainTabs active="bank" />

        <div class="space-y-4">
            <h2 class="text-center text-sm font-bold uppercase tracking-wide text-white">
                Thông tin ngân hàng nhận lì xì
            </h2>
            <p class="text-center text-xs leading-relaxed text-white ">
                Điền để ban tổ chức chuyển tiền lì xì về đúng tài khoản của bạn.
            </p>

            <div v-if="flashSuccess"
                class="rounded-xl bg-emerald-50 px-3 py-2 text-center text-xs font-medium text-emerald-900 ring-1 ring-emerald-200"
                role="status">
                {{ flashSuccess }}
            </div>

            <form class="space-y-4" @submit.prevent="submit">
                <div class="space-y-1">
                    <Label for="bank_name">Tên ngân hàng</Label>
                    <Input class="text-xs" id="bank_name" v-model="form.bank_name" type="text" autocomplete="off"
                        placeholder="Ví dụ: Vietcombank" />
                    <p v-if="form.errors.bank_name" class="text-xs text-red-600">
                        {{ form.errors.bank_name }}
                    </p>
                </div>
                <div class="space-y-1">
                    <Label for="bank_account_number">Số tài khoản</Label>
                    <Input class="text-xs" id="bank_account_number" v-model="form.bank_account_number" type="text"
                        inputmode="numeric" autocomplete="off" placeholder="Chỉ số, không dấu cách" />
                    <p v-if="form.errors.bank_account_number" class="text-xs text-red-600">
                        {{ form.errors.bank_account_number }}
                    </p>
                </div>
                <div class="space-y-1">
                    <Label for="bank_account_holder">Tên người thụ hưởng</Label>
                    <Input class="text-xs" id="bank_account_holder" v-model="form.bank_account_holder" type="text"
                        autocomplete="name" placeholder="Theo tên trên thẻ / CCCD" />
                    <p v-if="form.errors.bank_account_holder" class="text-xs text-red-600">
                        {{ form.errors.bank_account_holder }}
                    </p>
                </div>

                <Button type="submit" class="w-full  " :disabled="form.processing">
                    <Spinner v-if="form.processing" class="mr-2" />
                    Lưu thông tin
                </Button>
            </form>
        </div>
    </ClubMobileShell>
</template>
