@extends('layouts.app')

@section('title', 'Inscription')

@section('content')
    <div class="auth-wrapper">
        <div class="auth-card">
            <header class="auth-header">
                <h1>Créer un compte</h1>
                <p class="auth-subtitle">Rejoignez la communauté et partagez vos outils.</p>
            </header>

            <form method="post" action="{{ route('register.store') }}" novalidate>
                @csrf

                <div class="form-group">
                    <label for="nom">Nom</label>
                    <input id="nom" name="nom" type="text"
                           value="{{ old('nom') }}"
                           autocomplete="family-name" required>
                    @error('nom') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="form-group">
                    <label for="prenom">Prénom</label>
                    <input id="prenom" name="prenom" type="text"
                           value="{{ old('prenom') }}"
                           autocomplete="given-name" required>
                    @error('prenom') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="form-group">
                    <label for="courriel">Adresse courriel</label>
                    <input id="courriel" name="courriel" type="email"
                           value="{{ old('courriel') }}"
                           autocomplete="email" required>
                    @error('courriel') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="form-group">
                    <label for="id_ville">Ville</label>
                    <select id="id_ville" name="id_ville" required>
                        <option value="" disabled @selected(old('id_ville') === null)>Choisir une ville…</option>
                        @foreach ($villes as $ville)
                            <option value="{{ $ville->id_ville }}" @selected((string) old('id_ville') === (string) $ville->id_ville)>
                                {{ $ville->nom_fr }}
                            </option>
                        @endforeach
                    </select>
                    @error('id_ville') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="form-group">
                    <label for="id_quartier">Quartier (optionnel)</label>
                    <select id="id_quartier" name="id_quartier">
                        <option value="">Ville seulement</option>
                        @foreach ($villes as $ville)
                            @foreach ($ville->quartiers as $quartier)
                                <option value="{{ $quartier->id_quartier }}"
                                        data-ville="{{ $ville->id_ville }}"
                                        @selected((string) old('id_quartier') === (string) $quartier->id_quartier)>
                                    {{ $ville->nom_fr }} — {{ $quartier->nom_fr }}
                                </option>
                            @endforeach
                        @endforeach
                    </select>
                    @error('id_quartier') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="form-group">
                    <label for="telephone">Téléphone</label>
                    <input id="telephone" name="telephone" type="tel"
                           value="{{ old('telephone') }}"
                           autocomplete="tel" maxlength="30">
                    @error('telephone') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="form-group">
                    <label for="bio">Bio</label>
                    <textarea id="bio" name="bio" rows="3" maxlength="1000">{{ old('bio') }}</textarea>
                    @error('bio') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="form-group">
                    <label for="mot_de_passe">Mot de passe</label>
                    <input id="mot_de_passe" name="mot_de_passe" type="password"
                           autocomplete="new-password" required>
                    @error('mot_de_passe') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="form-group">
                    <label for="mot_de_passe_confirmation">Confirmation du mot de passe</label>
                    <input id="mot_de_passe_confirmation" name="mot_de_passe_confirmation" type="password"
                           autocomplete="new-password" required>
                </div>

                <div class="g-recaptcha" data-sitekey="{{ config('services.recaptcha.site_key') }}"></div>
@error('g-recaptcha-response') <p>{{ $message }}</p> @enderror


                <button type="submit" class="btn-primary">Créer le compte</button>
            </form>

            <p class="auth-footer">
                Déjà un compte ? <a href="{{ route('login') }}">Se connecter</a>
            </p>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const ville = document.getElementById('id_ville');
            const quartier = document.getElementById('id_quartier');

            if (!ville || !quartier) {
                return;
            }

            const filtrer = () => {
                const choix = ville.value;

                Array.from(quartier.options).forEach((option) => {
                    if (!option.value) {
                        return;
                    }

                    const correspond = option.dataset.ville === choix;
                    option.hidden = !correspond;
                    option.disabled = !correspond;

                    if (!correspond && option.selected) {
                        quartier.value = '';
                    }
                });
            };

            ville.addEventListener('change', filtrer);
            filtrer();
        });
    </script>
@endpush
