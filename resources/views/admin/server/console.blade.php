<x-app-layout>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Server') }}
        </h2>
    </x-slot>
        <div class="card">
            <div class="card-header row align-items-center">
                <div class="col-md-6">
                    <h5 class="card-title"><span class="text-danger"><b>nO.</b></span> Terminal</h5>
                </div>
                <div class="col-md-6 d-flex justify-content-end">
                    <a href="{{ route('admin.server.index') }}" class="btn btn-primary">
                        Server List
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h4> Server Name: <span class="text-danger">{{ $server->name }}</span></h4>
                        <h4> Server IP: <span class="text-danger">{{ $server->ip }}</span></h4>                        
                    </div>
                    <div class="col-md-6">
                        <h4> Server Port: <span class="text-danger">{{ $server->port }}</span></h4>
                        <h4> Server License: 
                            <span class="text-danger">
                                @if ($server->license == 'rc')
                                    RC Licensing System
                                @elseif ($server->license == 'gb')
                                    GBLicensing System
                                @else
                                    Sysconfig Licensing System
                                @endif
                            </span>
                        </h4>
                    </div>
                </div>
            </div>
        </div>

    <div class="card mt-3">
        <div class="card-header">
            <h5 class="card-title">Terminal</h5>
        </div>
        <div class="card-body">
            @foreach ($commands as $command)
                <button class="btn btn-primary exec-command" 
                    data-server-id="{{ $server->id }}" 
                    data-command="{{ $command->full_command }}">
                    {{ $command->name }}
                </button>
            @endforeach
        </div>
        
    </div>
    

    <!-- Output Modal -->
<div id="commandOutputModal" class="modal fade" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Command Output</h5>
                <button type="button" class="close btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <pre id="commandOutput">Executing...</pre>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary close-modal" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).on('click', '.exec-command', function() {
    var serverId = $(this).data('server-id');
    var command = $(this).data('command');

    // Clear previous output
    $('#commandOutput').empty().text('Connecting...');
    
    // Show the modal
    $('#commandOutputModal').modal('show');

    // Get CSRF token
    var csrfToken = $('meta[name="csrf-token"]').attr('content');

    // Construct URL with proper parameters
    var url = new URL('{{ route('admin.server.command.exec', '') }}/' + serverId);
    url.searchParams.append('command', command);
    url.searchParams.append('_token', csrfToken);

    // Create new EventSource connection
    var source = new EventSource(url.toString());

    source.onopen = function() {
        $('#commandOutput').empty();
    };

    source.onmessage = function(event) {
        try {
            var data = JSON.parse(event.data);
            if (data.output) {
                $('#commandOutput').append(data.output);
            }
        } catch (e) {
            console.error('Failed to parse server response:', e);
            $('#commandOutput').append(event.data + '\n');
        }
    };

    source.onerror = function(event) {
        console.error("EventSource error:", event);
        $('#commandOutput').append('\nConnection closed.\n');
        source.close();
    };

    // Clean up when modal closes
    $('#commandOutputModal').on('hidden.bs.modal', function () {
        source.close();
        $('#commandOutput').empty();
    });
});

</script>
@endpush
        
</x-app-layout>