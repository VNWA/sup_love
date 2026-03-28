<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CreditPointsRequest;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\User;
use App\Services\PointCreditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function __construct(
        private PointCreditService $pointCreditService
    ) {}

    public function index(Request $request): Response
    {
        $search = $request->string('search')->trim()->value();

        $users = User::query()
            ->when($search !== '', function ($q) use ($search): void {
                $q->where(function ($q2) use ($search): void {
                    $q2->where('username', 'like', '%'.$search.'%')
                        ->orWhere('name', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%');
                });
            })
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('admin/users/Index', [
            'users' => $users,
            'filters' => [
                'search' => $search,
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/users/Create');
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $initialPoints = (int) ($data['point'] ?? 0);
        unset($data['point']);

        $user = User::query()->create([
            ...$data,
            'password' => Hash::make($data['password']),
            'point' => 0,
        ]);

        if ($initialPoints > 0) {
            $this->pointCreditService->creditFromAdmin(
                $request->user(),
                $user,
                $initialPoints,
                'Điểm khởi tạo khi tạo tài khoản'
            );
        }

        return redirect()->route('admin.users.edit', $user)
            ->with('success', 'Đã tạo người dùng.');
    }

    public function edit(User $user): Response
    {
        return Inertia::render('admin/users/Edit', [
            'editUser' => $user,
        ]);
    }

    public function points(User $user): Response
    {
        $user->load([
            'pointTransactions' => fn ($q) => $q->latest()->limit(50),
            'pointTransactions.actor:id,name,username',
        ]);

        return Inertia::render('admin/users/Credit', [
            'creditUser' => $user,
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();

        if (! empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }
        unset($data['password']);

        $user->fill($data);
        $user->save();

        return redirect()->back()->with('success', 'Đã cập nhật người dùng.');
    }

    public function credit(CreditPointsRequest $request, User $user): RedirectResponse
    {
        $this->pointCreditService->creditFromAdmin(
            $request->user(),
            $user,
            (int) $request->validated('amount'),
            $request->validated('note')
        );

        return redirect()
            ->route('admin.users.points', $user)
            ->with('success', 'Đã nạp điểm cho người dùng.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->is($request->user())) {
            abort(403, 'Không thể xóa chính mình.');
        }

        if ($user->hasRole('admin') && User::role('admin')->count() <= 1) {
            abort(403, 'Không thể xóa admin cuối cùng.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'Đã xóa người dùng.');
    }
}
