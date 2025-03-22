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

        // Check server reachability
        foreach ($servers as $server) {
            $server->serverStatus = $this->pingServer($server->ip);
        }

        return view('admin.server.index', compact('servers'));
    }

    /**
     * Function to ping a server and check if it is reachable.
     */
    private function pingServer($ip)
    {
        // Detect OS
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            // Windows command (-n 1 means only send 1 ping)
            $pingResult = shell_exec("ping -n 1 $ip");
            return (strpos($pingResult, 'TTL=') !== false) ? 'Online' : 'Offline';
        } else {
            // Linux/macOS command (-c 1 sends 1 packet)
            $pingResult = shell_exec("ping -c 1 -W 1 $ip");
            return (strpos($pingResult, '1 received') !== false) ? 'Online' : 'Offline';
        }
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

public function exec(Request $request, $serverId) 
{
    // Get server details from database
    $server = Server::findOrFail($serverId);

    try {
        // Create SSH connection with server credentials
        $ssh = new SSH2($server->ip);

        // Authenticate via SSH
        if (!$ssh->login($server->username, $server->password)) {
            return response()->json(['error' => 'SSH login failed'], 500);
        }

        // Start streamed response
        $response = new StreamedResponse(function() use ($ssh, $request) {
            $command = $request->input('command', 'apt update');

            // Set headers for EventSource
            header('Content-Type: text/event-stream');
            header('Cache-Control: no-cache');
            header('Connection: keep-alive');
            
            // Clean output buffer
            if (ob_get_level()) ob_end_clean();

            // Execute SSH command and stream output
            $ssh->exec($command, function($data) {
                if (!empty($data)) {
                    $cleanData = trim($data);
                    
                    // Skip HTML content
                    if (strpos($cleanData, '<') !== false || strpos($cleanData, '>') !== false) {
                        return;
                    }

                    echo "data: " . $cleanData . "\n\n";
                    flush();
                }
            });
        });

        return $response;

    } catch (\Exception $e) {
        Log::error('SSH command failed: ' . $e->getMessage());
        return response()->json(['error' => 'SSH connection failed'], 500);
    }
}
 
}
