<section class="card">
    <header class="card-header">
        <h5 class="text-lg font-medium text-gray-900">
            {{ __('Update Password') }}
        </h5>
        <p class="mt-1 text-sm text-gray-600">
            {{ __("Change your account's password.") }}
        </p>
    </header>

    <div class="card-body">
        <form method="POST" action="{{ route('password.update') }}" class="space-y-6">
            @csrf
            @method('put')

            <!-- Current Password -->
            <div>
                <label for="update_password_current_password" class="form-label">{{ __('Current Password') }}</label>
                <input id="update_password_current_password" name="current_password" type="password" class="form-control mt-1 block w-full" autocomplete="current-password" required/>
                {{-- @if ($errors->has('current_password'))
                    <div class="text-danger mt-2">{{ $errors->first('current_password') }}</div>
                @endif --}}
            </div>

            <!-- New Password -->
            <div>
                <label for="update_password_password" class="form-label">{{ __('New Password') }}</label>
                <input id="update_password_password" name="password" type="password" class="form-control mt-1 block w-full" autocomplete="new-password" required/>
                @if ($errors->has('password'))
                    <div class="text-danger mt-2">{{ $errors->first('password') }}</div>
                @endif
            </div>

            <!-- Confirm New Password -->
            <div class="mb-3">
                <label for="update_password_password_confirmation" class="form-label">{{ __('Confirm Password') }}</label>
                <input id="update_password_password_confirmation" name="password_confirmation" type="password" class="form-control mt-1 block w-full" autocomplete="new-password" required/>
                @if ($errors->has('password_confirmation'))
                    <div class="text-danger mt-2">{{ $errors->first('password_confirmation') }}</div>
                @endif
            </div>

            <!-- Save Button -->
            <div class="d-flex gap-4">
                <button type="submit" class="btn btn-primary">{{ __('Save changes') }}</button>
            </div>
        </form>
    </div>
</section>
