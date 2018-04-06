@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        @forelse($tasks as $task)
        <div class="col-md-3 pb-3">
            <div class="card">
                <div class="card-header">{{ __('Task') }} #{{ $task->id }} - {{ __('By') }} {{ $task->user->name }}</div>

                <div class="card-body" style="height: 200px">
                    {{ str_limit($task->task, 160) }}
                </div>
                
                <div class="card-footer">
                    <a href="{{ route('tasks.show', $task) }}" class="btn btn-primary btn-sm">
                        <i class="fa fa-eye"></i> {{ __('Show') }}
                    </a>
                    @if(auth()->id() === $task->user->id)
                    <a href="{{ route('tasks.edit', $task) }}" class="btn btn-warning btn-sm">
                        <i class="fa fa-pencil"></i> {{ __('Edit') }}
                    </a>
                    <a href="javascript:;" class="btn btn-danger btn-sm" onclick="document.getElementById('task-{{$task->id}}').submit();">
                        <i class="fa fa-trash"></i> {{ __('Delete') }}
                    </a>
                    <form id="task-{{$task->id}}" action="{{ route('tasks.destroy', $task) }}" method="POST" style="display: none;">
                        @method('DELETE')
                        @csrf
                    </form>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="col-md-3">
            <div class="card">
                <div class="card-header">{{ __('No Task') }}</div>
                <div class="card-body">
                    <a href="{{ route('tasks.create') }}" class="btn btn-primary">{{ __('Create A New Task') }}</a>
                </div>
            </div>
        </div>
        @endforelse
        {{--  @forelse($tasks as $task)
        Task id: {{ $task->id }} <br>
        Task: {{ $task->task }} <br>

        @empty
        No Task
        @endforelse  --}}
    </div>
</div>
@endsection
