<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // ─── Général ──────────────────────────────────────
            [
                'key'         => 'boutique.nom',
                'value'       => config('boutique.nom'),
                'type'        => 'string',
                'group'       => 'general',
                'label'       => 'Nom de la boutique',
                'is_public'   => true,
            ],
            [
                'key'         => 'boutique.slogan',
                'value'       => config('boutique.slogan'),
                'type'        => 'string',
                'group'       => 'general',
                'label'       => 'Slogan',
                'is_public'   => true,
            ],
            [
                'key'         => 'boutique.telephone',
                'value'       => config('boutique.telephone'),
                'type'        => 'string',
                'group'       => 'general',
                'label'       => 'Téléphone',
                'is_public'   => true,
            ],
            [
                'key'         => 'boutique.email',
                'value'       => config('boutique.email'),
                'type'        => 'string',
                'group'       => 'general',
                'label'       => 'Email',
                'is_public'   => true,
            ],
            [
                'key'         => 'boutique.adresse',
                'value'       => config('boutique.adresse'),
                'type'        => 'string',
                'group'       => 'general',
                'label'       => 'Adresse',
                'is_public'   => true,
            ],
            [
                'key'         => 'boutique.devise',
                'value'       => config('boutique.devise'),
                'type'        => 'string',
                'group'       => 'general',
                'label'       => 'Devise',
                'is_public'   => true,
            ],

            // ─── Thème ────────────────────────────────────────
            [
                'key'         => 'theme.primary',
                'value'       => config('boutique.theme.primary'),
                'type'        => 'color',
                'group'       => 'theme',
                'label'       => 'Couleur principale',
                'is_public'   => true,
            ],
            [
                'key'         => 'theme.sidebar_bg',
                'value'       => config('boutique.theme.sidebar_bg'),
                'type'        => 'color',
                'group'       => 'theme',
                'label'       => 'Couleur sidebar',
                'is_public'   => true,
            ],

            // ─── Règles métier ────────────────────────────────
            [
                'key'         => 'vente.credit_client',
                'value'       => config('boutique.vente.permettre_credit_client') ? '1' : '0',
                'type'        => 'boolean',
                'group'       => 'vente',
                'label'       => 'Autoriser le crédit client',
                'description' => 'Si activé, un client peut partir sans tout payer',
                'is_public'   => false,
            ],
            [
                'key'         => 'vente.acompte_minimum_revendeur',
                'value'       => config('boutique.vente.acompte_minimum_revendeur'),
                'type'        => 'integer',
                'group'       => 'vente',
                'label'       => 'Acompte minimum revendeur (%)',
                'description' => '0 = pas de minimum requis',
                'is_public'   => false,
            ],
            [
                'key'         => 'vente.delai_paiement_max_jours',
                'value'       => config('boutique.vente.delai_paiement_max_jours'),
                'type'        => 'integer',
                'group'       => 'vente',
                'label'       => 'Délai maximum de paiement (jours)',
                'is_public'   => false,
            ],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
