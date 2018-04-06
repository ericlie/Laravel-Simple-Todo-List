@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12 pb-3">
            <div class="card">
                <div class="card-header">{{ __('Task') }} #{{ $task->id }} - {{ __('By') }} {{ $task->user->name }}</div>

                <div class="card-body" style="height: 200px">
                    {{ $task->task }}
                </div>
                <div class="card-footer">
                    <a href="{{ route('tasks.index') }}" class="btn btn-primary">
                        <i class="fa fa-reply"></i> {{ __('Back') }}
                    </a>
                    @if(auth()->id() === $task->user->id)
                    <a href="{{ route('tasks.edit', $task) }}" class="btn btn-warning">
                        <i class="fa fa-pencil"></i> {{ __('Edit') }}
                    </a>
                    <a href="javascript:;" class="btn btn-danger" onclick="document.getElementById('task-{{$task->id}}').submit();">
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
    </div>
</div>
@endsection
