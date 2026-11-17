<?php

namespace App\Http\Controllers;

use App\Models\Passager;
use App\Services\DocumentService;

class DocumentController extends Controller
{
    public function show(Passager $passager)
    {
        $reservation = $passager->reservation;

        $autorise = auth()->user()?->isAdmin() === true
            || ($reservation->user_id !== null && (int) $reservation->user_id === (int) auth()->id())
            || in_array($passager->reservation_id, (array) session('acces_reservations', []), true);

        abort_unless($autorise, 403);
        abort_unless($passager->fichier || $passager->fichier_donnees, 404);

        $enTetes = [
            'Content-Type' => $passager->fichier_type ?: 'application/octet-stream',
            'Content-Disposition' => 'inline; filename="' . basename($passager->fichier_nom ?: 'document') . '"',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store',
        ];

        if ($passager->fichier_donnees) {
            return response(base64_decode($passager->fichier_donnees), 200, $enTetes);
        }

        $documents = new DocumentService();

        return response()->file($documents->chemin($passager->fichier), $enTetes);
    }
}
