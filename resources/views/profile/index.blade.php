@extends('layouts.app')

@section('content')
<div class="bg-[#F8F8F6] min-h-screen py-10">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Header Section -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-[#111111] tracking-tight">
                    Profile Settings
                </h1>
                <p class="text-xs sm:text-sm text-gray-500 font-medium mt-1">
                    Manage your account information and preferences.
                </p>
            </div>

            <!-- Pure HTML Logout Button -->
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" 
                        class="px-4 py-2.5 bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 text-xs font-extrabold rounded-xl transition-colors cursor-pointer flex items-center justify-center gap-2 shadow-xs">
                    <svg class="w-4 h-4 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    <span>Logout Account</span>
                </button>
            </form>
        </div>

        <!-- Flash Alert Messages -->
        <x-alert />

        @if ($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-800 text-xs font-medium rounded-2xl p-4 mb-6 space-y-1">
                <p class="font-extrabold text-red-900 mb-1">Please correct the following errors:</p>
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="space-y-6">
            <!-- ------------------------------------------ -->
            <!-- SECTION A: PROFILE INFORMATION -->
            <!-- ------------------------------------------ -->
            <div class="bg-white rounded-2xl border border-gray-200/80 p-6 sm:p-8 shadow-xs">
                <div class="flex items-center gap-3 border-b border-gray-100 pb-5 mb-6">
                    <div class="w-10 h-10 rounded-xl bg-orange-50 text-[#F97316] flex items-center justify-center font-bold border border-orange-100 shrink-0">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-base sm:text-lg font-extrabold text-[#111111]">Profile Information</h2>
                        <p class="text-xs text-gray-500 font-normal">Update your account name and view assigned roles.</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('profile.update') }}" class="space-y-5">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <!-- Full Name (Editable) -->
                        <div>
                            <label for="name" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">
                                Full Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name', $profile['name']) }}"
                                   required
                                   class="w-full bg-white border border-gray-300 text-xs sm:text-sm font-semibold text-[#111111] rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-[#F97316]/40 focus:border-[#F97316] outline-none transition-all">
                        </div>

                        <!-- Email (Read-Only) -->
                        <div>
                            <label for="email" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">
                                Email Address <span class="text-gray-400 font-normal text-[11px]">(Read-only)</span>
                            </label>
                            <input type="email" 
                                   id="email" 
                                   value="{{ $profile['email'] }}" 
                                   disabled
                                   class="w-full bg-gray-50 border border-gray-200 text-xs sm:text-sm font-semibold text-gray-500 rounded-xl px-4 py-2.5 cursor-not-allowed">
                        </div>

                        <!-- Role (Read-Only) -->
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">
                                Role
                            </label>
                            <div class="w-full bg-gray-50 border border-gray-200 text-xs sm:text-sm font-semibold text-gray-700 rounded-xl px-4 py-2.5 flex items-center justify-between">
                                <span class="capitalize">{{ $profile['role'] }}</span>
                                <span class="text-[10px] uppercase font-extrabold tracking-wider px-2 py-0.5 rounded bg-gray-200 text-gray-700">
                                    {{ $profile['role'] === 'customer' ? 'Customer Account' : 'Staff' }}
                                </span>
                            </div>
                        </div>

                        <!-- Branch (Read-Only) -->
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">
                                Branch Access
                            </label>
                            <div class="w-full bg-gray-50 border border-gray-200 text-xs sm:text-sm font-semibold text-gray-700 rounded-xl px-4 py-2.5">
                                @if($profile['role'] === 'customer')
                                    Customer / All Branches
                                @elseif($profile['branch'] === 'all')
                                    All Branches (Owner)
                                @else
                                    Cabang {{ $profile['branch'] }}
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end pt-3">
                        <button type="submit" 
                                class="w-full sm:w-auto px-6 py-2.5 bg-[#F97316] hover:bg-orange-600 text-white text-xs font-extrabold rounded-xl shadow-xs transition-colors cursor-pointer flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>

            <!-- ------------------------------------------ -->
            <!-- SECTION B: ACCOUNT INFORMATION -->
            <!-- ------------------------------------------ -->
            <div class="bg-white rounded-2xl border border-gray-200/80 p-6 sm:p-8 shadow-xs">
                <div class="flex items-center gap-3 border-b border-gray-100 pb-5 mb-6">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold border border-blue-100 shrink-0">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-base sm:text-lg font-extrabold text-[#111111]">Account Information</h2>
                        <p class="text-xs text-gray-500 font-normal">Summary of user identifiers and session status.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Account ID -->
                    <div class="bg-gray-50/80 rounded-xl p-4 border border-gray-200/60">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-gray-500 block mb-1">Account ID</span>
                        <span class="text-xs sm:text-sm font-extrabold text-[#111111]">#USR-{{ str_pad($profile['user_id'], 3, '0', STR_PAD_LEFT) }}</span>
                    </div>

                    <!-- Email -->
                    <div class="bg-gray-50/80 rounded-xl p-4 border border-gray-200/60 truncate">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-gray-500 block mb-1">Email</span>
                        <span class="text-xs font-semibold text-[#111111] truncate block" title="{{ $profile['email'] }}">{{ $profile['email'] }}</span>
                    </div>

                    <!-- Account Role -->
                    <div class="bg-gray-50/80 rounded-xl p-4 border border-gray-200/60">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-gray-500 block mb-1">Account Role</span>
                        <span class="text-xs sm:text-sm font-extrabold text-[#111111] capitalize">{{ $profile['role'] }}</span>
                    </div>

                    <!-- Account Status -->
                    <div class="bg-gray-50/80 rounded-xl p-4 border border-gray-200/60 flex flex-col justify-between">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-gray-500 block mb-1">Account Status</span>
                        <div>
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-extrabold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                {{ $profile['status'] ?? 'Active' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ------------------------------------------ -->
            <!-- SECTION C: SECURITY -->
            <!-- ------------------------------------------ -->
            <div class="bg-white rounded-2xl border border-gray-200/80 p-6 sm:p-8 shadow-xs">
                <div class="flex items-center gap-3 border-b border-gray-100 pb-5 mb-6">
                    <div class="w-10 h-10 rounded-xl bg-gray-100 text-gray-800 flex items-center justify-center font-bold border border-gray-200 shrink-0">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-base sm:text-lg font-extrabold text-[#111111]">Security</h2>
                        <p class="text-xs text-gray-500 font-normal">Change your account password to keep your profile secure.</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('profile.password') }}" class="space-y-5">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                        <!-- Current Password -->
                        <div>
                            <label for="current_password" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">
                                Current Password
                            </label>
                            <input type="password" 
                                   id="current_password" 
                                   name="current_password" 
                                   placeholder="••••••••"
                                   required
                                   class="w-full bg-white border border-gray-300 text-xs sm:text-sm font-semibold text-[#111111] rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-[#111111]/20 focus:border-[#111111] outline-none transition-all">
                        </div>

                        <!-- New Password -->
                        <div>
                            <label for="new_password" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">
                                New Password
                            </label>
                            <input type="password" 
                                   id="new_password" 
                                   name="new_password" 
                                   placeholder="••••••••"
                                   required
                                   class="w-full bg-white border border-gray-300 text-xs sm:text-sm font-semibold text-[#111111] rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-[#111111]/20 focus:border-[#111111] outline-none transition-all">
                        </div>

                        <!-- Confirm Password -->
                        <div>
                            <label for="confirm_password" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">
                                Confirm Password
                            </label>
                            <input type="password" 
                                   id="confirm_password" 
                                   name="confirm_password" 
                                   placeholder="••••••••"
                                   required
                                   class="w-full bg-white border border-gray-300 text-xs sm:text-sm font-semibold text-[#111111] rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-[#111111]/20 focus:border-[#111111] outline-none transition-all">
                        </div>
                    </div>

                    <div class="flex justify-end pt-3">
                        <button type="submit" 
                                class="w-full sm:w-auto px-6 py-2.5 bg-[#111111] hover:bg-black text-white text-xs font-extrabold rounded-xl shadow-xs transition-colors cursor-pointer flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                            </svg>
                            Change Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
