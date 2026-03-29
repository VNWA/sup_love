<script setup lang="ts">
import { Head, Link, router, useHttp, usePage } from '@inertiajs/vue3';
import { CloudOff, PartyPopper, Sparkles, X } from 'lucide-vue-next';
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from 'vue';
import ClubMobileShell from '@/components/club/ClubMobileShell.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { subscribeWheelRoom } from '@/echo';
import { drawLuckyWheel } from '@/lib/luckyWheelCanvas';
import {
    history as accountHistory,
    prizeWins as accountPrizeWins,
} from '@/routes/account';
import { spin as spinWheelRequest } from '@/routes/game';
import type { User } from '@/types';

type WheelChoiceRow = {
    id: number;
    name: string;
    color: string;
};

const props = defineProps<{
    wheelRoom: { id: number; name: string; slug: string; is_active: boolean };
    wheelRound: { id: number; round_number: number } | null;
    hasSpunCurrentRound: boolean;
    lastSpinPreview: {
        result_name: string;
        result_color: string;
        bet_amount: number;
        is_consolation: boolean;
    } | null;
    wheelChoices: WheelChoiceRow[];
}>();

/** Các lĩnh vực mong muốn — cố định frontend, không lấy từ API. */
const WISH_OPTIONS = [
    { value: 'hon_nhan', label: 'Hôn nhân' },
    { value: 'suc_khoe', label: 'Sức khỏe' },
    { value: 'tinh_yeu', label: 'Tình yêu' },
    { value: 'gia_dinh', label: 'Gia đình' },
    { value: 'su_nghiep', label: 'Sự nghiệp' },
    { value: 'ban_be', label: 'Bạn bè' },
    { value: 'du_lich', label: 'Du lịch' },
    { value: 'tai_chinh', label: 'Tài chính' },
] as const;

const phongDangMo = computed(() => props.wheelRoom.is_active);

type GiaiThuong = {
    id: number;
    label: string;
    labelNgan: string;
    color: string;
};

