<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')"/>

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div class="flex justify-center">
            <select name="region">
                <option value="eu">eu</option>
                <option value="us">us</option>
            </select>
            <x-primary-button class="ml-3">
                {{ __('Log in with Battlenet') }}
            </x-primary-button>
        </div>

    </form>
</x-guest-layout>
