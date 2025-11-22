# Settings Views

## Settings Layout

### resources/views/settings/layout.blade.php

```blade
@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50" x-data="settingsPage()">
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="lg:grid lg:grid-cols-12 lg:gap-x-5">
            <!-- Settings Navigation -->
            <aside class="py-6 px-2 sm:px-6 lg:py-0 lg:px-0 lg:col-span-3">
                <nav class="space-y-1">
                    <a href="{{ route('settings.profile') }}"
                       class="group rounded-md px-3 py-2 flex items-center text-sm font-medium {{ request()->routeIs('settings.profile') ? 'bg-gray-100 text-indigo-600' : 'text-gray-900 hover:bg-gray-50' }}">
                        <svg class="flex-shrink-0 -ml-1 mr-3 h-6 w-6 {{ request()->routeIs('settings.profile') ? 'text-indigo-600' : 'text-gray-400 group-hover:text-gray-500' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        <span class="truncate">Profile</span>
                    </a>

                    <a href="{{ route('settings.account') }}"
                       class="group rounded-md px-3 py-2 flex items-center text-sm font-medium {{ request()->routeIs('settings.account') ? 'bg-gray-100 text-indigo-600' : 'text-gray-900 hover:bg-gray-50' }}">
                        <svg class="flex-shrink-0 -ml-1 mr-3 h-6 w-6 {{ request()->routeIs('settings.account') ? 'text-indigo-600' : 'text-gray-400 group-hover:text-gray-500' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span class="truncate">Account</span>
                    </a>

                    <a href="{{ route('settings.billing') }}"
                       class="group rounded-md px-3 py-2 flex items-center text-sm font-medium {{ request()->routeIs('settings.billing') ? 'bg-gray-100 text-indigo-600' : 'text-gray-900 hover:bg-gray-50' }}">
                        <svg class="flex-shrink-0 -ml-1 mr-3 h-6 w-6 {{ request()->routeIs('settings.billing') ? 'text-indigo-600' : 'text-gray-400 group-hover:text-gray-500' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                        </svg>
                        <span class="truncate">Billing</span>
                    </a>

                    <a href="{{ route('settings.domains') }}"
                       class="group rounded-md px-3 py-2 flex items-center text-sm font-medium {{ request()->routeIs('settings.domains') ? 'bg-gray-100 text-indigo-600' : 'text-gray-900 hover:bg-gray-50' }}">
                        <svg class="flex-shrink-0 -ml-1 mr-3 h-6 w-6 {{ request()->routeIs('settings.domains') ? 'text-indigo-600' : 'text-gray-400 group-hover:text-gray-500' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                        </svg>
                        <span class="truncate">Domains</span>
                    </a>

                    <a href="{{ route('settings.notifications') }}"
                       class="group rounded-md px-3 py-2 flex items-center text-sm font-medium {{ request()->routeIs('settings.notifications') ? 'bg-gray-100 text-indigo-600' : 'text-gray-900 hover:bg-gray-50' }}">
                        <svg class="flex-shrink-0 -ml-1 mr-3 h-6 w-6 {{ request()->routeIs('settings.notifications') ? 'text-indigo-600' : 'text-gray-400 group-hover:text-gray-500' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                        <span class="truncate">Notifications</span>
                    </a>
                </nav>
            </aside>

            <!-- Main Content -->
            <div class="space-y-6 sm:px-6 lg:px-0 lg:col-span-9">
                @yield('settings-content')
            </div>
        </div>
    </div>
</div>

<script>
function settingsPage() {
    return {
        init() {
            // Initialize settings page
        }
    }
}
</script>
@endsection
```

---

## Profile Settings

### resources/views/settings/profile.blade.php

