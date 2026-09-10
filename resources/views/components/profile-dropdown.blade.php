@auth
    @php
        $user = session('user') ?? [];
        $userName = $user['name'] ?? 'User';
        $userEmail = $user['email'] ?? 'user@buildnfix.test';
        $userRole = $user['role'] ?? 'customer';
        $userBranch = $user['branch'] ?? null;
        
        $words = array_filter(explode(' ', trim($userName)));
        $initials = '';
        if (count($words) >= 2) {
            $initials = strtoupper(substr(array_shift($words), 0, 1) . substr(array_pop($words), 0, 1));
        } else {
            $initials = strtoupper(substr($userName, 0, 2));
        }

        $roleBadgeColor = match($userRole) {
            'admin' => 'bg-emerald-500/20 text-emerald-400 border-emerald-500/30',
            'owner' => 'bg-purple-500/20 text-purple-400 border-purple-500/30',
            default => 'bg-[#F97316]/20 text-[#F97316] border-[#F97316]/30',
        };
    @endphp

    <!-- Native HTML5 Pure CSS Dropdown (Zero JS Dependency) -->
    <details class="relative group">
        <!-- Profile Summary Trigger -->
        <summary class="flex items-center gap-2 p-1.5 rounded-xl hover:bg-white/10 transition-colors focus:outline-none cursor-pointer list-none [&::-webkit-details-marker]:hidden select-none"
                 aria-label="User Profile Menu">
            <div class="w-8 h-8 rounded-xl bg-white/10 border border-white/20 text-[#F97316] flex items-center justify-center font-black text-xs group-hover:border-[#F97316] transition-colors shrink-0">
                {{ $initials }}
            </div>
            <div class="hidden md:flex flex-col items-start leading-tight">
                <span class="text-xs font-bold text-gray-200 group-hover:text-white transition-colors max-w-[120px] truncate">
                    {{ $userName }}
                </span>
                <span class="text-[9px] font-extrabold uppercase px-1 py-0.2 rounded border {{ $roleBadgeColor }}">
                    {{ $userRole }} {{ $userBranch && $userBranch !== 'all' ? '('.$userBranch.')' : '' }}
                </span>
            </div>
            <svg class="w-3.5 h-3.5 text-gray-400 group-hover:text-white transition-transform duration-200 group-open:rotate-180" 
                 fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </summary>

        <!-- Dropdown Menu Card -->
        <div class="absolute right-0 mt-2 w-64 bg-white rounded-2xl border border-gray-200 shadow-2xl py-2 z-50 text-gray-900 font-sans">
            
            <!-- User Info Header -->
            <div class="px-4 py-3 border-b border-gray-100 bg-gray-50/50 rounded-t-2xl">
                <div class="flex items-center justify-between gap-2">
                    <p class="text-xs font-bold text-[#111111] truncate">{{ $userName }}</p>
                    <span class="text-[10px] font-extrabold uppercase px-1.5 py-0.5 rounded bg-gray-200 text-gray-700">
                        {{ $userRole }}
                    </span>
                </div>
                <p class="text-[11px] text-gray-500 truncate font-normal mt-0.5">{{ $userEmail }}</p>
                @if($userBranch)
                    <p class="text-[10px] text-[#F97316] font-semibold mt-1 flex items-center gap-1">
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        </svg>
                        Cabang: {{ $userBranch === 'all' ? 'Semua Cabang' : $userBranch }}
                    </p>
                @endif
            </div>



            <!-- Profile Settings Link -->
            <a href="{{ route('profile.index') }}" 
               class="flex items-center gap-2.5 px-4 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 hover:text-[#F97316] transition-colors">
                <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <span>Profile Settings</span>
            </a>

            <!-- Direct Logout Form Button -->
            <form method="POST" action="{{ route('logout') }}" class="border-t border-gray-100 mt-1 pt-1">
                @csrf
                <button type="submit" 
                        class="w-full flex items-center gap-2.5 px-4 py-2 text-xs font-semibold text-rose-600 hover:bg-rose-50 transition-colors text-left cursor-pointer">
                    <svg class="w-4 h-4 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    <span>Logout</span>
                </button>
            </form>
        </div>
    </details>
@endauth
