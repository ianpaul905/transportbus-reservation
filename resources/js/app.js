import './bootstrap';

document.addEventListener('DOMContentLoaded', function () {
    const total = document.getElementById('total');
    const nombrePlaces = document.getElementById('nombre_places');

    if (total && nombrePlaces) {
        const mettreAJourTotal = function () {
            const prixUnitaire = Number(total.dataset.prix) || 0;
            const quantite = Number(nombrePlaces.value) || 1;
            total.innerHTML = 'Total : <strong>' + (prixUnitaire * quantite).toLocaleString('fr-FR') + ' FCFA</strong>';
        };

        nombrePlaces.addEventListener('input', mettreAJourTotal);
        mettreAJourTotal();
    }

    const modeIndividuel = document.querySelector('[data-mode-individuel]');
    const modeFamille = document.querySelector('[data-mode-famille]');
    const blocIndividuel = document.getElementById('bloc-individuel');
    const blocFamille = document.getElementById('bloc-famille');
    let appliquerMode = null;

    if (modeIndividuel && modeFamille && blocIndividuel && blocFamille) {
        const basculerChamps = function (bloc, actif) {
            bloc.querySelectorAll('input, select, textarea, button').forEach(function (champ) {
                champ.disabled = !actif;
            });
        };

        appliquerMode = function () {
            const famille = modeFamille.checked;
            blocIndividuel.hidden = famille;
            blocFamille.hidden = !famille;
            basculerChamps(blocIndividuel, !famille);
            basculerChamps(blocFamille, famille);
        };

        modeIndividuel.addEventListener('change', appliquerMode);
        modeFamille.addEventListener('change', appliquerMode);
        appliquerMode();
    }

    const conteneurPassagers = document.getElementById('passagers');
    const modelePassager = document.getElementById('modele-passager');
    const ajouterPassager = document.querySelector('[data-ajouter-passager]');
    const compteurPassagers = document.getElementById('compteur-passagers');
    const placesFamille = document.getElementById('places-famille');

    if (conteneurPassagers && modelePassager && ajouterPassager && compteurPassagers && placesFamille) {
        const nombrePassagers = function () {
            return conteneurPassagers.querySelectorAll('.passager-row').length;
        };

        const renumeroterPassagers = function () {
            conteneurPassagers.querySelectorAll('.passager-row').forEach(function (ligne, index) {
                const libelle = 'Voyageur ' + (index + 1);
                ligne.dataset.numero = libelle;

                const numero = ligne.querySelector('.passager-numero');
                if (numero) {
                    numero.textContent = libelle;
                }
            });

            compteurPassagers.textContent = String(nombrePassagers());
            placesFamille.textContent = String(nombrePassagers());
        };

        ajouterPassager.addEventListener('click', function () {
            if (nombrePassagers() >= 6) {
                return;
            }

            conteneurPassagers.appendChild(modelePassager.content.cloneNode(true));
            renumeroterPassagers();

            if (appliquerMode) {
                appliquerMode();
            }
        });

        conteneurPassagers.addEventListener('click', function (evenement) {
            const bouton = evenement.target.closest('[data-retirer-passager]');

            if (!bouton) {
                return;
            }

            if (nombrePassagers() <= 2) {
                window.alert('Une réservation famille doit comporter au moins 2 voyageurs.');
                return;
            }

            const ligne = bouton.closest('.passager-row');
            if (ligne) {
                ligne.remove();
                renumeroterPassagers();
            }
        });

        renumeroterPassagers();
    }

    document.querySelectorAll('[data-auto-submit]').forEach(function (select) {
        select.addEventListener('change', function () {
            if (select.form) {
                select.form.submit();
            }
        });
    });

    document.querySelectorAll('[data-confirm]').forEach(function (element) {
        const form = element.tagName === 'FORM' ? element : element.closest('form');

        if (!form) {
            return;
        }

        form.addEventListener('submit', function (event) {
            if (!window.confirm(element.dataset.confirm)) {
                event.preventDefault();
            }
        });
    });

    document.querySelectorAll('[data-print]').forEach(function (button) {
        button.addEventListener('click', function () {
            window.print();
        });
    });
});