```blade
@extends('settings.layout')

@section('settings-content')
<div x-data="profileSettings()">
    <!-- Profile Information -->
    <div class="bg-white shadow sm:rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Profile Information</h3>
            <p class="mt-1 text-sm text-gray-500">Update your account's profile information and email address.</p>

            <form @submit.prevent="updateProfile" class="mt-6 space-y-6">
                <!-- Avatar Upload -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Avatar</label>
                    <div class="mt-2 flex items-center space-x-5">
                        <span class="inline-block h-20 w-20 rounded-full overflow-hidden bg-gray-100">
                            <template x-if="avatarPreview">
                                <img :src="avatarPreview" alt="Avatar preview" class="h-full w-full object-cover">
                            </template>
                            <template x-if="!avatarPreview && user.avatar">
                                <img :src="user.avatar" alt="Current avatar" class="h-full w-full object-cover">
                            </template>
                            <template x-if="!avatarPreview && !user.avatar">
                                <svg class="h-full w-full text-gray-300" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M24 20.993V24H0v-2.996A14.977 14.977 0 0112.004 15c4.904 0 9.26 2.354 11.996 5.993zM16.002 8.999a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                            </template>
                        </span>
                        <div class="flex space-x-3">
                            <label class="relative cursor-pointer bg-white py-2 px-3 border border-gray-300 rounded-md shadow-sm text-sm leading-4 font-medium text-gray-700 hover:bg-gray-50 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                                <span>Change</span>
                                <input type="file"
                                       class="sr-only"
                                       accept="image/*"
                                       @change="handleAvatarChange($event)">
                            </label>
                            <button type="button"
                                    x-show="user.avatar || avatarPreview"
                                    @click="removeAvatar"
                                    class="py-2 px-3 border border-gray-300 rounded-md shadow-sm text-sm leading-4 font-medium text-gray-700 hover:bg-gray-50">
                                Remove
                            </button>
                        </div>
                    </div>
                    <p class="mt-2 text-xs text-gray-500">JPG, PNG or GIF. Max size 2MB.</p>
                </div>

                <!-- Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                    <input type="text"
                           id="name"
                           x-model="form.name"
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                           required>
                    <template x-if="errors.name">
                        <p class="mt-1 text-sm text-red-600" x-text="errors.name"></p>
                    </template>
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email address</label>
                    <input type="email"
                           id="email"
                           x-model="form.email"
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                           required>
                    <template x-if="errors.email">
                        <p class="mt-1 text-sm text-red-600" x-text="errors.email"></p>
                    </template>
                    <template x-if="!user.email_verified_at">
                        <p class="mt-2 text-sm text-yellow-600">
                            Your email address is unverified.
                            <button type="button" @click="resendVerification" class="underline text-yellow-700 hover:text-yellow-600">
                                Click here to resend verification email.
                            </button>
                        </p>
                    </template>
                </div>

                <!-- Bio -->
                <div>
                    <label for="bio" class="block text-sm font-medium text-gray-700">Bio</label>
                    <textarea id="bio"
                              x-model="form.bio"
                              rows="3"
                              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                              placeholder="Tell us a little about yourself"></textarea>
                    <p class="mt-2 text-sm text-gray-500">Brief description for your profile.</p>
                </div>

                <!-- Website -->
                <div>
                    <label for="website" class="block text-sm font-medium text-gray-700">Website</label>
                    <div class="mt-1 flex rounded-md shadow-sm">
                        <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500 sm:text-sm">
                            https://
                        </span>
                        <input type="text"
                               id="website"
                               x-model="form.website"
                               class="flex-1 min-w-0 block w-full px-3 py-2 rounded-none rounded-r-md border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                               placeholder="www.example.com">
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit"
                            :disabled="saving"
                            class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50">
                        <svg x-show="saving" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function profileSettings() {
    return {
        user: @json(auth()->user()),
        form: {
            name: '{{ auth()->user()->name }}',
            email: '{{ auth()->user()->email }}',
            bio: '{{ auth()->user()->bio ?? '' }}',
            website: '{{ auth()->user()->website ?? '' }}'
        },
        avatarPreview: null,
        avatarFile: null,
        errors: {},
        saving: false,

        handleAvatarChange(event) {
            const file = event.target.files[0];
            if (file) {
                if (file.size > 2 * 1024 * 1024) {
                    alert('File size must be less than 2MB');
                    return;
                }
                this.avatarFile = file;
                this.avatarPreview = URL.createObjectURL(file);
            }
        },

        removeAvatar() {
            this.avatarPreview = null;
            this.avatarFile = null;
            this.form.remove_avatar = true;
        },

        async updateProfile() {
            this.saving = true;
            this.errors = {};

            try {
                const formData = new FormData();
                formData.append('name', this.form.name);
                formData.append('email', this.form.email);
                formData.append('bio', this.form.bio);
                formData.append('website', this.form.website);

                if (this.avatarFile) {
                    formData.append('avatar', this.avatarFile);
                }
                if (this.form.remove_avatar) {
                    formData.append('remove_avatar', '1');
                }

                const response = await fetch('/settings/profile', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                const data = await response.json();

                if (!response.ok) {
                    this.errors = data.errors || {};
                    return;
                }

                this.user = data.user;
                this.avatarPreview = null;
                this.avatarFile = null;

                // Show success notification
                window.dispatchEvent(new CustomEvent('notify', {
                    detail: { message: 'Profile updated successfully', type: 'success' }
                }));
            } catch (error) {
                console.error('Error updating profile:', error);
            } finally {
                this.saving = false;
            }
        },

        async resendVerification() {
            try {
                await fetch('/email/verification-notification', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });

                window.dispatchEvent(new CustomEvent('notify', {
                    detail: { message: 'Verification email sent', type: 'success' }
                }));
            } catch (error) {
                console.error('Error sending verification:', error);
            }
        }
    }
}
</script>
@endsection
```

---

## Account Settings

### resources/views/settings/account.blade.php

