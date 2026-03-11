<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Informations de la boutique
    |--------------------------------------------------------------------------
    */
    'nom'        => env('BOUTIQUE_NOM', 'Ma Boutique'),
    'slogan'     => env('BOUTIQUE_SLOGAN', ''),
    'telephone'  => env('BOUTIQUE_TELEPHONE', ''),
    'email'      => env('BOUTIQUE_EMAIL', ''),
    'adresse'    => env('BOUTIQUE_ADRESSE', ''),
    'logo'       => env('BOUTIQUE_LOGO', null), // chemin dans storage/app/public
    'devise'     => env('BOUTIQUE_DEVISE', 'FCFA'),
    'devise_symbole' => env('BOUTIQUE_DEVISE_SYMBOLE', 'F'),

    /*
    |--------------------------------------------------------------------------
    | Thème / Couleurs
    |--------------------------------------------------------------------------
    | Ces valeurs sont injectées dans tailwind via CSS variables
    */
    'theme' => [
        'primary'        => env('THEME_PRIMARY', '#6366f1'),        // indigo
        'primary_dark'   => env('THEME_PRIMARY_DARK', '#4f46e5'),
        'secondary'      => env('THEME_SECONDARY', '#0ea5e9'),      // sky
        'accent'         => env('THEME_ACCENT', '#f59e0b'),         // amber
        'sidebar_bg'     => env('THEME_SIDEBAR_BG', '#1e1b4b'),
        'sidebar_text'   => env('THEME_SIDEBAR_TEXT', '#e0e7ff'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Règles métier
    |--------------------------------------------------------------------------
    */
    'vente' => [
        'permettre_credit_client'    => env('VENTE_CREDIT_CLIENT', false),
        'acompte_minimum_revendeur'  => env('ACOMPTE_MIN_REVENDEUR', 0), // en %
        'delai_paiement_max_jours'   => env('DELAI_PAIEMENT_MAX', 30),
    ],

    /*
    |--------------------------------------------------------------------------
    | Préfixes des références
    |--------------------------------------------------------------------------
    */
    'prefixes' => [
        'vente'    => env('PREFIX_VENTE', 'VTE'),
        'achat'    => env('PREFIX_ACHAT', 'ACH'),
        'retour'   => env('PREFIX_RETOUR', 'RET'),
    ],

];
