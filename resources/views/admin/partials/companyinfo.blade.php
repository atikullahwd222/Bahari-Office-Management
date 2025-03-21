<!-- Form Section -->
<div class="card">
    <div class="card-header">
        <h1 class="card-title">Update Company Info</h1>
    </div>
<div class="card-body">


    @if (session('verify') === 'company-info-updated')
        <div class="alert alert-{{ session('status') }} alert-dismissible text-dark"
            role="alert">
            <b>{{ session('message') }}</b>
            <button type="button" class="btn-close" data-bs-dismiss="alert"
                aria-label="Close"></button>
        </div>
    @endif


    <form method="POST" action="{{ route('admin.company-info.update', $company->id) }}"
        class="space-y-6" enctype="multipart/form-data">
        @csrf
        @method('POST')

        <!-- Row 1: First Name and Last Name -->
        <div class="row mb-3">
            <div class="col-md-6">
                <label for="company_name" class="form-label">{{ __('Company Name') }}</label>
                <input id="company_name" name="company_name" type="text"
                    class="form-control mt-1"
                    value="{{ old('company_name', $company->company_name) }}" required
                    autocomplete="name" />
                @if ($errors->has('company_name'))
                    <div class="text-danger mt-2">{{ $errors->first('company_name') }}</div>
                @endif
            </div>

            <div class="col-md-6">
                <label for="company_email" class="form-label">{{ __('Company Email') }}</label>
                <input id="company_email" name="company_email" type="text" class="form-control mt-1"
                    value="{{ old('company_email', $company->company_email) }}" required />
                @if ($errors->has('company_email'))
                    <div class="text-danger mt-2">{{ $errors->first('company_email') }}</div>
                @endif
            </div>
        </div>

        <!-- Row 2: Email and Phone Number -->
        <div class="row mb-3">
            <div class="col-md-12">
                <label for="company_phone" class="form-label">{{ __('Company Phone') }}</label>
                <input id="company_phone" name="company_phone" type="text" class="form-control mt-1"
                    value="{{ old('company_phone', $company->company_phone) }}" required
                    autocomplete="company_phone" />

                @if ($errors->has('company_phone'))
                    <div class="text-danger mt-2">{{ $errors->first('company_phone') }}</div>
                @endif
            </div>
        </div>

        <!-- Row 3: Address -->
        <div class="row mb-3">
            <div class="col-md-12">
                <label for="company_address" class="form-label">{{ __('Company Address') }}</label>
                <input id="company_address" name="company_address" type="text"
                    class="form-control mt-1" placeholder=""
                    value="{{ old('company_address', $company->company_address) }}" />
                @if ($errors->has('company_address'))
                    <div class="text-danger mt-2">{{ $errors->first('company_address') }}</div>
                @endif
            </div>
        </div>

        <!-- Row 4: state and Zip Code -->
        <div class="row mb-3">
            <div class="col-md-6">
                <label for="company_city" class="form-label">{{ __('City') }}</label>
                <input id="company_city" name="company_city" type="text"
                    class="form-control mt-1" placeholder="Dhaka"
                    value="{{ old('company_city', $company->company_city) }}" />
                @if ($errors->has('company_city'))
                    <div class="text-danger mt-2">{{ $errors->first('company_city') }}</div>
                @endif
            </div>

            <div class="col-md-6">
                <label for="company_state" class="form-label">{{ __('State') }}</label>
                <input id="company_state" name="company_state" type="text"
                    class="form-control mt-1" placeholder="Mohammadpur"
                    value="{{ old('company_state', $company->company_state) }}" />
                @if ($errors->has('company_state'))
                    <div class="text-danger mt-2">{{ $errors->first('company_state') }}</div>
                @endif
            </div>
        </div>

        <!-- Row 5: Website and Facebook url -->
        <div class="row mb-3">
            <div class="col-md-6">
                <label for="company_website" class="form-label">{{ __('Website') }}</label>
                <input id="company_website" name="company_website" type="text"
                    class="form-control mt-1" placeholder="https://www.example.com"
                    value="{{ old('company_website', $company->company_website) }}" />
                @if ($errors->has('company_website'))
                    <div class="text-danger mt-2">{{ $errors->first('company_website') }}</div>
                @endif
            </div>

            <div class="col-md-6">
                <label for="company_facebook" class="form-label">{{ __('Facebook') }}</label>
                <input id="company_facebook" name="company_facebook" type="text"
                    class="form-control mt-1" placeholder="https://www.facebook.com/example"
                    value="{{ old('company_facebook', $company->company_facebook) }}" />
                @if ($errors->has('company_facebook'))
                    <div class="text-danger mt-2">{{ $errors->first('company_facebook') }}</div>
                @endif
            </div>
        </div>

        <!-- Save Button -->
        <div class="d-flex gap-4">
            <button type="submit" class="btn btn-primary">{{ __('Save changes') }}</button>
        </div>
    </form>
</div>