```blade
@extends('settings.layout')

@section('settings-content')
<div x-data="accountSettings()" class="space-y-6">
    <!-- Update Password -->
    <div class="bg-white shadow sm:rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Update Password</h3>
            <p class="mt-1 text-sm text-gray-500">Ensure your account is using a long, random password to stay secure.</p>

            <form @submit.prevent="updatePassword" class="mt-6 space-y-6">
                <div>
                    <label for="current_password" class="block text-sm font-medium text-gray-700">Current Password</label>
                    <input type="password"
                           id="current_password"
                           x-model="passwordForm.current_password"
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                           required>
                    <template x-if="passwordErrors.current_password">
                        <p class="mt-1 text-sm text-red-600" x-text="passwordErrors.current_password"></p>
                    </template>
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">New Password</label>
                    <input type="password"
                           id="password"
                           x-model="passwordForm.password"
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                           required>
                    <template x-if="passwordErrors.password">
                        <p class="mt-1 text-sm text-red-600" x-text="passwordErrors.password"></p>
                    </template>
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirm Password</label>
                    <input type="password"
                           id="password_confirmation"
                           x-model="passwordForm.password_confirmation"
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                           required>
                </div>

                <div class="flex justify-end">
                    <button type="submit"
                            :disabled="savingPassword"
                            class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50">
                        <svg x-show="savingPassword" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Update Password
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Two Factor Authentication -->
    <div class="bg-white shadow sm:rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Two Factor Authentication</h3>
            <p class="mt-1 text-sm text-gray-500">Add additional security to your account using two factor authentication.</p>

            <div class="mt-5">
                <template x-if="!twoFactorEnabled">
                    <div>
                        <p class="text-sm text-gray-600 mb-4">
                            When two factor authentication is enabled, you will be prompted for a secure, random token during authentication.
                        </p>
                        <button @click="enableTwoFactor"
                                :disabled="enablingTwoFactor"
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700">
                            Enable Two Factor Auth
                        </button>
                    </div>
                </template>

                <template x-if="twoFactorEnabled">
                    <div>
                        <p class="text-sm text-green-600 mb-4">
                            Two factor authentication is currently enabled.
                        </p>
                        <button @click="showDisableModal = true"
                                class="inline-flex items-center px-4 py-2 border border-red-300 text-sm font-medium rounded-md text-red-700 bg-white hover:bg-red-50">
                            Disable Two Factor Auth
                        </button>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <!-- Browser Sessions -->
    <div class="bg-white shadow sm:rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Browser Sessions</h3>
            <p class="mt-1 text-sm text-gray-500">Manage and log out your active sessions on other browsers and devices.</p>

            <div class="mt-5 space-y-4">
                <template x-for="session in sessions" :key="session.id">
                    <div class="flex items-center justify-between py-3 border-b border-gray-200">
                        <div class="flex items-center">
                            <svg x-show="session.device === 'desktop'" class="h-8 w-8 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            <svg x-show="session.device === 'mobile'" class="h-8 w-8 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-900" x-text="session.browser + ' on ' + session.platform"></p>
                                <p class="text-sm text-gray-500">
                                    <span x-text="session.ip_address"></span>
                                    <span x-show="session.is_current" class="text-green-500 font-semibold"> - This device</span>
                                    <span x-show="!session.is_current"> - Last active <span x-text="session.last_active"></span></span>
                                </p>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <div class="mt-5">
                <button @click="showLogoutOtherSessionsModal = true"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    Log Out Other Browser Sessions
                </button>
            </div>
        </div>
    </div>

    <!-- Logout Other Sessions Modal -->
    <div x-show="showLogoutOtherSessionsModal"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         aria-labelledby="modal-title"
         role="dialog"
         aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="showLogoutOtherSessionsModal"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
                 @click="showLogoutOtherSessionsModal = false"></div>

            <div x-show="showLogoutOtherSessionsModal"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                <div>
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Log Out Other Browser Sessions</h3>
                    <p class="mt-2 text-sm text-gray-500">Please enter your password to confirm you would like to log out of your other browser sessions.</p>

                    <div class="mt-4">
                        <input type="password"
                               x-model="logoutPassword"
                               placeholder="Password"
                               class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>
                </div>
                <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3">
                    <button @click="showLogoutOtherSessionsModal = false"
                            class="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:text-sm">
                        Cancel
                    </button>
                    <button @click="logoutOtherSessions"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 sm:text-sm">
                        Log Out Sessions
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function accountSettings() {
    return {
        passwordForm: {
            current_password: '',
            password: '',
            password_confirmation: ''
        },
        passwordErrors: {},
        savingPassword: false,
        twoFactorEnabled: {{ auth()->user()->two_factor_secret ? 'true' : 'false' }},
        enablingTwoFactor: false,
        showDisableModal: false,
        sessions: @json($sessions ?? []),
        showLogoutOtherSessionsModal: false,
        logoutPassword: '',

        async updatePassword() {
            this.savingPassword = true;
            this.passwordErrors = {};

            try {
                const response = await fetch('/settings/password', {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(this.passwordForm)
                });

                const data = await response.json();

                if (!response.ok) {
                    this.passwordErrors = data.errors || {};
                    return;
                }

                this.passwordForm = {
                    current_password: '',
                    password: '',
                    password_confirmation: ''
                };

                window.dispatchEvent(new CustomEvent('notify', {
                    detail: { message: 'Password updated successfully', type: 'success' }
                }));
            } catch (error) {
                console.error('Error updating password:', error);
            } finally {
                this.savingPassword = false;
            }
        },

        async enableTwoFactor() {
            this.enablingTwoFactor = true;
            // Implementation for enabling 2FA
            this.enablingTwoFactor = false;
        },

        async logoutOtherSessions() {
            try {
                const response = await fetch('/settings/sessions', {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ password: this.logoutPassword })
                });

                if (response.ok) {
                    this.showLogoutOtherSessionsModal = false;
                    this.logoutPassword = '';
                    window.location.reload();
                }
            } catch (error) {
                console.error('Error logging out sessions:', error);
            }
        }
    }
}
</script>
@endsection
```

---

## Billing Settings

### resources/views/settings/billing.blade.php

