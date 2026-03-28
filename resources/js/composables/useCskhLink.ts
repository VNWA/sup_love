import { usePage } from '@inertiajs/vue3';
import { computed, type ComputedRef } from 'vue';

const fallbackCskh = 'tel:1900123456';

/**
 * URL / tel từ shared prop `cskhLink` (.env CSKH_LINK), mặc định số CSKH.
 */
export function useCskhHref(): ComputedRef<string> {
    const page = usePage();

    return computed(() => {
        const s = String(page.props.cskhLink ?? '').trim();

        return s !== '' ? s : fallbackCskh;
    });
}

/**
 * Thuộc tính cho thẻ <a>: mở tab mới với http(s).
 */
export function useCskhAnchorAttrs(): ComputedRef<
    Record<string, string | undefined>
> {
    const href = useCskhHref();

    return computed(() => {
        const h = href.value;

        if (/^https?:\/\//i.test(h)) {
            return {
                href: h,
                target: '_blank',
                rel: 'noopener noreferrer',
            };
        }

        return { href: h };
    });
}
