@extends('layouts.public')

@section('title', 'Login - Oweru Tech Solutions')

@section('content')
<section class="bg-black text-white py-16">
    <div class="max-w-md mx-auto px-4 sm:px-6 text-center">
        <div class="w-16 h-16 bg-yellow-600 rounded-xl flex items-center justify-center mx-auto mb-4">
            <span class="text-black font-bold text-2xl">O</span>
        </div>
        <h1 class="text-2xl font-bold mb-2">Admin Login</h1>
        <p class="text-gray-400 mb-8">Sign in to access the admin panel</p>

        <form method="POST" action="{{ route('login') }}" class="bg-gray-900 rounded-xl p-6 border border-gray-700">
            @csrf
            <div class="space-y-4">
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-300 mb-1">Email Address</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}"
                        class="w-full px-4 py-2.5 border border-gray-600 rounded-lg text-white bg-gray-800 focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500"
                        placeholder="admin@example.com" required>
                    @error('email') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-300 mb-1">Password</label>
                    <input type="password" name="password" id="password"
                        class="w-full px-4 py-2.5 border border-gray-600 rounded-lg text-white bg-gray-800 focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500"
                        placeholder="Enter your password" required>
                    @error('password') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <button type="submit" class="btn-accent w-full justify-center text-base py-3">
                    Sign In
                </button>
            </div>
        </form>

        <p class="text-gray-500 text-sm mt-6">
            Demo credentials: <code class="bg-gray-800 px-2 py-1 rounded text-yellow-600">admin@oweru.com</code> / <code class="bg-gray-800 px-2 py-1 rounded text-yellow-600">admin123</code>
        </p>
    </div>
</section>
@endsection
