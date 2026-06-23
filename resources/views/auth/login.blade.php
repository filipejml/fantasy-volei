<x-guest-layout>
    <div class="mb-6">
        <span class="inline-flex items-center rounded-full bg-blue-50 px-3 py-1 text-xs font-bold uppercase tracking-[0.18em] text-blue-700">
            Área do jogador
        </span>
        <h2 class="mt-4 text-3xl font-extrabold tracking-tight text-slate-950 sm:text-4xl">
            Bem-vindo de volta!
        </h2>
        <p class="mt-2 text-base leading-6 text-slate-500">
            Acesse sua conta para montar a escalação da próxima rodada.
        </p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-6 rounded-xl bg-emerald-50 px-4 py-3" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" value="E-mail" class="mb-2 text-sm font-bold text-slate-700" />
            <div class="relative">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path d="M3 6.75A2.75 2.75 0 0 1 5.75 4h12.5A2.75 2.75 0 0 1 21 6.75v10.5A2.75 2.75 0 0 1 18.25 20H5.75A2.75 2.75 0 0 1 3 17.25V6.75Z"/>
                        <path d="m4 6 8 6 8-6"/>
                    </svg>
                </span>
                <x-text-input
                    id="email"
                    class="block w-full rounded-xl border-slate-200 bg-slate-50 py-3 pl-12 pr-4 text-slate-900 shadow-none transition placeholder:text-slate-400 focus:border-blue-600 focus:bg-white focus:ring-blue-600"
                    type="email"
                    name="email"
                    :value="old('email')"
                    placeholder="voce@exemplo.com"
                    required
                    autofocus
                    autocomplete="username"
                />
            </div>
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div>
            <x-input-label for="password" value="Senha" class="mb-2 text-sm font-bold text-slate-700" />
            <div class="relative">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <rect x="4" y="10" width="16" height="11" rx="2.5"/>
                        <path d="M8 10V7a4 4 0 0 1 8 0v3"/>
                    </svg>
                </span>
                <x-text-input
                    id="password"
                    class="block w-full rounded-xl border-slate-200 bg-slate-50 py-3 pl-12 pr-4 text-slate-900 shadow-none transition placeholder:text-slate-400 focus:border-blue-600 focus:bg-white focus:ring-blue-600"
                    type="password"
                    name="password"
                    placeholder="Digite sua senha"
                    required
                    autocomplete="current-password"
                />
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="flex items-center justify-between gap-4">
            <label for="remember_me" class="inline-flex cursor-pointer items-center">
                <input id="remember_me" type="checkbox" class="rounded border-slate-300 text-blue-700 shadow-sm focus:ring-blue-600" name="remember">
                <span class="ms-2 text-sm font-medium text-slate-600">Lembrar de mim</span>
            </label>

            @if (Route::has('password.request'))
                <a class="rounded-md text-sm font-bold text-blue-700 transition hover:text-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:ring-offset-2" href="{{ route('password.request') }}">
                    Esqueceu a senha?
                </a>
            @endif
        </div>

        <div class="pt-2">
            <button type="submit" class="group inline-flex w-full items-center justify-center gap-3 rounded-xl bg-blue-700 px-5 py-3.5 text-sm font-extrabold uppercase tracking-[0.16em] text-white shadow-lg shadow-blue-700/25 transition hover:-translate-y-0.5 hover:bg-blue-800 hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-blue-600 focus:ring-offset-2 active:translate-y-0">
                Entrar na quadra
                <svg class="h-5 w-5 transition group-hover:translate-x-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path d="M5 12h14M13 6l6 6-6 6"/>
                </svg>
            </button>
        </div>
    </form>

    @if (Route::has('register'))
        <p class="mt-6 text-center text-sm text-slate-500">
            Ainda não tem uma conta?
            <a href="{{ route('register') }}" class="font-extrabold text-blue-700 transition hover:text-blue-900">
                Criar cadastro
            </a>
        </p>
    @endif
</x-guest-layout>
