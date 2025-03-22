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
                    <h5 class="card-title">Server List</h5>
                </div>
                <div class="col-md-6 d-flex justify-content-end">
                    <a href="{{ route('admin.server.create') }}" class="btn btn-primary">
                        Add Server
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
                                <th>Server Name</th>
                                <th>Hostname</th>
                                <th>Server IP</th>
                                <th>Server Username</th>
                                <th>Server Port</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($servers as $server)
                                <tr>
                                    <td>
                                        <div id="server-status-{{ $server->id }}" data-server-ip="{{ $server->ip }}">
                                            <span class="badge bg-secondary">Checking...</span>
                                        </div>
                                        {{ $server->name }}
                                    </td>
                                    <td>{{ $server->hostname }}</td>
                                    <td>{{ $server->ip }}</td>
                                    <td>{{ $server->username }}</td>
                                    <td>{{ $server->port }}</td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('admin.server.console', $server->id) }}" class="btn btn-dark btn-sm">
                                                <i class='bx bx-terminal'></i>
                                            </a>
                                            <a href="{{ route('admin.server.edit', $server->id) }}" class="btn btn-primary btn-sm">
                                                <i class="bx bx-pencil"></i>
                                            </a>
                                            {{-- Hidden input for copying --}}
                                            <input type="text" id="command-{{ $server->password }}" value="{{ $server->password }}" style="position: absolute; left: -9999px;">
                                            
                                            <button class="btn btn-sm btn-info" onclick="copyToClipboard('command-{{ $server->password }}')" title="Copy">
                                                <i class='bx bxs-key'></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger delete-server" data-server-id="{{ $server->id }}" data-server-purpose="{{ $server->name }}">
                                                <i class="bx bx-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                    
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
       // Handle delete button clicks
        document.querySelectorAll('.delete-server').forEach(button => {
                        button.addEventListener('click', function() {
                            const serverId = this.dataset.serverId;
                            const serverPurpose = this.dataset.serverPurpose;

                            showDeleteConfirmation({
                                title: 'Delete server',
                                text: `Are you sure you want to delete the server "${serverPurpose}"?`
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    // Create a form to submit DELETE request
                                    const form = document.createElement('form');
                                    form.method = 'POST';
                                    form.action =
                                        '{{ route('admin.server.delete', '') }}/' +
                                        serverId;

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
        function copyToClipboard(elementId) {
            var copyText = document.getElementById(elementId);
            copyText.select();
            copyText.setSelectionRange(0, 99999); // For mobile devices
            navigator.clipboard.writeText(copyText.value).then(function() {
                alert('Copied: ' + copyText.value);
            }).catch(function(err) {
                console.error('Failed to copy text: ', err);
            });
        }

        function checkServerStatus(ip, elementId) {
            fetch(`/server-status/${ip}`)
                .then(response => response.json())
                .then(data => {
                    let statusBadge = document.getElementById(elementId);
                    if (data.status === 'Online') {
                        statusBadge.innerHTML = '<span class="badge bg-success">Online</span>';
                    } else {
                        statusBadge.innerHTML = '<span class="badge bg-danger">Offline</span>';
                    }
                })
                .catch(error => console.error('Error checking server status:', error));
            }

            function refreshStatuses() {
                document.querySelectorAll("[data-server-ip]").forEach(element => {
                    let ip = element.getAttribute("data-server-ip");
                    checkServerStatus(ip, element.id);
                });
        }

        // Refresh status every 10 seconds
        setInterval(refreshStatuses, 1000);
        window.onload = refreshStatuses;
    </script>
    @endpush
    

</x-app-layout>