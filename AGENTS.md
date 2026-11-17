# TransportBus L2 — contexte projet (version Laravel)

Réécriture en **Laravel 12** (PHP 8.2) du projet TransportBus L2 en PHP natif (`F:\reservation-bus-l2`, lecture seule). Base **MySQL**, vues **Blade**, CSS compilé avec **Vite**. Ne pas modifier `F:\reservation-bus-l2` ni `F:\reservation-bus\app`.

## Environnement

- Racine du projet : `F:\reservation-bus-l2-laravel`.
- Base : MySQL 8.4 noinstall, `F:\mysql-8.4.4-winx64` (démarrer avec `start-mysql.bat`), `root` sans mot de passe, port 3306.
- Base de données : `transportbus_l2_laravel` (`DB_DATABASE` dans `.env`).
- PHP CLI : `D:\wamp64\bin\php\php8.2.29\php.exe` (hors PATH).
- Serveur local : `php artisan serve --port=8002` → `http://127.0.0.1:8002`.
- Comptes démo : `admin@busl2.ga` / `admin123`, `client@busl2.ga` / `client123`.
- Justificatifs : stockés hors racine web dans `F:\reservation-bus-l2-laravel-data\documents` (`DOCUMENTS_PATH` dans `.env`), servis par Laravel avec contrôle d'accès admin/propriétaire.

## Raccourcis utiles

Créer la base :

```powershell
& "F:\mysql-8.4.4-winx64\bin\mysql.exe" --protocol=tcp --host=127.0.0.1 --port=3306 --user=root -e "CREATE DATABASE IF NOT EXISTS transportbus_l2_laravel CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

Réinitialiser la base et les données de démo :

```powershell
& "D:\wamp64\bin\php\php8.2.29\php.exe" artisan migrate:fresh --seed
```

Toujours utiliser les options longues (`--host=...`) : PowerShell casse `-h127.0.0.1`.

`migrate:fresh --seed` supprime les réservations famille et leurs passagers ; penser à vider aussi `F:\reservation-bus-l2-laravel-data\documents` pour retirer les fichiers orphelins.

## Modèle famille

- `reservations` : `type_reservation` (`individuel`/`famille`), `nom_famille`, `documents_statut` (`non_requis`/`en_attente`/`valide`/`refuse`).
- `passagers` : un enregistrement par voyageur, lié à la réservation (`ON DELETE CASCADE`), avec type de pièce (`cni`, `carte_scolaire`, `recepisse`, `nip`), numéro et fichier facultatif.
- 2 à 6 voyageurs, une place par voyageur.
- Fichiers déposés dans `DOCUMENTS_PATH`, jamais dans `public/`.
- Fiche admin : consultation de la réservation, validation ou refus des pièces.

## Commandes

```powershell
& "D:\wamp64\bin\php\php8.2.29\php.exe" artisan migrate:fresh --seed
& "D:\wamp64\bin\php\php8.2.29\php.exe" artisan serve --port=8002
npm run build
```

## Conventions

- Répondre en français.
- Pas de commentaires dans le code.
- Ne jamais commit/push sans demande explicite.
- Le projet source L2 PHP natif (`F:\reservation-bus-l2`) est en lecture seule.
- Le projet Gabon/Laravel (`F:\reservation-bus\app`) est également hors périmètre.
