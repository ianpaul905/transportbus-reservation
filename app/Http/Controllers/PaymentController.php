<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Reservation;
use App\Services\QrCodeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function form(Reservation $reservation)
    {
        if ($reservation->statut !== 'en_attente') {
            return redirect()->route('billet', ['reservation' => $reservation->id])
                ->with('error', 'Cette réservation a déjà été traitée.');
        }

        return view('payment.form', compact('reservation'));
    }

    public function payer(Request $request, Reservation $reservation)
    {
        $validated = $request->validate([
            'operateur' => 'required|in:airtel_money,moov_money',
            'telephone' => ['required', 'regex:/^[0-9 +().-]{6,30}$/'],
        ]);

        if ($reservation->statut !== 'en_attente') {
            return redirect()->route('billet', ['reservation' => $reservation->id])
                ->with('error', 'Cette réservation a déjà été traitée.');
        }

        DB::transaction(function () use ($reservation, $validated) {
            Payment::create([
                'reservation_id' => $reservation->id,
                'operateur' => $validated['operateur'],
                'telephone' => $validated['telephone'],
                'montant' => $reservation->trajet->prix * $reservation->nombre_places,
                'reference' => 'PAY-' . strtoupper(bin2hex(random_bytes(4))),
                'statut' => 'reussi',
            ]);

            $reservation->update(['statut' => 'payee']);
        });

        return redirect()->route('billet', ['reservation' => $reservation->id])
            ->with('success', 'Paiement simulé accepté. Votre e-billet est prêt.');
    }

    public function billet(Reservation $reservation)
    {
        $qr = QrCodeService::grille($reservation->reference);

        return view('payment.ebillet', compact('reservation', 'qr'));
    }
}
