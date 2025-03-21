<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Create User') }}
        </h2>
    </x-slot>

    <div class="col-xl d-flex justify-content-center">
        <div class="card mb-4 col-7">
          <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Create User</h5>
            <small class="text-dark float-end text-end font-weight-bold">You logged in as a <br><b>{{ $user->role == 'admin' || $user->role == 'super-admin' ? 'Super Admin' : 'Admin' }}</b></small>
          </div>
          <div class="card-body">
            <form action="{{ route('admin.company.user.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('POST')
                <div class="mb-3">
                    <label class="form-label" for="first-name">First Name</label>
                    <div class="input-group input-group-merge">
                    <span id="first-name" class="input-group-text"><i class="bx bx-user"></i></span>
                    <input type="text" class="form-control" id="first-name" placeholder="John" aria-label="John" name="first_name" aria-describedby="first-name"/>
                    </div>
                    @if ($errors->has('last_name'))
                        <div class="text-danger mt-2">{{ $errors->first('first_name') }}</div>
                    @endif
                </div>

                <div class="mb-3">
                    <label class="form-label" for="last-name">Last Name</label>
                    <div class="input-group input-group-merge">
                    <span id="last-name" class="input-group-text"><i class="bx bx-user"></i></span>
                    <input type="text" class="form-control" id="last-name" placeholder="Doe" aria-label="Doe" name="last_name" aria-describedby="last-name"/>
                    </div>
                    @if ($errors->has('last_name'))
                        <div class="text-danger mt-2">{{ $errors->first('last_name') }}</div>
                    @endif
                </div>

                <div class="mb-3">
                    <label class="form-label" for="email">Email</label>
                    <div class="input-group input-group-merge">
                    <span id="email" class="input-group-text"><i class="bx bx-envelope"></i></span>
                    <input type="email" class="form-control" id="email" placeholder="john.doe@example.com" aria-label="john.doe@example.com" name="email" aria-describedby="email"/>
                    </div>
                    @if ($errors->has('email'))
                        <div class="text-danger mt-2">{{ $errors->first('email') }}</div>
                    @endif
                </div>

                <div class="mb-3">
                    <label class="form-label" for="phone">Phone (with country code)</label>
                    <div class="input-group input-group-merge">
                    <span id="phone" class="input-group-text"><i class="bx bx-phone"></i></span>
                    <input type="text" class="form-control" id="phone" placeholder="+8801726708442" aria-label="+8801726708442" name="phone" maxlength="14" aria-describedby="phone"/>
                    </div>
                    @if ($errors->has('phone'))
                        <div class="text-danger mt-2">{{ $errors->first('phone') }}</div>
                    @endif
                </div>

                <div class="mb-3">
                    <label class="form-label" for="address">Address</label>
                    <div class="input-group input-group-merge">
                    <span id="address" class="input-group-text"><i class="bx bx-map"></i></span>
                    <input type="text" class="form-control" id="address" placeholder="123 Main St, Anytown, USA" aria-label="123 Main St, Anytown, USA" name="address" aria-describedby="address"/>
                    </div>
                    @if ($errors->has('address'))
                        <div class="text-danger mt-2">{{ $errors->first('address') }}</div>
                    @endif
                </div>

                <div class="mb-3">
                    <label class="form-label" for="city">City</label>
                    <div class="input-group input-group-merge">
                    <span id="city" class="input-group-text"><i class="bx bx-map"></i></span>
                    <input type="text" class="form-control" id="city" placeholder="Anytown" aria-label="Anytown" name="city" aria-describedby="city"/>
                    </div>
                    @if ($errors->has('city'))
                        <div class="text-danger mt-2">{{ $errors->first('city') }}</div>
                    @endif
                </div>

                <div class="mb-3">
                    <label class="form-label" for="state">State</label>
                    <div class="input-group input-group-merge">
                    <span id="state" class="input-group-text"><i class="bx bx-map"></i></span>
                    <input type="text" class="form-control" id="state" placeholder="Anytown" aria-label="Anytown" name="state" aria-describedby="state"/>
                    </div>
                    @if ($errors->has('state'))
                        <div class="text-danger mt-2">{{ $errors->first('state') }}</div>
                    @endif
                </div>

                <div class="mb-3">
                    <label class="form-label" for="role">Role</label>
                    <div class="input-group input-group-merge">
                      <span id="role" class="input-group-text"><i class="bx bx-user"></i></span>
                      <select class="form-select" id="role" aria-label="Role" name="role" aria-describedby="role">
                        <option value="user" selected>User</option>
                        <option value="admin">Admin</option>
                      </select>
                    </div>
                    @if ($errors->has('role'))
                        <div class="text-danger mt-2">{{ $errors->first('role') }}</div>
                    @endif
                </div>

                <div class="mb-3">
                    <label class="form-label" for="status">Status</label>
                    <div class="input-group input-group-merge">
                      <span id="status" class="input-group-text"><i class="bx bx-user"></i></span>
                      <select class="form-select" id="status" aria-label="Status" name="status" aria-describedby="status">
                        <option value="active" selected>Active</option>
                        <option value="inactive">Inactive</option>
                      </select>
                    </div>
                    @if ($errors->has('status'))
                        <div class="text-danger mt-2">{{ $errors->first('status') }}</div>
                    @endif
                </div>

                @if($user->role == 'super-admin')
                <div class="mb-3">
                    <label class="form-label" for="company">Company</label>
                    <div class="input-group input-group-merge">
                    <span id="company" class="input-group-text"><i class="bx bx-building"></i></span>
                    <select class="form-control" id="company" aria-label="Company" name="company_uid" aria-describedby="company">
                        @foreach ($companies as $company)
                            <option value="{{ $company->company_uid }}">{{ $company->company_name }}</option>
                        @endforeach
                    </select>
                    </div>
                    @if ($errors->has('company_uid'))
                        <div class="text-danger mt-2">{{ $errors->first('company_uid') }}</div>
                    @endif
                </div>
                @else
                    <input type="hidden" name="company_uid" value="{{ $user->company_uid }}">
                @endif

                <div class="mb-3">
                    <label class="form-label" for="password">Password</label>
                    <div class="input-group input-group-merge">
                    <span id="password" class="input-group-text"><i class="bx bx-lock"></i></span>
                    <input type="password" class="form-control" id="password" placeholder="Password" aria-label="Password" name="password" aria-describedby="password"/>
                    </div>
                    @if ($errors->has('password'))
                        <div class="text-danger mt-2">{{ $errors->first('password') }}</div>
                    @endif
                </div>

                <div class="d-flex align-items-start align-items-sm-center gap-4 mb-3">
                    <img src="{{ asset('assets/img/avatars/default.png') }}" alt="user-avatar"
                        class="d-block rounded" height="100" width="100" id="uploadedAvatar" />
                        <div class="button-wrapper">
                            <label for="upload" class="btn btn-primary me-2 mb-4" tabindex="0">
                                <span class="d-none d-sm-block">Upload new photo</span>
                                <i class="bx bx-upload d-block d-sm-none"></i>
                                <input type="file" id="upload" name="profile_photo" class="account-file-input" hidden accept="image/png, image/jpeg" />
                            </label>
                            <button type="button" class="account-image-reset mb-4" style="border: none; background: none;">
                                <i class="bx bx-reset d-block d-sm-none"></i>
                                <span class="d-none d-sm-block">Reset</span>
                            </button>
                            <p class="text-muted mb-0">Allowed JPG, GIF or PNG. <b class="text-danger">(Recommended ratio: 1:1)</b></p>
                        </div>
                        @if ($errors->has('profile_photo'))
                            <div class="text-danger mt-2">{{ $errors->first('profile_photo') }}</div>
                        @endif
                    </div>
                </div>


                <button type="submit" class="btn btn-primary">Submit</button>

            </form>
          </div>
        </div>
      </div>


    @section('scripts')
        <script src="{{ asset('assets/js/pages-account-settings-account.js') }}"></script>
    @endsection



</x-app-layout>