```blade
@extends('settings.layout')

@section('settings-content')
<div x-data="billingSettings()" class="space-y-6">
    <!-- Current Plan -->
    <div class="bg-white shadow sm:rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Subscription Plan</h3>

            <div class="mt-4 flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-900" x-text="currentPlan.name"></p>
                    <p class="text-sm text-gray-500">
                        <span x-text="'$' + currentPlan.price + '/' + currentPlan.interval"></span>
                    </p>
                </div>
                <span :class="currentPlan.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'"
                      class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
                    <span x-text="currentPlan.status"></span>
                </span>
            </div>

            <div class="mt-4">
                <h4 class="text-sm font-medium text-gray-900">Plan Features:</h4>
                <ul class="mt-2 space-y-2">
                    <template x-for="feature in currentPlan.features" :key="feature">
                        <li class="flex items-center text-sm text-gray-600">
                            <svg class="flex-shrink-0 h-5 w-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                            <span x-text="feature"></span>
                        </li>
                    </template>
                </ul>
            </div>

            <div class="mt-5 flex space-x-3">
                <button @click="showPlanModal = true"
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700">
                    Change Plan
                </button>
                <button x-show="currentPlan.status === 'active'"
                        @click="showCancelModal = true"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    Cancel Subscription
                </button>
            </div>
        </div>
    </div>

    <!-- Payment Method -->
    <div class="bg-white shadow sm:rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Payment Method</h3>

            <div class="mt-4">
                <template x-if="paymentMethod">
                    <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <img x-show="paymentMethod.brand === 'visa'" src="/images/cards/visa.svg" class="h-8" alt="Visa">
                                <img x-show="paymentMethod.brand === 'mastercard'" src="/images/cards/mastercard.svg" class="h-8" alt="Mastercard">
                                <img x-show="paymentMethod.brand === 'amex'" src="/images/cards/amex.svg" class="h-8" alt="American Express">
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-900">
                                    <span x-text="paymentMethod.brand.charAt(0).toUpperCase() + paymentMethod.brand.slice(1)"></span>
                                    ending in <span x-text="paymentMethod.last4"></span>
                                </p>
                                <p class="text-sm text-gray-500">
                                    Expires <span x-text="paymentMethod.exp_month + '/' + paymentMethod.exp_year"></span>
                                </p>
                            </div>
                        </div>
                        <button @click="showUpdatePaymentModal = true"
                                class="text-sm text-indigo-600 hover:text-indigo-500">
                            Update
                        </button>
                    </div>
                </template>

                <template x-if="!paymentMethod">
                    <div class="text-center py-6">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                        </svg>
                        <p class="mt-2 text-sm text-gray-500">No payment method on file</p>
                        <button @click="showUpdatePaymentModal = true"
                                class="mt-3 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700">
                            Add Payment Method
                        </button>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <!-- Billing History -->
    <div class="bg-white shadow sm:rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Billing History</h3>

            <div class="mt-4">
                <template x-if="invoices.length > 0">
                    <div class="overflow-hidden">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                    <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                    <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-3 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Invoice</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <template x-for="invoice in invoices" :key="invoice.id">
                                    <tr>
                                        <td class="px-3 py-4 whitespace-nowrap text-sm text-gray-900" x-text="invoice.date"></td>
                                        <td class="px-3 py-4 whitespace-nowrap text-sm text-gray-500" x-text="invoice.description"></td>
                                        <td class="px-3 py-4 whitespace-nowrap text-sm text-gray-900" x-text="'$' + invoice.amount"></td>
                                        <td class="px-3 py-4 whitespace-nowrap">
                                            <span :class="invoice.status === 'paid' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                                                  class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                                  x-text="invoice.status">
                                            </span>
                                        </td>
                                        <td class="px-3 py-4 whitespace-nowrap text-right text-sm">
                                            <a :href="invoice.download_url" class="text-indigo-600 hover:text-indigo-500">Download</a>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </template>

                <template x-if="invoices.length === 0">
                    <p class="text-sm text-gray-500 text-center py-4">No billing history available</p>
                </template>
            </div>
        </div>
    </div>

    <!-- Plan Selection Modal -->
    <div x-show="showPlanModal"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showPlanModal = false"></div>

            <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Select a Plan</h3>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <template x-for="plan in availablePlans" :key="plan.id">
                        <div :class="selectedPlan === plan.id ? 'border-indigo-500 ring-2 ring-indigo-500' : 'border-gray-200'"
                             @click="selectedPlan = plan.id"
                             class="relative bg-white border rounded-lg shadow-sm p-4 cursor-pointer">
                            <div class="flex flex-col">
                                <h4 class="text-lg font-medium text-gray-900" x-text="plan.name"></h4>
                                <p class="mt-1">
                                    <span class="text-2xl font-bold text-gray-900" x-text="'$' + plan.price"></span>
                                    <span class="text-sm text-gray-500" x-text="'/' + plan.interval"></span>
                                </p>
                                <ul class="mt-4 space-y-2">
                                    <template x-for="feature in plan.features" :key="feature">
                                        <li class="flex items-center text-sm text-gray-600">
                                            <svg class="h-4 w-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                            </svg>
                                            <span x-text="feature"></span>
                                        </li>
                                    </template>
                                </ul>
                            </div>
                        </div>
                    </template>
                </div>

                <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3">
                    <button @click="showPlanModal = false"
                            class="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:text-sm">
                        Cancel
                    </button>
                    <button @click="changePlan"
                            :disabled="!selectedPlan || changingPlan"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 sm:text-sm disabled:opacity-50">
                        <span x-show="changingPlan">Processing...</span>
                        <span x-show="!changingPlan">Confirm Change</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Update Payment Method Modal -->
    <div x-show="showUpdatePaymentModal"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showUpdatePaymentModal = false"></div>

            <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Update Payment Method</h3>

                <div id="card-element" class="p-3 border border-gray-300 rounded-md">
                    <!-- Stripe Card Element will be mounted here -->
                </div>
                <p id="card-errors" class="mt-2 text-sm text-red-600"></p>

                <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3">
                    <button @click="showUpdatePaymentModal = false"
                            class="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:text-sm">
                        Cancel
                    </button>
                    <button @click="updatePaymentMethod"
                            :disabled="updatingPayment"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 sm:text-sm disabled:opacity-50">
                        <span x-show="updatingPayment">Processing...</span>
                        <span x-show="!updatingPayment">Save Card</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://js.stripe.com/v3/"></script>
<script>
function billingSettings() {
    return {
        currentPlan: @json($currentPlan ?? [
            'name' => 'Free',
            'price' => 0,
            'interval' => 'month',
            'status' => 'active',
            'features' => ['5 Landing Pages', 'Basic Analytics', 'Email Support']
        ]),
        paymentMethod: @json($paymentMethod ?? null),
        invoices: @json($invoices ?? []),
        availablePlans: @json($availablePlans ?? []),
        showPlanModal: false,
        showCancelModal: false,
        showUpdatePaymentModal: false,
        selectedPlan: null,
        changingPlan: false,
        updatingPayment: false,
        stripe: null,
        cardElement: null,

        init() {
            this.stripe = Stripe('{{ config('services.stripe.key') }}');

            this.$watch('showUpdatePaymentModal', (value) => {
                if (value) {
                    this.$nextTick(() => {
                        const elements = this.stripe.elements();
                        this.cardElement = elements.create('card');
                        this.cardElement.mount('#card-element');
                    });
                }
            });
        },

        async changePlan() {
            this.changingPlan = true;

            try {
                const response = await fetch('/settings/billing/plan', {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ plan: this.selectedPlan })
                });

                if (response.ok) {
                    window.location.reload();
                }
            } catch (error) {
                console.error('Error changing plan:', error);
            } finally {
                this.changingPlan = false;
            }
        },

        async updatePaymentMethod() {
            this.updatingPayment = true;

            try {
                const { setupIntent, error } = await this.stripe.confirmCardSetup(
                    '{{ $clientSecret ?? '' }}',
                    {
                        payment_method: {
                            card: this.cardElement
                        }
                    }
                );

                if (error) {
                    document.getElementById('card-errors').textContent = error.message;
                    return;
                }

                const response = await fetch('/settings/billing/payment-method', {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ payment_method: setupIntent.payment_method })
                });

                if (response.ok) {
                    window.location.reload();
                }
            } catch (error) {
                console.error('Error updating payment method:', error);
            } finally {
                this.updatingPayment = false;
            }
        }
    }
}
</script>
@endsection
```

