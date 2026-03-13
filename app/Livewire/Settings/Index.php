<?php

namespace App\Livewire\Settings;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;
use Mary\Traits\Toast;

class Index extends Component
{
    use Toast, WithFileUploads;

    public string $activeTab = 'general';

    // ─── Boutique ─────────────────────────────────────────────
    public string $nom       = '';
    public string $slogan    = '';
    public string $telephone = '';
    public string $email     = '';
    public string $adresse   = '';
    public string $message_recu = '';
    public $logo = null; // fichier uploadé
    public string $logoPreview = '';

    // ─── Thème ────────────────────────────────────────────────
    public string $primary      = '';
    public string $primary_dark = '';
    public string $secondary    = '';
    public string $accent       = '';
    public string $sidebar_bg   = '';
    public string $sidebar_text = '';

    // ─── Vente ────────────────────────────────────────────────
    public bool   $permettre_credit_client    = false;
    public int    $acompte_minimum_revendeur  = 0;
    public int    $delai_paiement_max_jours   = 30;

    public function mount(): void
    {
        abort_unless(auth()->user()->isAdmin(), 403);
        $this->loadSettings();
    }

    private function loadSettings(): void
    {
        $this->nom          = Setting::get('boutique.nom',      config('boutique.nom'));
        $this->slogan       = Setting::get('boutique.slogan',   config('boutique.slogan')) ?? '';
        $this->telephone    = Setting::get('boutique.telephone', config('boutique.telephone')) ?? '';
        $this->email        = Setting::get('boutique.email',    config('boutique.email')) ?? '';
        $this->adresse      = Setting::get('boutique.adresse',  config('boutique.adresse')) ?? '';
        $this->message_recu = Setting::get('boutique.message_recu', config('boutique.message_recu')) ?? '';
        $this->logoPreview  = Setting::get('boutique.logo',     config('boutique.logo')) ?? '';

        $this->primary      = Setting::get('theme.primary',     config('boutique.theme.primary'));
        $this->primary_dark = Setting::get('theme.primary_dark', config('boutique.theme.primary_dark'));
        $this->secondary    = Setting::get('theme.secondary',   config('boutique.theme.secondary'));
        $this->accent       = Setting::get('theme.accent',      config('boutique.theme.accent'));
        $this->sidebar_bg   = Setting::get('theme.sidebar_bg',  config('boutique.theme.sidebar_bg'));
        $this->sidebar_text = Setting::get('theme.sidebar_text', config('boutique.theme.sidebar_text'));

        $this->permettre_credit_client   = (bool) Setting::get('vente.permettre_credit_client',  config('boutique.vente.permettre_credit_client'));
        $this->acompte_minimum_revendeur = (int)  Setting::get('vente.acompte_minimum_revendeur', config('boutique.vente.acompte_minimum_revendeur'));
        $this->delai_paiement_max_jours  = (int)  Setting::get('vente.delai_paiement_max_jours',  config('boutique.vente.delai_paiement_max_jours'));
    }

    public function saveGeneral(): void
    {
        $this->validate([
            'nom'      => 'required|string|max:100',
            'slogan'   => 'nullable|string|max:200',
            'telephone' => 'nullable|string|max:30',
            'email'    => 'nullable|email|max:100',
            'adresse'  => 'nullable|string|max:255',
            'message_recu' => 'nullable|string|max:255',
            'logo'     => 'nullable|image|max:2048',
        ]);

        Setting::set('boutique.nom',      $this->nom);
        Setting::set('boutique.slogan',   $this->slogan);
        Setting::set('boutique.telephone', $this->telephone);
        Setting::set('boutique.email',    $this->email);
        Setting::set('boutique.adresse',  $this->adresse);
        Setting::set('boutique.message_recu', $this->message_recu);

        if ($this->logo) {
            $path = $this->logo->store('logos', 'public');
            Setting::set('boutique.logo', $path);
            $this->logoPreview = $path;
            $this->logo = null;
        }

        $this->success('Informations sauvegardées.');
    }

    public function saveTheme(): void
    {
        $this->validate([
            'primary'      => 'required|string|max:20',
            'primary_dark' => 'required|string|max:20',
            'secondary'    => 'required|string|max:20',
            'accent'       => 'required|string|max:20',
            'sidebar_bg'   => 'required|string|max:20',
            'sidebar_text' => 'required|string|max:20',
        ]);

        Setting::set('theme.primary',      $this->primary);
        Setting::set('theme.primary_dark', $this->primary_dark);
        Setting::set('theme.secondary',    $this->secondary);
        Setting::set('theme.accent',       $this->accent);
        Setting::set('theme.sidebar_bg',   $this->sidebar_bg);
        Setting::set('theme.sidebar_text', $this->sidebar_text);

        $this->success('Thème sauvegardé. Rechargez la page pour voir les changements.');

        $this->redirect(route('settings.index'), navigate: false);
    }

    public function saveVente(): void
    {
        $this->validate([
            'acompte_minimum_revendeur' => 'integer|min:0|max:100',
            'delai_paiement_max_jours'  => 'integer|min:1|max:365',
        ]);

        Setting::set('vente.permettre_credit_client',   $this->permettre_credit_client ? '1' : '0');
        Setting::set('vente.acompte_minimum_revendeur', $this->acompte_minimum_revendeur);
        Setting::set('vente.delai_paiement_max_jours',  $this->delai_paiement_max_jours);

        $this->success('Règles métier sauvegardées.');
    }

    public function render()
    {
        $tabs = [
            ['id' => 'general', 'name' => 'Boutique',    'icon' => 'o-building-storefront'],
            ['id' => 'theme',   'name' => 'Thème',       'icon' => 'o-swatch'],
            ['id' => 'vente',   'name' => 'Règles vente', 'icon' => 'o-shopping-bag'],
        ];

        return view('livewire.settings.index', compact('tabs'))
            ->layout('layouts.app', ['title' => 'Paramètres']);
    }
}
