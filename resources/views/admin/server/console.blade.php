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
                <button class="btn btn-primary exec-command m-2" 
                    data-server-id="{{ $server->id }}" 
                    data-command="{{ $command->full_command }}">
                    {{ $command->name }}
                </button>
            @endforeach
        </div>
        
    </div>

    <style>
        #commandOutput {
            background-color: #1e1e1e;
            color: #ffffff;
            padding: 10px;
            font-family: 'Courier New', monospace;
            height: 400px;
            overflow-y: auto;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
    </style>
    <!-- Output Modal -->
<div id="commandOutputModal" class="modal fade" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Command Output</h5>
                {{-- <button type="button" class="close btn-close" data-bs-dismiss="modal"></button> --}}
            </div>
            <div class="modal-body">
                <pre id="commandOutput">Executing...</pre>
            </div>
            <div class="modal-footer">
                <p class="text-danger">Do not close the modal or the tab! it will fucked up your server</p>
                <button type="button" class="btn btn-secondary close-modal" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).off('click', '.exec-command').on('click', '.exec-command', function() {
    var serverId = $(this).data('server-id');
    var command = $(this).data('command');
    var isExecuting = true;
    var commandCompleted = false;

    // Clear previous output
    $('#commandOutput').empty().text('Connecting...');
    
    // Show the modal
    $('#commandOutputModal').modal('show');

    // Create URL for EventSource
    var url = new URL('{{ route('admin.server.command.exec', '') }}/' + serverId);
    url.searchParams.append('command', decodeURIComponent(command));
    url.searchParams.append('_', Date.now());
    
    // Close any existing EventSource instance before creating a new one
    if (window.currentSource) {
        window.currentSource.close();
    }

    // Create new EventSource connection
    var source = new EventSource(url.toString());
    window.currentSource = source; // Store reference to close it later

    source.onopen = function() {
        $('#commandOutput').empty();
        console.log('Connection established');
    };

    source.onmessage = function(event) {
        try {
            var data = JSON.parse(event.data);
            if (data.output) {
                // Format the output
                var formattedOutput = data.output
                    .split('\n')
                    .map(line => line.trim())
                    .filter(line => line.length > 0)
                    .join('\n');
                
                $('#commandOutput').append(ansiToHtml(formattedOutput) + '\n');
            }
            if (data.status === 'completed') {
                commandCompleted = true;
                isExecuting = false;
                $('#commandOutput').append('<span style="color: green">\nCommand completed successfully!</span>\n');
                source.close();
                $('.close-modal').prop('disabled', false);
            }
        } catch (e) {
            var rawData = event.data;
            if (rawData.startsWith('data: ')) {
                rawData = rawData.substring(6);
            }
            // Format raw output
            var formattedRawOutput = rawData
                .split('\n')
                .map(line => line.trim())
                .filter(line => line.length > 0)
                .join('\n');
            
            $('#commandOutput').append(ansiToHtml(formattedRawOutput) + '\n');
        }
        
        // Auto-scroll
        var pre = $('#commandOutput')[0];
        pre.scrollTop = pre.scrollHeight;
    };

    source.onerror = function(event) {
        if (isExecuting && !commandCompleted) {
            console.error("EventSource error:", event);
            $('#commandOutput').append('<span style="color: red">\nConnection error. Please check the server logs.</span>\n');
            isExecuting = false;
            $('.close-modal').prop('disabled', false);
            source.close();
        }
    };

    // Modal settings
    $('#commandOutputModal').modal({ backdrop: 'static', keyboard: false });
    
    // Cleanup when modal is closed
    $('#commandOutputModal').on('hidden.bs.modal', function () {
        if (source) source.close();
        isExecuting = false;
        window.currentSource = null;
    });
});

function ansiToHtml(text) {
    if (!text) return '';
    return text
        .replace(/\033\[0;31m/g, '<span style="color: red">')
        .replace(/\033\[0;32m/g, '<span style="color: green">')
        .replace(/\033\[0;33m/g, '<span style="color: yellow">')
        .replace(/\033\[0m/g, '</span>')
        .replace(/\n/g, '<br>');
}
</script>
@endpush
        
</x-app-layout>