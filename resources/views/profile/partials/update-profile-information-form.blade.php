<section class="card">
    <header class="card-header">
        <div class="error pb-3">
            @if (session('verify') === 'profile-updated')
                <div class="alert alert-{{session('status')}} alert-dismissible text-dark" role="alert">
                    <b>{{ session('message') }}</b>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
        </div>
        <h5 class="text-lg font-medium text-gray-900">
            {{ __('Profile Details') }}
        </h5>
        <p class="mt-1 text-sm text-gray-600">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    <!-- Account Section -->
    <div class="card-body">
        <div class="d-flex align-items-start align-items-sm-center gap-4">
          <img
            src="{{ asset($user->profile_photo) }}"
            alt="user-avatar"
            class="d-block rounded"
            height="100"
            width="100"
            id="uploadedAvatar"
          />
          <form action="{{ route('profile.update.photo') }}" method="post" enctype="multipart/form-data">
            @csrf
            @method('POST')
                <div class="button-wrapper">
                    <label for="upload" class="btn btn-primary me-2 mb-4" tabindex="0">
                    <span class="d-none d-sm-block">Upload new photo</span>
                    <i class="bx bx-upload d-block d-sm-none"></i>
                    <input
                        type="file"
                        id="upload"
                        name="profile_photo"
                        class="account-file-input"
                        hidden
                        accept="image/png, image/jpeg"
                    />
                    </label>

                    <button type="submit" class="btn btn-success mb-4">
                        <i class="bx bx-reset d-block d-sm-none"></i>
                        <span class="d-none d-sm-block">Save</span>
                    </button>

                    <button type="button" class="account-image-reset mb-4" style="border: none; background: none;">
                        <i class="bx bx-reset d-block d-sm-none"></i>
                        <span class="d-none d-sm-block">Reset</span>
                    </button>

                    <p class="text-muted mb-0">Allowed JPG, GIF or PNG. Max size of 800K</p>
                </div>
          </form>
        </div>
      </div>
    <hr class="my-0" />

    <!-- Form Section -->
    <div class="card-body">
        <form method="POST" action="{{ route('profile.update') }}" class="space-y-6" enctype="multipart/form-data">
            @csrf
            @method('POST')

            <!-- Row 1: First Name and Last Name -->
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="first_name" class="form-label">{{ __('First Name') }}</label>
                    <input id="first_name" name="first_name" type="text" class="form-control mt-1" value="{{ old('first_name', $user->first_name) }}" required autofocus autocomplete="name" />
                    @if ($errors->has('first_name'))
                        <div class="text-danger mt-2">{{ $errors->first('first_name') }}</div>
                    @endif
                </div>

                <div class="col-md-6">
                    <label for="last_name" class="form-label">{{ __('Last Name') }}</label>
                    <input id="last_name" name="last_name" type="text" class="form-control mt-1" value="{{ old('last_name', $user->last_name) }}" required />
                    @if ($errors->has('last_name'))
                        <div class="text-danger mt-2">{{ $errors->first('last_name') }}</div>
                    @endif
                </div>
            </div>

            <!-- Row 2: Email and Phone Number -->
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="email" class="form-label">{{ __('E-mail') }}</label>
                    <input id="email" name="email" type="email" class="form-control mt-1" value="{{ old('email', $user->email) }}" required autocomplete="username" />
                    @if ($errors->has('email'))
                        <div class="text-danger mt-2">{{ $errors->first('email') }}</div>
                    @endif

                    @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                        <div>
                            <p class="text-sm mt-2 text-gray-800">
                                {{ __('Your email address is unverified.') }}
                                <button form="send-verification" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md">
                                    {{ __('Click here to re-send the verification email.') }}
                                </button>
                            </p>

                            @if (session('status') === 'verification-link-sent')
                                <p class="mt-2 font-medium text-sm text-green-600">
                                    {{ __('A new verification link has been sent to your email address.') }}
                                </p>
                            @endif
                        </div>
                    @endif
                </div>

                <div class="col-md-6">
                    <label for="phone" class="form-label">{{ __('Phone Number') }}</label>
                    <div class="input-group">
                        <input id="phone" name="phone" type="text" class="form-control mt-1" placeholder="202 555 0111" value="{{ old('phone', $user->phone) }}" />
                    </div>
                    @if ($errors->has('phone'))
                        <div class="text-danger mt-2">{{ $errors->first('phone') }}</div>
                    @endif
                </div>
            </div>

            <!-- Row 3: Address -->
            <div class="row mb-3">
                <div class="col-md-12">
                    <label for="address" class="form-label">{{ __('Address') }}</label>
                    <input id="address" name="address" type="text" class="form-control mt-1" placeholder="address" value="{{ old('address', $user->address) }}" />
                    @if ($errors->has('address'))
                        <div class="text-danger mt-2">{{ $errors->first('address') }}</div>
                    @endif
                </div>
            </div>

            <!-- Row 4: state and Zip Code -->
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="city" class="form-label">{{ __('City') }}</label>
                    <input id="city" name="city" type="text" class="form-control mt-1" placeholder="Dhaka" value="{{ old('city', $user->city) }}" />
                    @if ($errors->has('city'))
                        <div class="text-danger mt-2">{{ $errors->first('city') }}</div>
                    @endif
                </div>

                <div class="col-md-6">
                    <label for="state" class="form-label">{{ __('State') }}</label>
                    <input id="state" name="state" type="text" class="form-control mt-1" placeholder="Mohammadpur" value="{{ old('state', $user->state) }}" />
                    @if ($errors->has('state'))
                        <div class="text-danger mt-2">{{ $errors->first('state') }}</div>
                    @endif
                </div>
            </div>

            <!-- Save Button -->
            <div class="d-flex gap-4">
                <button type="submit" class="btn btn-primary">{{ __('Save changes') }}</button>
            </div>
        </form>
    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', function (e) {
        // Profile photo preview functionality
        (function () {
        let logoImage = document.getElementById('uploadedAvatar');
        const logoInput = document.querySelector('.account-file-input'),
            logoReset = document.querySelector('.account-image-reset');

        if (logoImage && logoInput && logoReset) {
            const resetLogoImage = logoImage.src;
            logoInput.onchange = () => {
                if (logoInput.files[0]) {
                    logoImage.src = window.URL.createObjectURL(logoInput.files[0]);
                }
            };
            logoReset.onclick = () => {
                logoInput.value = '';
                logoImage.src = resetLogoImage;
            };
        }
    })();
    });
</script>
