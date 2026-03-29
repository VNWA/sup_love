import dayjs from 'dayjs';
import 'dayjs/locale/vi';
import relativeTime from 'dayjs/plugin/relativeTime';
import timezone from 'dayjs/plugin/timezone';
import utc from 'dayjs/plugin/utc';

dayjs.extend(utc);
dayjs.extend(timezone);
dayjs.extend(relativeTime);
dayjs.locale('vi');

const VN_TZ = 'Asia/Ho_Chi_Minh';

/**
 * Chuỗi ISO từ Laravel (UTC) → múi giờ VN, hiển thị dạng "X phút trước".
 */
export function vnFromNow(iso: string | null | undefined): string {
    if (iso === null || iso === undefined || iso === '') {
        return '';
    }

    return dayjs.utc(iso).tz(VN_TZ).fromNow();
}
