<?php

namespace App\Http\Controllers;

use App\Models\SubTask;
use App\Models\Task;
use App\Enums\SubTaskStatus;
use Illuminate\Http\Request;

class SubTaskController extends Controller
{
    /**
     * Store a newly created sub-task.
     */
    public function store(Request $request, Task $task)
    {
        $this->authorize('manageSubTasks', $task);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
        ]);

        // Obtener el orden máximo actual
        $maxOrder = $task->subTasks()->max('order') ?? 0;

        $subTask = $task->subTasks()->create([
            'title' => $validated['title'],
            'order' => $maxOrder + 1,
            'status' => SubTaskStatus::PENDIENTE,
        ]);

        return back()->with('success', 'Sub-tarea creada exitosamente.');
    }

    /**
     * Update the specified sub-task.
     */
    public function update(Request $request, SubTask $subTask)
    {
        $this->authorize('manageSubTasks', $subTask->task);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
        ]);

        $subTask->update($validated);

        return back()->with('success', 'Sub-tarea actualizada exitosamente.');
    }

    /**
     * Toggle sub-task status.
     */
    public function toggleStatus(SubTask $subTask)
    {
        $this->authorize('manageSubTasks', $subTask->task);

        if ($subTask->status === SubTaskStatus::PENDIENTE) {
            $subTask->markAsCompleted();
        } else {
            $subTask->markAsPending();
        }

        return back();
    }

    /**
     * Remove the specified sub-task.
     */
    public function destroy(SubTask $subTask)
    {
        $this->authorize('manageSubTasks', $subTask->task);

        $subTask->delete();

        return back()->with('success', 'Sub-tarea eliminada exitosamente.');
    }

    /**
     * Update sub-tasks order.
     */
    public function updateOrder(Request $request, Task $task)
    {
        $this->authorize('manageSubTasks', $task);

        $validated = $request->validate([
            'sub_tasks' => 'required|array',
            'sub_tasks.*.id' => 'required|exists:sub_tasks,id',
            'sub_tasks.*.order' => 'required|integer|min:0',
        ]);

        foreach ($validated['sub_tasks'] as $subTaskData) {
            SubTask::where('id', $subTaskData['id'])
                ->where('task_id', $task->id)
                ->update(['order' => $subTaskData['order']]);
        }

        return back()->with('success', 'Orden actualizado exitosamente.');
    }
}
