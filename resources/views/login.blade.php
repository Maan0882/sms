<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>IAPES Portal — TechStrota</title>
        
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600|jetbrains-mono:400,500,700" rel="stylesheet" />
        
        <script src="https://cdn.tailwindcss.com"></script>

        <style>
            .portal-mono { font-family: 'JetBrains Mono', monospace; }
            
            /* Glassmorphism Logic */
            .glass-container {
                background: rgba(255, 255, 255, 0.45);
                backdrop-filter: blur(20px) saturate(180%);
                -webkit-backdrop-filter: blur(20px) saturate(180%);
                border: 1px solid rgba(255, 255, 255, 0.25);
                box-shadow: 0 12px 40px 0 rgba(0, 0, 0, 0.05);
            }

            .glass-btn {
                background: rgba(255, 255, 255, 0.5);
                backdrop-filter: blur(8px);
                border: 1px solid rgba(255, 255, 255, 0.3);
                transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            }

            /* Automatic Dark Mode Adjustments */
            @media (prefers-color-scheme: dark) {
                .glass-container {
                    background: rgba(18, 18, 17, 0.8);
                    border: 1px solid rgba(255, 255, 255, 0.08);
                    box-shadow: 0 12px 40px 0 rgba(0, 0, 0, 0.5);
                }
                .glass-btn {
                    background: rgba(255, 255, 255, 0.03);
                    border-color: rgba(255, 255, 255, 0.1);
                }
                .glass-btn:hover {
                    background: rgba(255, 255, 255, 0.08);
                    border-color: rgba(255, 255, 255, 0.25);
                }
            }
        </style>
    </head>
    <body class="bg-[#FDFDFC] dark:bg-[#080808] text-[#1b1b18] dark:text-[#EDEDEC] min-h-screen flex items-center justify-center p-4 md:p-8 relative overflow-hidden transition-colors duration-500">
        
        <div class="absolute top-[-15%] left-[-10%] w-[55%] h-[55%] bg-[#f7a93b]/20 dark:bg-[#f7a93b]/10 rounded-full blur-[140px]"></div>
        <div class="absolute bottom-[-15%] right-[-10%] w-[55%] h-[55%] bg-[#1d70b8]/20 dark:bg-[#1d70b8]/10 rounded-full blur-[140px]"></div>

        <main class="w-full sm:max-w-lg md:max-w-xl lg:max-w-xl glass-container rounded-[40px] overflow-hidden relative z-10 p-1 animate-in fade-in zoom-in duration-700">
    
            <div class="flex items-center gap-2 px-8 py-4 border-b border-black/5 dark:border-white/5">
                <div class="flex gap-2">
                    <div class="w-3 h-3 rounded-full bg-[#ff5f56]"></div>
                    <div class="w-3 h-3 rounded-full bg-[#ffbd2e]"></div>
                    <div class="w-3 h-3 rounded-full bg-[#27c93f]"></div>
                </div>
                <div class="ml-6 flex-1 bg-black/5 dark:bg-white/5 rounded-xl py-2 px-5 text-[10px] lg:text-[12px] portal-mono text-gray-400 dark:text-gray-500 truncate tracking-widest">
                    <a href="https://techstrota.com/">TechStrota.com</a>
                </div>
            </div>

            <div class="p-6 md:p-10 lg:p-12">
                <div class="flex flex-col items-center text-center mb-8 lg:mb-10">
                    <div class="p-3 bg-white dark:bg-white/5 rounded-[22px] shadow-sm mb-4 transition-all duration-300 hover:scale-110">
                        <img src="{{ asset('images/TsLogo.png') }}" alt="Tech स्त्रोत" class="h-12 md:h-16 lg:h-18 w-auto">
                    </div>
                    <h1 class="text-3xl md:text-4xl lg:text-5xl font-black tracking-tighter text-gray-900 dark:text-white uppercase italic">
                        IAPES <span class="text-[#f7a93b] dark:text-[#f7a93b]">PORTAL</span>
                    </h1>
                    <p class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-[0.4em] mt-2">
                        Powered by TechStrota &bull; Enterprise
                    </p>
                </div>
            <!-- Login Form Section -->
                <form method="POST" action="{{ route('login') }}" class="flex flex-col gap-4 w-full mt-4">
                    @csrf
                    <div>
                        <input type="email" name="email" placeholder="Email Address" required
                            class="w-full glass-btn px-6 py-4 rounded-[20px] text-gray-900 dark:text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-[#f7a93b]/50" 
                            value="{{ old('email') }}"
                        >
                        @error('email')
                            <p class="text-[#ff5f56] text-[11px] font-bold mt-2 pl-4 tracking-widest">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <input type="password" name="password" placeholder="Password" required
                            class="w-full glass-btn px-6 py-4 rounded-[20px] text-gray-900 dark:text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-[#f7a93b]/50"
                        >
                    </div>
                    <button type="submit" class="w-full bg-[#f7a93b] hover:bg-[#e09834] text-white font-black uppercase tracking-widest py-4 rounded-[20px] transition-all hover:scale-[1.02] mt-2 shadow-lg shadow-[#f7a93b]/20">
                        Secure Login
                    </button>
                </form>
                <!-- //////////////////// -->
                <div class="mt-10 lg:mt-12 pt-6 border-t border-black/5 dark:border-white/5 flex flex-col md:flex-row justify-between items-center gap-4">
                    <div class="flex items-center gap-3">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                        </span>
                        <span class="text-[9px] portal-mono text-gray-400 dark:text-gray-500 uppercase tracking-widest font-bold">Active</span>
                    </div>
                    <p class="text-[9px] portal-mono text-gray-400 dark:text-gray-500 uppercase tracking-[0.2em] font-medium">
                        &copy; 2026 TechStrota
                    </p>
                </div>
            </div>
        </main>
    </body>
</html>