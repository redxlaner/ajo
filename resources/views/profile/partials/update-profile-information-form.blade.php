<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('Profile Information') }}
        </h2>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __("Update your photo, personal details, and contact information.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        {{-- Avatar --}}
        <div class="flex items-center gap-4">
            <img src="{{ $user->avatarUrl() }}"
                 alt="{{ $user->name }}"
                 class="w-20 h-20 rounded-full object-cover border border-gray-200 dark:border-gray-700">
            <div class="min-w-0">
                <x-input-label for="avatar" value="Profile photo" />
                <input id="avatar" name="avatar" type="file" accept="image/jpeg,image/png,image/webp"
                       class="mt-1 block w-full text-sm text-gray-500 dark:text-gray-400">
                <p class="mt-1 text-xs text-gray-400">JPG, PNG or WebP · max 2MB</p>
                <x-input-error class="mt-2" :messages="$errors->get('avatar')" />
            </div>
        </div>

        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                          :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

                <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full"
                          :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-gray-800 dark:text-gray-200">
                        {{ __('Your email address is unverified.') }}
                        <button form="send-verification"
                                class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>
                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-green-600 dark:text-green-400">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div>
            <x-input-label for="phone" value="Phone number" />
            <x-text-input id="phone" name="phone" type="tel" class="mt-1 block w-full"
                          :value="old('phone', $user->phone)" autocomplete="tel"
                          placeholder="e.g. 08012345678" />
            <p class="mt-1 text-xs text-gray-400">You can log in with this number or your email.</p>
            <x-input-error class="mt-2" :messages="$errors->get('phone')" />
        </div>

        {{-- Personal --}}
        <div class="pt-6 border-t border-gray-200 dark:border-gray-700">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Personal details</h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                

                <div>
                    <x-input-label for="occupation" value="Occupation / profession" />
                    <x-text-input id="occupation" name="occupation" type="text" class="mt-1 block w-full"
                                  :value="old('occupation', $user->occupation)" />
                    <x-input-error class="mt-2" :messages="$errors->get('occupation')" />
                </div>

                <div>
                    <x-input-label for="date_of_birth" value="Date of birth" />
                    <x-text-input id="date_of_birth" name="date_of_birth" type="date" class="mt-1 block w-full"
                                  :value="old('date_of_birth', optional($user->date_of_birth)->format('Y-m-d'))" />
                    <x-input-error class="mt-2" :messages="$errors->get('date_of_birth')" />
                </div>

                <div>
                    <x-input-label for="marital_status" value="Marital status" />
                    <select id="marital_status" name="marital_status"
                            class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                        <option value="">Prefer not to say</option>
                        @foreach (['single' => 'Single', 'married' => 'Married', 'divorced' => 'Divorced', 'widowed' => 'Widowed', 'prefer_not' => 'Prefer not to say'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('marital_status', $user->marital_status) === $value)>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    <x-input-error class="mt-2" :messages="$errors->get('marital_status')" />
                </div>

                <div>
                    <x-input-label for="gender" value="Gender" />
                    <select id="gender" name="gender"
                            class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                        <option value="">Prefer not to say</option>
                        @foreach (['male' => 'Male', 'female' => 'Female', 'other' => 'Other', 'prefer_not' => 'Prefer not to say'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('gender', $user->gender) === $value)>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    <x-input-error class="mt-2" :messages="$errors->get('gender')" />
                </div>

                <div>
                    <x-input-label for="city" value="City" />
                    <x-text-input id="city" name="city" type="text" class="mt-1 block w-full"
                                  :value="old('city', $user->city)" />
                    <x-input-error class="mt-2" :messages="$errors->get('city')" />
                </div>

                <div>
                    <x-input-label for="state" value="State" />
                    <x-text-input id="state" name="state" type="text" class="mt-1 block w-full"
                                  :value="old('state', $user->state)" />
                    <x-input-error class="mt-2" :messages="$errors->get('state')" />
                </div>
            </div>

            <div class="mt-4">
                <x-input-label for="bio" value="About you" />
                <textarea id="bio" name="bio" rows="3" maxlength="500"
                          class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('bio', $user->bio) }}</textarea>
                <p class="mt-1 text-xs text-gray-400">Optional · visible to teammates · max 500 characters</p>
                <x-input-error class="mt-2" :messages="$errors->get('bio')" />
            </div>
        </div>

        {{-- Bank --}}
        <div class="pt-6 border-t border-gray-200 dark:border-gray-700">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-1">
                Bank account details
            </h3>
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">
                Used for payouts and disbursements. Keep these accurate.
            </p>

            <div class="space-y-4">
                <div>
                    <x-input-label for="bank_name" value="Bank name" />
                    <x-text-input id="bank_name" name="bank_name" type="text" class="mt-1 block w-full"
                                  :value="old('bank_name', $user->bank_name)" autocomplete="off" />
                    <x-input-error class="mt-2" :messages="$errors->get('bank_name')" />
                </div>
                <div>
                    <x-input-label for="account_number" value="Account number" />
                    <x-text-input id="account_number" name="account_number" type="text" class="mt-1 block w-full"
                                  :value="old('account_number', $user->account_number)" autocomplete="off" />
                    <x-input-error class="mt-2" :messages="$errors->get('account_number')" />
                </div>
                <div>
                    <x-input-label for="account_name" value="Account name" />
                    <x-text-input id="account_name" name="account_name" type="text" class="mt-1 block w-full"
                                  :value="old('account_name', $user->account_name)" autocomplete="off" />
                    <x-input-error class="mt-2" :messages="$errors->get('account_name')" />
                </div>
            </div>
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p x-data="{ show: true }"
                   x-show="show"
                   x-transition
                   x-init="setTimeout(() => show = false, 2000)"
                   class="text-sm text-gray-600 dark:text-gray-400">
                    {{ __('Saved.') }}
                </p>
            @endif
        </div>
    </form>
</section>