# TransportBus L2 — version Laravel

Réécriture en Laravel 12 du projet de réservation de bus **TransportBus L2** (initialement en PHP natif). Elle reproduit le comportement de L2 avec des vues Blade.

## Fonctionnalités

- **Client** : recherche de trajets, réservation, paiement simulé, e-billet, mes réservations, annulation.
- **Famille** : 2 à 6 voyageurs, un passager par place, justificatifs (CNI, carte scolaire, récépissé, NIP) stockés hors racine web, suivi du statut des pièces.
- **Admin** : gestion des bus et des trajets, consultation des réservations, validation ou refus des pièces.
- Deux rôles seulement : client et admin.

## Stack

Laravel 12 (PHP 8.2), MySQL, Blade, CSS compilé avec Vite.

## Installation

Prérequis : PHP 8.2 avec `pdo_mysql`, MySQL, Node.js.

```powershell
copy .env.example .env
& "D:\wamp64\bin\php\php8.2.29\php.exe" artisan key:generate
& "F:\mysql-8.4.4-winx64\bin\mysql.exe" --protocol=tcp --host=127.0.0.1 --port=3306 --user=root -e "CREATE DATABASE IF NOT EXISTS transportbus_l2_laravel CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
& "D:\wamp64\bin\php\php8.2.29\php.exe" artisan migrate:fresh --seed
npm install && npm run build
& "D:\wamp64\bin\php\php8.2.29\php.exe" artisan serve --port=8002
```

Ouvrir ensuite `http://127.0.0.1:8002`.

### Comptes de démonstration

| Rôle   | E-mail              | Mot de passe |
|--------|---------------------|--------------|
| Admin  | `admin@busl2.ga`    | `admin123`   |
| Client | `client@busl2.ga`   | `client123`  |

## Documents

Les justificatifs sont enregistrés dans `F:\reservation-bus-l2-laravel-data\documents` (variable `DOCUMENTS_PATH` du `.env`), hors racine web. Ce dossier doit exister et être accessible en écriture par PHP.

## Différences avec L2

Cette version reproduit le comportement de L2, avec les simplifications suivantes :

- QR code de l'e-billet simulé (aucun scan réel).
- Paiement simulé en une seule étape.
- Billet et paiement accessibles publiquement par identifiant de réservation.
- Aucun rôle contrôleur : seuls client et admin existent.