---

## Domain Settings

### resources/views/settings/domains.blade.php

```blade
@extends('settings.layout')

@section('settings-content')
<div x-data="domainSettings()" class="space-y-6">
    <!-- Custom Domains -->
    <div class="bg-white shadow sm:rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Custom Domains</h3>
                    <p class="mt-1 text-sm text-gray-500">Connect your own domains to your landing pages.</p>
                </div>
                <button @click="showAddDomainModal = true"
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700">
                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Add Domain
                </button>
            </div>

            <div class="mt-6">
                <template x-if="domains.length > 0">
                    <div class="space-y-4">
                        <template x-for="domain in domains" :key="domain.id">
                            <div class="border border-gray-200 rounded-lg p-4">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <div :class="domain.verified ? 'bg-green-100' : 'bg-yellow-100'"
                                             class="flex-shrink-0 h-10 w-10 rounded-full flex items-center justify-center">
                                            <svg x-show="domain.verified" class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                            <svg x-show="!domain.verified" class="h-6 w-6 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </div>
                                        <div class="ml-4">
                                            <p class="text-sm font-medium text-gray-900" x-text="domain.domain"></p>
                                            <p class="text-sm text-gray-500">
                                                <span x-show="domain.verified" class="text-green-600">Verified</span>
                                                <span x-show="!domain.verified" class="text-yellow-600">Pending verification</span>
                                            </p>
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <button x-show="!domain.verified"
                                                @click="showDnsInstructions(domain)"
                                                class="text-sm text-indigo-600 hover:text-indigo-500">
                                            View DNS Settings
                                        </button>
                                        <button @click="verifyDomain(domain)"
                                                :disabled="domain.verifying"
                                                class="text-sm text-indigo-600 hover:text-indigo-500">
                                            <span x-show="domain.verifying">Verifying...</span>
                                            <span x-show="!domain.verifying">Verify</span>
                                        </button>
                                        <button @click="removeDomain(domain)"
                                                class="text-sm text-red-600 hover:text-red-500">
                                            Remove
                                        </button>
                                    </div>
                                </div>

                                <!-- DNS Instructions -->
                                <div x-show="domain.showDns" class="mt-4 bg-gray-50 rounded-lg p-4">
                                    <h4 class="text-sm font-medium text-gray-900 mb-2">DNS Configuration</h4>
                                    <p class="text-sm text-gray-500 mb-3">Add the following DNS records to verify your domain:</p>

                                    <div class="space-y-3">
                                        <div class="bg-white p-3 rounded border">
                                            <p class="text-xs font-medium text-gray-500">CNAME Record</p>
                                            <div class="flex items-center justify-between mt-1">
                                                <code class="text-sm text-gray-900" x-text="domain.domain + ' -> ' + domain.cname_target"></code>
                                                <button @click="copyToClipboard(domain.cname_target)" class="text-indigo-600 hover:text-indigo-500">
                                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>

                                        <div class="bg-white p-3 rounded border">
                                            <p class="text-xs font-medium text-gray-500">TXT Record (Verification)</p>
                                            <div class="flex items-center justify-between mt-1">
                                                <code class="text-sm text-gray-900" x-text="'_verify.' + domain.domain + ' -> ' + domain.verification_token"></code>
                                                <button @click="copyToClipboard(domain.verification_token)" class="text-indigo-600 hover:text-indigo-500">
                                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>

                <template x-if="domains.length === 0">
                    <div class="text-center py-6">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                        </svg>
                        <p class="mt-2 text-sm text-gray-500">No custom domains configured</p>
                        <p class="text-sm text-gray-400">Add a domain to use your own URL for landing pages</p>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <!-- Add Domain Modal -->
    <div x-show="showAddDomainModal"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showAddDomainModal = false"></div>

            <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Add Custom Domain</h3>

                <div>
                    <label for="domain" class="block text-sm font-medium text-gray-700">Domain Name</label>
                    <input type="text"
                           id="domain"
                           x-model="newDomain"
                           placeholder="example.com"
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    <p class="mt-2 text-sm text-gray-500">Enter your domain without http:// or https://</p>
                </div>

                <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3">
                    <button @click="showAddDomainModal = false"
                            class="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:text-sm">
                        Cancel
                    </button>
                    <button @click="addDomain"
                            :disabled="!newDomain || addingDomain"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 sm:text-sm disabled:opacity-50">
                        <span x-show="addingDomain">Adding...</span>
                        <span x-show="!addingDomain">Add Domain</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function domainSettings() {
    return {
        domains: @json($domains ?? []),
        showAddDomainModal: false,
        newDomain: '',
        addingDomain: false,

        showDnsInstructions(domain) {
            domain.showDns = !domain.showDns;
        },

        async addDomain() {
            this.addingDomain = true;

            try {
                const response = await fetch('/settings/domains', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ domain: this.newDomain })
                });

                const data = await response.json();

                if (response.ok) {
                    this.domains.push(data.domain);
                    this.showAddDomainModal = false;
                    this.newDomain = '';
                }
            } catch (error) {
                console.error('Error adding domain:', error);
            } finally {
                this.addingDomain = false;
            }
        },

        async verifyDomain(domain) {
            domain.verifying = true;

            try {
                const response = await fetch(`/settings/domains/${domain.id}/verify`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (response.ok && data.verified) {
                    domain.verified = true;
                    window.dispatchEvent(new CustomEvent('notify', {
                        detail: { message: 'Domain verified successfully', type: 'success' }
                    }));
                } else {
                    window.dispatchEvent(new CustomEvent('notify', {
                        detail: { message: 'Domain verification failed. Please check your DNS settings.', type: 'error' }
                    }));
                }
            } catch (error) {
                console.error('Error verifying domain:', error);
            } finally {
                domain.verifying = false;
            }
        },

        async removeDomain(domain) {
            if (!confirm('Are you sure you want to remove this domain?')) return;

            try {
                const response = await fetch(`/settings/domains/${domain.id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });

                if (response.ok) {
                    this.domains = this.domains.filter(d => d.id !== domain.id);
                }
            } catch (error) {
                console.error('Error removing domain:', error);
            }
        },

        copyToClipboard(text) {
            navigator.clipboard.writeText(text);
            window.dispatchEvent(new CustomEvent('notify', {
                detail: { message: 'Copied to clipboard', type: 'success' }
            }));
        }
    }
}
</script>
@endsection
```

---

## Notification Settings

### resources/views/settings/notifications.blade.php

```blade
@extends('settings.layout')

