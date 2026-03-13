<style>
    :root {
        --boutique-primary:       {{ \App\Models\Setting::get('theme.primary',     config('boutique.theme.primary')) }};
        --boutique-primary-dark:  {{ \App\Models\Setting::get('theme.primary_dark',config('boutique.theme.primary_dark')) }};
        --boutique-secondary:     {{ \App\Models\Setting::get('theme.secondary',   config('boutique.theme.secondary')) }};
        --boutique-accent:        {{ \App\Models\Setting::get('theme.accent',      config('boutique.theme.accent')) }};
        --boutique-sidebar-bg:    {{ \App\Models\Setting::get('theme.sidebar_bg',  config('boutique.theme.sidebar_bg')) }};
        --boutique-sidebar-text:  {{ \App\Models\Setting::get('theme.sidebar_text',config('boutique.theme.sidebar_text')) }};
    }
</style>
