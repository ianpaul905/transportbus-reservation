<?php

use Illuminate\Support\Facades\DB;

if (! function_exists('e')) {
    function e($value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

if (! function_exists('champ')) {
    function champ($name, $default = '')
    {
        return old($name, $default);
    }
}

if (! function_exists('types_pieces')) {
    function types_pieces(): array
    {
        return [
            'cni' => 'Carte nationale d\'identité',
            'carte_scolaire' => 'Carte scolaire',
            'recepisse' => 'Récépissé',
            'nip' => 'NIP',
        ];
    }
}

if (! function_exists('libelle_piece')) {
    function libelle_piece(?string $type): string
    {
        return types_pieces()[$type] ?? '';
    }
}

if (! function_exists('libelle_type_reservation')) {
    function libelle_type_reservation(?string $type): string
    {
        return match ($type) {
            'individuel' => 'Individuel',
            'famille' => 'Famille',
            default => '',
        };
    }
}

if (! function_exists('libelle_statut')) {
    function libelle_statut(?string $statut): string
    {
        return match ($statut) {
            'en_attente' => 'En attente de paiement',
            'payee' => 'Payée',
            'utilisee' => 'Utilisée',
            'annulee' => 'Annulée',
            default => '',
        };
    }
}

if (! function_exists('classe_statut')) {
    function classe_statut(?string $statut): string
    {
        return match ($statut) {
            'en_attente' => 'badge badge-jaune',
            'payee' => 'badge badge-vert',
            'utilisee' => 'badge badge-bleu',
            'annulee' => 'badge badge-rouge',
            default => 'badge',
        };
    }
}

if (! function_exists('libelle_documents')) {
    function libelle_documents(?string $statut): string
    {
        return match ($statut) {
            'non_requis' => 'Non requis',
            'en_attente' => 'En attente',
            'valide' => 'Validé',
            'refuse' => 'Refusé',
            default => '',
        };
    }
}

if (! function_exists('classe_documents')) {
    function classe_documents(?string $statut): string
    {
        return match ($statut) {
            'non_requis' => 'badge',
            'en_attente' => 'badge badge-jaune',
            'valide' => 'badge badge-vert',
            'refuse' => 'badge badge-rouge',
            default => 'badge',
        };
    }
}

if (! function_exists('masquer_piece')) {
    function masquer_piece(?string $numero): string
    {
        $numero = preg_replace('/\s+/u', '', $numero ?? '') ?? '';

        if (mb_strlen($numero) <= 4) {
            return $numero;
        }

        return '••••'.mb_substr($numero, -4);
    }
}

if (! function_exists('prix')) {
    function prix($montant): string
    {
        return number_format((float) $montant, 0, ',', ' ').' FCFA';
    }
}

if (! function_exists('date_fr')) {
    function date_fr($date): string
    {
        if ($date === null || $date === '') {
            return '';
        }

        try {
            return Illuminate\Support\Carbon::parse($date)->format('d/m/Y');
        } catch (Throwable $exception) {
            return '';
        }
    }
}

if (! function_exists('heure_fr')) {
    function heure_fr($heure): string
    {
        return substr((string) $heure, 0, 5);
    }
}

if (! function_exists('villes')) {
    function villes(): array
    {
        $departs = DB::table('trajets')->distinct()->pluck('ville_depart')->filter()->all();
        $arrivees = DB::table('trajets')->distinct()->pluck('ville_arrivee')->filter()->all();
        $villes = array_values(array_unique(array_merge($departs, $arrivees)));
        sort($villes, SORT_STRING);

        return $villes;
    }
}
