<x-guest-layout>
    <h1 class="mb-6 text-lg font-semibold">Choose a new password</h1>

    @if ($errors->any())
        <p class="mb-4 rounded border border-red-200 bg-red-50 p-3 text-sm text-red-800">
            {{ $errors->first() }}
        </p>
    @endif

    <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">
        <div>
            <label for="email" class="mb-1 block text-sm font-medium">Email address</label>
            <input id="email" name="email" type="email" value="{{ old('email', $request->email) }}" required
                   class="w-full rounded border border-stone-300 px-3 py-2 text-sm focus:border-stone-500 focus:outline-none">
        </div>
        <div>
            <label for="password" class="mb-1 block text-sm font-medium">New password</label>
            <input id="password" name="password" type="password" required autocomplete="new-password"
                   class="w-full rounded border border-stone-300 px-3 py-2 text-sm focus:border-stone-500 focus:outline-none">
            <p class="mt-1 text-xs text-stone-500">At least 12 characters.</p>
        </div>
        <div>
            <label for="password_confirmation" class="mb-1 block text-sm font-medium">Confirm new password</label>
            <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password"
                   class="w-full rounded border border-stone-300 px-3 py-2 text-sm focus:border-stone-500 focus:outline-none">
        </div>
        <button type="submit"
                class="w-full rounded bg-stone-800 px-4 py-2 text-sm font-medium text-white hover:bg-stone-700">
            Reset password
        </button>
    </form>
</x-guest-layout>
