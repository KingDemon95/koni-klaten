<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $cabor = null;
        $routeName = '';

        if ($user->hasRole('admin_koni') || $user->hasRole('ketua_koni')) {
            $layout = 'layouts.admin';
            $routeName = 'admin.audit.index';
            $scope = AuditLog::query();
        } elseif ($user->hasRole('sekretaris_koni')) {
            $layout = 'layouts.sekretaris';
            $routeName = 'sekretaris.audit.index';
            $scope = AuditLog::query();
        } elseif ($user->hasRole('admin_cabor')) {
            $cabor = $user->cabangOlahraga;
            abort_unless($cabor && $cabor->status === 'aktif', 403);

            $layout = 'layouts.cabor';
            $routeName = 'cabor.audit.index';
            $scope = AuditLog::query()->where('cabang_olahraga_id', $cabor->id);
        } elseif ($user->hasRole('staff_koni')) {
            $layout = 'layouts.staff';
            $routeName = 'staff.audit.index';
            $scope = AuditLog::query()->where('actor_id', $user->id);
        } else {
            abort(403);
        }

        $searchInput = $request->query('q', '');
        $eventInput = $request->query('event', '');
        $fromInput = $request->query('from', '');
        $toInput = $request->query('to', '');

        abort_unless(
            is_string($searchInput)
                && is_string($eventInput)
                && is_string($fromInput)
                && is_string($toInput),
            422
        );

        $search = mb_substr(trim($searchInput), 0, 100);
        $event = trim($eventInput);
        $from = trim($fromInput);
        $to = trim($toInput);
        $eventLabels = AuditLog::eventLabels();

        abort_unless($event === '' || array_key_exists($event, $eventLabels), 422);
        abort_unless($this->validDate($from) && $this->validDate($to), 422);
        abort_unless($from === '' || $to === '' || $from <= $to, 422);

        $total = (clone $scope)->count();
        $today = (clone $scope)->whereDate('created_at', today())->count();

        $query = clone $scope;

        if ($search !== '') {
            $query->where(function (Builder $nested) use ($search): void {
                $nested->where('actor_name', 'like', "%{$search}%")
                    ->orWhere('actor_role', 'like', "%{$search}%")
                    ->orWhere('subject_label', 'like', "%{$search}%")
                    ->orWhere('event', 'like', "%{$search}%")
                    ->orWhere('route_name', 'like', "%{$search}%");
            });
        }

        if ($event !== '') {
            $query->where('event', $event);
        }

        if ($from !== '') {
            $query->whereDate('created_at', '>=', $from);
        }

        if ($to !== '') {
            $query->whereDate('created_at', '<=', $to);
        }

        $items = $query
            ->latest('created_at')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('audit.index', compact(
            'items',
            'layout',
            'routeName',
            'cabor',
            'search',
            'event',
            'from',
            'to',
            'eventLabels',
            'total',
            'today',
        ));
    }

    private function validDate(string $value): bool
    {
        if ($value === '') {
            return true;
        }

        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $value);

        return $date !== false && $date->format('Y-m-d') === $value;
    }
}
