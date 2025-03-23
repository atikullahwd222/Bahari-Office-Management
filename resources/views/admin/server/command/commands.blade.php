<x-app-layout>
<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('Server Commands') }}
    </h2>
</x-slot>
    @if (session('verify') === 'command-updated')
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
                        <h5 class="card-title">WHM Server Commands</h5>
                    </div>
                </div>
            </div>

            <div class="col-md-6 d-flex justify-content-end gap-3">
                <a href="{{ route('admin.server.commands.create') }}" class="btn btn-primary align-self-center">
                    Add Command
                </a>
            </div>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover">
                    <thead class="table-dark">
                        <style>
                            .table-dark th {
                                color: #fff !important;
                                text-align: center;
                            }
                        </style>
                        <tr>
                            <th>Software Name</th>
                            <th>Command</th>
                            <th>License</th>
                            <th>Copy</th>
                            @if ($user->role == "super-admin")
                                <th>Action</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($commands as $command)
                            <tr>
                                <td data-bs-toggle="tooltip" data-bs-offset="0,4" data-bs-placement="bottom" data-bs-html="true" title="{{ $command->description ?? "No comment" }}">
                                        {{ $command->name }}
                                </td>
                                <td style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 200px;">
                                    <span id="command-{{ $command->id }}" title="{{ $command->full_command }}">
                                        {{ $command->full_command }}
                                    </span>
                                </td>
                                <td class="text-center text-uppercase">
                                        {{ $command->licensing_system }} Lisencig
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-primary" onclick="copyToClipboard('command-{{ $command->id }}')" title="Copy">
                                        <i class='bx bxs-copy-alt'></i>
                                    </button>
                                </td>
                                @if ($user->role == "super-admin")
                                    <td class="text-center" >
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('admin.server.commands.edit', $command->id) }}" class="btn btn-primary btn-sm">
                                                <i class="bx bx-pencil"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-danger delete-command" data-command-id="{{ $command->id }}" data-command-purpose="{{ $command->name }}">
                                                <i class="bx bx-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>

@push('scripts')
<script>
    function copyToClipboard(elementId) {
        var text = document.getElementById(elementId).innerText;
        navigator.clipboard.writeText(text).then(function() {
            alert('Command copied to clipboard!');
        }).catch(function(err) {
            console.error('Failed to copy text: ', err);
        });
    }
    document.addEventListener('DOMContentLoaded', function() {
       // Handle delete button clicks
       document.querySelectorAll('.delete-command').forEach(button => {
                    button.addEventListener('click', function() {
                        const commandId = this.dataset.commandId;
                        const commandPurpose = this.dataset.commandPurpose;

                        showDeleteConfirmation({
                            title: 'Delete Command',
                            text: `Are you sure you want to delete the command "${commandPurpose}"?`
                        }).then((result) => {
                            if (result.isConfirmed) {
                                // Create a form to submit DELETE request
                                const form = document.createElement('form');
                                form.method = 'POST';
                                form.action =
                                    '{{ route('admin.server.commands.delete', '') }}/' +
                                    commandId;

                                // Add CSRF token
                                const csrfToken = document.createElement('input');
                                csrfToken.type = 'hidden';
                                csrfToken.name = '_token';
                                csrfToken.value = '{{ csrf_token() }}';
                                form.appendChild(csrfToken);

                                // Add method override for DELETE
                                const methodField = document.createElement('input');
                                methodField.type = 'hidden';
                                methodField.name = '_method';
                                methodField.value = 'DELETE';
                                form.appendChild(methodField);

                                // Add form to document and submit
                                document.body.appendChild(form);
                                form.submit();
                            }
                        });
                    });
                });
    });
</script>
@endpush

</x-app-layout> 