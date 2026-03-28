<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Check } from 'lucide-vue-next';
import ClubMobileShell from '@/components/club/ClubMobileShell.vue';
import { game } from '@/routes';

withDefaults(
    defineProps<{
        canRegister: boolean;
    }>(),
    {
        canRegister: true,
    },
);
</script>

<template>
    <Head title="Trang chủ" />

    <ClubMobileShell nav-active="home">
        <h2
            class="mb-3 text-center text-sm font-bold uppercase tracking-wide text-neutral-900"
        >
            VÒNG QUAY TÌNH YÊU
        </h2>

        <div class="mb-4 text-center">
            <Link
                :href="game().url"
                class="inline-flex items-center justify-center rounded-full bg-[#DA2778] px-5 py-2 text-xs font-extrabold uppercase tracking-wide text-white shadow-md ring-2 ring-pink-200 transition hover:bg-[#c2185b] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#DA2778]"
            >
                Chơi vòng quay may mắn
            </Link>
        </div>

        <!-- Banner 1: tours / journey -->
        <article
            class="relative mb-4 overflow-hidden rounded-2xl bg-gradient-to-br from-pink-200 via-rose-100 to-amber-100 shadow-md ring-1 ring-pink-200/80"
        >
            <div
                class="absolute inset-0 opacity-40"
                aria-hidden="true"
            >
                <div
                    class="absolute -left-8 top-6 h-24 w-24 rotate-45 rounded-lg bg-pink-400/60"
                />
                <div
                    class="absolute bottom-4 right-4 h-28 w-28 rotate-45 rounded-lg bg-rose-300/50"
                />
                <div
                    class="absolute right-12 top-10 h-20 w-20 rotate-45 rounded-lg bg-amber-200/70"
                />
            </div>
            <div class="relative p-4">
                <p
                    class="max-w-[55%] text-xs font-bold leading-snug text-[#b91560] drop-shadow-sm"
                >
                    HÀNH TRÌNH KẾT NỐI &amp; YÊU THƯƠNG
                </p>
                <div
                    class="mt-3 flex flex-wrap gap-1.5 text-[10px] font-semibold text-white"
                >
                    <span
                        class="rounded-full bg-[#DA2778]/90 px-2 py-0.5 shadow-sm"
                        >Tour Đà Lạt</span
                    >
                    <span
                        class="rounded-full bg-[#c2185b]/90 px-2 py-0.5 shadow-sm"
                        >Tour Mộc Châu</span
                    >
                    <span
                        class="rounded-full bg-[#ad1457]/90 px-2 py-0.5 shadow-sm"
                        >Tour Châu Âu</span
                    >
                    <span
                        class="rounded-full bg-[#880e4f]/90 px-2 py-0.5 shadow-sm"
                        >Tour Phượng Hoàng Cổ Trấn</span
                    >
                </div>
                <p
                    class="mt-4 text-right text-[10px] font-bold tracking-wide text-[#DA2778]"
                >
                    New Sky
                </p>
            </div>
        </article>

        <!-- Banner 2: lucky prize -->
        <article
            class="relative mb-2 overflow-hidden rounded-2xl bg-gradient-to-r from-sky-100 via-pink-50 to-pink-100 shadow-md ring-1 ring-pink-200/60"
        >
            <div
                class="flex min-h-[200px] flex-col gap-3 p-3 sm:flex-row sm:items-stretch"
            >
                <div
                    class="relative flex flex-1 flex-col items-center justify-center rounded-xl bg-gradient-to-b from-sky-200/80 to-pink-100/90 p-4"
                >
                    <div
                        class="mb-1 text-center text-[10px] font-bold uppercase tracking-wider text-[#DA2778]"
                    >
                        LOVE Connection
                    </div>
                    <p
                        class="text-center text-[11px] font-semibold text-neutral-800"
                    >
                        Hành trình
                        <span class="text-[#c2185b]">KẾT NỐI YÊU THƯƠNG</span>
                    </p>
                    <div
                        class="relative mt-3 flex h-28 w-28 items-center justify-center rounded-full border-4 border-white bg-gradient-to-br from-sky-300 to-pink-200 shadow-inner"
                        aria-hidden="true"
                    >
                        <div
                            class="absolute -right-1 -top-1 h-4 w-4 rounded-full bg-red-500"
                        />
                        <div
                            class="h-16 w-20 rounded-full border-2 border-white/80 bg-sky-400/40"
                        />
                    </div>
                    <div
                        class="absolute bottom-2 left-2 text-[9px] font-bold text-red-600 [writing-mode:vertical-rl]"
                    >
                        The sky
                    </div>
                </div>

                <div
                    class="flex w-full shrink-0 flex-col justify-center rounded-xl bg-red-600 p-3 text-white shadow-lg sm:max-w-[52%]"
                >
                    <h3
                        class="text-center text-xs font-extrabold uppercase leading-tight text-yellow-300"
                    >
                        GIẢI THƯỞNG MAY MẮN
                    </h3>
                    <ul class="mt-2 space-y-1.5 text-[10px] font-medium leading-snug">
                        <li class="flex gap-1.5">
                            <Check
                                class="mt-0.5 size-3.5 shrink-0 text-yellow-300"
                                stroke-width="3"
                            />
                            <span>Chuyến đi du lịch cho 2 người</span>
                        </li>
                        <li class="flex gap-1.5">
                            <Check
                                class="mt-0.5 size-3.5 shrink-0 text-yellow-300"
                                stroke-width="3"
                            />
                            <span>Thời gian: 3 ngày 2 đêm</span>
                        </li>
                        <li class="flex gap-1.5">
                            <Check
                                class="mt-0.5 size-3.5 shrink-0 text-yellow-300"
                                stroke-width="3"
                            />
                            <span>Địa điểm tự chọn (Việt Nam)</span>
                        </li>
                        <li class="flex gap-1.5">
                            <Check
                                class="mt-0.5 size-3.5 shrink-0 text-yellow-300"
                                stroke-width="3"
                            />
                            <span
                                >Có thể quy đổi phần thưởng may mắn thành tiền
                                hoặc hiện vật</span
                            >
                        </li>
                    </ul>
                </div>
            </div>
        </article>
    </ClubMobileShell>
</template>
