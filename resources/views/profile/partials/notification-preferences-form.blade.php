<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('Notification Preferences') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __('Manage how you receive notifications.') }}
        </p>
    </header>

    <form method="post" action="{{ route('profile.notifications.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="email_notification_preference" :value="__('Email Notifications')" />
            <select id="email_notification_preference" name="email_notification_preference" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="all" {{ old('email_notification_preference', $user->email_notification_preference) === 'all' ? 'selected' : '' }}>
                    {{ __('All') }}
                </option>
                <option value="digest" {{ old('email_notification_preference', $user->email_notification_preference) === 'digest' ? 'selected' : '' }}>
                    {{ __('Digest') }}
                </option>
                <option value="off" {{ old('email_notification_preference', $user->email_notification_preference) === 'off' ? 'selected' : '' }}>
                    {{ __('Off') }}
                </option>
            </select>
            <x-input-error class="mt-2" :messages="$errors->get('email_notification_preference')" />
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                {{ __('All: Receive every notification immediately. Digest: Receive a daily summary. Off: No email notifications.') }}
            </p>
        </div>

        <div>
            <x-input-label for="in_app_notifications" :value="__('In-App Notifications')" />
            <div class="mt-2 flex items-center">
                <input type="checkbox" id="in_app_notifications" name="in_app_notifications" value="1" {{ old('in_app_notifications', $user->in_app_notifications) ? 'checked' : '' }} class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                <label for="in_app_notifications" class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                    {{ __('Enable in-app notifications') }}
                </label>
            </div>
            <x-input-error class="mt-2" :messages="$errors->get('in_app_notifications')" />
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                {{ __('Show notifications within the application.') }}
            </p>
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'notifications-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600 dark:text-gray-400"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>