@section('settings-content')
<div x-data="notificationSettings()">
    <div class="bg-white shadow sm:rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Notification Preferences</h3>
            <p class="mt-1 text-sm text-gray-500">Manage how and when you receive notifications.</p>

            <form @submit.prevent="saveNotifications" class="mt-6 space-y-6">
                <!-- Email Notifications -->
                <div>
                    <h4 class="text-sm font-medium text-gray-900">Email Notifications</h4>
                    <div class="mt-4 space-y-4">
                        <div class="flex items-start">
                            <div class="flex items-center h-5">
                                <input type="checkbox"
                                       id="email_marketing"
                                       x-model="settings.email_marketing"
                                       class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                            </div>
                            <div class="ml-3 text-sm">
                                <label for="email_marketing" class="font-medium text-gray-700">Marketing emails</label>
                                <p class="text-gray-500">Receive emails about new features, tips, and promotions.</p>
                            </div>
                        </div>

                        <div class="flex items-start">
                            <div class="flex items-center h-5">
                                <input type="checkbox"
                                       id="email_security"
                                       x-model="settings.email_security"
                                       class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                            </div>
                            <div class="ml-3 text-sm">
                                <label for="email_security" class="font-medium text-gray-700">Security alerts</label>
                                <p class="text-gray-500">Get notified about security events like new sign-ins.</p>
                            </div>
                        </div>

                        <div class="flex items-start">
                            <div class="flex items-center h-5">
                                <input type="checkbox"
                                       id="email_updates"
                                       x-model="settings.email_updates"
                                       class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                            </div>
                            <div class="ml-3 text-sm">
                                <label for="email_updates" class="font-medium text-gray-700">Product updates</label>
                                <p class="text-gray-500">Receive updates about product changes and improvements.</p>
                            </div>
                        </div>

                        <div class="flex items-start">
                            <div class="flex items-center h-5">
                                <input type="checkbox"
                                       id="email_billing"
                                       x-model="settings.email_billing"
                                       class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                            </div>
                            <div class="ml-3 text-sm">
                                <label for="email_billing" class="font-medium text-gray-700">Billing notifications</label>
                                <p class="text-gray-500">Get notified about billing events like successful payments or failed charges.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Page Notifications -->
                <div class="pt-6 border-t border-gray-200">
                    <h4 class="text-sm font-medium text-gray-900">Landing Page Notifications</h4>
                    <div class="mt-4 space-y-4">
                        <div class="flex items-start">
                            <div class="flex items-center h-5">
                                <input type="checkbox"
                                       id="notify_form_submission"
                                       x-model="settings.notify_form_submission"
                                       class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                            </div>
                            <div class="ml-3 text-sm">
                                <label for="notify_form_submission" class="font-medium text-gray-700">Form submissions</label>
                                <p class="text-gray-500">Get notified when someone submits a form on your landing page.</p>
                            </div>
                        </div>

                        <div class="flex items-start">
                            <div class="flex items-center h-5">
                                <input type="checkbox"
                                       id="notify_page_published"
                                       x-model="settings.notify_page_published"
                                       class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                            </div>
                            <div class="ml-3 text-sm">
                                <label for="notify_page_published" class="font-medium text-gray-700">Page published</label>
                                <p class="text-gray-500">Get notified when your landing page is published.</p>
                            </div>
                        </div>

                        <div class="flex items-start">
                            <div class="flex items-center h-5">
                                <input type="checkbox"
                                       id="notify_analytics_weekly"
                                       x-model="settings.notify_analytics_weekly"
                                       class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                            </div>
                            <div class="ml-3 text-sm">
                                <label for="notify_analytics_weekly" class="font-medium text-gray-700">Weekly analytics report</label>
                                <p class="text-gray-500">Receive a weekly summary of your landing page performance.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Push Notifications -->
                <div class="pt-6 border-t border-gray-200">
                    <h4 class="text-sm font-medium text-gray-900">Push Notifications</h4>
                    <div class="mt-4 space-y-4">
                        <div class="flex items-start">
                            <div class="flex items-center h-5">
                                <input type="checkbox"
                                       id="push_enabled"
                                       x-model="settings.push_enabled"
                                       @change="togglePushNotifications"
                                       class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                            </div>
                            <div class="ml-3 text-sm">
                                <label for="push_enabled" class="font-medium text-gray-700">Enable push notifications</label>
                                <p class="text-gray-500">Receive real-time notifications in your browser.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end pt-4">
                    <button type="submit"
                            :disabled="saving"
                            class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50">
                        <svg x-show="saving" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Save Preferences
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function notificationSettings() {
    return {
        settings: @json($notificationSettings ?? [
            'email_marketing' => false,
            'email_security' => true,
            'email_updates' => true,
            'email_billing' => true,
            'notify_form_submission' => true,
            'notify_page_published' => true,
            'notify_analytics_weekly' => false,
            'push_enabled' => false
        ]),
        saving: false,

        async togglePushNotifications() {
            if (this.settings.push_enabled) {
                try {
                    const permission = await Notification.requestPermission();
                    if (permission !== 'granted') {
                        this.settings.push_enabled = false;
                        alert('Push notifications require browser permission.');
                    }
                } catch (error) {
                    this.settings.push_enabled = false;
                    console.error('Error requesting notification permission:', error);
                }
            }
        },

        async saveNotifications() {
            this.saving = true;

            try {
                const response = await fetch('/settings/notifications', {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(this.settings)
                });

                if (response.ok) {
                    window.dispatchEvent(new CustomEvent('notify', {
                        detail: { message: 'Notification preferences saved', type: 'success' }
                    }));
                }
            } catch (error) {
                console.error('Error saving notifications:', error);
            } finally {
                this.saving = false;
            }
        }
    }
}
</script>
@endsection
```

---

## Danger Zone

### resources/views/settings/danger-zone.blade.php

```blade
@extends('settings.layout')

