<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import ClubMobileShell from '@/components/club/ClubMobileShell.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { game } from '@/routes';

withDefaults(
    defineProps<{
        canRegister: boolean;
    }>(),
    {
        canRegister: true,
    },
);

const page = usePage();

const flashError = computed(
    () => (page.props.flash as { error?: string | null } | undefined)?.error,
);

const roomCode = ref('1');

function enterWheelRoom(): void {
    const v = roomCode.value.trim();
    if (v === '') {
        return;
    }
    router.visit(game.url({ query: { room: v } }));
}
</script>

<template>

    <Head title="Trang chủ" />

    <ClubMobileShell nav-active="home">
        <!-- <div class="mb-4 rounded-2xl border border-pink-100 bg-pink-50/80 px-4 py-4 shadow-sm ring-1 ring-pink-100/60">
           <h2 class="text-center text-sm font-bold text-[#9d174d]">
            Vòng quay may mắn
        </h2>
        <p class="mt-2 text-center text-xs leading-relaxed text-neutral-600">
            Nhập
            <strong>ID phòng</strong>
            (số) hoặc
            <strong>mã phòng</strong>
            (slug) do quản trị cấp — ví dụ
            <span class="font-mono text-[11px] text-[#DA2778]">1</span>
            hoặc
            <span class="font-mono text-[11px] text-[#DA2778]">default</span>
            — rồi bấm vào phòng để chơi.
        </p>

        <div v-if="flashError"
            class="mt-3 rounded-lg bg-red-50 px-3 py-2 text-center text-xs font-medium text-red-800 ring-1 ring-red-200"
            role="alert">
            {{ flashError }}
        </div>

        <div class="mt-4 space-y-2">
            <Label for="wheel-room-code" class="text-neutral-700">
                ID phòng hoặc mã phòng
            </Label>
            <Input id="wheel-room-code" v-model="roomCode" type="text" inputmode="text" autocomplete="off"
                placeholder="Ví dụ: 1 hoặc default" class="bg-white" @keydown.enter.prevent="enterWheelRoom" />
        </div>

            <Button type="button"
                class="mt-4 w-full rounded-2xl bg-[#DA2778] py-6 text-base font-semibold text-white hover:bg-[#b91560]"
                @click="enterWheelRoom">
                Vào phòng quay
            </Button>
        </div> -->
        <Button type="button"
            class="my-4 w-full rounded-2xl bg-[#DA2778] py-6 text-base font-semibold text-white hover:bg-[#b91560]"
            @click="enterWheelRoom">
            Vào phòng quay
        </Button>
        <article
            class="relative mb-4 overflow-hidden rounded-2xl bg-gradient-to-br from-pink-200 via-rose-100 to-amber-100 shadow-md ring-1 ring-pink-200/80">
            <img src="/banner1.jpeg" alt="Banner chương trình" class="h-full w-full rounded-2xl object-cover" />
        </article>
        <article
            class="relative mb-4 overflow-hidden rounded-2xl bg-gradient-to-br from-pink-200 via-rose-100 to-amber-100 shadow-md ring-1 ring-pink-200/80">
            <img src="/banner2.jpeg" alt="Banner chương trình" class="h-full w-full rounded-2xl object-cover" />
        </article>


    </ClubMobileShell>
</template>
