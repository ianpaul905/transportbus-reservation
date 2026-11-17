<?php

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$base = getenv('APP_URL_TEST') ?: 'http://127.0.0.1:8002';
$PASS = 0;
$FAIL = 0;

function check(string $cas, bool $ok, string $detail = ''): void
{
    global $PASS, $FAIL;
    if ($ok) {
        $PASS++;
        echo "[PASS] $cas\n";
    } else {
        $FAIL++;
        echo "[FAIL] $cas — $detail\n";
    }
}

function client(string $cookie): CurlHandle
{
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_COOKIEJAR => $cookie,
        CURLOPT_COOKIEFILE => $cookie,
    ]);

    return $ch;
}

function req(CurlHandle $ch, string $url, ?array $post = null): array
{
    curl_setopt($ch, CURLOPT_URL, $url);
    if ($post === null) {
        curl_setopt($ch, CURLOPT_HTTPGET, true);
    } else {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
    }
    $html = curl_exec($ch);

    return [$html ?: '', curl_getinfo($ch, CURLINFO_HTTP_CODE), curl_getinfo($ch, CURLINFO_EFFECTIVE_URL)];
}

function token(string $html): ?string
{
    return preg_match('/name="_token" value="([^"]+)"/', $html, $m) ? $m[1] : null;
}

function login(CurlHandle $ch, string $base, string $email, string $password): int
{
    [$page] = req($ch, $base . '/login');
    [, $code] = req($ch, $base . '/login', [
        'email' => $email,
        'mot_de_passe' => $password,
        '_token' => token($page),
    ]);

    return $code;
}

