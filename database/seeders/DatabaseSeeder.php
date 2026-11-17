<?php

namespace Database\Seeders;

use App\Models\Agency;
use App\Models\Bus;
use App\Models\Passager;
use App\Models\Payment;
use App\Models\Reservation;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@transportbus.ga'],
            [
                'nom' => 'Administrateur',
                'telephone' => '+241 07 00 00 01',
                'mot_de_passe' => Hash::make('admin123'),
                'role' => 'admin',
            ],
        );

        $client = User::updateOrCreate(
            ['email' => 'client@transportbus.ga'],
            [
                'nom' => 'Client Démo',
                'telephone' => '+241 07 00 00 02',
                'mot_de_passe' => Hash::make('client123'),
                'role' => 'client',
            ],
        );

        $agence1 = Agency::firstOrCreate(
            ['nom' => 'Major Transport'],
            ['ville' => 'Libreville', 'telephone' => '+241 01 12 34 56'],
        );

        $agence2 = Agency::firstOrCreate(
            ['nom' => 'Transporteur Voyages'],
            ['ville' => 'Libreville', 'telephone' => '+241 01 98 76 54'],
        );

        $busDonnees = [
            ['AB-101', 40, 'VIP', 'actif', $agence1->id],
            ['AB-102', 35, null, 'actif', $agence1->id],
            ['AB-103', 40, null, 'en_maintenance', $agence1->id],
            ['TV-201', 45, null, 'actif', $agence2->id],
            ['TV-202', 15, 'VIP', 'actif', $agence2->id],
        ];

        $busIds = [];

        foreach ($busDonnees as [$immatriculation, $places, $classe, $statut, $agenceId]) {
            $bus = Bus::firstOrCreate(
                ['immatriculation' => $immatriculation],
                [
                    'nombre_places' => $places,
                    'classe' => $classe,
                    'statut' => $statut,
                    'agence_id' => $agenceId,
                ],
            );

            $busIds[$immatriculation] = $bus->id;
        }

        $trajets = [
            [1, 'Libreville', 'Mouila', 2, '05:00:00', 15000, 'AB-101'],
            [2, 'Libreville', 'Mouila', 3, '05:00:00', 15000, 'AB-102'],
            [3, 'Libreville', 'Mouila', 4, '05:00:00', 15000, 'AB-101'],
            [4, 'Libreville', 'Mouila', 5, '06:00:00', 18000, 'TV-202'],
            [5, 'Libreville', 'Lambaréné', 2, '04:00:00', 8000, 'AB-102'],
            [6, 'Libreville', 'Lambaréné', 3, '04:00:00', 8000, 'AB-101'],
            [7, 'Libreville', 'Oyem', 2, '07:00:00', 17000, 'TV-201'],
            [8, 'Libreville', 'Oyem', 4, '07:00:00', 17000, 'AB-101'],
            [9, 'Libreville', 'Franceville', 3, '08:00:00', 22000, 'TV-201'],
            [10, 'Libreville', 'Franceville', 6, '08:00:00', 22000, 'AB-102'],
            [11, 'Libreville', 'Port-Gentil', 2, '06:00:00', 16000, 'AB-102'],
            [12, 'Libreville', 'Port-Gentil', 5, '06:00:00', 16000, 'TV-201'],
            [13, 'Mouila', 'Libreville', 2, '10:00:00', 15000, 'AB-101'],
            [14, 'Oyem', 'Libreville', 3, '09:00:00', 17000, 'TV-201'],
            [15, 'Libreville', 'Mouila', 6, '05:00:00', 15000, 'AB-101'],
            [16, 'Libreville', 'Mouila', 7, '05:00:00', 15000, 'AB-102'],
        ];

        foreach ($trajets as [$id, $depart, $arrivee, $jours, $heure, $prix, $immatriculation]) {
            Trip::updateOrCreate(
                ['id' => $id],
                [
                    'ville_depart' => $depart,
                    'ville_arrivee' => $arrivee,
                    'date_depart' => now()->addDays($jours)->toDateString(),
                    'heure_depart' => $heure,
                    'prix' => $prix,
                    'bus_id' => $busIds[$immatriculation],
                ],
            );
        }

        $reservation1 = Reservation::updateOrCreate(
            ['reference' => 'RES-DEMO0001'],
            [
                'client_nom' => 'Client Démo',
                'client_telephone' => '+241 07 00 00 02',
                'nombre_places' => 15,
                'type_reservation' => 'individuel',
                'nom_famille' => null,
                'documents_statut' => 'non_requis',
                'statut' => 'payee',
                'trajet_id' => 4,
                'user_id' => $client->id,
            ],
        );

        $reservation2 = Reservation::updateOrCreate(
            ['reference' => 'RES-DEMO0002'],
            [
                'client_nom' => 'Client Démo',
                'client_telephone' => '+241 07 00 00 02',
                'nombre_places' => 2,
                'type_reservation' => 'individuel',
                'nom_famille' => null,
                'documents_statut' => 'non_requis',
                'statut' => 'payee',
                'trajet_id' => 1,
                'user_id' => $client->id,
            ],
        );

        $reservation3 = Reservation::updateOrCreate(
            ['reference' => 'RES-DEMO0003'],
            [
                'client_nom' => 'Famille Alou',
                'client_telephone' => '+241 07 00 00 03',
                'nombre_places' => 3,
                'type_reservation' => 'famille',
                'nom_famille' => 'Famille Alou',
                'documents_statut' => 'en_attente',
                'statut' => 'payee',
                'trajet_id' => 3,
                'user_id' => $client->id,
            ],
        );

        $passagers = [
            ['Jean Alou', 'cni', 'CNI-2019-001234'],
            ['Marie Alou', 'carte_scolaire', 'CS-2025-7788'],
            ['Paul Alou', 'nip', 'NIP-908765'],
        ];

        foreach ($passagers as [$nomComplet, $typePiece, $numeroPiece]) {
            Passager::firstOrCreate(
                ['reservation_id' => $reservation3->id, 'numero_piece' => $numeroPiece],
                ['nom_complet' => $nomComplet, 'type_piece' => $typePiece],
            );
        }

        Payment::updateOrCreate(
            ['reference' => 'PAY-DEMO0001'],
            [
                'reservation_id' => $reservation1->id,
                'operateur' => 'airtel_money',
                'telephone' => '+241 07 00 00 02',
                'montant' => 270000,
                'statut' => 'reussi',
            ],
        );

        Payment::updateOrCreate(
            ['reference' => 'PAY-DEMO0002'],
            [
                'reservation_id' => $reservation2->id,
                'operateur' => 'moov_money',
                'telephone' => '+241 07 00 00 02',
                'montant' => 30000,
                'statut' => 'reussi',
            ],
        );

        Payment::updateOrCreate(
            ['reference' => 'PAY-DEMO0003'],
            [
                'reservation_id' => $reservation3->id,
                'operateur' => 'moov_money',
                'telephone' => '+241 07 00 00 03',
                'montant' => 45000,
                'statut' => 'reussi',
            ],
        );
    }
}
