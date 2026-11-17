<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class DocumentService
{
    private const TAILLE_MAX = 2 * 1024 * 1024;

    private const TYPES = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'application/pdf' => 'pdf',
    ];

    public function __construct()
    {
        File::ensureDirectoryExists((string) config('filesystems.disks.documents.root'));
    }

    public function enregistrer(UploadedFile $fichier): array
    {
        if (! $fichier->isValid()) {
            throw ValidationException::withMessages(['fichier' => 'Le fichier téléversé est invalide.']);
        }

        $taille = $fichier->getSize();

        if ($taille === false || $taille > self::TAILLE_MAX) {
            throw ValidationException::withMessages(['fichier' => 'Le fichier ne doit pas dépasser 2 Mo.']);
        }

        $mime = $this->detecterMime($fichier);

        if (! isset(self::TYPES[$mime])) {
            throw ValidationException::withMessages(['fichier' => 'Ce type de fichier n\'est pas autorisé.']);
        }

        $extension = self::TYPES[$mime];
        $nom = bin2hex(random_bytes(16)).'.'.$extension;
        $chemin = $fichier->getRealPath();

        $contenu = File::get($chemin === false ? '' : $chemin);
        Storage::disk('documents')->put($nom, $contenu);

        return [
            'fichier' => $nom,
            'fichier_nom' => $this->nettoyerNom($fichier->getClientOriginalName(), $extension),
            'fichier_type' => $mime,
            'fichier_taille' => (int) $taille,
            'fichier_donnees' => base64_encode($contenu),
        ];
    }

    public function supprimer(?string $fichier): void
    {
        if ($fichier === null || $fichier === '') {
            return;
        }

        $nom = basename(str_replace('\\', '/', $fichier));

        if ($nom === '' || $nom === '.' || $nom === '..') {
            return;
        }

        Storage::disk('documents')->delete($nom);
    }

    public function chemin(string $fichier): string
    {
        return Storage::disk('documents')->path(basename(str_replace('\\', '/', $fichier)));
    }

    private function detecterMime(UploadedFile $fichier): string
    {
        $chemin = $fichier->getRealPath();

        if ($chemin !== false && function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);

            if ($finfo !== false) {
                $mime = finfo_file($finfo, $chemin);
                finfo_close($finfo);

                if (is_string($mime) && $mime !== '') {
                    return strtolower(trim(explode(';', $mime)[0]));
                }
            }
        }

        return strtolower((string) $fichier->getMimeType());
    }

    private function nettoyerNom(string $original, string $extension): string
    {
        $nom = str_replace(['\\', '/', "\0"], '', $original);
        $nom = preg_replace('/[[:cntrl:]]/u', '', $nom) ?? '';
        $nom = trim($nom);

        if ($nom === '') {
            $nom = 'document.'.$extension;
        }

        return mb_substr($nom, 0, 255);
    }
}
