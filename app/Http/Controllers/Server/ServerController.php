<?php

namespace App\Http\Controllers\Server;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use phpseclib3\Net\SSH2;
use Illuminate\Support\Facades\Auth;
use App\Models\Server;
use App\Models\ServerCommands;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\Log;

class ServerController extends Controller
{
    public function index()
    {
        if (Auth::user()->role != 'super-admin') {
            $servers = Server::where('company_uid', Auth::user()->company_uid)->get();
        } else {
            $servers = Server::all();
        }
        return view('admin.server.index', compact('servers'));
    }

    public function create()
    {
        return view('admin.server.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'hostname' => 'required|string|max:255',
            'ip' => 'required|ip',
            'username' => 'required|string|max:255',
            'password' => 'required|string|min:6',
            'port' => 'required|numeric',
            'control_panel' => 'required|string',
            'license' => 'required|string',
            'status' => 'required|in:0,1',
            'company_uid' => 'required|string|max:255',
        ]);

        Server::create($request->all());

        return redirect()->route('admin.server.index')->with(['verify' => 'server-updated', 'status' => 'success', 'message' => 'Server Added successfully']);
    }

    public function edit($id)
    {
        $server = Server::findOrFail($id);
        return view('admin.server.edit', compact('server'));
    }

    public function update(Request $request, $id) {
        $request->validate([
            'name' => 'required|string|max:255',
            'hostname' => 'required|string|max:255',
            'ip' => 'required|ip',
            'username' => 'required|string|max:255',
            'password' => 'required|string|min:6',
            'port' => 'required|numeric',
            'control_panel' => 'required|string',
            'license' => 'required|string',
            'status' => 'required|in:0,1',
            'company_uid' => 'required|string|max:255',
        ]);

        $server = Server::findOrFail($id);
        $server->update($request->all());

        return redirect()->route('admin.server.index')->with(['verify' => 'server-updated', 'status' => 'success', 'message' => 'Server Updated successfully']);
    }

    public function destroy($id)
    {
        $server = Server::findOrFail($id);
        $server->delete();

        return redirect()->route('admin.server.index')->with(['verify' => 'server-updated', 'status' => 'success', 'message' => 'Server Deleted successfully']);
    }

    public function console($id)
    {
        $server = Server::findOrFail($id);
        $commands = ServerCommands::where('licensing_system', $server->license)->get();
        return view('admin.server.console', compact('server', 'commands'));
    }

//     public function exec(Request $request, $serverId)
// {
//     $host = '161.248.201.134';  // Replace with actual host
//     $username = 'root';          // Replace with actual SSH username
//     $password = 'Ba123456';      // Replace with actual SSH password

//     // Create an SSH connection
//     $ssh = new SSH2($host);

//     // Authenticate via SSH
//     if (!$ssh->login($username, $password)) {
//         return response()->json(['error' => 'SSH login failed'], 500);
//     }

//     // Start a streamed response
//     $response = new StreamedResponse(function() use ($ssh) {
//         $command = 'apt update';  // Replace with your desired command

//         // Set proper EventSource headers
//         header('Content-Type: text/event-stream');
//         header('Cache-Control: no-cache');
//         header('Connection: keep-alive');
//         ob_end_flush();  // Ensure output buffering is flushed
//         ob_implicit_flush(true); // Turn off buffering to send data immediately

//         // Run the command and capture the output progressively
//         $ssh->exec($command, function($data) {
//             // Format the data as EventSource expects:
//             echo "data: " . $data . "\n\n";  // This is the required format for EventSource
//             flush();  // Ensure data is sent immediately
//         });
//     });

//     // Return the response (this will allow the streaming)
//     return $response;
// }

public function exicute(Request $request, $serverId) 
{
    $server = Server::findOrFail($serverId);
    $command = $request->input('command');
    
    // Create command lock before SSH connection
    $commandKey = 'command_lock_' . md5($command . $request->ip() . $serverId);
    if (cache()->has($commandKey)) {
        return response()->json(['error' => 'Command is already running'], 429);
    }

    // Set command lock with longer timeout
    cache()->put($commandKey, true, now()->addMinutes(5));

    try {
        $ssh = new SSH2($server->ip, $server->port);
        $ssh->setTimeout(30);
        
        if (!$ssh->login($server->username, $server->password)) {
            cache()->forget($commandKey);
            return response()->json(['error' => 'SSH login failed'], 500);
        }

        return new StreamedResponse(function() use ($ssh, $request, $commandKey) {
            try {
                header('Content-Type: text/event-stream');
                header('Cache-Control: no-cache');
                header('Connection: keep-alive');
                header('X-Accel-Buffering: no');
                
                echo "data: Connected\n\n";
                flush();
                
                $command = $request->input('command');
                if (ob_get_level()) ob_end_clean();
                
                $ssh->exec($command, function($data) {
                    $cleanData = trim($data);
                    if (!empty($cleanData) && strpos($cleanData, '<') === false && strpos($cleanData, '>') === false) {
                        echo "data: $cleanData\n\n";
                        flush();
                    }
                });

                echo "data: Command completed\n\n";
                flush();
            } finally {
                cache()->forget($commandKey);
                $ssh->disconnect();
            }
        }, 200, [
            'Cache-Control' => 'no-cache',
            'X-Accel-Buffering' => 'no'
        ]);
    } catch (\Exception $e) {
        cache()->forget($commandKey);
        Log::error('SSH command failed: ' . $e->getMessage());
        return response()->json(['error' => 'SSH connection failed: ' . $e->getMessage()], 500);
    }
}

// public function exec(Request $request, $serverId) 
// {
//     $server = Server::findOrFail($serverId);

//     try {
//         $ssh = new SSH2($server->ip, $server->port);
//         $ssh->setTimeout(30); // Set timeout to 30 seconds
        
//         if (!$ssh->login($server->username, $server->password)) {
//             Log::error('SSH login failed for server: ' . $server->ip);
//             return response()->json(['error' => 'SSH login failed'], 500);
//         }

//         return new StreamedResponse(function() use ($ssh, $request) {
//             header('Content-Type: text/event-stream');
//             header('Cache-Control: no-cache');
//             header('Connection: keep-alive');
//             header('X-Accel-Buffering: no'); // Disable nginx buffering

//             $command = $request->input('command');
//             if (empty($command)) {
//                 echo "data: No command provided\n\n";
//                 flush();
//                 return;
//             }

//             if (ob_get_level()) ob_end_clean();

//             // Send initial connection message
//             echo "data: Connection established\n\n";
//             flush();

//             // Keep-alive ping
//             while (true) {
//                 // Execute SSH command and stream output live
//                 $ssh->exec($command, function($data) {
//                     $lines = explode("\n", trim($data));
//                     foreach ($lines as $line) {
//                         $cleanData = trim($line);
//                         if (!empty($cleanData)) {
//                             echo "data: $cleanData\n\n";
//                             flush();
//                         }
//                     }
//                 });

//                 // Send keep-alive ping every 30 seconds
//                 echo "data: ping\n\n";
//                 flush();
//                 sleep(30);
//             }
//         }, 200, [
//             'Cache-Control' => 'no-cache',
//             'X-Accel-Buffering' => 'no',
//         ]);
//     } catch (\Exception $e) {
//         Log::error('SSH command failed: ' . $e->getMessage());
//         return response()->json(['error' => 'SSH connection failed'], 500);
//     }
// }

 
}
