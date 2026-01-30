@extends('layouts.app')

@section('title', 'Register - Proj Mgr')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-indigo-50 to-purple-100 flex items-center justify-center p-4">
    <div class="max-w-md w-full">
        <!-- Header -->
        <div class="text-center mb-8">
            <div class="w-16 h-16 bg-gradient-to-br from-indigo-600 to-purple-600 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <span class="text-white font-bold text-xl">TM</span>
            </div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Create Account</h1>
            <p class="text-gray-600">Join Proj Mgr and start managing projects</p>
        </div>

        <!-- Register Form -->
        <div class="bg-white rounded-2xl shadow-xl p-8">
            <form action="{{ route('register') }}" method="POST" class="space-y-6">
                @csrf
                
                <!-- General Error -->
                @if($errors->any() && !$errors->has('name') && !$errors->has('email') && !$errors->has('password'))
                    <div class="p-4 bg-red-50 border border-red-200 rounded-lg flex items-center space-x-3">
                        <i class="fas fa-exclamation-circle text-red-600 flex-shrink-0"></i>
                        <p class="text-red-700 text-sm">{{ $errors->first() }}</p>
                    </div>
                @endif

                @if(session('error'))
                    <div class="p-4 bg-red-50 border border-red-200 rounded-lg flex items-center space-x-3">
                        <i class="fas fa-exclamation-circle text-red-600 flex-shrink-0"></i>
                        <p class="text-red-700 text-sm">{{ session('error') }}</p>
                    </div>
                @endif

                <!-- Name Field -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                        Full Name
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-user text-gray-400"></i>
                        </div>
                        <input
                            type="text"
                            id="name"
                            name="name"
                            value="{{ old('name') }}"
                            class="w-full pl-10 pr-4 py-3 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors @error('name') border-red-300 bg-red-50 @else border-gray-300 @enderror"
                            placeholder="Enter your full name"
                            required
                        >
                    </div>
                    @error('name')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email Field -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                        Email Address
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-envelope text-gray-400"></i>
                        </div>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            value="{{ old('email') }}"
                            class="w-full pl-10 pr-4 py-3 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors @error('email') border-red-300 bg-red-50 @else border-gray-300 @enderror"
                            placeholder="Enter your email"
                            required
                        >
                    </div>
                    @error('email')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Role Field -->
                <div>
                    <label for="role" class="block text-sm font-medium text-gray-700 mb-2">
                        Role
                    </label>
                    <select
                        id="role"
                        name="role"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                    >
                        <option value="developer" {{ old('role') == 'developer' ? 'selected' : '' }}>Developer</option>
                        <option value="designer" {{ old('role') == 'designer' ? 'selected' : '' }}>Designer</option>
                        <option value="manager" {{ old('role') == 'manager' ? 'selected' : '' }}>Manager</option>
                    </select>
                </div>

                <!-- Password Field -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                        Password
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-lock text-gray-400"></i>
                        </div>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="w-full pl-10 pr-12 py-3 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors @error('password') border-red-300 bg-red-50 @else border-gray-300 @enderror"
                            placeholder="Create a strong password"
                            required
                            oninput="checkPasswordStrength()"
                        >
                        <button
                            type="button"
                            onclick="togglePasswordField('password', 'togglePasswordIcon')"
                            class="absolute inset-y-0 right-0 pr-3 flex items-center"
                        >
                            <i id="togglePasswordIcon" class="fas fa-eye text-gray-400 hover:text-gray-600"></i>
                        </button>
                    </div>
                    
                    <!-- Password Strength Indicator -->
                    <div id="passwordStrength" class="mt-2 hidden">
                        <div class="flex items-center space-x-2">
                            <div class="flex-1 bg-gray-200 rounded-full h-2">
                                <div id="strengthBar" class="h-2 rounded-full transition-all duration-300"></div>
                            </div>
                            <span id="strengthLabel" class="text-xs font-medium"></span>
                        </div>
                    </div>
                    
                    @error('password')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Confirm Password Field -->
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                        Confirm Password
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-lock text-gray-400"></i>
                        </div>
                        <input
                            type="password"
                            id="password_confirmation"
                            name="password_confirmation"
                            class="w-full pl-10 pr-12 py-3 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors @error('password_confirmation') border-red-300 bg-red-50 @else border-gray-300 @enderror"
                            placeholder="Confirm your password"
                            required
                            oninput="checkPasswordMatch()"
                        >
                        <button
                            type="button"
                            onclick="togglePasswordField('password_confirmation', 'toggleConfirmIcon')"
                            class="absolute inset-y-0 right-0 pr-3 flex items-center"
                        >
                            <i id="toggleConfirmIcon" class="fas fa-eye text-gray-400 hover:text-gray-600"></i>
                        </button>
                    </div>
                    
                    <!-- Password Match Indicator -->
                    <div id="passwordMatch" class="mt-2 hidden">
                        <div class="flex items-center space-x-2">
                            <i id="matchIcon" class="fas"></i>
                            <span id="matchLabel" class="text-sm"></span>
                        </div>
                    </div>
                    
                    @error('password_confirmation')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Submit Button -->
                <button
                    type="submit"
                    class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 text-white py-3 px-4 rounded-lg hover:from-indigo-700 hover:to-purple-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-all duration-200 flex items-center justify-center space-x-2"
                >
                    <span>Create Account</span>
                    <i class="fas fa-arrow-right"></i>
                </button>
            </form>

            <!-- Login Link -->
            <div class="mt-6 text-center">
                <p class="text-gray-600">
                    Already have an account?
                    <a href="{{ route('login') }}" class="text-indigo-600 hover:text-indigo-800 font-medium transition-colors">
                        Sign in here
                    </a>
                </p>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center mt-8">
            <p class="text-gray-500 text-sm">
                © {{ date('Y') }} Proj Mgr. All rights reserved.
            </p>
        </div>
    </div>
