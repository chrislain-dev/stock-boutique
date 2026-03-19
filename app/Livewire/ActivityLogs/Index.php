<?php

namespace App\Livewire\ActivityLogs;

use App\Models\ActivityLog;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search      = '';
    public string $actionFilter = '';
    public string $userFilter  = '';
    public string $dateFrom    = '';
    public string $dateTo      = '';

    public function mount(): void
    {
        abort_unless(auth()->user()->isAdmin(), 403);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $logs = ActivityLog::query()
            ->with('user')
            ->when($this->search,       fn($q) => $q->where('description', 'ilike', "%{$this->search}%"))
            ->when($this->actionFilter, fn($q) => $q->where('action', $this->actionFilter))
            ->when($this->userFilter,   fn($q) => $q->where('user_id', $this->userFilter))
            ->when($this->dateFrom,     fn($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo,       fn($q) => $q->whereDate('created_at', '<=', $this->dateTo))
            ->orderByDesc('created_at')
            ->paginate(25);

        // Compteurs globaux (indépendants des filtres)
        $totalCounts = ActivityLog::selectRaw('action, count(*) as total')
            ->groupBy('action')
            ->pluck('total', 'action')
            ->toArray();

        $actions = [
            ['id' => '',        'name' => 'Toutes les actions'],
            ['id' => 'create',  'name' => 'Création'],
            ['id' => 'update',  'name' => 'Modification'],
            ['id' => 'delete',  'name' => 'Suppression'],
            ['id' => 'login',   'name' => 'Connexion'],
            ['id' => 'logout',  'name' => 'Déconnexion'],
            ['id' => 'export',  'name' => 'Export'],
        ];

        $users = User::orderBy('name')
            ->get()
            ->map(fn($u) => ['id' => $u->id, 'name' => $u->name])
            ->prepend(['id' => '', 'name' => 'Tous les utilisateurs'])
            ->toArray();

        return view('livewire.activity-logs.index', compact('logs', 'actions', 'users', 'totalCounts'))
            ->layout('layouts.app', ['title' => "Journaux d'activité"]);
    }
}
