<script setup lang="ts">
import { Head, Link, router, useHttp, usePage } from '@inertiajs/vue3';
import { PartyPopper, Sparkles } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import ClubMobileShell from '@/components/club/ClubMobileShell.vue';
import { useCskhAnchorAttrs, useCskhHref } from '@/composables/useCskhLink';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { home } from '@/routes';
import { prizeWins as accountPrizeWins } from '@/routes/account';
import { spin as spinWheelRequest } from '@/routes/game';
import type { User } from '@/types';

type WheelPrizeRaw = {
    label: string;
    label_ngan: string;
    color: string;
    weight?: number;
};

const props = defineProps<{
    wheelPrizes: WheelPrizeRaw[];
    costPerSpin: number;
}>();

type GiaiThuong = {
    label: string;
    labelNgan: string;
    color: string;
    weight?: number;
};

const giaiThuong = computed<GiaiThuong[]>(() =>
    (props.wheelPrizes ?? []).map((p) => ({
        label: p.label,
        labelNgan: p.label_ngan,
        color: p.color,
        weight: p.weight,
    })),
);

const gocMoiO = computed(() => 360 / Math.max(1, giaiThuong.value.length));

const rotation = ref(0);

watch(
    gocMoiO,
    (g) => {
        rotation.value = -g / 2;
    },
    { immediate: true },
);

const segmentAngle = computed(() => gocMoiO.value);

/** Ô màu liền (giống mẫu), không vạch trắng giữa các cung. */
const conicBackground = computed(() => {
    const goc = gocMoiO.value;
    const list = giaiThuong.value;

    if (list.length === 0) {
        return 'transparent';
    }

    const stops = list
        .map((g, i) => {
            const start = i * goc;
            const end = (i + 1) * goc;

            return `${g.color} ${start}deg ${end}deg`;
        })
        .join(', ');

    return `conic-gradient(from 0deg at 50% 50%, ${stops})`;
});

/** Góc xoay chữ theo bán kính (đế chữ hướng tâm), chỉnh nửa dưới để không ngược. */
function gocChuTheoBanKinh(midDeg: number): number {
    const t = midDeg + 90;

    if (midDeg > 90 && midDeg < 270) {
        return t + 180;
    }

    return t;
}

/** Ưu tiên tên ngắn trên vòng; nếu ít ô thì dùng label đầy đủ. */
function chuHienThiTrenVong(g: GiaiThuong): string {
    const n = giaiThuong.value.length;

    if (n <= 8 && g.label.length <= 22) {
        return g.label;
    }

    if (n <= 12 && g.label.length <= 16) {
        return g.label;
    }

    return g.labelNgan || g.label;
}

function mauChuSangTuongPhan(hex: string): boolean {
    const h = hex.replace('#', '').trim();

    if (h.length !== 3 && h.length !== 6) {
        return false;
    }

    const full =
        h.length === 3
            ? h
                  .split('')
                  .map((c) => c + c)
                  .join('')
            : h;

    const n = Number.parseInt(full, 16);
    const r = (n >> 16) & 255;
    const g = (n >> 8) & 255;
    const b = n & 255;
    const luminance = (0.299 * r + 0.587 * g + 0.114 * b) / 255;

    return luminance > 0.72;
}

function gocTamOI(index: number): number {
    const goc = gocMoiO.value;

    return index * goc + goc / 2;
}

function tinhGocThem(winnerIndex: number, gocHienTai: number): number {
    const tamOI = gocTamOI(winnerIndex);
    const chuanHoa = ((gocHienTai % 360) + 360) % 360;
    const canPhai = (360 - tamOI + 360) % 360;
    const them = (canPhai - chuanHoa + 360) % 360;
    const vongDay = 5;

    return vongDay * 360 + them;
}

const page = usePage();
const user = computed(() => page.props.auth.user as User | null);

const localSpinPoints = ref(user.value?.point ?? 0);

watch(
    user,
    (u) => {
        if (u?.point !== undefined) {
            localSpinPoints.value = u.point;
        }
    },
    { deep: true },
);

const spinHttp = useHttp({});

const spinning = ref(false);

const winModalOpen = ref(false);
const giaiVuaTrung = ref<GiaiThuong | null>(null);

type SpinApiResponse = {
    winner_index: number;
    prize: { label: string; label_ngan: string; color: string };
    points: number;
};

