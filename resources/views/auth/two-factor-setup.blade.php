<x-app-layout>
    <div class="mx-auto max-w-xl">
        <h1 class="mb-2 text-lg font-semibold">Two-factor authentication</h1>

        @if ($required && ! $confirmed)
            <p class="mb-6 rounded border border-amber-200 bg-amber-50 p-3 text-sm text-amber-900">
                Your administrative role requires two-factor authentication.
                Please complete enrollment before continuing.
            </p>
        @endif

        @if (session('status') === 'two-factor-authentication-enabled')
            <p class="mb-4 rounded border border-stone-200 bg-stone-50 p-3 text-sm">
                Scan the code below, then confirm with a code from your authenticator application.
            </p>
        @elseif (session('status') === 'two-factor-authentication-confirmed')
            <p class="mb-4 rounded border border-stone-200 bg-stone-50 p-3 text-sm">
                Two-factor authentication is confirmed. Store your recovery codes somewhere safe.
            </p>
        @endif

        @if ($errors->any())
            <p class="mb-4 rounded border border-red-200 bg-red-50 p-3 text-sm text-red-800">
                {{ $errors->first() }}
            </p>
        @endif

        @if (! $enabled)
            <form method="POST" action="{{ url('user/two-factor-authentication') }}">
                @csrf
                <button type="submit"
                        class="rounded bg-stone-800 px-4 py-2 text-sm font-medium text-white hover:bg-stone-700">
                    Begin enrollment
                </button>
            </form>
        @elseif (! $confirmed)
            <div class="space-y-6">
                <div class="rounded border border-stone-200 bg-white p-4">
                    {!! auth()->user()->twoFactorQrCodeSvg() !!}
                    <p class="mt-3 text-xs text-stone-500">
                        Setup key: <span class="font-mono">{{ decrypt(auth()->user()->two_factor_secret) }}</span>
                    </p>
                </div>
                <form method="POST" action="{{ url('user/confirmed-two-factor-authentication') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label for="code" class="mb-1 block text-sm font-medium">Confirmation code</label>
                        <input id="code" name="code" type="text" inputmode="numeric" autocomplete="one-time-code" required
                               class="w-full rounded border border-stone-300 px-3 py-2 text-sm focus:border-stone-500 focus:outline-none">
                    </div>
                    <button type="submit"
                            class="rounded bg-stone-800 px-4 py-2 text-sm font-medium text-white hover:bg-stone-700">
                        Confirm enrollment
                    </button>
                </form>
            </div>
        @else
            <div class="space-y-4">
                <p class="text-sm text-stone-600">Two-factor authentication is active on your account.</p>
                <div class="rounded border border-stone-200 bg-white p-4">
                    <p class="mb-2 text-sm font-medium">Recovery codes</p>
                    <ul class="space-y-1 font-mono text-sm text-stone-700">
                        @foreach (auth()->user()->recoveryCodes() as $code)
                            <li>{{ $code }}</li>
                        @endforeach
                    </ul>
                    <p class="mt-3 text-xs text-stone-500">
                        Each code can be used once if you lose access to your authenticator application.
                    </p>
                </div>
                @if (auth()->user()->hasPermission('platform.admin.access'))
                    <a href="{{ route('admin.dashboard') }}"
                       class="inline-block rounded bg-stone-800 px-4 py-2 text-sm font-medium text-white hover:bg-stone-700">
                        Continue to administration
                    </a>
                @endif
            </div>
        @endif
    </div>
</x-app-layout>
