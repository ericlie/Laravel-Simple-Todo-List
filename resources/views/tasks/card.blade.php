<div class="{{ $class ?? 'col-md-3' }}">
    <div class="card">
        <div class="card-header">{{ __('Task') }} #{{ $task->id }} - {{ __('By') }} {{ $task->user->name }}</div>

        <div class="card-body" style="height: 200px">
            {{ str_limit($task->task, 160) }}
        </div>

        <div class="card-footer">
            @if(! request()->is('tasks/*'))
                <a href="{{ route('tasks.show', $task) }}" class="btn btn-primary btn-sm">
                    <i class="fa fa-eye"></i> {{ __('Show') }}
                </a>
            @else
                <a href="{{ route('tasks.index') }}" class="btn btn-primary btn-sm">
                    <i class="fa fa-reply"></i> {{ __('Back') }}
                </a>
            @endif
            @if(auth()->id() === $task->user->id)
            <a href="{{ route('tasks.edit', $task) }}" class="btn btn-warning btn-sm">
                <i class="fa fa-pencil"></i> {{ __('Edit') }}
            </a>
            <a href="javascript:;" class="btn btn-danger btn-sm" onclick="document.getElementById('task-{{$task->id}}').submit();">
                <i class="fa fa-trash"></i> {{ __('Delete') }}
            </a>
            <form id="task-{{$task->id}}" action="{{ route('tasks.destroy', $task) }}" method="POST" style="display: none;">
                @method('DELETE') @csrf
            </form>
            @endif
        </div>
    </div>
</div>