@section('settings-content')
<div x-data="dangerZone()" class="space-y-6">
    <!-- Export Data -->
    <div class="bg-white shadow sm:rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Export Your Data</h3>
            <p class="mt-1 text-sm text-gray-500">Download a copy of all your data including landing pages, analytics, and account information.</p>

            <div class="mt-5">
                <button @click="exportData"
                        :disabled="exporting"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    <svg x-show="!exporting" class="-ml-1 mr-2 h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    <svg x-show="exporting" class="animate-spin -ml-1 mr-2 h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span x-show="!exporting">Export Data</span>
                    <span x-show="exporting">Preparing Export...</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Deactivate Account -->
    <div class="bg-white shadow sm:rounded-lg border-l-4 border-yellow-400">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Deactivate Account</h3>
            <p class="mt-1 text-sm text-gray-500">Temporarily disable your account. Your data will be preserved and you can reactivate at any time.</p>

            <div class="mt-5">
                <button @click="showDeactivateModal = true"
                        class="inline-flex items-center px-4 py-2 border border-yellow-300 text-sm font-medium rounded-md text-yellow-700 bg-yellow-50 hover:bg-yellow-100">
                    Deactivate Account
                </button>
            </div>
        </div>
    </div>

    <!-- Delete Account -->
    <div class="bg-white shadow sm:rounded-lg border-l-4 border-red-400">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg leading-6 font-medium text-red-600">Delete Account</h3>
            <p class="mt-1 text-sm text-gray-500">Permanently delete your account and all associated data. This action cannot be undone.</p>

            <div class="mt-5">
                <button @click="showDeleteModal = true"
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700">
                    Delete Account
                </button>
            </div>
        </div>
    </div>

    <!-- Deactivate Modal -->
    <div x-show="showDeactivateModal"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showDeactivateModal = false"></div>

            <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-yellow-100 sm:mx-0 sm:h-10 sm:w-10">
                        <svg class="h-6 w-6 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Deactivate Account</h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500">Are you sure you want to deactivate your account? Your landing pages will be unpublished but your data will be saved.</p>
                        </div>
                        <div class="mt-4">
                            <label for="deactivate_password" class="block text-sm font-medium text-gray-700">Confirm with password</label>
                            <input type="password"
                                   id="deactivate_password"
                                   x-model="deactivatePassword"
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-yellow-500 focus:border-yellow-500 sm:text-sm">
                        </div>
                    </div>
                </div>
                <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                    <button @click="deactivateAccount"
                            :disabled="!deactivatePassword || deactivating"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-yellow-600 text-base font-medium text-white hover:bg-yellow-700 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50">
                        <span x-show="deactivating">Processing...</span>
                        <span x-show="!deactivating">Deactivate</span>
                    </button>
                    <button @click="showDeactivateModal = false"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Modal -->
    <div x-show="showDeleteModal"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showDeleteModal = false"></div>

            <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                        <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-lg leading-6 font-medium text-red-600">Delete Account</h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500">This action is permanent and cannot be undone. All your data will be permanently deleted including:</p>
                            <ul class="mt-2 text-sm text-gray-500 list-disc list-inside">
                                <li>All landing pages</li>
                                <li>Analytics data</li>
                                <li>Form submissions</li>
                                <li>Custom domains</li>
                                <li>Account settings</li>
                            </ul>
                        </div>
                        <div class="mt-4">
                            <label for="delete_confirmation" class="block text-sm font-medium text-gray-700">
                                Type <span class="font-mono text-red-600">DELETE</span> to confirm
                            </label>
                            <input type="text"
                                   id="delete_confirmation"
                                   x-model="deleteConfirmation"
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm"
                                   placeholder="DELETE">
                        </div>
                        <div class="mt-4">
                            <label for="delete_password" class="block text-sm font-medium text-gray-700">Password</label>
                            <input type="password"
                                   id="delete_password"
                                   x-model="deletePassword"
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm">
                        </div>
                    </div>
                </div>
                <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                    <button @click="deleteAccount"
                            :disabled="deleteConfirmation !== 'DELETE' || !deletePassword || deleting"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50">
                        <span x-show="deleting">Deleting...</span>
                        <span x-show="!deleting">Delete Account</span>
                    </button>
                    <button @click="showDeleteModal = false"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function dangerZone() {
    return {
        exporting: false,
        showDeactivateModal: false,
        showDeleteModal: false,
        deactivatePassword: '',
        deleteConfirmation: '',
        deletePassword: '',
        deactivating: false,
        deleting: false,

        async exportData() {
            this.exporting = true;

            try {
                const response = await fetch('/settings/export', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });

                if (response.ok) {
                    const blob = await response.blob();
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = 'account-export.zip';
                    document.body.appendChild(a);
                    a.click();
                    window.URL.revokeObjectURL(url);
                    a.remove();
                }
            } catch (error) {
                console.error('Error exporting data:', error);
            } finally {
                this.exporting = false;
            }
        },

        async deactivateAccount() {
            this.deactivating = true;

            try {
                const response = await fetch('/settings/deactivate', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ password: this.deactivatePassword })
                });

                if (response.ok) {
                    window.location.href = '/';
                }
            } catch (error) {
                console.error('Error deactivating account:', error);
            } finally {
                this.deactivating = false;
            }
        },

        async deleteAccount() {
            this.deleting = true;

            try {
                const response = await fetch('/settings/account', {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ password: this.deletePassword })
                });

                if (response.ok) {
                    window.location.href = '/';
                }
            } catch (error) {
                console.error('Error deleting account:', error);
            } finally {
                this.deleting = false;
            }
        }
    }
}
</script>
@endsection
```