const giaiThuong = computed<GiaiThuong[]>(() =>
    (props.wheelChoices ?? []).map((p) => ({
        id: p.id,
        label: p.name,
        labelNgan: p.name.length > 14 ? `${p.name.slice(0, 12)}…` : p.name,
        color: p.color,
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

const wheelCanvasSegments = computed(() =>
    giaiThuong.value.map((g) => ({
        color: g.color,
        label: chuHienThiTrenVong(g),
        textColor: mauChuSangTuongPhan(g.color) ? '#3e2723' : '#ffffff',
    })),
);

const wheelCanvasRef = ref<HTMLCanvasElement | null>(null);
const wheelFrameRef = ref<HTMLElement | null>(null);
let wheelResizeObserver: ResizeObserver | null = null;

function paintWheel(): void {
    const canvas = wheelCanvasRef.value;

    if (!canvas) {
        return;
    }

    const rect = canvas.getBoundingClientRect();

    if (rect.width < 2 || rect.height < 2) {
        return;
    }

    const w = Math.floor(rect.width);
    const h = Math.floor(rect.height);
    const dpr = window.devicePixelRatio || 1;

    canvas.width = w * dpr;
    canvas.height = h * dpr;
    const ctx = canvas.getContext('2d');

    if (!ctx) {
        return;
    }

    ctx.setTransform(1, 0, 0, 1, 0, 0);
    ctx.scale(dpr, dpr);
    ctx.clearRect(0, 0, w, h);
    drawLuckyWheel(ctx, {
        width: w,
        height: h,
        segments: wheelCanvasSegments.value,
        /** Đẩy chữ ra gần viền để nút giữa ít che hơn. */
        labelRadiusRatio: 0.46,
        outerRadiusRatio: 0.94,
    });
}

watch(
    wheelCanvasSegments,
    () => {
        nextTick(() => {
            requestAnimationFrame(() => {
                paintWheel();
            });
        });
    },
    { deep: true },
);

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

const localPoints = ref(user.value?.point ?? 0);

watch(
    user,
    (u) => {
        if (u?.point !== undefined) {
            localPoints.value = u.point;
        }
    },
    { deep: true },
);

/** useHttp chỉ gửi body từ form.data(), không dùng options.data khi post. */
const spinHttp = useHttp({
    wheel_room_id: 0,
    wheel_round_id: 0,
    bet_amount: 0,
    wish_category: '',
});

const activeRound = ref(props.wheelRound);
const hasSpunThisRound = ref(props.hasSpunCurrentRound);

watch(
    () => props.hasSpunCurrentRound,
    (v) => {
        hasSpunThisRound.value = v;
    },
);

const spinning = ref(false);

const winModalOpen = ref(false);
const ketQuaTen = ref('');
const ketQuaMau = ref('#e91e63');
/** Trúng giải (không phải ô an ủi) — hiệu ứng chúc mừng + pháo hoa. */
const laGiaiThuong = ref(false);
const laAnUi = ref(false);

watch(
    () => props.wheelRound,
    (v) => {
        activeRound.value = v;

        if (v === null) {
            winModalOpen.value = false;
        }
    },
);

watch(
    () => props.wheelRound?.id ?? null,
    (id, prevId) => {
        if (prevId !== undefined && id !== prevId) {
            betAmount.value = 0;
            wishCategory.value = '';
        }
    },
);

watch(
    () => [props.lastSpinPreview, props.hasSpunCurrentRound] as const,
    () => {
        const p = props.lastSpinPreview;

        if (p) {
            ketQuaTen.value = p.result_name;
            ketQuaMau.value = p.result_color;
            laAnUi.value = p.is_consolation;
            laGiaiThuong.value = !p.is_consolation;
            winModalOpen.value = true;
        } else if (!props.hasSpunCurrentRound) {
            winModalOpen.value = false;
        }
    },
    { immediate: true },
);

let unsubscribeWheel: (() => void) | null = null;

onMounted(() => {
    unsubscribeWheel = subscribeWheelRoom(props.wheelRoom.id, {
        onRoundStarted: () => {
            router.reload({
                only: [
                    'wheelRound',
                    'hasSpunCurrentRound',
                    'lastSpinPreview',
                    'wheelChoices',
                ],
            });
        },
        onRoundEnded: () => {
            router.reload({
                only: [
                    'wheelRound',
                    'hasSpunCurrentRound',
                    'lastSpinPreview',
                    'wheelChoices',
                ],
            });
        },
    });

    nextTick(() => {
        const schedulePaint = (): void => {
            requestAnimationFrame(() => {
                paintWheel();
            });
        };

        schedulePaint();

        const frame = wheelFrameRef.value;

        if (frame) {
            wheelResizeObserver = new ResizeObserver(() => {
                schedulePaint();
            });
            wheelResizeObserver.observe(frame);
        }
    });
});

onUnmounted(() => {
    wheelResizeObserver?.disconnect();
    wheelResizeObserver = null;
    unsubscribeWheel?.();
});

/** Số tiền ghi nhận (không trừ ví trong game). */
const betAmount = ref(0);
const wishCategory = ref('');

const effectiveBet = computed(() => {
    const n = Math.floor(Number(betAmount.value));

    return Number.isNaN(n) ? 0 : n;
});

type SpinApiResponse = {
    winner_index: number;
    result: { id: number; name: string; color: string };
    is_consolation: boolean;
    bet_amount: number;
    wish_category: string;
    points: number;
    wheel_round_id: number;
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
    manhConfetti.value = Array.from({ length: 52 }, (_, i) => ({
        id: i,
        left: `${Math.random() * 100}%`,
        delay: `${Math.random() * 0.35}s`,
        duration: `${2.4 + Math.random() * 2.1}s`,
        color: mauConfetti[i % mauConfetti.length] ?? '#e91e63',
        rotateEnd: Math.floor(Math.random() * 1080),
        size: `${5 + Math.random() * 9}px`,
    }));
}

watch(winModalOpen, (mo) => {
    if (mo && laGiaiThuong.value) {
        taoConfetti();
    }
});

function dongKetQuaModal(): void {
    winModalOpen.value = false;
}

const coTheQuay = computed(
    () =>
        phongDangMo.value &&
        activeRound.value !== null &&
        !hasSpunThisRound.value &&
        wishCategory.value !== '' &&
        effectiveBet.value >= 1 &&
        giaiThuong.value.length > 0,
);

async function spin(): Promise<void> {
    if (spinning.value || !coTheQuay.value) {
        return;
    }

    spinning.value = true;
    winModalOpen.value = false;

    if (activeRound.value === null) {
        return;
    }

    try {
        spinHttp.wheel_room_id = props.wheelRoom.id;
        spinHttp.wheel_round_id = activeRound.value.id;
        spinHttp.bet_amount = effectiveBet.value;
        spinHttp.wish_category = wishCategory.value;

        const res = (await spinHttp.post(
            spinWheelRequest.url(),
        )) as SpinApiResponse;

        const winner = res.winner_index;
        const them = tinhGocThem(winner, rotation.value);

        rotation.value += them;
        localPoints.value = res.points;

        window.setTimeout(() => {
            spinning.value = false;
            ketQuaTen.value = res.result.name;
            ketQuaMau.value = res.result.color;
            laAnUi.value = res.is_consolation;
            laGiaiThuong.value = !res.is_consolation;
            winModalOpen.value = true;
            hasSpunThisRound.value = true;
            wishCategory.value = '';
            betAmount.value = 0;
            router.reload({
                only: ['hasSpunCurrentRound', 'lastSpinPreview', 'wheelRound'],
            });
        }, 4200);
    } catch {
        spinning.value = false;
    }
}

function spinErrorMessage(): string {
    const e = spinHttp.errors as Record<string, string | undefined>;

    return (
        e.wheel_room_id ||
        e.wheel_round_id ||
        e.bet_amount ||
        e.wish_category ||
        e.wheel ||
        'Không quay được.'
    );
}
</script>

<template>

    <Head title="Vòng quay may mắn" />

    <ClubMobileShell :points-display="localPoints">
        <div class="mx-auto w-full min-w-0 max-w-sm">
            <!-- <div class="mb-2 flex items-center justify-between gap-2">
                <h2
                    class="text-sm font-bold uppercase tracking-wide text-neutral-900"
                >
                    Vòng quay may mắn
                </h2>
                <Link
                    :href="home().url"
                    class="shrink-0 text-xs font-semibold text-[#DA2778] underline decoration-pink-300 underline-offset-2"
                >
                    Đổi phòng
                </Link>
            </div> -->

            <!-- <p class="mb-1 text-center text-[10px] text-neutral-500">
                ID phòng {{ wheelRoom.id }} · Mã
                <span class="font-mono">{{ wheelRoom.slug }}</span>
            </p> -->

            <!-- <p class="mb-2 text-center text-[11px] font-medium text-[#9d174d]">
                Phòng: {{ wheelRoom.name }}
                <span v-if="phongDangMo"
                    class="ml-1.5 inline-block rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-semibold text-emerald-800">
                    Đang mở
                </span>
                <span v-else
                    class="ml-1.5 inline-block rounded-full bg-neutral-200 px-2 py-0.5 text-[10px] font-semibold text-neutral-700">
                    Tạm dừng
                </span>
            </p> -->

            <div v-if="!phongDangMo"
                class="mb-3 rounded-xl bg-amber-50 px-3 py-2.5 text-center text-xs font-medium text-amber-950 ring-1 ring-amber-200">
                Phòng này đang tạm dừng. Bạn không thể đặt cược hay quay cho đến
                khi quản trị bật lại phòng.
            </div>

            <div v-else-if="phongDangMo && activeRound === null"
                class="mb-3 rounded-xl bg-sky-50 px-3 py-2.5 text-center text-xs font-medium text-sky-950 ring-1 ring-sky-200">
                Chưa có vòng quay đang diễn ra. Vui lòng chờ ban tổ chức khởi tạo
                vòng mới.
            </div>

            <div v-else-if="phongDangMo && activeRound !== null && hasSpunThisRound"
                class="mb-3 rounded-xl bg-violet-50 px-3 py-2.5 text-center text-xs font-medium text-violet-950 ring-1 ring-violet-200">
                Bạn đã hoàn thành lượt quay ở vòng
                <strong>#{{ activeRound.round_number }}</strong>
                . Xem kết quả bên dưới; chờ vòng tiếp theo để quay lại.
            </div>

            <div v-else-if="phongDangMo && activeRound !== null"
                class="mb-3 rounded-xl bg-emerald-50 px-3 py-2.5 text-center text-xs font-medium text-emerald-950 ring-1 ring-emerald-200">
                Vòng quay
                <strong>#{{ activeRound.round_number }}</strong>
                đang diễn ra — mỗi người chỉ quay một lần trong vòng.
            </div>

            <p class="mb-3 px-2 text-center text-xs leading-relaxed text-neutral-600">
                Nhập
                <strong class="text-neutral-800">số điểm</strong>
                và chọn
                <strong class="text-neutral-800">lĩnh vực mong muốn</strong>
                , rồi bấm
                <strong class="text-neutral-800">Quay</strong>
                . Vòng quay dừng theo kết quả ban tổ chức đã cài đặt; không trừ
                điểm ví từ đây.
            </p>

            <div v-if="phongDangMo && activeRound !== null && !hasSpunThisRound"
                class="mb-4 w-full min-w-0 space-y-3 rounded-xl border border-pink-200 bg-pink-50/90 px-2.5 py-3 shadow-sm ring-1 ring-pink-100 sm:px-3">
                <div class="min-w-0 space-y-2">
                    <Label class="text-neutral-700">Lĩnh vực mong muốn</Label>
                    <div class="flex max-w-full flex-wrap gap-1.5 sm:gap-2" role="radiogroup"
                        aria-label="Lĩnh vực mong muốn">
                        <label v-for="opt in WISH_OPTIONS" :key="opt.value"
                            class="flex min-w-0 max-w-full cursor-pointer items-center gap-1.5 rounded-full border px-2 py-1.5 text-[11px] font-semibold transition-colors has-disabled:cursor-not-allowed has-disabled:opacity-60 sm:gap-2 sm:px-2.5 sm:text-xs"
                            :class="wishCategory === opt.value
                                ? 'border-[#DA2778] bg-pink-100 text-[#9d174d] ring-2 ring-pink-200/80'
                                : 'border-pink-200/90 bg-white text-neutral-700 hover:border-pink-300'
                                ">
                            <input v-model="wishCategory" type="radio"
                                class="size-3.5 shrink-0 border-neutral-300 accent-[#DA2778]" :value="opt.value"
                                :disabled="spinning" />
                            <span class="min-w-0 break-words">{{ opt.label }}</span>
                        </label>
                    </div>
                </div>
                <div class="space-y-1">
                    <Label for="bet-amount-field" class="text-neutral-700">Số điểm </Label>
                    <Input id="bet-amount-field" v-model.number="betAmount" type="number" min="1" :disabled="spinning"
                        inputmode="numeric" autocomplete="off" class="bg-white" placeholder="Ví dụ: 500000" />
                </div>
            </div>

            <p v-if="spinHttp.hasErrors" class="mb-2 text-center text-xs font-medium text-red-600">
                {{ spinErrorMessage() }}
            </p>

            <div
                ref="wheelFrameRef"
                class="game-wheel-frame relative mx-auto aspect-square w-full max-w-[min(100%,22rem)] shrink-0 overflow-hidden rounded-full"
            >
                <div class="absolute inset-0 box-border overflow-hidden rounded-full border-[7px] border-[#ff5722] shadow-[0_4px_14px_rgba(0,0,0,0.12)]"
                    :style="{
                        transformOrigin: '50% 50%',
                        transform: `rotate(${rotation}deg)`,
                        transition: spinning
                            ? 'transform 4s cubic-bezier(0.12, 0.8, 0.22, 1)'
                            : 'none',
                    }">
                    <canvas ref="wheelCanvasRef"
                        class="absolute inset-0 block size-full max-h-full max-w-full rounded-full"
                        aria-hidden="true" />
                </div>

                <button type="button"
                    class="absolute left-1/2 top-1/2 z-30 flex h-16 w-16 -translate-x-1/2 -translate-y-1/2 items-center justify-center rounded-full border-[4px] border-white bg-[#DA2778] text-center text-[11px] font-extrabold uppercase leading-tight text-white shadow-md transition enabled:hover:scale-105 enabled:active:scale-95 disabled:cursor-not-allowed disabled:opacity-60 sm:h-[4.25rem] sm:w-[4.25rem] sm:border-[5px] sm:text-[12px]"
                    :disabled="spinning || !coTheQuay" @click="spin">
                    <span class="pointer-events-none absolute left-1/2 top-0 z-10 -translate-x-1/2 -translate-y-[2px]"
                        aria-hidden="true">
                        <span
                            class="block h-0 w-0 border-x-[10px] border-b-[14px] border-x-transparent border-b-white drop-shadow-sm" />
                    </span>
                    {{ spinning ? 'Đang quay…' : 'Quay' }}
                </button>
            </div>

            <Teleport to="body">
                <Transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0"
                    enter-to-class="opacity-100" leave-active-class="transition duration-150 ease-in"
                    leave-from-class="opacity-100" leave-to-class="opacity-0">
                    <div v-if="winModalOpen && laGiaiThuong"
                        class="fixed inset-0 z-[200] flex items-center justify-center overflow-y-auto overflow-x-hidden  sm:p-4">
                        <div class="absolute inset-0 bg-black/45 backdrop-blur-[1px]" aria-hidden="true"
                            @click="dongKetQuaModal" />
                        <div role="dialog" aria-modal="true" aria-live="polite"
                            class="game-result-panel relative z-10 my-auto flex max-h-[min(90dvh,640px)] w-full min-w-0 max-w-[min(24rem,calc(100vw-1.5rem))] flex-col overflow-hidden rounded-2xl border border-[#DA2778]/30 bg-gradient-to-b from-pink-50 via-white to-rose-50 shadow-xl ring-1 ring-pink-100/80">
                            <button type="button"
                                class="absolute right-2 top-2 z-20 rounded-full p-2 text-neutral-500 transition hover:bg-black/5 hover:text-neutral-800"
                                aria-label="Đóng" @click="dongKetQuaModal">
                                <X class="size-5" stroke-width="2" />
                            </button>

                            <div class="pointer-events-none absolute inset-0 overflow-hidden rounded-2xl"
                                aria-hidden="true">
                                <div class="game-firework-burst" />
                                <div v-for="m in manhConfetti" :key="m.id"
                                    class="game-confetti absolute -top-3 rounded-sm opacity-90 shadow-sm" :style="{
                                        left: m.left,
                                        width: m.size,
                                        height: m.size,
                                        backgroundColor: m.color,
                                        animationDelay: m.delay,
                                        animationDuration: m.duration,
                                        '--game-rotate-end': `${m.rotateEnd}deg`,
                                    }" />
                            </div>

                            <div class="relative z-10 flex flex-col items-center px-4 pb-2 pt-8">
                                <div
                                    class="game-trophy flex size-16 items-center justify-center rounded-full bg-gradient-to-br from-[#DA2778] to-[#9d174d] text-white shadow-lg ring-4 ring-pink-200/80">
                                    <PartyPopper class="size-9" stroke-width="2" />
                                </div>

                                <h3
                                    class="game-shimmer mt-4 text-center text-xl font-extrabold tracking-tight text-[#9d174d] sm:text-2xl">
                                    Chúc mừng!
                                </h3>

                                <div class="mt-3 text-center text-sm leading-relaxed text-neutral-700">
                                    <span class="game-text-pop inline-block font-medium text-neutral-800">
                                        Vòng quay dừng tại:
                                    </span>
                                    <span
                                        class="mt-2 block rounded-xl px-3 py-2.5 text-base font-extrabold leading-snug shadow-inner ring-1 ring-pink-200/60"
                                        :style="{
                                            color: '#9d174d',
                                            backgroundColor: `${ketQuaMau}22`,
                                        }">
                                        {{ ketQuaTen }}
                                    </span>
                                    <span
                                        class="mt-3 flex items-center justify-center gap-1.5 text-xs font-medium text-neutral-600">
                                        <Sparkles class="size-3.5 shrink-0 text-amber-500" />
                                        Chúc bạn một năm mới thật nhiều may mắn!
                                    </span>
                                </div>
                            </div>

                            <div
                                class="relative z-10 flex flex-col gap-2 border-t border-pink-100 bg-white/70 px-4 py-4">
                                <Button variant="outline" class="w-full border-pink-200" as-child>
                                    <Link :href="accountPrizeWins().url" class="w-full">
                                        Lịch sử các lượt quay
                                    </Link>
                                </Button>
                                <Button variant="outline" class="w-full border-pink-200" as-child>
                                    <Link :href="accountHistory().url" class="w-full">
                                        Lịch sử điểm
                                    </Link>
                                </Button>
                            </div>
                        </div>
                    </div>
                </Transition>
            </Teleport>

            <Teleport to="body">
                <Transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0"
                    enter-to-class="opacity-100" leave-active-class="transition duration-150 ease-in"
                    leave-from-class="opacity-100" leave-to-class="opacity-0">
                    <div v-if="winModalOpen && laAnUi"
                        class="fixed inset-0 z-[200] flex items-center justify-center overflow-y-auto overflow-x-hidden p-3 sm:p-4">
                        <div class="absolute inset-0 bg-black/45 backdrop-blur-[1px]" aria-hidden="true"
                            @click="dongKetQuaModal" />
                        <div role="dialog" aria-modal="true" aria-live="polite"
                            class="game-result-panel relative z-10 my-auto flex max-h-[min(90dvh,640px)] w-full min-w-0 max-w-[min(24rem,calc(100vw-1.5rem))] flex-col overflow-hidden rounded-2xl border border-slate-300/80 bg-gradient-to-b from-slate-100 via-slate-50 to-slate-100 shadow-xl ring-1 ring-slate-200/90">
                            <button type="button"
                                class="absolute right-2 top-2 z-20 rounded-full p-2 text-neutral-500 transition hover:bg-black/5 hover:text-neutral-800"
                                aria-label="Đóng" @click="dongKetQuaModal">
                                <X class="size-5" stroke-width="2" />
                            </button>

                            <div class="pointer-events-none absolute inset-0 opacity-[0.12]" aria-hidden="true"
                                style="background-image: radial-gradient(circle at 20% 30%, #64748b 0, transparent 45%), radial-gradient(circle at 80% 70%, #94a3b8 0, transparent 40%);" />

                            <div class="relative z-10 flex flex-col items-center px-4 pb-2 pt-8">
                                <div
                                    class="flex size-16 items-center justify-center rounded-full bg-gradient-to-br from-slate-400 to-slate-600 text-white shadow-lg ring-4 ring-slate-200/90">
                                    <CloudOff class="size-9" stroke-width="2" />
                                </div>

                                <h3
                                    class="mt-4 text-center text-lg font-extrabold tracking-tight text-slate-700 sm:text-xl">
                                    Tiếc quá…
                                </h3>

                                <div class="mt-3 text-center text-sm leading-relaxed text-slate-600">
                                    <p class="text-[13px] font-medium text-slate-700">
                                        {{ ketQuaTen }}
                                    </p>
                                    <p class="mt-3 text-xs leading-relaxed text-slate-600">
                                        Lần sau chắc may mắn sẽ mỉm cười với bạn. Đừng
                                        nản lòng nhé!
                                    </p>
                                </div>
                            </div>

                            <div
                                class="relative z-10 flex flex-col gap-2 border-t border-slate-200 bg-white/80 px-4 py-4">
                                <Button variant="outline" class="w-full border-slate-300" as-child>
                                    <Link :href="accountPrizeWins().url" class="w-full">
                                        Lịch sử các lượt quay
                                    </Link>
                                </Button>
                                <Button variant="outline" class="w-full border-slate-300" as-child>
                                    <Link :href="accountHistory().url" class="w-full">
                                        Lịch sử điểm
                                    </Link>
                                </Button>
                            </div>
                        </div>
                    </div>
                </Transition>
            </Teleport>

            <div v-if="giaiThuong.length === 0"
                class="mt-4 rounded-xl bg-neutral-100 px-3 py-2 text-center text-xs text-neutral-700">
                Vòng quay chưa được cấu hình. Vui lòng quay lại sau.
            </div>
        </div>
    </ClubMobileShell>
</template>

<style scoped>
.game-firework-burst {
    position: absolute;
    left: 50%;
    top: 35%;
    width: 140%;
    height: 140%;
    transform: translate(-50%, -50%);
    background: radial-gradient(circle,
            rgba(255, 235, 59, 0.45) 0%,
            rgba(255, 152, 0, 0.2) 25%,
            transparent 55%),
        repeating-conic-gradient(from 0deg,
            rgba(218, 39, 120, 0.25) 0deg 8deg,
            transparent 8deg 16deg);
    animation: game-firework-pulse 1.2s ease-out infinite;
    pointer-events: none;
}

@keyframes game-firework-pulse {
    0% {
        opacity: 0.35;
        transform: translate(-50%, -50%) scale(0.85);
    }

    45% {
        opacity: 0.95;
        transform: translate(-50%, -50%) scale(1.05);
    }

    100% {
        opacity: 0.5;
        transform: translate(-50%, -50%) scale(1);
    }
}

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
    background: linear-gradient(110deg,
            #9d174d 0%,
            #da2778 25%,
            #f48fb1 50%,
            #da2778 75%,
            #9d174d 100%);
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
    animation: game-text-pop 0.55s cubic-bezier(0.34, 1.56, 0.64, 1) 0.35s both;
}
</style>
