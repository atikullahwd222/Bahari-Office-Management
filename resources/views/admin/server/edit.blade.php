<x-app-layout>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Server') }}
        </h2>
    </x-slot>

    <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
        <div class="card">
            <div class="card-header row align-items-center">
                <div class="col-md-6">
                    <h5 class="card-title">Add new server</h5>
                </div>
                <div class="col-md-6 d-flex justify-content-end">
                    <a href="{{ route('admin.server.create') }}" class="btn btn-primary">
                        Add Server
                    </a>
                </div>
            </div>
            <div class="card-body">

                <form action="{{ route('admin.server.update', $server->id) }}" method="post">
                    @csrf
                    @method('POST')
                    <div class="mb-3">
                        <label for="name" class="form-label">Server Name</label>
                        <input type="text" class="form-control" id="name" name="name" value="{{ $server->name }}">
                        @if ($errors->has('name'))
                            <div class="text-danger mt-2">{{ $errors->first('name') }}</div>
                        @endif
                    </div>
                    <div class="mb-3">
                        <label for="hostname" class="form-label">Hostname</label>
                        <input type="text" class="form-control" id="hostname" name="hostname" value="{{ $server->hostname }}">
                        @if ($errors->has('hostname'))
                            <div class="text-danger mt-2">{{ $errors->first('hostname') }}</div>
                        @endif
                    </div>
                    <div class="mb-3">
                        <label for="ip" class="form-label">Server IP</label>
                        <input type="text" class="form-control" id="ip" name="ip" value="{{ $server->ip }}">
                        @if ($errors->has('ip'))
                            <div class="text-danger mt-2">{{ $errors->first('ip') }}</div>
                        @endif                        
                    </div>
                    <div class="mb-3">
                        <label for="username" class="form-label">Server Username</label>
                        <input type="text" class="form-control" id="username" name="username" value="{{ $server->username }}">
                        @if ($errors->has('username'))
                            <div class="text-danger mt-2">{{ $errors->first('username') }}</div>
                        @endif
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Server Password</label>
                        <input type="password" class="form-control" id="password" name="password" value="{{ $server->password }}">
                        @if ($errors->has('password'))
                            <div class="text-danger mt-2">{{ $errors->first('password') }}</div>
                        @endif
                    </div>
                    <div class="mb-3">
                        <label for="port" class="form-label">Server Port</label>
                        <input type="text" class="form-control" id="port" name="port" value="{{ $server->port }}">
                        @if ($errors->has('port'))
                            <div class="text-danger mt-2">{{ $errors->first('port') }}</div>
                        @endif
                    </div>
                    <div class="mb-3">
                        <label for="control_panel" class="form-label">Control panel</label>
                        <select class="form-select" id="control_panel" name="control_panel">
                            <option value="whm-cpanel" {{ $server->control_panel == 'whm-cpanel' ? 'selected' : '' }}>WHM/Cpanel</option>
                            <option value="other" disabled>Comming soon</option>
                        </select>
                        @if ($errors->has('control_panel'))
                            <div class="text-danger mt-2">{{ $errors->first('control_panel') }}</div>
                        @endif
                    </div>
                    <div class="mb-3">
                        <label for="license" class="form-label">License Provider</label>
                        <select class="form-select" id="license" name="license">
                            <option value="rc" {{ $server->license == 'rc' ? 'selected' : '' }}>RC Licensing System</option>
                            <option value="gb" {{ $server->license == 'gb' ? 'selected' : '' }}>GB Licensing System</option>
                            <option value="sysconfig" {{ $server->license == 'sysconfig' ? 'selected' : '' }}>Sysconfig Licensing System</option>
                            <option value="none" {{ $server->license == 'none' ? 'selected' : '' }}>Licensing System not required</option>
                        </select>
                        @if ($errors->has('license'))
                            <div class="text-danger mt-2">{{ $errors->first('license') }}</div>
                        @endif
                    </div>
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="1" {{ $server->status == 1 ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ $server->status == 0 ? 'selected' : '' }}>Inactive</option>
                        </select>
                        @if ($errors->has('status'))
                            <div class="text-danger mt-2">{{ $errors->first('status') }}</div>
                        @endif
                    </div>
                    <input type="hidden" name="company_uid" value="{{ Auth::user()->company_uid }}">
                    <button type="submit" class="btn btn-primary">Submit</button>
                </form>

            </div>
        </div>
    </div>
    
        
</x-app-layout>