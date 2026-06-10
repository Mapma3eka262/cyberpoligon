<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Services\ProxmoxService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProxmoxController extends Controller
{
    protected $proxmoxService;

    public function __construct(ProxmoxService $proxmoxService)
    {
        $this->proxmoxService = $proxmoxService;
    }

    public function getVmConsole(Request $request, $taskId)
    {
        $user = Auth::user();
        $team = $user->team;
        
        if (!$team) {
            return response()->json([
                'success' => false,
                'message' => 'Вы не состоите в команде.',
            ], 403);
        }

        $task = Task::findOrFail($taskId);
        
        $teamTask = $team->tasks()->where('task_id', $task->id)->first();
        
        if (!$teamTask) {
            return response()->json([
                'success' => false,
                'message' => 'Это задание не назначено вашей команде.',
            ], 403);
        }

        if (!$task->vm_id) {
            return response()->json([
                'success' => false,
                'message' => 'Для этого задания не настроена виртуальная машина.',
            ], 400);
        }

        $node = $task->proxmox_node ?? config('proxmox.node');
        $result = $this->proxmoxService->getVncProxy($node, $task->vm_id);

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['error'],
            ], 500);
        }

        $host = parse_url(config('proxmox.host'), PHP_URL_HOST);
        $port = parse_url(config('proxmox.host'), PHP_URL_PORT) ?: 8006;
        
        $novnc_url = "https://{$host}:{$port}/?console=kvm&novnc=1&node={$node}&resize=1&vmid={$task->vm_id}&vncticket={$result['ticket']}";

        return response()->json([
            'success' => true,
            'novnc_url' => $novnc_url,
            'ticket' => $result['ticket'],
        ]);
    }
}