type ManhConfetti = {
    id: number;
    left: string;
    delay: string;
    duration: string;
    color: string;
    rotateEnd: number;
    size: string;
};

const manhConfetti = ref<ManhConfetti[]>([]);

const mauConfetti = [
    '#e91e63',
    '#f48fb1',
    '#ffeb3b',
    '#ff9800',
    '#9c27b0',
    '#DA2778',
    '#fff',
];

function taoConfetti(): void {
    manhConfetti.value = Array.from({ length: 36 }, (_, i) => ({
        id: i,
        left: `${Math.random() * 100}%`,
        delay: `${Math.random() * 0.4}s`,
        duration: `${2.2 + Math.random() * 1.8}s`,
        color: mauConfetti[i % mauConfetti.length] ?? '#e91e63',
        rotateEnd: Math.floor(Math.random() * 1080),
        size: `${6 + Math.random() * 6}px`,
    }));
}

watch(winModalOpen, (mo) => {
    if (mo) {
        taoConfetti();
    }
});

const cskhHrefResolved = useCskhHref();
const cskhAttrs = useCskhAnchorAttrs();

const cskhButtonLabel = computed(() =>
    /^tel:/i.test(cskhHrefResolved.value)
        ? 'Gọi CSKH ngay'
        : 'Liên hệ CSKH',
);

async function spin(): Promise<void> {
    if (spinning.value || localSpinPoints.value < props.costPerSpin) {
        return;
    }

    spinning.value = true;
    winModalOpen.value = false;
    giaiVuaTrung.value = null;
    try {
        const res = (await spinHttp.post(spinWheelRequest.url(), {
            data: {},
        })) as SpinApiResponse;

        const winner = res.winner_index;
        const them = tinhGocThem(winner, rotation.value);

        rotation.value += them;
        localSpinPoints.value = res.points;

        window.setTimeout(() => {
            spinning.value = false;
            giaiVuaTrung.value = {
                label: res.prize.label,
                labelNgan: res.prize.label_ngan,
                color: res.prize.color,
            };
            winModalOpen.value = true;
            router.reload({ only: ['auth'] });
        }, 4200);
    } catch {
        spinning.value = false;
    }
}

function dongModal(): void {
    winModalOpen.value = false;
}
</script>