$dir = sys_get_temp_dir() . '/tb_l2_' . uniqid();
@mkdir($dir);
$clientCookie = "$dir/client.txt";
$adminCookie = "$dir/admin.txt";
$guestCookie = "$dir/guest.txt";
$png = "$dir/piece.png";
file_put_contents($png, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/nZwAAAAASUVORK5CYII='));

echo "=== Tests fonctionnels TransportBus Gabon (Laravel) ===\n";
echo "Cible : $base\n\n";

$c = client($clientCookie);
[$home, $code] = req($c, $base . '/');
check('Cas 1 — Accueil et recherche', $code === 200 && strpos($home, 'Voyagez sans file') !== false && strpos($home, 'Prochains départs') !== false, "http=$code");

$date2 = date('Y-m-d', strtotime('+2 days'));
$date5 = date('Y-m-d', strtotime('+5 days'));
[, $code, $url] = req($c, $base . '/resultats', [
    'ville_depart' => 'Libreville',
    'ville_arrivee' => 'Mouila',
    'date_depart' => $date2,
    '_token' => token($home),
]);
check('Cas 2 — Recherche sur une date exacte', $code === 200 && strpos($url, '/resultats') !== false, "http=$code url=$url");

[$results] = req($c, $base . '/resultats', [
    'ville_depart' => 'Libreville',
    'ville_arrivee' => 'Mouila',
    'date_depart' => $date2,
    '_token' => token($home),
]);
check('Cas 3 — Trajets et places libres affichés', strpos($results, 'Trajets disponibles') !== false && strpos($results, 'place(s) libre(s)') !== false);

[$complet] = req($c, $base . '/resultats', [
    'ville_depart' => 'Libreville',
    'ville_arrivee' => 'Mouila',
    'date_depart' => $date5,
    '_token' => token($home),
]);
check('Cas 4 — Dates alternatives si complet', strpos($complet, 'Prochains départs disponibles') !== false, 'axe Libreville→Mouila +5 jours');

$codeLogin = login($c, $base, 'client@transportbus.ga', 'client123');
[$mes] = req($c, $base . '/mes-reservations');
check('Cas 5 — Connexion client et mes réservations', $codeLogin === 200 && strpos($mes, 'RES-DEMO0001') !== false, "http=$codeLogin");

[$form] = req($c, $base . '/reserver/1');
[$html, $code, $url] = req($c, $base . '/reserver/1', [
    'type_reservation' => 'individuel',
    'client_nom' => 'Client Test Laravel',
    'client_telephone' => '+24107000001',
    'nombre_places' => '1',
    '_token' => token($form),
]);
preg_match('#/paiement/(\d+)#', $url, $m);
$resIndividuel = $m[1] ?? null;
check('Cas 6 — Réservation individuelle', $code === 200 && $resIndividuel !== null, "url=$url");

if ($resIndividuel) {
    [$payment] = req($c, $base . "/paiement/$resIndividuel");
    [$billet, $code] = req($c, $base . "/paiement/$resIndividuel", [
        'operateur' => 'airtel_money',
        'telephone' => '+24107000001',
        '_token' => token($payment),
    ]);
    check('Cas 7 — Paiement simulé et e-billet', $code === 200 && strpos($billet, 'E-billet') !== false && strpos($billet, 'non scannable') !== false && strpos($billet, 'class="on"') !== false, "http=$code");
}

[$formFamille] = req($c, $base . '/reserver/2');
$famillePost = [
    'type_reservation' => 'famille',
    'nom_famille' => 'Famille Test',
    'client_telephone' => '+24107000002',
    'passagers_nom[0]' => 'Jean Test',
    'passagers_nom[1]' => 'Marie Test',
    'passagers_type[0]' => 'cni',
    'passagers_type[1]' => 'carte_scolaire',
    'passagers_numero[0]' => 'TEST-1234',
    'passagers_numero[1]' => 'TEST-5678',
    '_token' => token($formFamille),
];
$famillePost['fichiers[0]'] = new CURLFile($png, 'image/png', 'piece.png');
curl_setopt($c, CURLOPT_URL, $base . '/reserver/2');
curl_setopt($c, CURLOPT_POST, true);
curl_setopt($c, CURLOPT_POSTFIELDS, $famillePost);
$htmlFamille = curl_exec($c) ?: '';
$codeFamille = curl_getinfo($c, CURLINFO_HTTP_CODE);
$urlFamille = curl_getinfo($c, CURLINFO_EFFECTIVE_URL);
preg_match('#/paiement/(\d+)#', $urlFamille, $m2);
$resFamille = $m2[1] ?? null;
check('Cas 8 — Réservation famille avec justificatif', $codeFamille === 200 && $resFamille !== null, "url=$urlFamille");

if ($resFamille) {
    [$paymentFamille] = req($c, $base . "/paiement/$resFamille");
    [$billetFamille, $code] = req($c, $base . "/paiement/$resFamille", [
        'operateur' => 'moov_money',
        'telephone' => '+24107000002',
        '_token' => token($paymentFamille),
    ]);
    check('Cas 9 — Billet famille et pièces masquées', $code === 200 && strpos($billetFamille, 'Famille Test') !== false && strpos($billetFamille, 'Jean Test') !== false && strpos($billetFamille, '••••1234') !== false, "http=$code");

    $passager = App\Models\Passager::where('nom_complet', 'Jean Test')->first();
    [$document, $codeDoc] = req($c, $base . '/document/' . ($passager?->id ?? 0));
    check('Cas 10 — Justificatif servi au propriétaire', $codeDoc === 200 && strpos($document, 'PNG') !== false, "http=$codeDoc");
}

[$formAnnul] = req($c, $base . '/reserver/3');
[, , $urlAnnul] = req($c, $base . '/reserver/3', [
    'type_reservation' => 'individuel',
    'client_nom' => 'Client À Annuler',
    'client_telephone' => '+24107000003',
    'nombre_places' => '1',
    '_token' => token($formAnnul),
]);
preg_match('#/paiement/(\d+)#', $urlAnnul, $m3);
$resAnnul = $m3[1] ?? null;
if ($resAnnul) {
    [$mesAnnul] = req($c, $base . '/mes-reservations');
    [, $code] = req($c, $base . "/annuler/$resAnnul", [
        '_token' => token($mesAnnul),
    ]);
    [$mesApres] = req($c, $base . '/mes-reservations');
    check('Cas 11 — Annulation réservation en attente', $code === 200 && strpos($mesApres, 'Annulée') !== false, "http=$code");
}

$a = client($adminCookie);
$codeAdmin = login($a, $base, 'admin@transportbus.ga', 'admin123');
[$dashboard] = req($a, $base . '/admin');
check('Cas 12 — Connexion admin et tableau de bord', $codeAdmin === 200 && strpos($dashboard, 'Tableau de bord') !== false && strpos($dashboard, 'Chiffre d\'affaires') !== false, "http=$codeAdmin");

[$bus] = req($a, $base . '/admin/bus');
[$trajets] = req($a, $base . '/admin/trajets');
[$reservations] = req($a, $base . '/admin/reservations');
check('Cas 13 — Pages admin bus/trajets/réservations', strpos($bus, 'AB-101') !== false && strpos($trajets, 'Libreville') !== false && strpos($reservations, 'RES-DEMO0003') !== false);

[$fiche] = req($a, $base . '/admin/reservation/3');
check('Cas 14 — Fiche famille admin avec pièces en clair', strpos($fiche, 'Jean Alou') !== false && strpos($fiche, 'CNI-2019-001234') !== false);

[, $code] = req($a, $base . '/admin/reservation/3/documents', [
    'action' => 'valider',
    '_token' => token($fiche),
]);
[$ficheApres] = req($a, $base . '/admin/reservation/3');
check('Cas 15 — Validation des pièces famille', $code === 200 && strpos($ficheApres, 'Validé') !== false, "http=$code");

foreach (glob("$dir/*") as $f) {
    @unlink($f);
}
@rmdir($dir);

echo "\n==========================\n";
echo "Résultat : $PASS PASS / $FAIL FAIL\n";
exit($FAIL === 0 ? 0 : 1);
