<x-guest-layout>
    <h1 class="mb-2 text-lg font-semibold">Reset your password</h1>
    <p class="mb-6 text-sm text-stone-600">
        Enter your email address and we will send a link to choose a new password.
    </p>

    @if (session('status'))
        <p class="mb-4 rounded border border-stone-200 bg-stone-50 p-3 text-sm">{{ session('status') }}</p>
    @endif

    <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
        @csrf
        <div>
            <label for="email" class="mb-1 block text-sm font-medium">Email address</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus
                   class="w-full rounded border border-stone-300 px-3 py-2 text-sm focus:border-stone-500 focus:outline-none">
        </div>
        <button type="submit"
                class="w-full rounded bg-stone-800 px-4 py-2 text-sm font-medium text-white hover:bg-stone-700">
            Send reset link
        </button>
        <p class="text-center text-sm">
            <a href="{{ route('login') }}" class="text-stone-600 underline-offset-2 hover:underline">Back to sign in</a>
        </p>
    </form>
</x-guest-layout>