</div>

<script>
function togglePasswordField(fieldId, iconId) {
    const passwordInput = document.getElementById(fieldId);
    const toggleIcon = document.getElementById(iconId);
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleIcon.classList.remove('fa-eye');
        toggleIcon.classList.add('fa-eye-slash');
    } else {
        passwordInput.type = 'password';
        toggleIcon.classList.remove('fa-eye-slash');
        toggleIcon.classList.add('fa-eye');
    }
}

function checkPasswordStrength() {
    const password = document.getElementById('password').value;
    const strengthDiv = document.getElementById('passwordStrength');
    const strengthBar = document.getElementById('strengthBar');
    const strengthLabel = document.getElementById('strengthLabel');
    
    if (password.length === 0) {
        strengthDiv.classList.add('hidden');
        return;
    }
    
    strengthDiv.classList.remove('hidden');
    
    let strength = 0;
    let label = '';
    let color = '';
    let barWidth = '';
    
    if (password.length < 6) {
        strength = 1;
        label = 'Weak';
        color = 'text-red-600';
        barWidth = 'w-1/4';
        strengthBar.className = 'h-2 rounded-full transition-all duration-300 bg-red-500 w-1/4';
    } else if (password.length < 8) {
        strength = 2;
        label = 'Fair';
        color = 'text-yellow-600';
        barWidth = 'w-2/4';
        strengthBar.className = 'h-2 rounded-full transition-all duration-300 bg-yellow-500 w-2/4';
    } else if (password.length >= 8 && /(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/.test(password)) {
        strength = 4;
        label = 'Strong';
        color = 'text-green-600';
        barWidth = 'w-full';
        strengthBar.className = 'h-2 rounded-full transition-all duration-300 bg-green-500 w-full';
    } else {
        strength = 3;
        label = 'Good';
        color = 'text-blue-600';
        barWidth = 'w-3/4';
        strengthBar.className = 'h-2 rounded-full transition-all duration-300 bg-blue-500 w-3/4';
    }
    
    strengthLabel.textContent = label;
    strengthLabel.className = `text-xs font-medium ${color}`;
}

function checkPasswordMatch() {
    const password = document.getElementById('password').value;
    const confirmation = document.getElementById('password_confirmation').value;
    const matchDiv = document.getElementById('passwordMatch');
    const matchIcon = document.getElementById('matchIcon');
    const matchLabel = document.getElementById('matchLabel');
    
    if (confirmation.length === 0) {
        matchDiv.classList.add('hidden');
        return;
    }
    
    matchDiv.classList.remove('hidden');
    
    if (password === confirmation) {
        matchIcon.className = 'fas fa-check-circle text-green-600';
        matchLabel.textContent = 'Passwords match';
        matchLabel.className = 'text-sm text-green-600';
    } else {
        matchIcon.className = 'fas fa-exclamation-circle text-red-600';
        matchLabel.textContent = "Passwords don't match";
        matchLabel.className = 'text-sm text-red-600';
    }
}
</script>
@endsection

