<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { Headphones, Home, UserRound } from 'lucide-vue-next';
import { computed } from 'vue';
import { useCskhAnchorAttrs } from '@/composables/useCskhLink';
import { home } from '@/routes';
import { index as accountIndex } from '@/routes/account';
import type { User } from '@/types';

const props = withDefaults(
    defineProps<{
        /** Tab đáy đang active */
        navActive?: 'home' | 'account';
        /** Hiển thị bên trái thanh hồng (mặc định ID thành viên) */
        topBarLeft?: string;
        /** Hiển thị bên phải thanh hồng (mặc định điểm tài khoản) */
        topBarRight?: string;
        /** Override điểm quay ở footer (nếu không truyền thì lấy từ user) */
        spinPointsDisplay?: number;
    }>(),
    {},
);

const page = usePage();

const user = computed(() => page.props.auth.user as User | null);

const memberId = computed(() => user.value?.id ?? 0);

const memberCode = computed(
    () => user.value?.username ?? String(user.value?.id ?? '—'),
);

const accountPoints = computed(() => user.value?.point ?? 0);

const spinPoints = computed(
    () => props.spinPointsDisplay ?? user.value?.point ?? 0,
);

const topLeft = computed(
    () => props.topBarLeft ?? `ID: ${memberId.value + 5800}`,
);

const topRight = computed(
    () =>
        props.topBarRight ?? `Số điểm tài khoản: ${accountPoints.value}`,
);

const cskhAttrs = useCskhAnchorAttrs();
</script>

<template>
    <div
        class="relative mx-auto flex min-h-dvh w-full max-w-md flex-col bg-white text-neutral-900"
    >
        <header class="shrink-0 bg-[#fff5f9] px-4 pb-3 pt-4 text-center">
            <p class="text-xs font-medium text-neutral-800">{{ page.props.name }}</p>
            <h1
                class="mt-1 text-lg font-extrabold leading-tight tracking-tight text-[#DA2778] sm:text-xl"
            >
                CLB HẸN HÒ YÊU THƯƠNG
            </h1>
        </header>

        <div
            class="flex shrink-0 items-center justify-between gap-2 bg-[#F677BC] px-3 py-2.5 text-xs font-semibold text-neutral-900 sm:text-sm"
        >
            <span>{{ topLeft }}</span>
            <span class="text-right">{{ topRight }}</span>
        </div>

        <main class="min-h-0 flex-1 overflow-y-auto bg-white px-3 pb-36 pt-4">
            <slot />
        </main>

        <div
            class="fixed inset-x-0 bottom-0 z-50 mx-auto w-full max-w-md border-t border-pink-300/30 bg-white/95 shadow-[0_-4px_20px_rgba(218,39,120,0.12)] backdrop-blur-sm"
        >
            <div
                class="flex items-center justify-between bg-[#F677BC] px-3 py-2 text-xs font-semibold text-white"
            >
                <span>Điểm quay hiện có: {{ spinPoints }}</span>
                <span class="text-right">Mã thành viên: {{ memberCode }}</span>
            </div>

            <nav
                class="flex items-stretch justify-around bg-[#9d174d] px-1 py-2 pb-[max(0.5rem,env(safe-area-inset-bottom))] text-white"
                aria-label="Điều hướng chính"
            >
                <Link
                    :href="home().url"
                    class="flex min-w-0 flex-1 flex-col items-center gap-1 rounded-lg py-1 text-[11px] font-medium transition hover:bg-white/10 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white"
                    :class="
                        navActive === 'home'
                            ? 'text-white'
                            : 'text-white/80'
                    "
                    :aria-current="navActive === 'home' ? 'page' : undefined"
                >
                    <Home class="size-6" stroke-width="2" />
                    <span>Trang chủ</span>
                </Link>

                <Link
                    :href="accountIndex().url"
                    class="flex min-w-0 flex-1 flex-col items-center gap-1 rounded-lg py-1 text-[11px] font-medium transition hover:bg-white/10 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white"
                    :class="
                        navActive === 'account'
                            ? 'text-white'
                            : 'text-white/80'
                    "
                    :aria-current="navActive === 'account' ? 'page' : undefined"
                >
                    <UserRound class="size-6" stroke-width="2" />
                    <span>Tài khoản</span>
                </Link>

                <a
                    v-bind="cskhAttrs"
                    class="flex min-w-0 flex-1 flex-col items-center gap-1 rounded-lg py-1 text-[11px] font-medium text-white/80 transition hover:bg-white/10 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white"
                >
                    <Headphones class="size-6" stroke-width="2" />
                    <span>CSKH 24h</span>
                </a>
            </nav>
        </div>
    </div>
</template>
