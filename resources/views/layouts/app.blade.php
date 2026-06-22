<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-white">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'NBTS') }} - Blood Donation Platform</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Styles & Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        body {
            font-family: 'Outfit', sans-serif;
        }
        .glass {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
    </style>
</head>
<body class="antialiased min-h-full flex flex-col text-slate-900">
    <!-- Navigation -->
    <nav x-data="{ open: false }" class="sticky top-0 z-50 glass border-b border-slate-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-20">
                <div class="flex items-center">
                    <a href="/" class="flex items-center space-x-2">
                        <div class="w-10 h-10 bg-red-600 rounded-xl flex items-center justify-center shadow-lg shadow-red-200">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                            </svg>
                        </div>
                        <span class="text-2xl font-bold tracking-tight text-slate-900">NBTS<span class="text-red-600">.</span></span>
                    </a>
                    
                    <!-- Desktop Menu -->
                    <div class="hidden md:ml-10 md:flex md:space-x-8">
                        <a href="{{ route('home') }}" class="inline-flex items-center px-1 pt-1 text-sm font-medium {{ request()->routeIs('home') ? 'text-red-600' : 'text-slate-900 hover:text-red-600' }} transition-colors">Home</a>
                        <a href="{{ route('about') }}" class="inline-flex items-center px-1 pt-1 text-sm font-medium {{ request()->routeIs('about') ? 'text-red-600' : 'text-slate-500 hover:text-red-600' }} transition-colors">About</a>
                        <a href="{{ route('centers.index') }}" class="inline-flex items-center px-1 pt-1 text-sm font-medium {{ request()->routeIs('centers.index') ? 'text-red-600' : 'text-slate-500 hover:text-red-600' }} transition-colors">Blood Centers</a>
                        <a href="{{ route('campaigns.index') }}" class="inline-flex items-center px-1 pt-1 text-sm font-medium {{ request()->routeIs('campaigns.index') ? 'text-red-600' : 'text-slate-500 hover:text-red-600' }} transition-colors">Campaigns</a>
                        <a href="{{ route('analytics') }}" class="inline-flex items-center px-1 pt-1 text-sm font-medium {{ request()->routeIs('analytics') ? 'text-red-600' : 'text-slate-500 hover:text-red-600' }} transition-colors">Real-Time Impact</a>
                        <a href="{{ route('eligibility') }}" class="inline-flex items-center px-1 pt-1 text-sm font-bold uppercase tracking-widest italic {{ request()->routeIs('eligibility') ? 'text-red-600' : 'text-red-500 hover:text-slate-900' }} transition-all">Can I Donate?</a>
                    </div>
                </div>

                <div class="hidden md:flex md:items-center md:space-x-4">
                    <a href="{{ route('download') }}" class="inline-flex items-center px-6 py-2.5 border border-transparent text-sm font-semibold rounded-full shadow-lg shadow-red-100 text-white bg-slate-900 hover:bg-slate-800 transition-all active:scale-95">
                        Download Mobile App
                    </a>
                </div>

                <!-- Mobile menu button -->
                <div class="flex items-center md:hidden">
                    <button @click="open = !open" class="inline-flex items-center justify-center p-2 rounded-md text-slate-400 hover:text-slate-500 hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-red-500">
                        <svg class="h-6 h-6" :class="{ 'hidden': open, 'block': !open }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                        <svg class="h-6 h-6" :class="{ 'block': open, 'hidden': !open }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div x-show="open" x-transition class="md:hidden glass border-t border-slate-100">
            <div class="pt-2 pb-3 space-y-1 px-4">
                <a href="{{ route('home') }}" class="block px-3 py-2 text-base font-medium {{ request()->routeIs('home') ? 'text-red-600 bg-red-50 border-l-4 border-red-500' : 'text-slate-500 hover:text-red-600 hover:bg-slate-50' }}">Home</a>
                <a href="{{ route('about') }}" class="block px-3 py-2 text-base font-medium {{ request()->routeIs('about') ? 'text-red-600 bg-red-50 border-l-4 border-red-500' : 'text-slate-500 hover:text-red-600 hover:bg-slate-50' }}">About</a>
                <a href="{{ route('centers.index') }}" class="block px-3 py-2 text-base font-medium {{ request()->routeIs('centers.index') ? 'text-red-600 bg-red-50 border-l-4 border-red-500' : 'text-slate-500 hover:text-red-600 hover:bg-slate-50' }}">Blood Centers</a>
                <a href="{{ route('campaigns.index') }}" class="block px-3 py-2 text-base font-medium {{ request()->routeIs('campaigns.index') ? 'text-red-600 bg-red-50 border-l-4 border-red-500' : 'text-slate-500 hover:text-red-600 hover:bg-slate-50' }}">Campaigns</a>
                <a href="{{ route('analytics') }}" class="block px-3 py-2 text-base font-medium {{ request()->routeIs('analytics') ? 'text-red-600 bg-red-50 border-l-4 border-red-500' : 'text-slate-500 hover:text-red-600 hover:bg-slate-50' }}">Real-Time Impact</a>
                <a href="{{ route('eligibility') }}" class="block px-3 py-2 text-base font-bold uppercase tracking-widest italic {{ request()->routeIs('eligibility') ? 'text-red-600 bg-red-50 border-l-4 border-red-500' : 'text-red-500 hover:text-red-600 hover:bg-slate-50' }}">Can I Donate?</a>
            </div>
            <div class="pt-4 pb-3 border-t border-slate-100 px-4">
                <a href="{{ route('download') }}" class="block px-3 py-2 text-base font-semibold text-slate-900 bg-slate-100 rounded-xl text-center">Download Mobile App</a>
            </div>
        </div>
    </nav>

    <main class="flex-grow">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-slate-900 text-slate-400 py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-12">
                <div class="col-span-1 md:col-span-1">
                    <div class="flex items-center space-x-2 text-white mb-6">
                        <div class="w-8 h-8 bg-red-600 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                            </svg>
                        </div>
                        <span class="text-xl font-bold italic tracking-wider">NBTS</span>
                    </div>
                    <p class="text-sm leading-relaxed mb-6">
                        Empowering communities through safe and reliable blood donation systems. Every drop counts.
                    </p>
                </div>
                <div>
                    <h3 class="text-white font-semibold mb-6 uppercase tracking-widest text-xs">Informational</h3>
                    <ul class="space-y-4 text-sm">
                        <li><a href="{{ route('about') }}" class="hover:text-white transition-colors">About NBTS</a></li>
                        <li><a href="{{ route('centers.index') }}" class="hover:text-white transition-colors">Find Blood Centers</a></li>
                        <li><a href="{{ route('campaigns.index') }}" class="hover:text-white transition-colors">Active Campaigns</a></li>
                        <li><a href="{{ route('analytics') }}" class="hover:text-white transition-colors">National Impact</a></li>
                        <li><a href="{{ route('download') }}" class="hover:text-white transition-colors">Mobile Application</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-white font-semibold mb-6 uppercase tracking-widest text-xs">Legal</h3>
                    <ul class="space-y-4 text-sm">
                        <li><a href="#" class="hover:text-white transition-colors">Privacy Policy</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Terms of Service</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Cookie Policy</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Disclaimer</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-white font-semibold mb-6 uppercase tracking-widest text-xs">Get the App</h3>
                    <div class="flex flex-col space-y-3">
                        <a href="{{ route('download') }}" class="flex items-center space-x-3 bg-slate-800 p-3 rounded-xl hover:bg-slate-700 transition-colors">
                            <div class="w-8 h-8 flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M17.525 10.035c-.022-2.856-2.355-5.151-5.207-5.151-2.036 0-3.818 1.168-4.706 2.872l-.001.001c-.131-.02-.266-.032-.404-.032-1.381 0-2.5 1.119-2.5 2.5 0 .151.014.298.04.441l-.001-.001c-.422.585-.672 1.304-.672 2.083 0 1.933 1.567 3.5 3.5 3.5h7.5c2.485 0 4.5-2.015 4.5-4.5 0-.825-.224-1.595-.62-2.257l.001.002z"/></svg>
                            </div>
                            <div class="text-[10px] uppercase font-black text-white italic">Download on PlayStore</div>
                        </a>
                        <a href="{{ route('download') }}" class="flex items-center space-x-3 bg-slate-800 p-3 rounded-xl hover:bg-slate-700 transition-colors">
                            <div class="w-8 h-8 flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.1 2.48-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .76-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.81-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M13 3.5c.73-.83 1.94-1.46 2.94-1.5.13 1.17-.36 2.35-1.04 3.19-.69.85-1.83 1.51-2.95 1.42-.15-1.15.41-2.35 1.05-3.11z"/></svg>
                            </div>
                            <div class="text-[10px] uppercase font-black text-white italic">Download on AppStore</div>
                        </a>
                    </div>
                </div>
            </div>
            <div class="border-t border-slate-800 mt-12 pt-8 flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0 text-xs text-slate-500">
                <p>&copy; {{ date('Y') }} National Blood Transfusion Service. All rights reserved.</p>
                <div class="flex space-x-6 text-[10px] font-bold uppercase tracking-widest italic">
                    <a href="#" class="hover:text-white">Terms</a>
                    <a href="#" class="hover:text-white">Cookies</a>
                </div>
            </div>
        </div>
    </footer>
</body>
</html>
