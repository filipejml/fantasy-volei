<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Volley Fantasy') }}</title>
        <link rel="icon" type="image/svg+xml" href="{{ asset('volley-favicon.svg') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-slate-900 antialiased">
        <div class="relative min-h-screen overflow-hidden bg-slate-950">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(250,204,21,0.18),_transparent_32%),radial-gradient(circle_at_bottom_right,_rgba(37,99,235,0.35),_transparent_38%)]"></div>

            <div class="relative mx-auto flex min-h-screen max-w-7xl items-center px-4 py-4 sm:px-6 lg:px-8">
                <div class="grid w-full overflow-hidden rounded-[2rem] bg-white shadow-2xl shadow-blue-950/40 lg:grid-cols-[1.08fr_0.92fr]">
                    <section class="relative hidden min-h-[620px] overflow-hidden bg-blue-700 p-9 text-white lg:flex lg:flex-col lg:justify-between">
                        <div class="absolute inset-0 opacity-20">
                            <div class="absolute left-1/2 top-0 h-full w-px bg-white"></div>
                            <div class="absolute left-0 top-1/2 h-px w-full bg-white"></div>
                            <div class="absolute left-[14%] top-[12%] h-[76%] w-[72%] border-2 border-white"></div>
                            <div class="absolute left-0 top-[29%] h-px w-full bg-white"></div>
                            <div class="absolute left-0 top-[71%] h-px w-full bg-white"></div>
                        </div>

                        <div class="relative z-10">
                            <a href="/" class="inline-flex items-center gap-3">
                                <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-yellow-400 text-blue-950 shadow-lg shadow-blue-950/20">
                                    <svg class="h-7 w-7" viewBox="0 0 64 64" fill="none" aria-hidden="true">
                                        <circle cx="32" cy="32" r="25" stroke="currentColor" stroke-width="5"/>
                                        <path d="M18 12c8 6 12 12 14 20M52 20c-9-1-16 2-20 12M45 51c-1-9-5-15-13-19M12 43c8 0 15-3 20-11" stroke="currentColor" stroke-width="5" stroke-linecap="round"/>
                                    </svg>
                                </span>
                                <span>
                                    <span class="block text-xl font-extrabold tracking-tight">Fantasy Vôlei</span>
                                    <span class="block text-xs font-semibold uppercase tracking-[0.25em] text-blue-200">Monte seu time</span>
                                </span>
                            </a>
                        </div>

                        <div class="relative z-10 max-w-lg">
                            <div class="mb-6 flex h-40 w-40 items-center justify-center rounded-full bg-yellow-400 text-blue-950 shadow-[0_30px_70px_rgba(15,23,42,0.35)]">
                                <svg class="h-32 w-32 -rotate-12" viewBox="0 0 120 120" fill="none" aria-hidden="true">
                                    <circle cx="60" cy="60" r="50" fill="#FACC15" stroke="currentColor" stroke-width="5"/>
                                    <path d="M31 19c15 10 25 24 29 41M101 37C82 34 68 42 60 60M86 101c-2-19-11-33-26-41M19 86c18 0 32-8 41-26" stroke="currentColor" stroke-width="6" stroke-linecap="round"/>
                                </svg>
                            </div>

                            <p class="mb-4 text-sm font-bold uppercase tracking-[0.3em] text-yellow-300">Entre em quadra</p>
                            <h1 class="text-4xl font-extrabold leading-[1.05] tracking-tight">
                                Sua escalação.<br>
                                Sua estratégia.<br>
                                Sua vitória.
                            </h1>
                            <p class="mt-4 max-w-md text-base leading-7 text-blue-100">
                                Escale os melhores atletas, acompanhe cada rodada e dispute o topo da liga.
                            </p>
                        </div>

                        <div class="relative z-10 flex items-center gap-3 text-sm font-medium text-blue-200">
                            <span class="h-2.5 w-2.5 rounded-full bg-yellow-400"></span>
                            A temporada começa na sua escalação.
                        </div>
                    </section>

                    <main class="flex min-h-[590px] items-center bg-white px-6 py-8 sm:px-12 lg:min-h-[620px] lg:px-16">
                        <div class="mx-auto w-full max-w-md">
                            <div class="mb-10 flex items-center gap-3 lg:hidden">
                                <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-yellow-400 text-blue-950">
                                    <svg class="h-6 w-6" viewBox="0 0 64 64" fill="none" aria-hidden="true">
                                        <circle cx="32" cy="32" r="25" stroke="currentColor" stroke-width="5"/>
                                        <path d="M18 12c8 6 12 12 14 20M52 20c-9-1-16 2-20 12M45 51c-1-9-5-15-13-19M12 43c8 0 15-3 20-11" stroke="currentColor" stroke-width="5" stroke-linecap="round"/>
                                    </svg>
                                </span>
                                <span class="text-xl font-extrabold text-blue-950">Fantasy Vôlei</span>
                            </div>

                            {{ $slot }}
                        </div>
                    </main>
                </div>
            </div>
        </div>
    </body>
</html>
