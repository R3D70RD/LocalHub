const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

document.querySelectorAll('[data-traduction]').forEach((bloc) => {
    const bouton = bloc.querySelector('[data-traduction-bouton]');
    const zone = bloc.querySelector('[data-traduction-texte]');

    if (!bouton || !zone) {
        return;
    }

    const cible = bloc.dataset.cible ?? 'fr';
    const cle = `traduction:${cible}:${bloc.dataset.cle ?? ''}`;
    const libelleInitial = bouton.textContent.trim();
    let affichee = false;

    const afficher = (valeur) => {
        zone.textContent = valeur;
        zone.hidden = false;
        affichee = true;
        bouton.disabled = false;
        bouton.textContent = bloc.dataset.restaurer || libelleInitial;
    };

    const masquer = () => {
        zone.hidden = true;
        affichee = false;
        bouton.textContent = libelleInitial;
    };

    bouton.addEventListener('click', async () => {
        if (affichee) {
            masquer();
            return;
        }

        // Le résultat est mis en cache dans le navigateur : aucune traduction
        // n'est stockée côté serveur.
        const enCache = localStorage.getItem(cle);
        if (enCache) {
            afficher(enCache);
            return;
        }

        bouton.disabled = true;
        bouton.textContent = bloc.dataset.chargement || libelleInitial;

        try {
            const reponse = await fetch(bloc.dataset.url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrf,
                },
                body: JSON.stringify({ cible }),
            });

            if (!reponse.ok) {
                throw new Error('Traduction indisponible');
            }

            const donnees = await reponse.json();
            localStorage.setItem(cle, donnees.traduction);
            afficher(donnees.traduction);
        } catch (erreur) {
            bouton.disabled = false;
            bouton.textContent = libelleInitial;
            zone.hidden = false;
            zone.textContent = bloc.dataset.erreur || '';
        }
    });
});
