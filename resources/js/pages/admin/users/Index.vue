<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { dashboard } from '@/routes/admin';
import {
    create as createUser,
    destroy,
    edit as editUser,
    index as usersIndex,
    points as userPoints,
} from '@/routes/admin/users';

type UserRow = {
    id: number;
    username: string | null;
    name: string | null;
    email: string | null;
    point: number;
};

type Paginator<T> = {
    data: T[];
    links: { url: string | null; label: string; active: boolean }[];
    meta?: { current_page: number; last_page: number };
};

const props = defineProps<{
    users: Paginator<UserRow>;
    filters: { search: string };
}>();

function goSearch(ev: Event): void {
    const fd = new FormData(ev.target as HTMLFormElement);
    const q = String(fd.get('search') ?? '').trim();

    router.get(usersIndex().url, { search: q || undefined }, { preserveState: true });
}

function deleteUser(u: UserRow): void {
    if (!confirm(`Xóa người dùng @${u.username ?? u.id}?`)) {
        return;
    }

    router.delete(destroy.url(u.id));
}
</script>

<template>
    <Head title="Quản lý người dùng" />

    <div>
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-xl font-bold">Người dùng</h1>
                <Link
                    :href="dashboard().url"
                    class="text-xs text-neutral-500 hover:text-[#DA2778]"
                >
                    ← Dashboard
                </Link>
            </div>
            <Link
                :href="createUser().url"
                class="rounded-lg bg-[#DA2778] px-3 py-2 text-sm font-semibold text-white hover:bg-[#b91560]"
            >
                + Tạo user
            </Link>
        </div>

        <form
            class="mb-4 flex flex-wrap gap-2"
            @submit.prevent="goSearch"
        >
            <input
                name="search"
                type="search"
                :value="filters.search"
                placeholder="Tìm username, tên, email…"
                class="min-w-[200px] flex-1 rounded-md border border-neutral-300 px-3 py-2 text-sm"
            />
            <button
                type="submit"
                class="rounded-md bg-neutral-800 px-4 py-2 text-sm font-medium text-white"
            >
                Tìm
            </button>
        </form>

        <div class="overflow-x-auto rounded-lg border border-neutral-200 bg-white">
            <table class="w-full min-w-[480px] text-left text-sm">
                <thead class="border-b bg-neutral-50 text-xs uppercase text-neutral-500">
                    <tr>
                        <th class="px-3 py-2">ID</th>
                        <th class="px-3 py-2">Username</th>
                        <th class="px-3 py-2">Tên</th>
                        <th class="px-3 py-2">Điểm</th>
                        <th class="px-3 py-2 text-right">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="u in users.data"
                        :key="u.id"
                        class="border-b border-neutral-100"
                    >
                        <td class="px-3 py-2">{{ u.id }}</td>
                        <td class="px-3 py-2 font-medium">{{ u.username }}</td>
                        <td class="px-3 py-2 text-neutral-600">{{ u.name }}</td>
                        <td class="px-3 py-2 font-semibold text-[#DA2778]">
                            {{ u.point }}
                        </td>
                        <td class="px-3 py-2 text-right whitespace-nowrap">
                            <Link
                                :href="editUser.url(u.id)"
                                class="text-[#DA2778] hover:underline"
                            >
                                Sửa
                            </Link>
                            <Link
                                :href="userPoints.url(u.id)"
                                class="ml-3 text-neutral-700 hover:text-[#DA2778] hover:underline"
                            >
                                Nạp điểm
                            </Link>
                            <button
                                type="button"
                                class="ml-3 text-red-600 hover:underline"
                                @click="deleteUser(u)"
                            >
                                Xóa
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div
            v-if="users.links?.length > 3"
            class="mt-4 flex flex-wrap justify-center gap-2"
        >
            <Link
                v-for="(l, i) in users.links"
                :key="i"
                :href="l.url || '#'"
                class="rounded px-2 py-1 text-sm"
                :class="
                    l.active
                        ? 'bg-[#DA2778] text-white'
                        : 'bg-neutral-100 text-neutral-700 hover:bg-pink-50'
                "
                :preserve-scroll="true"
            >
                <span v-html="l.label" />
            </Link>
        </div>
    </div>
</template>
