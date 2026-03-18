# Stock Boutique

**Système de gestion d'inventaire et de ventes pour boutique de téléphones et appareils électroniques**

Un application Laravel Livewire complète pour gérer les stocks, ventes, achats, et créances avec traçabilité et audit complets.

---

## 🚀 Démarrage Rapide

### Prérequis
- PHP 8.2+
- PostgreSQL 12+
- Composer
- Node.js 16+

### Installation

```bash
# Clone repository
git clone <repo-url>
cd stock-boutique

# Install dependencies
composer install
npm install

# Setup environment
cp .env.example .env
php artisan key:generate

# Configure database in .env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=stock_boutique
DB_USERNAME=postgres
DB_PASSWORD=your_password

# Run migrations
php artisan migrate

# Build assets
npm run build

# Seed database (optional)
php artisan db:seed
```

### Lancer l'application

```bash
# Start dev server
php artisan serve

# In another terminal, watch assets
npm run dev

# Visit http://localhost:8000
```

---

## 📋 Architecture & Fonctionnalités

### Base de Données
- **19 tables** avec migrations versionées
- **Soft deletes** sur tous les modèles critiques
- **Audit trail complet** via `activity_logs`
- **Contraintes CHECK SQL** pour forcer les règles métier
- **Foreign keys** avec cascade/restrict approprié

### Modules Principaux

#### 1. Gestion des Produits
- 📦 Produits sérialisés (IMEI/N°série uniques)
- 🏷️ États FSM: AVAILABLE → SOLD/DEFECTIVE/RESERVED → RETURNED
- 🏠 Localisation: STORE, TRANSIT, CLIENT, RESELLER, REPAIR_SHOP
- 💾 Specs techniques par modèle
- 📊 Trois niveaux de prix: Achat, Client, Revendeur

#### 2. Gestion des Ventes
- 💰 Clients directs (anonymes) + Revendeurs (comptes)
- 💳 6 modes de paiement: Cash, Mobile Money, Virement, Chèque, Carte, Troc
- 📝 Paiements partiels avec suivi créances
- 🔄 Troc (trade-in): produit donné en échange  
- 📄 Génération automatique références (VTE-2024-00001)
- 🧾 Reçus/tickets

#### 3. Gestion des Achats
- 🏭 Achats fournisseurs avec paiements
- 📦 Import produits depuis achats
- 📍 Suivi réception (pending/received/cancelled)
- 🔢 Génération auto-ref (ACH-2024-00001)

#### 4. Stock & Mouvements
- 📊 Mouvements immuables (audit trail permanent)
- 📝 Types: STOCK_IN, STOCK_OUT, TRANSFER, LOSS, GAIN, ADJUSTMENT
- 🔍 Suivi complet: qui, quand, raison, avant/après
- 🛡️ Ajustements manuels admin-only

#### 5. Permissions Granulaires
- 👨‍💼 Rôles: ADMIN, VENDEUR
- 🔐 Permissions: 
  - `see_purchase_price` (ADMIN)
  - `see_profit` (ADMIN)
  - `cancel_sale` (ADMIN)
  - `adjust_stock` (ADMIN)
  - `manage_users` (ADMIN)

#### 6. Rapports & Analytics
- 📈 Dashboard en temps réel
- 📊 Rapports: CA, Marges, Profits, Créances
- 📉 Breakdown par marque/période
- 📥 Export: Excel multi-feuilles, PDF

#### 7. Audit & Traçabilité
- 📝 **ActivityLogs**: Toutes les CRUD avec old/new values
- 👤 **CreatedBy/UpdatedBy**: Tracé sur tous les modèles
- 💰 **PriceHistory**: Historique de prix
- 🌐 **IP & User-Agent** loggés

---

## 🛡️ Sécurité & Robustesse (Phase 1-3)

### Phase 1: Exception Handling & Middleware
✅ **Exception Handler** personnalisé avec logging contextuel  
✅ **Middleware CSRF, Auth, Permissions**  
✅ **Pages d'erreur** custom (404, 403, 500, generic)  
✅ **Form Request classes** centralisées avec validations métier  
✅ **GitHub Actions CI/CD** pour tests automatiques  

### Phase 2: Validations Métier & Contraintes DB
✅ **Validations strictes** au niveau Model:
   - Produits: IMEI unique, prix valides, état cohérent
   - Ventes: `paid_amount ≤ total_amount`, articles exist
   - Achats: fournisseur active, quantités > 0
   - Paiements: montant positif, total ne dépasse pas vente

✅ **Contraintes SQL CHECK** pour forcer les règles en DB:
   - Prices >= 0
   - Quantities > 0
   - `client_price >= purchase_price`
   - States valides
   - StockMovements immuables

✅ **Tests Unit & Feature** complets:
   - ProductBusinessRulesTest, SaleBusinessRulesTest, etc.
   - Coverage des cas nominaux ET edge cases

### Phase 3: Permissions Enforcement & Audit
✅ **Trait EnsurePermission** réutilisable  
✅ **Vérifications strictes** dans les Livewire components  
✅ **Tests des permissions** (vendeur ≠ admin)  
✅ **Audit logging** pour opérations sensibles  
✅ **Notifications safety** (database-only, pas SMS/email non testés)  

---

## 🧪 Tests

### Lancer les tests

```bash
# Run all tests
php artisan test

# Run specific test class
php artisan test tests/Feature/SaleValidationTest.php

# Run with coverage
php artisan test --coverage

# Watch mode
php artisan test --watch
```

### Couverture de tests

- **Unit Tests**: Models, Permissions, Business Rules
- **Feature Tests**: Validations, Exception Handling, Audit Trail, Notifications
- **Integration Tests**: Permission Enforcement

### Structure des tests

```
tests/
  Feature/
    SaleValidationTest.php        # Validations métier ventes
    ProductValidationTest.php     # Validations métier produits
    PermissionEnforcementTest.php # Tests permissions
    AuditTrailTest.php           # Tests audit logging
    NotificationTest.php         # Tests notifications
    ErrorHandlingTest.php        # Tests gestion erreurs
  Unit/
    UserPermissionsTest.php      # Tests permissions par rôle
    ProductBusinessRulesTest.php # Tests règles produits
    SaleBusinessRulesTest.php    # Tests règles ventes
    PaymentBusinessRulesTest.php # Tests paiements
```

---

## 📋 Checklist Production

- [ ] Database migrations exécutées et validées
- [ ] Tests tous passants (`php artisan test`)
- [ ] Erreurs de linting résolues (`php artisan lint`)
- [ ] Clé d'application générée (`php artisan key:generate`)
- [ ] Cache vidé (`php artisan cache:clear`)
- [ ] Permissions utilisateurs définies
- [ ] Mode debug à `false` en production
- [ ] Logs configurés (fichier/Sentry)
- [ ] Backups configu

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
