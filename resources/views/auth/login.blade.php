<x-guest-layout>
    <h1 class="mb-6 text-lg font-semibold">Collaborator sign in</h1>

    @if (session('status'))
        <p class="mb-4 rounded border border-stone-200 bg-stone-50 p-3 text-sm">{{ session('status') }}</p>
    @endif

    @if ($errors->any())
        <p class="mb-4 rounded border border-red-200 bg-red-50 p-3 text-sm text-red-800">
            {{ $errors->first() }}
        </p>
    @endif

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf
        <div>
            <label for="email" class="mb-1 block text-sm font-medium">Email address</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                   class="w-full rounded border border-stone-300 px-3 py-2 text-sm focus:border-stone-500 focus:outline-none">
        </div>
        <div>
            <label for="password" class="mb-1 block text-sm font-medium">Password</label>
            <input id="password" name="password" type="password" required autocomplete="current-password"
                   class="w-full rounded border border-stone-300 px-3 py-2 text-sm focus:border-stone-500 focus:outline-none">
        </div>
        <div class="flex items-center justify-between">
            <label class="flex items-center gap-2 text-sm text-stone-600">
                <input type="checkbox" name="remember" class="rounded border-stone-300">
                Remember me
            </label>
            <a href="{{ route('password.request') }}" class="text-sm text-stone-600 underline-offset-2 hover:underline">
                Forgotten password?
            </a>
        </div>
        <button type="submit"
                class="w-full rounded bg-stone-800 px-4 py-2 text-sm font-medium text-white hover:bg-stone-700">
            Sign in
        </button>
    </form>
</x-guest-layout>