<template>
    <Head title="Vòng quay may mắn" />

    <ClubMobileShell :spin-points-display="localSpinPoints">
        <div class="mx-auto max-w-sm">
            <div class="mb-2 flex items-center justify-between gap-2">
                <h2
                    class="text-sm font-bold uppercase tracking-wide text-neutral-900"
                >
                    Vòng quay may mắn
                </h2>
                <Link
                    :href="home().url"
                    class="shrink-0 text-xs font-semibold text-[#DA2778] underline decoration-pink-300 underline-offset-2"
                >
                    ← Trang chủ
                </Link>
            </div>

            <p class="mb-4 text-center text-xs text-neutral-600">
                Mỗi lượt quay trừ {{ costPerSpin }} điểm . Giải nhẹ
                có tỷ lệ cao hơn; giải lớn hiếm hơn.
            </p>
            <p
                v-if="spinHttp.hasErrors"
                class="mb-2 text-center text-xs font-medium text-red-600"
            >
                {{ spinHttp.errors.point ?? spinHttp.errors.wheel ?? 'Không quay được.' }}
            </p>

            <div
                class="game-wheel-frame relative mx-auto aspect-square w-full max-w-[300px]"
            >
                <!-- Khung cam + đèn vàng (xoay cùng bánh) -->
                <div
                    class="absolute inset-0 overflow-visible rounded-full border-[7px] border-[#ff5722] shadow-[0_4px_14px_rgba(0,0,0,0.12)]"
                    :style="{
                        transformOrigin: '50% 50%',
                        transform: `rotate(${rotation}deg)`,
                        transition: spinning
                            ? 'transform 4s cubic-bezier(0.12, 0.8, 0.22, 1)'
                            : 'none',
                    }"
                >
                    <div
                        class="absolute inset-[7px] overflow-hidden rounded-full"
                        :style="{ background: conicBackground }"
                    >
                        <!-- Đèn tại mỗi đường chia ô (góc 0°, L°, 2L°…) -->
                        <div
                            v-for="i in giaiThuong.length"
                            :key="'bulb-' + i"
                            class="pointer-events-none absolute left-1/2 top-1/2 z-[5] w-0"
                            :style="{
                                height: 'calc(50% - 2px)',
                                transformOrigin: '50% 100%',
                                transform: `translate(-50%, -100%) rotate(${i * segmentAngle}deg)`,
                            }"
                        >
                            <div
                                class="absolute left-1/2 top-0 size-[7px] -translate-x-1/2 -translate-y-1/2 rounded-full bg-[#ffeb3b] shadow-[0_0_10px_3px_rgba(255,235,59,0.95),0_0_2px_#fff]"
                            />
                        </div>

                        <!-- Chữ dọc theo bán kính (đế hướng tâm), giữa cung -->
                        <div
                            v-for="(giai, i) in giaiThuong"
                            :key="giai.label + String(i)"
                            class="pointer-events-none absolute left-1/2 top-1/2 z-[8] w-0"
                            :style="{
                                height: '38%',
                                transformOrigin: '50% 100%',
                                transform: `translate(-50%, -100%) rotate(${i * segmentAngle + segmentAngle / 2}deg)`,
                            }"
                        >
                            <span
                                class="absolute left-1/2 top-0 max-w-[min(6.5rem,36vw)] -translate-x-1/2 -translate-y-1/2 whitespace-normal text-center font-extrabold leading-none tracking-wide text-white [text-shadow:0_1px_2px_rgba(0,0,0,0.55)]"
                                :class="[
                                    mauChuSangTuongPhan(giai.color)
                                        ? '!text-[#3e2723]'
                                        : '',
                                    giaiThuong.length > 14
                                        ? 'text-[7px]'
                                        : giaiThuong.length > 10
                                          ? 'text-[8px]'
                                          : giaiThuong.length > 8
                                            ? 'text-[9px]'
                                            : 'text-[10px]',
                                ]"
                                :style="{
                                    transform: `translate(-50%, 0) rotate(${gocChuTheoBanKinh(i * segmentAngle + segmentAngle / 2)}deg)`,
                                }"
                            >
                                {{ chuHienThiTrenVong(giai) }}
                            </span>
                        </div>
                    </div>
                </div>

                <button
                    type="button"
                    class="absolute left-1/2 top-1/2 z-30 flex h-[4.5rem] w-[4.5rem] -translate-x-1/2 -translate-y-1/2 items-center justify-center rounded-full border-[5px] border-white bg-[#DA2778] text-center text-[12px] font-extrabold uppercase leading-tight text-white shadow-md transition enabled:hover:scale-105 enabled:active:scale-95 disabled:cursor-not-allowed disabled:opacity-60"
                    :disabled="spinning || localSpinPoints < costPerSpin"
                    @click="spin"
                >
                    <span
                        class="pointer-events-none absolute left-1/2 top-0 z-10 -translate-x-1/2 -translate-y-[2px]"
                        aria-hidden="true"
                    >
                        <span
                            class="block h-0 w-0 border-x-[10px] border-b-[14px] border-x-transparent border-b-white drop-shadow-sm"
                        />
                    </span>
                    {{ spinning ? 'Đang quay…' : 'Quay' }}
                </button>
            </div>

            <div
                v-if="localSpinPoints < costPerSpin && !spinning"
                class="mt-4 rounded-xl bg-amber-50 px-3 py-2 text-center text-xs font-medium text-amber-900 ring-1 ring-amber-200"
            >
                Bạn đã hết điểm quay. Hãy tham gia hoạt động CLB để nhận thêm
                lượt.
            </div>
        </div>

        <Dialog v-model:open="winModalOpen">
            <DialogContent
                class="game-win-dialog max-w-[min(calc(100vw-1.5rem),22rem)] overflow-hidden border-[#DA2778]/30 bg-gradient-to-b from-pink-50 via-white to-rose-50 p-0 pt-6 sm:max-w-md"
            >
                <div
                    class="pointer-events-none absolute inset-0 overflow-hidden rounded-lg"
                    aria-hidden="true"
                >
                    <div
                        v-for="m in manhConfetti"
                        :key="m.id"
                        class="game-confetti absolute -top-3 rounded-sm opacity-90 shadow-sm"
                        :style="{
                            left: m.left,
                            width: m.size,
                            height: m.size,
                            backgroundColor: m.color,
                            animationDelay: m.delay,
                            animationDuration: m.duration,
                            '--game-rotate-end': `${m.rotateEnd}deg`,
                        }"
                    />
                </div>

                <div
                    class="relative z-10 flex flex-col items-center px-5 pb-2 pt-2"
                >
                    <div
                        class="game-trophy flex size-16 items-center justify-center rounded-full bg-gradient-to-br from-[#DA2778] to-[#9d174d] text-white shadow-lg ring-4 ring-pink-200/80"
                    >
                        <PartyPopper class="size-9" stroke-width="2" />
                    </div>

                    <DialogHeader class="mt-4 space-y-2 text-center sm:text-center">
                        <DialogTitle
                            class="game-shimmer text-center text-xl font-extrabold tracking-tight text-[#9d174d] sm:text-2xl"
                        >
                            Chúc mừng!
                        </DialogTitle>
                        <DialogDescription as-child>
                            <div
                                class="text-center text-sm leading-relaxed text-neutral-700"
                            >
                                <span
                                    class="game-text-pop inline-block font-medium text-neutral-800"
                                >
                                    Chúc mừng quý khách đã trúng:
                                </span>
                                <span
                                    class="mt-2 block rounded-xl bg-white/90 px-3 py-2.5 text-base font-extrabold leading-snug text-[#DA2778] shadow-inner ring-1 ring-pink-200/60"
                                >
                                    {{ giaiVuaTrung?.label }}
                                </span>
                                <span
                                    class="mt-3 flex items-center justify-center gap-1.5 text-xs font-medium text-neutral-600"
                                >
                                    <Sparkles
                                        class="size-3.5 shrink-0 text-amber-500"
                                    />
                                    Liên hệ CSKH để nhận thưởng.
                                </span>
                            </div>
                        </DialogDescription>
                    </DialogHeader>
                </div>

                <DialogFooter
                    class="relative z-10 flex-col gap-2 border-t border-pink-100 bg-white/60 px-5 py-4 sm:flex-col"
                >
                    <Button
                        class="w-full bg-[#DA2778] font-semibold text-white hover:bg-[#b91560]"
                        as-child
                    >
                        <a v-bind="cskhAttrs">{{ cskhButtonLabel }}</a>
                    </Button>
                    <Button variant="outline" class="w-full border-pink-200" as-child>
                        <Link :href="accountPrizeWins().url" class="w-full">
                            Xem danh sách phần thưởng
                        </Link>
                    </Button>
                    <Button
                        variant="outline"
                        class="w-full border-pink-200"
                        type="button"
                        @click="dongModal"
                    >
                        Đóng
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </ClubMobileShell>
</template>

