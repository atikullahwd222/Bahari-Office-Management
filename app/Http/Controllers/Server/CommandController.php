<?php

namespace App\Http\Controllers\Server;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ServerCommands;

class CommandController extends Controller
{
    public function index()
    {
        $commands = ServerCommands::all();
        return view('admin.server.command.commands', compact('commands'));
    }
    
    public function create()
    {
        return view('admin.server.command.create-command');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'description' => 'nullable',
            'full_command' => 'required',
            'licensing_system' => 'required',
            'os' => 'required',
        ]);

        ServerCommands::create($request->all());

        return redirect()->route('admin.server.commands')->with(['verify' => 'command-updated', 'status' => 'success', 'message' => 'Command created successfully']);
    }

    public function edit($id)
    {
        $command = ServerCommands::find($id);

        if (!$command) {
            return redirect()->route('admin.server.commands')->with('error', 'Command not found.');
        }

        return view('admin.server.command.edit-command', compact('command'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required',
            'description' => 'nullable',
            'full_command' => 'required',
            'licensing_system' => 'required',
            'os' => 'required',
        ]);

        $command = ServerCommands::find($id);

        if (!$command) {
            return redirect()->route('admin.server.commands')->with('error', 'Command not found.');
        }

        $command->update($request->all());

        return redirect()->route('admin.server.commands')->with(['verify' => 'command-updated', 'status' => 'success', 'message' => 'Command updated successfully']);
    }

    public function destroy($id)
    {
        $command = ServerCommands::find($id);

        if (!$command) {
            return redirect()->route('admin.server.commands')->with('error', 'Command not found.');
        }

        $command->delete();

        return redirect()->route('admin.server.commands')->with(['verify' => 'command-updated', 'status' => 'success', 'message' => 'Command deleted successfully']);
    }

}
