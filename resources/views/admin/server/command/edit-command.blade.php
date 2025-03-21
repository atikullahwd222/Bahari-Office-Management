<x-app-layout>
<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('Server Commands') }}
    </h2>
</x-slot>
    @if (session('verify') === 'profile-updated')
        <div class="alert alert-{{ session('status') }} alert-dismissible text-dark" role="alert">
            <b>{{ session('message') }}</b>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">

        <div class="row">
            <!-- Header Section with a 6 Column width -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Edit WHM Server Commands</h5>
                    </div>
                </div>
            </div>

            <div class="col-md-6 d-flex justify-content-end gap-3">
                <a href="{{ route('admin.server.commands') }}" class="btn btn-primary align-self-center">
                    Go Back
                </a>
            </div>
        </div>

        <div class="card-body">

            <form method="POST" action="{{ route('admin.server.commands.update', $command->id) }}">
                @csrf
                <div class="mb-3">
                    <label for="name" class="form-label">Software Name</label>
                    <input type="text" class="form-control" id="name" name="name" value="{{ $command->name }}">
                    @if ($errors->has('name'))
                        <div class="text-danger mt-2">{{ $errors->first('name') }}</div>
                    @endif
                </div>

                <div class="mb-3">
                    <label for="full_command" class="form-label">Command</label>
                    <input type="text" class="form-control" id="full_command" name="full_command" value="{{ $command->full_command }}">
                    @if ($errors->has('full_command'))
                        <div class="text-danger mt-2">{{ $errors->first('full_command') }}</div>
                    @endif
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" id="description" name="description" >{{ $command->description }}</textarea>
                    @if ($errors->has('description'))
                        <div class="text-danger mt-2">{{ $errors->first('description') }}</div>
                    @endif
                </div>

                <div class="mb-3">
                    <label for="licensing_system" class="form-label">Licensing System</label>
                    <select class="form-select" id="licensing_system" name="licensing_system" >
                        <option value="rc" {{ $command->licensing_system == 'rc' ? 'selected' : '' }}>RC Licening System</option>
                        <option value="gb" {{ $command->licensing_system == 'gb' ? 'selected' : '' }}>GB Licening System</option>
                        <option value="sysconfig" {{ $command->licensing_system == 'sysconfig' ? 'selected' : '' }}>Sysconfig Licening System</option>
                    </select>

                    @if ($errors->has('licensing_system'))
                        <div class="text-danger mt-2">{{ $errors->first('licensing_system') }}</div>
                    @endif
                </div>
                
                <div class="mb-3">
                    <label for="os" class="form-label">Operating system</label>
                    <select class="form-select" id="os" name="os" >
                        <option value="almalinux" {{ $command->os == 'almalinux' ? 'selected' : '' }} >Almalinux</option>
                        <option value="ubuntu" {{ $command->os == 'ubuntu' ? 'selected' : '' }}>Ubuntu</option>
                        <option value="any" {{ $command->os == 'any' ? 'selected' : '' }}>Support in any OS</option>
                    </select>
                    @if ($errors->has('os'))
                        <div class="text-danger mt-2">{{ $errors->first('os') }}</div>
                    @endif
                </div>

                <button type="submit" class="btn btn-primary">Submit</button>
            </form>

        </div>
    </div>
    @section('scripts')
    
    @endsection
</x-app-layout> 