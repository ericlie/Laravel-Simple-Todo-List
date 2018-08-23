@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    {{ __('Edit Task') }} # {{ $task->id }} - {{ auth()->user()->id . ' - '. $task->user->id}}
                    <span class="pull-right">
                        <a href="{{ route('tasks.index') }}" class="btn btn-primary">
                            {{ __('Back') }}
                        </a>
                    </span>
                </div>
                {{-- <div class="card-body">
                    <form action="{{ route('tasks.update', $task) }}" method="POST">
                        @method('PUT')
                        @csrf
                        <div class="form-group">
                            <label>{{ __('Task') }}</label>
                            <input type="text" name="task" class="form-control" value="{{ $task->task }}">
                        </div>
                        <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> {{ __('Save') }}</button>
                    </form>
                </div> --}}
                <div class="card-body">
                    {!! Form::model($task, [
                        'url' => route('tasks.update', $task),
                        'method' => 'PATCH',
                    ]) !!}
                        {!! Form::cInput('task') !!}
                        <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> {{ __('Save') }}</button>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