<style scoped>
@keyframes game-confetti-fall {
    0% {
        transform: translateY(0) rotate(0deg);
        opacity: 1;
    }

    100% {
        transform: translateY(110vh) rotate(var(--game-rotate-end, 720deg));
        opacity: 0;
    }
}

.game-confetti {
    animation-name: game-confetti-fall;
    animation-timing-function: linear;
    animation-fill-mode: forwards;
}

@keyframes game-trophy-bounce {
    0%,
    100% {
        transform: scale(1) translateY(0);
    }

    40% {
        transform: scale(1.12) translateY(-6px);
    }

    60% {
        transform: scale(1.05) translateY(-2px);
    }
}

.game-trophy {
    animation: game-trophy-bounce 0.9s ease-out 0.15s both;
}

@keyframes game-shimmer {
    0%,
    100% {
        background-position: 0% 50%;
    }

    50% {
        background-position: 100% 50%;
    }
}

.game-shimmer {
    background: linear-gradient(
        110deg,
        #9d174d 0%,
        #da2778 25%,
        #f48fb1 50%,
        #da2778 75%,
        #9d174d 100%
    );
    background-size: 200% auto;
    -webkit-background-clip: text;
    background-clip: text;
    color: transparent;
    animation: game-shimmer 2.5s ease-in-out infinite;
}

@keyframes game-text-pop {
    0% {
        opacity: 0;
        transform: scale(0.92) translateY(6px);
    }

    70% {
        opacity: 1;
        transform: scale(1.02) translateY(0);
    }

    100% {
        opacity: 1;
        transform: scale(1) translateY(0);
    }
}

.game-text-pop {
    animation: game-text-pop 0.55s cubic-bezier(0.34, 1.56, 0.64, 1) 0.35s
        both;
}
</style>
